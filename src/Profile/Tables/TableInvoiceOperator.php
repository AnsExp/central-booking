<?php
namespace CentralTickets\Profile\Tables;

use CentralTickets\Operator;
use CentralTickets\Constants\TicketConstants;
use CentralTickets\Persistence\TicketRepository;
use CentralTickets\Persistence\TransportRepository;
use CentralTickets\Components\Component;
use DateTime;
use WP_Post;

class TableInvoiceOperator implements Component
{
    private Operator $operator;
    private int $current_page;
    private int $items_per_page;
    private int $total_items;
    private int $total_pages;

    private function get_operator()
    {
        $operator_id = $_GET['operator'] ?? 0;
        if (empty($operator_id) || !is_numeric($operator_id)) {
            return new Operator();
        }

        $operator = (new Operator())->find_by(['id' => $operator_id]);
        if (!$operator) {
            return new Operator();
        }

        return $operator;
    }

    public function compact()
    {
        ob_start();
        ?>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Nro. Ticket</th>
                    <th>Fecha de Compra</th>
                    <th>Pedido</th>
                    <th>Cliente</th>
                    <th>Precio</th>
                    <th>Cupon</th>
                    <th>Abono</th>
                    <th>Estado</th>
                    <th>Saldo</th>
                </tr>
            </thead>
            <tbody>
                <?php $tickets = $this->fetch_tickets();
                foreach ($tickets as $ticket):
                    $proof_payment = $ticket->get_meta('proof_payment');
                    $abono = 0;
                    if ($ticket->get_coupon() !== null) {
                        if ($ticket->status === TicketConstants::PAYMENT) {
                            $abono = $ticket->total_amount;
                        } elseif ($ticket->status === TicketConstants::PARTIAL) {
                            $abono = $proof_payment['amount'] ?? 0;
                        } elseif ($ticket->status === TicketConstants::CANCEL) {
                            $abono = 0;
                        } else {
                            $abono = $proof_payment['amount'] ?? 0;
                        }
                    } else {
                        $abono = $ticket->total_amount;
                    }
                    $saldo = $ticket->total_amount - $abono;
                    ?>
                    <tr class="<?= $saldo !== 0 ? 'table-danger' : '' ?>">
                        <td><?= $ticket->id; ?></td>
                        <td><?= git_datetime_format($ticket->get_order()->get_date_created()->format('Y-m-d')); ?></td>
                        <td><?= $ticket->get_order()->get_id(); ?></td>
                        <td><?= $ticket->get_order()->get_billing_first_name(); ?></td>
                        <td><?= git_currency_format($ticket->total_amount, true); ?></td>
                        <td><?= $ticket->get_coupon() ? $ticket->get_coupon()->post_title : '—'; ?></td>
                        <td><?= git_currency_format($abono, true); ?></td>
                        <td><?= git_get_text_by_status($ticket->status) ?></td>
                        <td><?= git_currency_format($saldo, true); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        if (!empty($tickets)) {
            $this->render_pagination();
        }
        return ob_get_clean();
    }

    private function pagination_invoice()
    {
        $this->operator = $this->get_operator();
        $this->current_page = isset($_GET['page_number']) && is_numeric($_GET['page_number']) ? intval($_GET['page_number']) : 1;
        $this->items_per_page = 10;
        $this->total_items = 0;
        $this->total_pages = 0;
    }

    private function fetch_transports()
    {
        $operator = git_get_operator_by_id($_GET['operator'] ?? 0);
        if ($operator) {
            return $operator->get_transports();
        }
        return [];
    }

    private function get_limits_from_month()
    {
        $year = $_GET['invoice_year'] ?? '';
        $month = $_GET['invoice_month'] ?? '';

        if (empty($year) || !is_numeric($year)) {
            $year = date('Y');
        }

        if (empty($month) || !is_numeric($month) || $month < 1 || $month > 12) {
            $month = date('m');
        }

        $date = DateTime::createFromFormat('Y-m', $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT));

