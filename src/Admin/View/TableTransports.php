<?php
namespace CentralBooking\Admin\View;

use CentralBooking\Admin\AdminRouter;
use CentralBooking\Admin\Form\FormTransport;
use CentralBooking\Data\Services\TransportService;
use CentralBooking\Data\Transport;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\GUI\PaginationComponent;

final class TableTransports implements DisplayerInterface
{
    /**
     * @var array<Transport>
     */
    private array $transports;
    private int $total_items;
    private int $per_page = 10;
    private int $total_pages;
    private int $current_page;

    public function __construct()
    {
        $this->transports = $this->fetchTransports();
    }

    private function fetchTransports(): array
    {
        $page_number = isset($_GET['page_number']) ? (int) $_GET['page_number'] : 1;
        $service = new TransportService();
        $result = $service->find(
            order: $_GET['order'] ?? 'DESC',
            orderBy: $_GET['order_by'] ?? 'id',
            pageNumber: $page_number,
            pageSize: $this->per_page
        );
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
            ['nicename', 'code', 'type', 'id_operator']
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

    public function render()
    {
        if (empty($this->transports)) {
            $this->no_content();
        } else {
            $this->table_content();
        }
    }

    private function no_content()
    {
        ?>
        <div style="text-align: center;">
            <span class="dashicons dashicons-admin-site"></span>
            <p>No se encontraron transportes.</p>
        </div>
        <?php
    }

    private function table_content()
    {
        wp_enqueue_script(
            'git-transport-table',
            CENTRAL_BOOKING_URL . '/assets/js/admin/transport-table.js',
        );
        wp_localize_script(
            'git-transport-table',
            'gitTransportTable',
            [
                'url' => admin_url('admin-ajax.php?action=git_transport_availability'),
                'nonce' => wp_create_nonce('transport_availability_nonce'),
            ]
        );

        $pagination = new PaginationComponent();
        $pagination->set_data(
            $this->total_items,
            $this->current_page,
            $this->total_pages
        );
        $pagination->set_links(
            link_first: add_query_arg(['page_number' => 1]),
            link_last: add_query_arg(['page_number' => $this->total_pages]),
            link_next: add_query_arg(['page_number' => ($this->current_page + 1)]),
            link_prev: add_query_arg(['page_number' => ($this->current_page - 1)])
        );

        ?>
        <div id="issues_container"></div>
        <div style="overflow-x: auto; max-width: 1200px;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col" style="width: 400px;"
                            class="manage-column <?= $this->get_current_order_by() === 'nicename' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('nicename', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Nombre</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                        <th scope="col" style="width: 100px;"
                            class="manage-column <?= $this->get_current_order_by() === 'code' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('code', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Código</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                        <th style="width: 100px;" scope="col">Capacidad</th>
                        <th scope="col" style="width: 100px;"
                            class="manage-column <?= $this->get_current_order_by() === 'type' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('type', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Tipo</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                        <th style="width: 100px;" scope="col">Alias</th>
                        <th style="width: 100px;" scope="col">Disponibilidad</th>
                        <th scope="col" style="width: 100px;"
                            class="manage-column <?= $this->get_current_order_by() === 'id_operator' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('id_operator', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Operador</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->transports as $transport): ?>
                        <tr>
                            <td>
                                <span>
                                    <?= esc_html($transport->nicename) ?>
                                </span>
                                <div class="row-actions visible">
                                    <span>ID: <?= esc_html($transport->id) ?></span>
                                    <span> | </span>
                                    <span class="edit">
                                        <a class="git-row-action-link" href="#routes-container-<?= $transport->id ?>">Rutas
                                            (<?= count($transport->getRoutes()) ?>)</a>
                                    </span>
                                    <span> | </span>
                                    <span class="edit">
                                        <a class="git-row-action-link" href="#services-container-<?= $transport->id ?>">Servicios
                                            (<?= count($transport->getServices()) ?>)</a>
                                    </span>
                                    <span> | </span>
                                    <span class="edit">
                                        <a class="git-row-action-link"
                                            href="#availability-container-<?= $transport->id ?>">Disponibilidad</a>
                                    </span>
                                    <span> | </span>
                                    <span class="edit">
                                        <a href="<?= esc_url(AdminRouter::get_url_for_class(FormTransport::class, ['id' => $transport->id])) ?>"
                                            aria-label="Editar Servicio">Editar</a>
                                    </span>
                                </div>
                            </td>
                            <td><?= esc_html($transport->code) ?></td>
                            <td><?= esc_html($transport->getCapacity()) ?></td>
                            <td><?= esc_html($transport->type->label()) ?></td>
                            <td>
                                <ul style="list-style-type: square; margin: 0;"><?= $transport->getAlias() ? join(
                                    '',
                                    array_map(fn($alias) => '<li>' . $alias . '</li>', $transport->getAlias())
                                ) : '' ?></ul>
                            </td>
                            <td><?= $transport->isAvailable() ? 'Disponible' : 'No disponible' ?></td>
                            <td><?= esc_html($transport->getOperator()->getUser()->first_name . ' ' . $transport->getOperator()->getUser()->last_name) ?>
                            </td>
                        </tr>
                        <tr id="actions-container-<?= $transport->id ?>" class="git-row-actions">
                            <td colspan="6">
                                <div id="routes-container-<?= $transport->id ?>" class="git-item-container hidden"
                                    data-parent="#actions-container-<?= $transport->id ?>">
                                    <?php foreach ($transport->getRoutes() as $route): ?>
                                        <div class="git-item">
                                            <table style="border-spacing: 20px 3px; border-collapse: separate;">
                                                <tr>
                                                    <td><b>Origen:</b></td>
                                                    <td><?= esc_html($route->getOrigin()->name) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><b>Destino:</b></td>
                                                    <td><?= esc_html($route->getDestiny()->name) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><b>Hora:</b></td>
                                                    <td><?= git_time_format($route->getDepartureTime()->format()) ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div id="services-container-<?= $transport->id ?>" class="git-item-container hidden"
                                    data-parent="#actions-container-<?= $transport->id ?>">
                                    <?php foreach ($transport->getServices() as $service): ?>
                                        <div class="git-item">
                                            <?= esc_html($service->name) ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div id="availability-container-<?= $transport->id ?>" class="git-item-container hidden"
                                    data-parent="#actions-container-<?= $transport->id ?>">
                                    <form class="form-availability" method="post" style="padding: 15px;">
                                        <?php
                                        $id_input = new InputComponent('id_transport', 'hidden');
                                        $date_end_input = new InputComponent('date_end', 'date');
                                        $date_start_input = new InputComponent('date_start', 'date');
                                        $id_input->setValue($transport->id);
                                        $date_end_input->setValue($transport->getMaintenanceDates()['date_end'] ?? '');
                                        $date_start_input->setValue($transport->getMaintenanceDates()['date_start'] ?? '');
                                        $date_end_input->setRequired(true);
                                        $date_start_input->setRequired(true);
                                        $id_input->render();
                                        ?>
                                        <h2 style="margin: 0;">Sin operación</h2>
                                        <table class="form-table" role="presentation" style="max-width: 500px;">
                                            <tr>
                                                <td scope="row">
                                                    <?php $date_start_input->getLabel('Inicio')->render() ?>
                                                </td>
                                                <td>
                                                    <?= $date_start_input->render() ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td scope="row">
                                                    <?php $date_end_input->getLabel('Fin')->render() ?>
                                                </td>
                                                <td>
                                                    <?= $date_end_input->render() ?>
                                                </td>
                                            </tr>
                                        </table>
                                        <input class="button button-primary" type="submit" value="Establecer">
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
