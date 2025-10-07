<?php
namespace CentralTickets\Admin\View;

use CentralTickets\Components\Displayer;
use CentralTickets\Components\PaginationComponent;
use CentralTickets\Services\LocationService;
use CentralTickets\Location;

final class TableLocations implements Displayer
{
    /**
     * @var array<Location>
     */
    private array $zones;
    private int $total_items;
    private int $per_page = 10;
    private int $total_pages;
    private int $current_page;

    public function __construct()
    {
        $this->zones = $this->fetchLocations();
    }

    private function fetchLocations(): array
    {
        $page_number = isset($_GET['page_number']) ? (int) $_GET['page_number'] : 1;
        $service = new LocationService();
        $result = $service->paginated(
            order_by: $_GET['order_by'] ?? 'id',
            order: $_GET['order'] ?? 'DESC',
            page_number: $page_number,
            page_size: $this->per_page,
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
            ['name', 'name_zone']
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
        <div style="max-width: 500px;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col"
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
                        <th scope="col"
                            class="manage-column <?= $this->get_current_order_by() === 'name_zone' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('name_zone', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Zona</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->zones as $location): ?>
                        <tr>
                            <td>
                                <span><?= esc_html($location->name) ?></span>
                                <div class="row-actions visible">
                                    <span class="edit">
                                        ID: <?= $location->id ?>
                                    </span>
                                    <span> | </span>
                                    <span class="edit">
                                        <a
                                            href="<?= add_query_arg(['action' => 'form', 'id' => $location->id], admin_url('admin.php?page=central_locations')) ?>">Editar</a>
                                    </span>
                                </div>
                            </td>
                            <td><?= esc_html($location->get_zone()->name) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php $pagination->display() ?>
        </div>
        <?php

    }
}