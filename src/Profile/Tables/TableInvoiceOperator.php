<?php
namespace CentralTickets\Profile\Tables;

use CentralTickets\Components\Constants\ButtonStyleConstants;
use CentralTickets\Components\ModalComponent;
use CentralTickets\Operator;
use CentralTickets\Constants\TicketConstants;
use CentralTickets\Components\Component;
use CentralTickets\Services\Actions\InvoiceInfoPagination;
use DateTime;
use WP_Post;

class TableInvoiceOperator implements Component
{
    private Operator $operator;
    private ModalComponent $download_modal;
    private InvoiceInfoPagination $invoice_pagination;

    public function __construct()
    {
        $this->download_modal = new ModalComponent('Descargar información de facturación');
        $this->init_modal_download();
    }

    private function get_operator()
    {
        $operator_id = $_GET['operator'] ?? 0;
        if (empty($operator_id) || !is_numeric($operator_id)) {
            return new Operator();
        }

        $operator = git_get_operator_by_id((int) $operator_id);
        if (!$operator) {
            return new Operator();
        }

        return $operator;
    }

    public function compact()
    {
        ob_start();
        $this->download_modal->display();
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
                        <td>
                            <time datetime="<?= $ticket->get_order()->get_date_created()->format('Y-m-d H:i:s'); ?>">
                                <?= git_datetime_format($ticket->get_order()->get_date_created()->format('Y-m-d H:i:s')); ?>
                            </time>
                        </td>
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

    private function get_limits_from_month()
    {
        return [
            'start' => $_GET['date_start'] ?? date('Y-m-01'),
            'end' => $_GET['date_end'] ?? date('Y-m-t'),
        ];

        // $year = $_GET['invoice_year'] ?? '';
        // $month = $_GET['invoice_month'] ?? '';

        // if (empty($year) || !is_numeric($year)) {
        //     $year = date('Y');
        // }

        // if (empty($month) || !is_numeric($month) || $month < 1 || $month > 12) {
        //     $month = date('m');
        // }

        // $date = DateTime::createFromFormat('Y-m', $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT));

        // if ($date === false) {
        //     $current_date = new DateTime();
        //     return [
        //         'start' => $current_date->format('Y-m-01'),
        //         'end' => $current_date->format('Y-m-t')
        //     ];
        // }

        // $first_day = $date->format('Y-m-01');
        // $last_day = $date->format('Y-m-t');

        // return [
        //     'start' => $first_day,
        //     'end' => $last_day,
        // ];
    }

    public function fetch_tickets()
    {
        $limits = $this->get_limits_from_month();

        $coupon = null;

        if (isset($_GET['coupon']) && is_numeric($_GET['coupon']) && $_GET['coupon'] > 0) {
            $coupon = get_post($_GET['coupon']);
            if (!$coupon || $coupon->post_type !== 'shop_coupon') {
                $coupon = null;
            }
        }

        $this->invoice_pagination = new InvoiceInfoPagination(
            $this->get_operator(),
            $limits['start'],
            $limits['end'],
            $coupon
        );

        $this->invoice_pagination->current_page = isset($_GET['page_number']) && is_numeric($_GET['page_number']) && $_GET['page_number'] > 0 ? (int) $_GET['page_number'] : 1;

        return $this->invoice_pagination->fetch_tickets();
    }

    private function init_modal_download()
    {
        ob_start();
        ?>
        <form method="POST" action="<?= admin_url('admin-post.php') ?>">
            <h3>Seleccionar columnas para descargar</h3>
            <?= wp_nonce_field('download_invoice', 'nonce', true, false) ?>
            <input type="hidden" name="action" value="download_invoice_csv">
            <input type="hidden" name="operator" value="<?= esc_attr($_GET['operator'] ?? 0) ?>">
            <input type="hidden" name="date_start" value="<?= esc_attr($_GET['date_start'] ?? '') ?>">
            <input type="hidden" name="date_end" value="<?= esc_attr($_GET['date_end'] ?? '') ?>">
            <input type="hidden" name="coupon" value="<?= esc_attr($_GET['coupon'] ?? 0) ?>">
            <input type="checkbox" name="columns[]" value="ticket_num" id="column_ticket_num" checked>
            <label for="column_ticket_num">Número de Ticket</label>
            <br>
            <input type="checkbox" name="columns[]" value="name" id="column_name" checked>
            <label for="column_name">Nombre del cliente</label>
            <br>
            <input type="checkbox" name="columns[]" value="order_num" id="column_order_num" checked>
            <label for="column_order_num">Número de Pedido</label>
            <br>
            <input type="checkbox" name="columns[]" value="purchase_date" id="column_purchase_date" checked>
            <label for="column_purchase_date">Fecha de Compra</label>
            <br>
            <input type="checkbox" name="columns[]" value="coupon_code" id="column_coupon_code" checked>
            <label for="column_coupon_code">Código de Cupon</label>
            <br>
            <input type="checkbox" name="columns[]" value="ticket_status" id="column_ticket_status" checked>
            <label for="column_ticket_status">Estado del ticket</label>
            <br>
            <input type="checkbox" name="columns[]" value="passengers" id="column_passengers" checked>
            <label for="column_passengers">Pasajeros</label>
            <br>
            <input type="checkbox" name="columns[]" value="total_amount" id="column_total_amount" checked>
            <label for="column_total_amount">Total</label>
            <br>
            <button class="btn btn-warning mt-3" type="submit">Descargar</button>
        </form>
        <?php
        $string = ob_get_clean();
        $this->download_modal->set_body_component(git_string_to_component($string));
    }

    private function render_pagination(): void
    {
        ?>
        <div class="row">
            <div class="col">
                <?php
                $button = $this->download_modal->create_button_launch(git_string_to_component('Descargar en formato CSV <i class="bi bi-download"></i>'));
                $button->set_style(ButtonStyleConstants::WARNING);
                $button->display();
                ?>
            </div>
            <div class="col">
                <div class="pagination-controls">
                    <nav aria-label="Navegación de páginas de facturas">
                        <ul class="pagination justify-content-end">
                            <?php
                            for ($i = 1; $i <= $this->invoice_pagination->total_pages; $i++): ?>
                                <li class="page-item <?= $i === $this->invoice_pagination->current_page ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $this->get_pagination_url($i) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            </div>
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