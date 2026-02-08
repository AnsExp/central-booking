<?php
namespace CentralBooking\Profile\Tables;

use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\GUI\ComponentBuilder;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\ModalComponentV2;
use CentralBooking\Utils\Actions\DownloadInvoiceInfo;
use CentralBooking\Utils\Actions\InvoiceInfoPagination;

final class TableInvoice implements DisplayerInterface
{
    private ModalComponentV2 $modal;
    private InvoiceInfoPagination $invoice_pagination;

    private function get_operator()
    {
        $operator_id = $_GET['operator'] ?? 0;
        if (!is_numeric($operator_id)) {
            return null;
        }
        return git_operator_by_id((int) $operator_id);
    }

    public function render()
    {
        $this->modal = new ModalComponentV2('Descargar información de facturación');
        $this->modal->setBodyComponent(ComponentBuilder::create($this->init_modal_download()));
        $this->modal->render();
        ?>
        <div class="git-table-wrapper" style="margin-top: 20px;">
            <table class="git-table">
                <thead>
                    <tr>
                        <th class="git-th-action">Nro. Ticket</th>
                        <th class="git-th-action">Fecha de Compra</th>
                        <th class="git-th-action">Pedido</th>
                        <th class="git-th-action">Cliente</th>
                        <th class="git-th-action">Precio</th>
                        <th class="git-th-action">Cupon</th>
                        <th class="git-th-action">Abono</th>
                        <th class="git-th-action">Estado</th>
                        <th class="git-th-action">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $tickets = $this->fetch_tickets();
                    foreach ($tickets as $ticket):
                        $proof_payment = $ticket->getProofPayment();
                        $abono = 0;
                        if ($ticket->getCoupon() !== null) {
                            if ($ticket->status === TicketStatus::PAYMENT) {
                                $abono = $ticket->total_amount;
                            } elseif ($ticket->status === TicketStatus::PARTIAL) {
                                $abono = $proof_payment?->amount ?? 0;
                            } elseif ($ticket->status === TicketStatus::CANCEL) {
                                $abono = 0;
                            } else {
                                $abono = $proof_payment?->amount ?? 0;
                            }
                        } else {
                            $abono = $ticket->total_amount;
                        }
                        $saldo = $ticket->total_amount - $abono;
                        ?>
                        <tr class="<?= $saldo !== 0 ? 'table-danger' : '' ?>">
                            <td><?= $ticket->id; ?></td>
                            <td>
                                <time datetime="<?= $ticket->getOrder()->get_date_created()->format('Y-m-d H:i:s'); ?>">
                                    <?= git_datetime_format($ticket->getOrder()->get_date_created()->format('Y-m-d H:i:s')); ?>
                                </time>
                            </td>
                            <td><?= $ticket->getOrder()->get_id(); ?></td>
                            <td><?= $ticket->getOrder()->get_billing_first_name(); ?></td>
                            <td><?= git_currency_format($ticket->total_amount, true); ?></td>
                            <td><?= $ticket->getCoupon() ? $ticket->getCoupon()->post_title : '—'; ?></td>
                            <td><?= git_currency_format($abono, true); ?></td>
                            <td><?= $ticket->status->label() ?></td>
                            <td><?= git_currency_format($saldo, true); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php
        if (!empty($tickets)) {
            $this->render_pagination();
        }
    }

    private function get_limits_from_month()
    {
        return [
            'start' => $_GET['date_start'] ?? date('Y-m-01'),
            'end' => $_GET['date_end'] ?? date('Y-m-t'),
        ];
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

        $operator = $this->get_operator();

        if ($operator === null) {
            return [];
        }

        $this->invoice_pagination = new InvoiceInfoPagination(
            $operator,
            $limits['start'],
            $limits['end'],
            $coupon
        );

        $this->invoice_pagination->current_page = isset($_GET['page_number']) && is_numeric($_GET['page_number']) && $_GET['page_number'] > 0 ? (int) $_GET['page_number'] : 1;

        return $this->invoice_pagination->fetch_tickets();
    }

    private function init_modal_download()
    {
        $action = add_query_arg(
            ['action' => 'download_invoice_csv'],
            admin_url('admin-ajax.php')
        );
        ob_start();
        ?>
        <form method="POST" action="<?= esc_url($action) ?>">
            <?= git_nonce_field() ?>
            <input type="hidden" name="operator" value="<?= esc_attr($_GET['operator'] ?? 0) ?>">
            <input type="hidden" name="date_start" value="<?= esc_attr($_GET['date_start'] ?? '') ?>">
            <input type="hidden" name="date_end" value="<?= esc_attr($_GET['date_end'] ?? '') ?>">
            <input type="hidden" name="coupon" value="<?= esc_attr($_GET['coupon'] ?? 0) ?>">
            <?php foreach (DownloadInvoiceInfo::COLUMNS as $column_key => $column_label): ?>
                <input type="checkbox" name="columns[]" value="<?= esc_attr($column_key) ?>"
                    id="column_<?= esc_attr($column_key) ?>" checked>
                <label for="column_<?= esc_attr($column_key) ?>"><?= esc_html($column_label) ?></label>
                <br>
            <?php endforeach; ?>
            <button class="git-btn git-btn-warning" type="submit">Descargar</button>
        </form>
        <?php
        return ob_get_clean();
    }

    private function render_pagination(): void
    {
        ?>
        <div class="row">
            <div class="col">
                <?php
                $this->modal->createButtonLaunch('Descargar en formato CSV')->render();
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