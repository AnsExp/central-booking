<?php
namespace CentralBooking\Admin\View;

use CentralBooking\Admin\AdminRouter;
use CentralBooking\Admin\Form\FormOperator;
use CentralBooking\Data\Operator;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\Data\Services\OperatorService;

final class TableOperators implements DisplayerInterface
{
    /**
     * @var array<Operator>
     */
    private array $operators;

    public function __construct()
    {
        $this->operators = $this->fetchOperators();
    }

    private function fetchOperators(): array
    {
        $service = new OperatorService();
        return $service->findAll();
    }

    public function render()
    {
        ?>
        <div style="overflow-x: auto; max-width: 800px;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 400px;" scope="col">Nombre</th>
                        <th style="width: 100px;" scope="col">Teléfono</th>
                        <th style="width: 100px;" scope="col">Usuario</th>
                        <th style="width: 100px;" scope="col">Cupones usados</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->operators as $operator): ?>
                        <tr>
                            <td>
                                <span><?= esc_html($operator->getUser()->first_name . ' ' . $operator->getUser()->last_name) ?></span>
                                <div class="row-actions visible">
                                    <span class="edit">
                                        ID: <?= esc_html($operator->getUser()->ID) ?>
                                    </span>
                                    <span>|</span>
                                    <span class="edit">
                                        <a href="#transport-container-<?= $operator->getUser()->ID ?>" class="git-row-action-link"
                                            data-route="<?= esc_attr($operator->getUser()->ID) ?>">
                                            Transportes (<?= count($operator->getTransports()) ?>)
                                        </a>
                                    </span>
                                    <span>|</span>
                                    <span class="edit">
                                        <a href="#coupon-container-<?= $operator->getUser()->ID ?>" class="git-row-action-link"
                                            data-route="<?= esc_attr($operator->getUser()->ID) ?>">
                                            Cupones (<?= count($operator->getCoupons()) ?>)
                                        </a>
                                    </span>
                                    <span>|</span>
                                    <span class="edit">
                                        <a
                                            href="<?= esc_url(AdminRouter::get_url_for_class(FormOperator::class, ['id' => $operator->getUser()->ID])) ?>">Editar</a>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <?= esc_html(get_user_meta($operator->getUser()->ID, 'phone_number', true)) ?? '—' ?>
                            </td>
                            <td>
                                <?= esc_html($operator->getUser()->user_login) ?>
                            </td>
                            <td>
                                <?= esc_html($operator->getBusinessPlan()['counter']) ?>
                                de
                                <?= esc_html($operator->getBusinessPlan()['limit']) ?>
                            </td>
                        </tr>
                        <tr id="actions-container-<?= $operator->getUser()->ID ?>" class="git-row-actions">
                            <td colspan="4">
                                <div id="transport-container-<?= $operator->getUser()->ID ?>" class="git-item-container hidden"
                                    data-parent="#actions-container-<?= $operator->getUser()->ID ?>">
                                    <?php foreach ($operator->getTransports() as $transport): ?>
                                        <div class="git-item">
                                            <?= esc_html($transport->nicename) ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div id="coupon-container-<?= $operator->getUser()->ID ?>" class="git-item-container hidden"
                                    data-parent="#actions-container-<?= $operator->getUser()->ID ?>">
                                    <?php foreach ($operator->getCoupons() as $coupon): ?>
                                        <div class="git-item">
                                            <?= esc_html($coupon->post_title) ?>
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