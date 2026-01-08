<?php
namespace CentralBooking\Admin\View;

use CentralBooking\Admin\AdminRouter;
use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\Data\Services\TicketService;
use CentralBooking\Data\Ticket;
use CentralBooking\GUI\AccordionComponent;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\GUI\PaginationComponent;
use CentralBooking\GUI\SelectComponent;
use CentralBooking\GUI\TextComponent;
use CentralBooking\Implementation\GUI\CouponSelect;
use CentralBooking\Implementation\GUI\TicketStatusSelect;

final class TableTickets implements DisplayerInterface
{
    /**
     * @var array<Ticket>
     */
    private array $tickets;
    private int $total_items;
    private int $per_page = 10;
    private int $total_pages;
    private int $current_page;

    public function __construct()
    {
        $this->tickets = $this->fetchTransports();
    }

    private function fetchTransports(): array
    {
        $page_number = isset($_GET['page_number']) ? (int) $_GET['page_number'] : 1;
        $filter = ['status_not' => TicketStatus::PERORDER->value];
        foreach ($_GET as $key => $value) {
            if (trim($value) !== '') {
                $filter[$key] = $value;
            }
        }
        $filter['order_by'] = $_GET['order_by'] ?? 'date_creation';
        $filter['order'] = $_GET['order'] ?? 'DESC';
        $filter['limit'] = $this->per_page;
        $filter['offset'] = ($page_number - 1) * $this->per_page;
        $result = git_tickets_result_set($filter);
        $this->total_items = $result->getTotalItems();
        $this->total_pages = $result->getTotalPages();
        $this->current_page = $result->getCurrentPage();
        return $result->getItems();
    }

    private function get_current_order_by()
    {
        $order_by = $_GET['order_by'] ?? 'id';
        return in_array(
            $order_by,
            ['date_creation', 'code_coupon', 'flexible', 'status', 'price']
        ) ? $order_by : 'id';
    }

    private function get_current_order()
    {
        $order = $_GET['order'] ?? 'DESC';
        return $order === 'DESC' ? 'DESC' : 'ASC';
    }

    private function create_order_link(string $order_by, string $order)
    {
        return add_query_arg([
            'order_by' => $order_by,
            'order' => $order
        ]);
    }

    private function filter_form()
    {
        $ticket_status_select = (new TicketStatusSelect('status'))->create();
        $coupon_select = (new CouponSelect('id_coupon'))->create();
        $flexible_select = new SelectComponent('flexible');
        $date_creation_input = new InputComponent('date_creation', 'date');
        $flexible_select->addOption('Seleccione...', '');
        $flexible_select->addOption('Sí', 'true');
        $flexible_select->addOption('No', 'false');
        $flexible_select->setValue($_GET['flexible'] ?? '');
        $date_creation_input->setValue($_GET['date_creation'] ?? '');
        $ticket_status_select->setValue($_GET['status'] ?? '');
        $coupon_select->setValue($_GET['id_coupon'] ?? '');
        ob_start();
        ?>
        <form method="GET">
            <input type="hidden" name="page" value="<?= esc_attr($_GET['page'] ?? 'git_tickets') ?>">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php $ticket_status_select->getLabel('Estado')->render(); ?></th>
                    <td><?php $ticket_status_select->render(); ?></td>
                    <th scope="row"><?php $date_creation_input->getLabel('Fecha de Compra')->render(); ?></th>
                    <td><?php $date_creation_input->render(); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php $coupon_select->getLabel('Cupon')->render(); ?></th>
                    <td><?php $coupon_select->render(); ?></td>
                    <th scope="row"><?php $flexible_select->getLabel('Flexible')->render(); ?></th>
                    <td><?php $flexible_select->render(); ?></td>
                </tr>
            </table>
            <button class="button button-primary" type="submit">Aplicar</button>
            <button class="button" type="reset">Limpiar</button>
        </form>
        <?php
        $result = ob_get_clean();
        return git_string_to_component($result);
    }

