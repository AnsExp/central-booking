<?php
namespace CentralBooking\Profile\Tables;

use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\Data\Services\TicketService;
use CentralBooking\GUI\ComponentInterface;

class TableCouponOperator implements ComponentInterface
{
    private string $danger_hex = '#F8D7DA';
    private string $warning_hex = '#FFF3CD';
    private string $success_hex = '#D1E7DD';
    private string $base_hex = '#F8F9FA';
    private int $total_pages;
    private int $current_page;

    private function fetch_tickets()
    {
        $coupon = $_GET['coupon'] ?? null;
        $date_end = $_GET['date_end'] ?? null;
        $date_start = $_GET['date_start'] ?? null;
        $this->total_pages = 0;
        $this->current_page = 0;

        $tickets = [];

        if ($coupon && $date_start && $date_end) {

            $service = new TicketService();

            $result = $service->find(
                [
                    'id_coupon' => $coupon,
                    'date_creation_from' => $date_start,
                    'date_creation_to' => $date_end,
                ],
                'date_creation',
                'DESC',
                -1
            );

            $tickets = $result->getItems();
            $this->total_pages = $result->getTotalPages();
            $this->current_page = $result->getCurrentPage();
        }
        return $tickets;
    }
    public function compact()
    {
        $tickets = $this->fetch_tickets();

        ob_start();
        ?>
        <div class="d-flex align-items-center mt-3">
            <div class="p-2 d-flex align-items-center">
                <div class="me-2"
                    style="width: 15px; height: 15px; border: solid 1px gray; background-color: <?= $this->base_hex ?>"></div>
                <span><?= TicketStatus::PENDING->label() ?></span>
            </div>
            <div class="p-2 d-flex align-items-center">
                <div class="me-2"
                    style="width: 15px; height: 15px; border: solid 1px gray; background-color: <?= $this->success_hex ?>">
                </div>
                <span><?= TicketStatus::PAYMENT->label() ?></span>
            </div>
            <div class="p-2 d-flex align-items-center">
                <div class="me-2"
                    style="width: 15px; height: 15px; border: solid 1px gray; background-color: <?= $this->warning_hex ?>">
                </div>
                <span><?= TicketStatus::PARTIAL->label() ?></span>
            </div>
            <div class="p-2 d-flex align-items-center">
                <div class="me-2"
                    style="width: 15px; height: 15px; border: solid 1px gray; background-color: <?= $this->danger_hex ?>"></div>
                <span><?= TicketStatus::CANCEL->label() ?></span>
            </div>
        </div>
        <table class="table table-striped table-bordered table-hover">
            <tr>
                <th></th>
                <th>Nro. Ticket</th>
                <th>Pedido</th>
                <th>Fecha de compra</th>
                <th>Estado</th>
                <th>Total</th>
            </tr>
            <?php foreach ($tickets as $ticket):
                $colors = [
                    TicketStatus::PENDING->name => 'table-light',
                    TicketStatus::PAYMENT->name => 'table-success',
                    TicketStatus::PARTIAL->name => 'table-warning',
                    TicketStatus::CANCEL->name => 'table-danger',
                ];
                $back = $colors[$ticket->status->name] ?? 'table-light';
                ?>
                <tr class="<?= $back ?>">
                    <td>
                        <a class="btn btn-primary w-100" href="<?= add_query_arg(
                            [
                                'action' => 'edit',
                                'ticket_id' => $ticket->id
                            ],
                            remove_query_arg(['coupon', 'date_start', 'date_end'])
                        ) ?>" class="button-primary">Editar Cupon</a>
                    </td>
                    <td><?= $ticket->id ?></td>
                    <td>#<?= $ticket->getOrder()->get_id() ?></td>
                    <td><?= git_datetime_format($ticket->getOrder()->get_date_created()) ?></td>
                    <td><?= $ticket->status->label() ?></td>
                    <td><?= git_currency_format($ticket->total_amount, true) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <nav aria-label="Page navigation coupon">
            <ul class="pagination">
                <?php if ($this->total_pages > 0): ?>
                    <?php for ($i = 1; $i <= $this->total_pages; $i++): ?>
                        <li class="page-item <?= $i === $this->current_page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= add_query_arg('page_number', $i) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                <?php endif; ?>
            </ul>
        </nav>
        <?php
        return ob_get_clean();
    }
}
