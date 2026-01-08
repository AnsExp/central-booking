<?php
namespace CentralBooking\Admin\View;

use CentralBooking\Admin\AdminRouter;
use CentralBooking\Admin\Form\FormService;
use CentralBooking\Data\Repository\ResultSetInterface;
use CentralBooking\Data\Service;
use CentralBooking\Data\Services\ServiceService;

final class TableServices extends TableAdmin
{
    /**
     * Summary of result_set
     * @var ResultSetInterface<Service>
     */
    private ResultSetInterface $result_set;

    public function __construct()
    {
        parent::__construct();
    }

    protected function get_pagination_links(): array
    {
        $url = AdminRouter::get_url_for_class(TableServices::class);
        return [
            'first' => add_query_arg(['page_number' => 1], $url),
            'prev' => add_query_arg(['page_number' => $this->result_set->getCurrentPage() - 1], $url),
            'next' => add_query_arg(['page_number' => $this->result_set->getCurrentPage() + 1], $url),
            'last' => add_query_arg(['page_number' => $this->result_set->getTotalPages()], $url),
        ];
    }

    protected function get_result_set(): ResultSetInterface
    {
        $page_number = isset($_GET['page_number']) ? (int) $_GET['page_number'] : 1;
        $service = new ServiceService();
        return git_services_result_set([
            'order' => $this->get_current_order(),
            'order_by' => 'name',
            'limit' => 10,
            'offset' => ($page_number - 1) * 10
        ]);
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
        $order = $_GET['order'] ?? 'ASC';
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
        $this->result_set = $this->get_result_set();
        $this->table();
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
                    <?php foreach ($this->result_set->getItems() as $service): ?>
                        <tr>
                            <td>
                                <span><?= esc_html($service->name) ?></span>
                                <div class="row-actions visible">
                                    <span>ID: <?= esc_html($service->id) ?> | </span>
                                    <span class="edit">
                                        <a class="git-row-action-link" href="#transport-container-<?= $service->id ?>">Transportes
                                            (<?= count($service->getTransports()) ?>)</a>
                                    </span>
                                    <span> | </span>
                                    <span class="edit">
                                        <a href="<?= AdminRouter::get_url_for_class(FormService::class, ['id' => $service->id]) ?>"
                                            aria-label="Editar Servicio">Editar</a>
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
                                    <?php foreach ($service->getTransports() as $transport): ?>
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