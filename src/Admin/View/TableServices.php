<?php
namespace CentralTickets\Admin\View;

use CentralTickets\Service;
use CentralTickets\Services\ServiceService;
use CentralTickets\Components\Displayer;
use CentralTickets\Components\PaginationComponent;

final class TableServices implements Displayer
{
    /**
     * @var array<Service>
     */
    private array $services;
    private int $total_items;
    private int $per_page = 10;
    private int $total_pages;
    private int $current_page;

    public function __construct()
    {
        $this->services = $this->fetchServices();
    }

    private function fetchServices(): array
    {
        $page_number = isset($_GET['page_number']) ? (int) $_GET['page_number'] : 1;
        $service = new ServiceService();
        $result = $service->paginated(
            order: $_GET['order'] ?? 'DESC',
            order_by: $_GET['order_by'] ?? 'id',
            page_number: $page_number,
            page_size: $this->per_page
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
            ['name', 'price']
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

    public function display()
    {
        $pagination = new PaginationComponent();
        $pagination->set_data(
            total_items: $this->total_items,
            total_pages: $this->total_pages,
            current_page: $this->current_page
        );
        $pagination->set_links(
            link_first: add_query_arg(['page_number' => 1]),
            link_prev: add_query_arg(['page_number' => max(1, $this->current_page - 1)]),
            link_next: add_query_arg(['page_number' => min($this->total_pages, $this->current_page + 1)]),
            link_last: add_query_arg(['page_number' => $this->total_pages])
        );
        ?>
        <div style="overflow-x: auto; max-width: 700px;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col" style="width: 400px;"
                            class="manage-column <?= $this->get_current_order_by() === 'name' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('name', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Ubicación</span>
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
                        <th style="width: 100px;" scope="col">Ícono</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->services as $service): ?>
                        <tr>
                            <td>
                                <span><?= esc_html($service->name) ?></span>
                                <div class="row-actions visible">
                                    <span>ID: <?= esc_html($service->id) ?> | </span>
                                    <span class="edit">
                                        <a class="git-row-action-link" href="#transport-container-<?= $service->id ?>">Transportes
                                            (<?= count($service->get_transports()) ?>)</a>
                                    </span>
                                    <span> | </span>
                                    <span class="edit">
                                        <a href="<?= add_query_arg(
                                            [
                                                'activity' => 'form',
                                                'id' => $service->id,
                                            ],
                                            admin_url('admin.php?page=central_services')
                                        ) ?>" aria-label="Editar Servicio">Editar</a>
                                    </span>
                                </div>
                            </td>
                            <td> <?= git_currency_format(esc_html($service->price), true) ?> </td>
                            <td> <img src="<?= esc_url($service->icon) ?>" alt="<?= esc_html($service->name) ?>" width="24px"> </td>
                        </tr>
                        <tr id="actions-container-<?= $service->id ?>" class="git-row-actions">
                            <td colspan="3">
                                <div id="transport-container-<?= $service->id ?>" class="git-item-container hidden"
                                    data-parent="#actions-container-<?= $service->id ?>">
                                    <?php foreach ($service->get_transports() as $transport): ?>
                                        <div class="git-item">
                                            <?= esc_html($transport->nicename) ?>
                                        </div>
                                    <?php endforeach; ?>
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