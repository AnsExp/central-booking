<?php
namespace CentralTickets\Admin\View;

use CentralTickets\Passenger;
use CentralTickets\Services\PassengerService;
use CentralTickets\Components\Displayer;
use CentralTickets\Components\AccordionComponent;
use CentralTickets\Components\InputComponent;
use CentralTickets\Components\PaginationComponent;
use CentralTickets\Components\SelectComponent;
use CentralTickets\Components\TextComponent;
use CentralTickets\Components\Implementation\LocationSelect;
use CentralTickets\Components\Implementation\NationalitySelect;
use CentralTickets\Components\Implementation\TicketStatusSelect;
use CentralTickets\Components\Implementation\TransportSelect;
use CentralTickets\Components\Implementation\TypeDocumentSelect;

final class TablePassengers implements Displayer
{
    /**
     * @var array<Passenger>
     */
    private array $passengers;
    private int $total_items;
    private int $per_page = 10;
    private int $total_pages;
    private int $current_page;

    public function __construct()
    {
        $this->passengers = $this->fetchTransports();
    }

    private function fetchTransports(): array
    {
        $page_number = isset($_GET['page_number']) ? (int) $_GET['page_number'] : 1;
        $service = new PassengerService();
        $filter = [];
        foreach ($_GET as $key => $value) {
            if ($value !== '') {
                $filter[$key] = $value;
            }
        }
        $result = $service->paginated(
            $filter,
            $this->get_current_order_by(),
            $this->get_current_order(),
            $page_number,
            $this->per_page
        );
        $this->total_items = $result['pagination']['total_elements'] ?? 0;
        $this->total_pages = $result['pagination']['total_pages'] ?? 0;
        $this->current_page = $result['pagination']['current_page'] ?? 1;
        return $result['data'] ?? [];
    }

    private function filter_pad()
    {
        $accordion = new AccordionComponent();
        $accordion_title = new TextComponent('span');
        $accordion_title->append(git_string_to_component('<i class="bi bi-sliders"></i>'));
        $accordion_title->append(' Filtro');
        $accordion->add_item($accordion_title, git_string_to_component($this->filter_form()));
        return $accordion;
    }

