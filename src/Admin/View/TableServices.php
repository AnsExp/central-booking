<?php
namespace CentralTickets\Admin\View;

use CentralTickets\Admin\AdminRouter;
use CentralTickets\Admin\Form\FormService;
use CentralTickets\Persistence\ResultSet;
use CentralTickets\Services\ServiceService;

final class TableServices extends TableAdmin
{
    private ResultSet $result_set;

    public function __construct()
    {
        parent::__construct();
    }

    protected function get_pagination_links(): array
    {
        $url = AdminRouter::get_url_for_class(TableServices::class);
        return [
            'first' => add_query_arg(['page_number' => 1], $url),
            'prev' => add_query_arg(['page_number' => $this->result_set->current_page - 1], $url),
            'next' => add_query_arg(['page_number' => $this->result_set->current_page + 1], $url),
            'last' => add_query_arg(['page_number' => $this->result_set->total_pages], $url),
        ];
    }

    protected function get_result_set(): ResultSet
    {
        if (!isset($this->result_set)) {
            $this->result_set = new ResultSet();
            $page_number = isset($_GET['page_number']) ? (int) $_GET['page_number'] : 1;
            $service = new ServiceService();
            $result = $service->paginated(
                order: $_GET['order'] ?? 'DESC',
                order_by: $_GET['order_by'] ?? 'id',
                page_number: $page_number,
                page_size: $this->result_set->per_page
            );
            $this->result_set->items = $result['data'] ?? [];
            $this->result_set->total_items = $result['pagination']['total_elements'] ?? 0;
            $this->result_set->total_pages = $result['pagination']['total_pages'] ?? 0;
            $this->result_set->current_page = $result['pagination']['current_page'] ?? 0;
            $this->result_set->has_items = $this->result_set->total_items > 0;
        }
        return $this->result_set;
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

    protected function no_content(): void
    {
        echo 'No services found.';
    }

    protected function table(): void
    {
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
                    <?php foreach ($this->get_result_set()->items as $service): ?>
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
                                        <a href="<?= AdminRouter::get_url_for_class(FormService::class,['id' => $service->id]) ?>" aria-label="Editar Servicio">Editar</a>
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
        </div>
        <?php
    }
}