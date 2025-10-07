<?php
namespace CentralTickets\Admin\View;

use CentralTickets\Ticket;
use CentralTickets\Services\TicketService;
use CentralTickets\Components\Displayer;
use CentralTickets\Components\InputComponent;
use CentralTickets\Components\AccordionComponent;
use CentralTickets\Components\PaginationComponent;
use CentralTickets\Components\SelectComponent;
use CentralTickets\Components\TextComponent;
use CentralTickets\Components\Implementation\CouponSelect;
use CentralTickets\Components\Implementation\TicketStatusSelect;

final class TableTickets implements Displayer
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
        $service = new TicketService();
        $filter = [];
        foreach ($_GET as $key => $value) {
            if (trim($value) !== '') {
                $filter[$key] = $value;
            }
        }
        $result = $service->paginated(
            $filter,
            $_GET['order_by'] ?? 'date_creation',
            $_GET['order'] ?? 'DESC',
            $page_number,
            $this->per_page,
        );
        $this->total_items = $result['pagination']['total_elements'] ?? 0;
        $this->total_pages = $result['pagination']['total_pages'] ?? 0;
        $this->current_page = $result['pagination']['current_page'] ?? 1;
        return $result['data'] ?? [];
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
        $flexible_select->add_option('Seleccione...', '');
        $flexible_select->add_option('Sí', 'true');
        $flexible_select->add_option('No', 'false');
        $flexible_select->set_value($_GET['flexible'] ?? '');
        $date_creation_input->set_value($_GET['date_creation'] ?? '');
        $ticket_status_select->set_value($_GET['status'] ?? '');
        $coupon_select->set_value($_GET['id_coupon'] ?? '');
        ob_start();
        ?>
        <form method="GET">
            <input type="hidden" name="page" value="<?= esc_attr($_GET['page'] ?? 'git_tickets') ?>">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php $ticket_status_select->get_label('Estado')->display(); ?></th>
                    <td><?php $ticket_status_select->display(); ?></td>
                    <th scope="row"><?php $date_creation_input->get_label('Fecha de Compra')->display(); ?></th>
                    <td><?php $date_creation_input->display(); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php $coupon_select->get_label('Cupon')->display(); ?></th>
                    <td><?php $coupon_select->display(); ?></td>
                    <th scope="row"><?php $flexible_select->get_label('Flexible')->display(); ?></th>
                    <td><?php $flexible_select->display(); ?></td>
                </tr>
            </table>
            <button class="button button-primary" type="submit">Aplicar</button>
            <button class="button" type="reset">Limpiar</button>
        </form>
        <?php
        $result = ob_get_clean();
        return git_string_to_component($result);
    }

    public function display()
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
        $accordion->display();
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
                                    <?= git_datetime_format($ticket->get_order()->get_date_created()->format('Y-m-d H:i:s')) ?>
                                </span>
                                <div class="row-actions visible">
                                    <span>ID: <?= esc_html($ticket->id) ?></span>
                                    <span> | </span>
                                    <span>
                                        <a target="_blank"
                                            href="<?= admin_url("post.php?post={$ticket->get_order()->get_id()}&action=edit") ?>">Pedido:
                                            <?= esc_html($ticket->get_order()->get_id()) ?></a>
                                    </span>
                                    <span> | </span>
                                    <span>
                                        <a target="_blank"
                                            href="<?= admin_url("admin.php?page=central_passengers&id_ticket={$ticket->id}") ?>">
                                            Pasajeros (<?= count($ticket->get_passengers()) ?>)
                                        </a>
                                    </span>
                                    <span> | </span>
                                    <span>
                                        <a class="git-row-action-link" target="_blank"
                                            href="<?= admin_url("admin.php?page=central_activity&tab=tickets&id={$ticket->id}") ?>">
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
                            <td><?= git_get_text_by_status(esc_html($ticket->status)) ?></td>
                            <td><?= $ticket->flexible ? 'Sí' : 'No' ?></td>
                            <td><?= $ticket->get_coupon() === null ? '—' : $ticket->get_coupon()->post_name ?></td>
                            <td><?= $ticket->get_order()->get_billing_phone() ?></td>
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
                                            $input_check->set_attribute('checked', '');
                                        }
                                        ?>
                                        <input type="hidden" name="nonce" value="<?= wp_create_nonce('git_ticket_nonce') ?>">
                                        <input type="hidden" name="ticket_id" value="<?= esc_attr($ticket->id) ?>">
                                        <table class="form-table" role="presentation" style="max-width: 300px;">
                                            <tr>
                                                <td>
                                                    <?php $input_check->get_label('Flexible')->display() ?>
                                                </td>
                                                <td>
                                                    <?php $input_check->display() ?>
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
            <?php $pagination->display() ?>
        </div>
        <?php
    }
}

add_action('wp_ajax_toggle_flexible', function () {
    $ticket_id = intval($_POST['ticket_id']);
    (new TicketService())->toggle_flexible($ticket_id, isset($_POST['flexible']));
    wp_die();
});