    private function filter_form()
    {
        $name_filter = new InputComponent('name');
        $ticket_status_filter = (new TicketStatusSelect('status'))->create();
        $type_document_filter = (new TypeDocumentSelect('type_document'))->create();
        $data_document_filter = new InputComponent('data_document');
        $date_trip_filter = new InputComponent('date_trip', 'date');
        $served_filter = new SelectComponent('served');
        $approve_filter = new SelectComponent('approved');
        $flexible_filter = new SelectComponent('ticket_flexible');
        $nationality_filter = (new NationalitySelect('nationality'))->create();
        $transport_filter = (new TransportSelect('id_transport'))->create();
        $origin_filter = (new LocationSelect('id_origin'))->create();
        $destiny_filter = (new LocationSelect('id_destiny'))->create();

        $approve_filter->add_option('Seleccione...', '');
        $approve_filter->add_option('Sí', 'true');
        $approve_filter->add_option('No', 'false');

        $served_filter->add_option('Seleccione...', '');
        $served_filter->add_option('Sí', 'true');
        $served_filter->add_option('No', 'false');

        $flexible_filter->add_option('Seleccione...', '');
        $flexible_filter->add_option('Sí', 'true');
        $flexible_filter->add_option('No', 'false');

        $name_filter->set_value($_GET['name'] ?? '');
        $ticket_status_filter->set_value($_GET['status'] ?? '');
        $type_document_filter->set_value($_GET['type_document'] ?? '');
        $data_document_filter->set_value($_GET['data_document'] ?? '');
        $date_trip_filter->set_value($_GET['date_trip'] ?? '');
        $served_filter->set_value($_GET['served'] ?? '');
        $approve_filter->set_value($_GET['approved'] ?? '');
        $flexible_filter->set_value($_GET['ticket_flexible'] ?? '');
        $nationality_filter->set_value($_GET['nationality'] ?? '');
        $transport_filter->set_value($_GET['id_transport'] ?? '');
        $origin_filter->set_value($_GET['id_origin'] ?? '');
        $destiny_filter->set_value($_GET['id_destiny'] ?? '');

        wp_enqueue_script(
            'admin-table-passengers',
            CENTRAL_BOOKING_URL . '/assets/js/admin/passengers-table.js'
        );

        ob_start();
        ?>
        <form method="GET" action="">
            <input type="hidden" name="page" value="<?= esc_attr($_GET['page'] ?? 'git_passengers') ?>">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <?php $name_filter->get_label('Nombre')->display() ?>
                    </th>
                    <td>
                        <?php $name_filter->display(); ?>
                    </td>
                    <th scope="row">
                        <?php $nationality_filter->get_label('Nacionalidad')->display() ?>
                    </th>
                    <td>
                        <?php $nationality_filter->display(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $approve_filter->get_label('Aprobado')->display() ?>
                    </th>
                    <td>
                        <?php $approve_filter->display(); ?>
                    </td>
                    <th scope="row">
                        <?php $served_filter->get_label('Transportado')->display() ?>
                    </th>
                    <td>
                        <?php $served_filter->display(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $type_document_filter->get_label('Tipo de Documento')->display(); ?>
                    </th>
                    <td>
                        <?php $type_document_filter->display(); ?>
                    </td>
                    <th scope="row">
                        <?php $data_document_filter->get_label('Número de Documento')->display(); ?>
                    </th>
                    <td>
                        <?php $data_document_filter->display(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $date_trip_filter->get_label('Fecha del viaje')->display(); ?>
                    </th>
                    <td>
                        <?php $date_trip_filter->display(); ?>
                    </td>
                    <th scope="row">
                        <?php $ticket_status_filter->get_label('Estado del ticket')->display(); ?>
                    </th>
                    <td>
                        <?php $ticket_status_filter->display(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $flexible_filter->get_label('Flexible')->display(); ?>
                    </th>
                    <td>
                        <?php $flexible_filter->display(); ?>
                    </td>
                    <th scope="row">
                        <?php $transport_filter->get_label('Transporte')->display(); ?>
                    </th>
                    <td>
                        <?php $transport_filter->display(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $origin_filter->get_label('Origen')->display(); ?>
                    </th>
                    <td>
                        <?php $origin_filter->display(); ?>
                    </td>
                    <th scope="row">
                        <?php $destiny_filter->get_label('Destino')->display(); ?>
                    </th>
                    <td>
                        <?php $destiny_filter->display(); ?>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" class="button-primary">Aplicar</button>
                <button type="reset" class="button">Limpiar</button>
            </p>
        </form>
        <?php
        return ob_get_clean();
    }

    private function get_current_order_by()
    {
        $order_by = $_GET['order_by'] ?? 'date_trip';
        return in_array(
            $order_by,
            ['name', 'nationality', 'type_document', 'data_document', 'served', 'approved', 'date_trip', 'date_birth', 'id_ticket', 'id_route', 'id_transport', 'ticket_status'],
        ) ? $order_by : 'date_trip';
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

    public function display(): void
    {
        $pagination = new PaginationComponent();
        $pagination->set_data(
            $this->total_items,
            $this->current_page,
            $this->total_pages
        );
        $pagination->set_links(
            link_first: add_query_arg(['page_number' => 1]),
            link_last: add_query_arg(['page_number' => $this->total_pages]),
            link_next: add_query_arg(['page_number' => $this->current_page + 1]),
            link_prev: add_query_arg(['page_number' => $this->current_page - 1])
        );
        $this->filter_pad()->display();
        ?>
        <div style="overflow-x: auto; max-width: 1500px">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 5px;" scope="col"></th>
                        <th scope="col" style="width: 400px;"
                            class="manage-column <?= $this->get_current_order_by() === 'name' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('name', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Nombre</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                        <th scope="col" style="width: 100px;"
                            class="manage-column <?= $this->get_current_order_by() === 'nationality' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('nationality', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Nacionalidad</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                        <th scope="col" style="width: 100px;"
                            class="manage-column <?= $this->get_current_order_by() === 'type_document' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('type_document', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Tipo de Documento</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                        <th scope="col" style="width: 100px;"
                            class="manage-column <?= $this->get_current_order_by() === 'data_document' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('data_document', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Número Documento</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                        <th scope="col" style="width: 100px;"
                            class="manage-column <?= $this->get_current_order_by() === 'served' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('served', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Transportado</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                        <th scope="col" style="width: 100px;"
                            class="manage-column <?= $this->get_current_order_by() === 'approved' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('approved', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Aprobado</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->passengers as $passenger): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="transfer-check" <?= !$passenger->get_ticket()->flexible || !$passenger->approved || $passenger->served ? 'disabled' : '' ?>
                                    value="<?= esc_attr($passenger->id) ?>" name="transfer_passengers[]"
                                    id="transfer-check-<?= esc_attr($passenger->id) ?>">
                            </td>
                            <td>
                                <label for="transfer-check-<?= esc_attr($passenger->id) ?>">
                                    <?= esc_html($passenger->name) ?>
                                </label>
                                <div class="row-actions visible">
                                    <span>ID: <?= esc_html($passenger->id) ?></span>
                                    <span> | </span>
                                    <span>
                                        <a class="git-row-action-link" target="_blank"
                                            href="<?= admin_url("admin.php?page=central_tickets&id={$passenger->get_ticket()->id}") ?>">
                                            Ticket: <?= $passenger->get_ticket()->id ?>
                                        </a>
                                    </span>
                                    <span> | </span>
                                    <span>
                                        <a class="git-row-action-link" target="_blank"
                                            href="<?= admin_url("admin.php?page=central_activity&tab=passengers&id={$passenger->id}") ?>">
                                            Logs
                                        </a>
                                    </span>
                                    <span> | </span>
                                    <span>
                                        <a class="git-row-action-link" href="#qr-code-container-<?= $passenger->id ?>">
                                            Código <i class="bi bi-qr-code"></i>
                                        </a>
                                    </span>
                                    <span> | </span>
                                    <span>
                                        <a class="git-row-action-link" href="#trip-container-<?= $passenger->id ?>">
                                            Viaje
                                        </a>
                                    </span>
                                </div>
                            </td>
                            <td><?= esc_html($passenger->nationality) ?></td>
                            <td><?= esc_html($passenger->type_document) ?></td>
                            <td><?= esc_html($passenger->data_document) ?></td>
                            <td><?= $passenger->served ? 'Sí' : 'No' ?></td>
                            <td><?= $passenger->approved ? 'Sí' : 'No' ?></td>
                        </tr>
                        <tr id="actions-container-<?= $passenger->id ?>" class="git-row-actions">
                            <td colspan="6">
                                <div id="trip-container-<?= $passenger->id ?>" class="git-item-container hidden"
                                    data-parent="#actions-container-<?= $passenger->id ?>">
                                    <div class="git-item">
                                        <table style="border-spacing: 20px 3px; border-collapse: separate;">
                                            <thead>
                                                <tr>
                                                    <td colspan="2" style="text-align: center;">Información de viaje</td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><b>Origen:</b></td>
                                                    <td><?= esc_html($passenger->get_route()->get_origin()->name) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><b>Destino:</b></td>
                                                    <td><?= esc_html($passenger->get_route()->get_destiny()->name) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><b>Hora:</b></td>
                                                    <td><?= git_time_format($passenger->get_route()->departure_time) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><b>Fecha:</b></td>
                                                    <td><?= git_date_format($passenger->date_trip) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><b>Transporte:</b></td>
                                                    <td><?= $passenger->get_transport()->nicename ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div id="qr-code-container-<?= $passenger->id ?>" class="git-item-container hidden"
                                    data-parent="#actions-container-<?= $passenger->id ?>">
                                    <div class="git-item">
                                        <div style="padding: 20px; text-align: center;">
                                            <img src="<?= git_get_ticket_viewer_url($passenger->get_ticket()->id, 250) ?>"
                                                alt="Código QR">
                                        </div>
                                        <a href="<?= git_get_ticket_viewer_qr_url($passenger->get_ticket()->id) ?>"
                                            target="_blank"><?= git_get_ticket_viewer_qr_url($passenger->get_ticket()->id) ?></a>
                                    </div>
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