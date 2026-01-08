<?php
namespace CentralBooking\Admin\View;

use CentralBooking\Admin\Form\FormRoute;
use CentralBooking\Data\Route;
use CentralBooking\Data\Services\RouteService;
use CentralBooking\Admin\AdminRouter;
use CentralBooking\GUI\Constants\AlignmentConstants;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\PaginationComponent;

final class TableRoutes implements DisplayerInterface
{
    /**
     * @var array<Route>
     */
    private array $routes;
    private int $total_items;
    private int $per_page = 10;
    private int $total_pages;
    private int $current_page;

    public function __construct()
    {
        $this->routes = $this->fetchServices();
    }

    private function fetchServices(): array
    {
        $page_number = isset($_GET['page_number']) ? (int) $_GET['page_number'] : 1;
        $result = git_routes_result_set([
            'order_by' => $this->get_current_order_by(),
            'order' => $this->get_current_order(),
            'limit' => $this->per_page,
            'offset' => ($page_number - 1) * $this->per_page,
        ]);
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
            ['name_origin', 'name_destiny', 'type', 'duration_trip', 'departure_time']
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
        $pagination = new PaginationComponent(false, AlignmentConstants::RIGHT);
        $pagination->set_data(
            total_items: $this->total_items,
            current_page: $this->current_page,
            total_pages: $this->total_pages,
        );
        $pagination->set_links(
            link_first: add_query_arg(['page_number' => 1]),
            link_last: add_query_arg(['page_number' => $this->total_pages]),
            link_next: add_query_arg(['page_number' => ($this->current_page + 1)]),
            link_prev: add_query_arg(['page_number' => ($this->current_page - 1)])
        );
        ?>
        <div style="overflow-x: auto; max-width: 1100px;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col" style="width: 200px;"
                            class="manage-column <?= $this->get_current_order_by() === 'name_origin' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('name_origin', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Origen</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                        <th scope="col" style="width: 200px;"
                            class="manage-column <?= $this->get_current_order_by() === 'name_destiny' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('name_destiny', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Destino</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
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
                        <th scope="col" style="width: 100px;"
                            class="manage-column <?= $this->get_current_order_by() === 'departure_time' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('departure_time', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Hora de salida</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->routes as $route): ?>
                        <tr>
                            <td>
                                <span style="display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="bi bi-geo"></i>
                                    <span><?= esc_html($route->getOrigin()->name) ?></span>
                                </span>
                                <div class="row-actions visible">
                                    <span class="edit">
                                        ID: <?= esc_html($route->id) ?>
                                    </span>
                                    <span>|</span>
                                    <span class="edit">
                                        <a href="#transport-container-<?= $route->id ?>" class="git-row-action-link"
                                            data-route="<?= esc_attr($route->id) ?>">
                                            Transportes (<?= count($route->getTransports()) ?>)
                                        </a>
                                    </span>
                                    <span>|</span>
                                    <span class="edit">
                                        <a
                                            href="<?= AdminRouter::get_url_for_class(FormRoute::class, ['id' => $route->id]) ?>">Editar</a>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span style="display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="bi bi-arrow-right"></i>
                                    <span><?= esc_html($route->getDestiny()->name) ?></span>
                                </span>
                            </td>
                            <td><?= esc_html($route->type->label()) ?></td>
                            <td><?= git_time_format($route->getDepartureTime()->format('H:i')) ?></td>
                        </tr>
                        <tr id="actions-container-<?= $route->id ?>" class="git-row-actions">
                            <td colspan="4">
                                <div id="transport-container-<?= $route->id ?>" class="git-item-container hidden"
                                    data-parent="#actions-container-<?= $route->id ?>">
                                    <?php foreach ($route->getTransports() as $transport): ?>
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
            <?php $pagination->render() ?>
        </div>
        <?php
    }
}