    public function render()
    {
        $pagination = new PaginationComponent();
        $pagination->set_data(
            $this->total_items,
            $this->current_page,
            $this->total_pages
        );
        $pagination->set_links(
            add_query_arg(['page_number' => 1]),
            add_query_arg(['page_number' => $this->total_pages]),
            add_query_arg(['page_number' => ($this->current_page + 1)]),
            add_query_arg(['page_number' => ($this->current_page - 1)]),
        );

        $accordion = new AccordionComponent();
        $filter_header = new TextComponent('span');
        $filter_header->append(git_string_to_component('<i class="bi bi-sliders"></i>'));
        $filter_header->append(' Filtro');
        $accordion->add_item($filter_header, $this->filter_form());
        $accordion->render();
        ?>
        <div style="overflow-x: auto; max-width: 1100px;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col" style="width: 400px;"
                            class="manage-column <?= $this->get_current_order_by() === 'date_creation' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('date_creation', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Fecha de Compra</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                        <th scope="col" style="width: 100px;"
                            class="manage-column <?= $this->get_current_order_by() === 'price' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('price', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Precio</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                        <th scope="col" style="width: 100px;"
                            class="manage-column <?= $this->get_current_order_by() === 'status' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('status', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Estado</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                        <th scope="col" style="width: 100px;"
                            class="manage-column <?= $this->get_current_order_by() === 'flexible' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('flexible', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Flexible</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                        <th scope="col" style="width: 100px;"
                            class="manage-column <?= $this->get_current_order_by() === 'code_coupon' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('code_coupon', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Cupón</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                        <th style="width: 100px;" scope="col">Teléfono</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->tickets as $ticket): ?>
                        <tr>
                            <td>
                                <span>
                                    <?= git_datetime_format($ticket->getOrder()->get_date_created()->format('Y-m-d H:i:s')) ?>
                                </span>
                                <div class="row-actions visible">
                                    <span>ID: <?= esc_html($ticket->id) ?></span>
                                    <span> | </span>
                                    <span>
                                        <a target="_blank"
                                            href="<?= admin_url("post.php?post={$ticket->getOrder()->get_id()}&action=edit") ?>">Pedido:
                                            <?= esc_html($ticket->getOrder()->get_id()) ?></a>
                                    </span>
                                    <span> | </span>
                                    <span>
                                        <a target="_blank"
                                            href="<?= AdminRouter::get_url_for_class(TablePassengers::class, ['id_ticket' => $ticket->id]) ?>">
                                            Pasajeros (<?= count($ticket->getPassengers()) ?>)
                                        </a>
                                    </span>
                                    <span> | </span>
                                    <span>
                                        <a class="git-row-action-link" target="_blank"
                                            href="<?= AdminRouter::get_url_for_class(TableTicketsLog::class, ['id' => $ticket->id]) ?>">
                                            Logs
                                        </a>
                                    </span>
                                    <span> | </span>
                                    <span>
                                        <a class="git-row-action-link" href="#edit-container-<?= $ticket->id ?>">
                                            Ticket Flexible
                                        </a>
                                    </span>
                                </div>
                            </td>
                            <td><?= git_currency_format($ticket->total_amount, true) ?></td>
                            <td><?= $ticket->status->label() ?></td>
                            <td><?= $ticket->flexible ? 'Sí' : 'No' ?></td>
                            <td><?= $ticket->getCoupon() === null ? '—' : $ticket->getCoupon()->post_title ?></td>
                            <td><?= $ticket->getOrder()->get_billing_phone() ?></td>
                        </tr>
                        <tr id="actions-container-<?= $ticket->id ?>" class="git-row-actions">
                            <td colspan="6">
                                <div id="edit-container-<?= $ticket->id ?>" class="git-item-container hidden"
                                    data-parent="#actions-container-<?= $ticket->id ?>">
                                    <form class="toggle-ticket-flexible"
                                        action="<?= admin_url('admin-ajax.php?action=git_toggle_flexible') ?>" method="post"
                                        style="padding: 20px;">
                                        <?php
                                        $input_check = new InputComponent('flexible', 'checkbox');
                                        if ($ticket->flexible) {
                                            $input_check->attributes->set('checked', '');
                                        }
                                        ?>
                                        <input type="hidden" name="nonce" value="<?= wp_create_nonce('git_ticket_nonce') ?>">
                                        <input type="hidden" name="ticket_id" value="<?= esc_attr($ticket->id) ?>">
                                        <table class="form-table" role="presentation" style="max-width: 300px;">
                                            <tr>
                                                <td>
                                                    <?php $input_check->getLabel('Flexible')->render() ?>
                                                </td>
                                                <td>
                                                    <?php $input_check->render() ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <button class="button button-primary" type="submit">Enviar</button>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php $pagination->render() ?>
        </div>
        <?php
    }
}

add_action('wp_ajax_toggle_flexible', function () {
    $ticket_id = intval($_POST['ticket_id']);
    $ticket_result = git_ticket_toggle_flexible($ticket_id, isset($_POST['flexible']));
    git_ticket_save($ticket_result);
    wp_die();
});