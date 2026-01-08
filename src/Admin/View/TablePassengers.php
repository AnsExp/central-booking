<?php
namespace CentralBooking\Admin\View;

use CentralBooking\Admin\AdminRouter;
use CentralBooking\Data\Passenger;
use CentralBooking\GUI\AccordionComponent;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\GUI\PaginationComponent;
use CentralBooking\GUI\SelectComponent;
use CentralBooking\GUI\TextComponent;
use CentralBooking\Implementation\GUI\LocationSelect;
use CentralBooking\Implementation\GUI\NationalitySelect;
use CentralBooking\Implementation\GUI\TicketStatusSelect;
use CentralBooking\Implementation\GUI\TransportSelect;
use CentralBooking\Implementation\GUI\TypeDocumentSelect;

final class TablePassengers implements DisplayerInterface
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
        $filter = [];

        foreach ($_GET as $key => $value) {
            if ($value !== '') {
                $filter[$key] = $value;
            }
        }

        $args = array_merge($filter, [
            'order_by' => $this->get_current_order_by(),
            'order' => $this->get_current_order(),
            'offset' => ($page_number - 1) * $this->per_page,
            'limit' => $this->per_page
        ]);
        $result_set = git_passengers_result_set($args);

        $this->total_items = $result_set->getTotalItems();
        $this->total_pages = $result_set->getTotalPages();
        $this->current_page = $result_set->getCurrentPage();

        return $result_set->getItems();
    }

    private function filter_pad()
    {
        $accordion = new AccordionComponent();
        $accordion_title = new TextComponent('span');
        $accordion_title->append(git_string_to_component('<i class="bi bi-sliders"></i>'));
        $accordion_title->append(' Filtro');
        $accordion->add_item($accordion_title, $this->filter_form());
        return $accordion;
    }

    private function filter_form()
    {
        $name_filter = new InputComponent('name');
        $ticket_status_filter = (new TicketStatusSelect('status'))->create();
        $type_document_filter = (new TypeDocumentSelect(name: 'type_document'))->create();
        $data_document_filter = new InputComponent('data_document');
        $date_trip_filter = new InputComponent('date_trip', 'date');
        $served_filter = new SelectComponent('served');
        $approve_filter = new SelectComponent('approved');
        $flexible_filter = new SelectComponent('ticket_flexible');
        $nationality_filter = (new NationalitySelect('nationality'))->create();
        $transport_filter = (new TransportSelect('id_transport'))->create();
        $origin_filter = (new LocationSelect('id_origin'))->create();
        $destiny_filter = (new LocationSelect('id_destiny'))->create();

        $approve_filter->addOption('Seleccione...', '');
        $approve_filter->addOption('Sí', 'true');
        $approve_filter->addOption('No', 'false');

        $served_filter->addOption('Seleccione...', '');
        $served_filter->addOption('Sí', 'true');
        $served_filter->addOption('No', 'false');
        $flexible_filter->addOption('Seleccione...', '');
        $flexible_filter->addOption('Sí', 'true');
        $flexible_filter->addOption('No', 'false');

        $name_filter->setValue($_GET['name'] ?? '');
        $ticket_status_filter->setValue($_GET['status'] ?? '');
        $type_document_filter->setValue($_GET['type_document'] ?? '');
        $data_document_filter->setValue($_GET['data_document'] ?? '');
        $date_trip_filter->setValue($_GET['date_trip'] ?? '');
        $served_filter->setValue($_GET['served'] ?? '');
        $approve_filter->setValue($_GET['approved'] ?? '');
        $flexible_filter->setValue($_GET['ticket_flexible'] ?? '');
        $nationality_filter->setValue($_GET['nationality'] ?? '');
        $transport_filter->setValue($_GET['id_transport'] ?? '');
        $origin_filter->setValue($_GET['id_origin'] ?? '');
        $destiny_filter->setValue($_GET['id_destiny'] ?? '');

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
                        <?php $name_filter->getLabel('Nombre')->render() ?>
                    </th>
                    <td>
                        <?php $name_filter->render(); ?>
                    </td>
                    <th scope="row">
                        <?php $nationality_filter->getLabel('Nacionalidad')->render() ?>
                    </th>
                    <td>
                        <?php $nationality_filter->render(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $approve_filter->getLabel('Aprobado')->render() ?>
                    </th>
                    <td>
                        <?php $approve_filter->render(); ?>
                    </td>
                    <th scope="row">
                        <?php $served_filter->getLabel('Transportado')->render() ?>
                    </th>
                    <td>
                        <?php $served_filter->render(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $type_document_filter->getLabel('Tipo de Documento')->render(); ?>
                    </th>
                    <td>
                        <?php $type_document_filter->render(); ?>
                    </td>
                    <th scope="row">
                        <?php $data_document_filter->getLabel('Número de Documento')->render(); ?>
                    </th>
                    <td>
                        <?php $data_document_filter->render(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $date_trip_filter->getLabel('Fecha del viaje')->render(); ?>
                    </th>
                    <td>
                        <?php $date_trip_filter->render(); ?>
                    </td>
                    <th scope="row">
                        <?php $ticket_status_filter->getLabel('Estado del ticket')->render(); ?>
                    </th>
                    <td>
                        <?php $ticket_status_filter->render(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $flexible_filter->getLabel('Flexible')->render(); ?>
                    </th>
                    <td>
                        <?php $flexible_filter->render(); ?>
                    </td>
                    <th scope="row">
                        <?php $transport_filter->getLabel('Transporte')->render(); ?>
                    </th>
                    <td>
                        <?php $transport_filter->render(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $origin_filter->getLabel('Origen')->render(); ?>
                    </th>
                    <td>
                        <?php $origin_filter->render(); ?>
                    </td>
                    <th scope="row">
                        <?php $destiny_filter->getLabel('Destino')->render(); ?>
                    </th>
                    <td>
                        <?php $destiny_filter->render(); ?>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" class="button-primary">Aplicar</button>
                <button type="reset" class="button">Limpiar</button>
            </p>
        </form>
        <?php
        $result = ob_get_clean();
        return git_string_to_component($result);
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

    public function render(): void
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
        $this->filter_pad()->render();
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
                                <input type="checkbox" class="transfer-check" <?= !$passenger->getTicket()->flexible || !$passenger->approved || $passenger->served ? 'disabled' : '' ?>
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
                                            href="<?= AdminRouter::get_url_for_class(TableTickets::class, ['id' => $passenger->getTicket()->id]) ?>">
                                            Ticket: <?= $passenger->getTicket()->id ?>
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
                            <td><?= esc_html($passenger->typeDocument) ?></td>
                            <td><?= esc_html($passenger->dataDocument) ?></td>
                            <td><?= $passenger->served ? 'Sí' : 'No' ?></td>
                            <td><?= $passenger->approved ? 'Sí' : 'No' ?></td>
                        </tr>
                        <tr id="actions-container-<?= $passenger->id ?>" class="git-row-actions">
                            <td colspan="7">
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
                                                    <td><?= esc_html($passenger->getRoute()->getOrigin()->name) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><b>Destino:</b></td>
                                                    <td><?= esc_html($passenger->getRoute()->getDestiny()->name) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><b>Hora:</b></td>
                                                    <td><?= git_time_format($passenger->getRoute()->getDepartureTime()->format()) ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><b>Fecha:</b></td>
                                                    <td><?= git_date_format($passenger->getDateTrip()->format('Y-m-d')) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><b>Transporte:</b></td>
                                                    <td><?= $passenger->getTransport()->nicename ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div id="qr-code-container-<?= $passenger->id ?>" class="git-item-container hidden"
                                    data-parent="#actions-container-<?= $passenger->id ?>">
                                    <div class="git-item">
                                        <div style="padding: 20px; text-align: center;">
                                            <img src="<?= git_get_ticket_viewer_url($passenger->getTicket()->id, 250) ?>"
                                                alt="Código QR">
                                        </div>
                                        <a href="<?= git_get_ticket_viewer_qr_url($passenger->getTicket()->id) ?>"
                                            target="_blank"><?= git_get_ticket_viewer_qr_url($passenger->getTicket()->id) ?></a>
                                    </div>
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