        if ($date === false) {
            $current_date = new DateTime();
            return [
                'start' => $current_date->format('Y-m-01'),
                'end' => $current_date->format('Y-m-t')
            ];
        }

        $first_day = $date->format('Y-m-01');
        $last_day = $date->format('Y-m-t');

        return [
            'start' => $first_day,
            'end' => $last_day,
        ];
    }

    public function fetch_tickets()
    {
        $limits = $this->get_limits_from_month();

        $this->pagination_invoice();

        $transports = $this->fetch_transports();
        $transport_ids = array_map(fn($transport) => $transport->id, $transports);

        if (empty($transport_ids)) {
            $this->total_items = 0;
            $this->total_pages = 1;
            return [];
        }

        return $this->get_tickets_by_sql($transport_ids, $limits);
    }

    private function get_tickets_by_sql($transport_ids, $limits)
    {
        global $wpdb;

        $placeholders = implode(',', array_fill(0, count($transport_ids), '%d'));

        $count_sql = "
        SELECT COUNT(DISTINCT t.id) as total
        FROM {$wpdb->prefix}git_tickets t
        INNER JOIN {$wpdb->prefix}git_passengers p ON p.id_ticket = t.id
        INNER JOIN {$wpdb->prefix}posts po ON po.ID = t.id_order
        WHERE p.id_transport IN ({$placeholders})
        AND po.post_date BETWEEN %s AND %s
        ";

        $this->total_items = (int) $wpdb->get_var(
            $wpdb->prepare(
                $count_sql,
                ...array_merge($transport_ids, [$limits['start'], $limits['end']])
            )
        );

        $this->total_pages = ceil($this->total_items / $this->items_per_page);

        if ($this->total_items === 0) {
            return [];
        }

        $offset = ($this->current_page - 1) * $this->items_per_page;

        $sql = "
            SELECT DISTINCT t.id
            FROM {$wpdb->prefix}git_tickets t
            INNER JOIN {$wpdb->prefix}git_passengers p ON p.id_ticket = t.id
            INNER JOIN {$wpdb->prefix}posts po ON po.ID = t.id_order
            WHERE p.id_transport IN ({$placeholders})
            AND po.post_date >= %s AND po.post_date <= %s
            ORDER BY po.post_date DESC
            LIMIT %d OFFSET %d
            ";
        // AND po.post_date BETWEEN %s AND %s

        $ticket_ids = $wpdb->get_col(
            $wpdb->prepare(
                $sql,
                ...array_merge(
                    $transport_ids,
                    [$limits['start'], $limits['end'], $this->items_per_page, $offset]
                )
            )
        );

        $tickets = [];
        $operator = git_get_operator_by_id($_GET['operator'] ?? 0);
        $coupons = array_map(function (WP_Post $coupon) {
            return $coupon->ID;
        }, $operator->get_coupons());

        foreach ($ticket_ids as $ticket_id) {
            $ticket = git_get_ticket_by_id($ticket_id);
            if ($ticket) {
                if ($_GET['coupon'] ?? false) {
                    $coupon = $ticket->get_coupon();
                    if ($coupon === null) {
                        continue;
                    }
                    if (in_array($coupon->ID, $coupons)) {
                        $tickets[] = $ticket;
                    }
                } else {
                    $tickets[] = $ticket;
                }
            }
        }

        return $tickets;
    }

    private function render_pagination(): void
    {
        ?>
        <div class="pagination-controls mt-3">
            <nav aria-label="Navegación de páginas de facturas">
                <ul class="pagination">
                    <?php
                    for ($i = 1; $i <= $this->total_pages; $i++): ?>
                        <li class="page-item <?= $i === $this->current_page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $this->get_pagination_url($i) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
        <?php
    }

    private function get_pagination_url($page_number): string
    {
        $current_params = $_GET;
        $current_params['page_number'] = $page_number;

        $base_url = strtok($_SERVER['REQUEST_URI'], '?');
        return $base_url . '?' . http_build_query($current_params);
    }
}