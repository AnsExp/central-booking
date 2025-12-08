<?php
namespace CentralTickets\Admin\View;

use CentralTickets\Admin\AdminRouter;
use CentralTickets\Admin\Form\FormOperator;
use CentralTickets\Components\Displayer;
use CentralTickets\Operator;

final class TableOperators implements Displayer
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
        return git_get_query_persistence()->get_operator_repository()->find_by();
    }

    public function display()
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
                                <span><?= esc_html($operator->first_name . ' ' . $operator->last_name) ?></span>
                                <div class="row-actions visible">
                                    <span class="edit">
                                        ID: <?= esc_html($operator->ID) ?>
                                    </span>
                                    <span>|</span>
                                    <span class="edit">
                                        <a href="#transport-container-<?= $operator->ID ?>" class="git-row-action-link"
                                            data-route="<?= esc_attr($operator->ID) ?>">
                                            Transportes (<?= count($operator->get_transports()) ?>)
                                        </a>
                                    </span>
                                    <span>|</span>
                                    <span class="edit">
                                        <a href="#coupon-container-<?= $operator->ID ?>" class="git-row-action-link"
                                            data-route="<?= esc_attr($operator->ID) ?>">
                                            Cupones (<?= count($operator->get_coupons()) ?>)
                                        </a>
                                    </span>
                                    <span>|</span>
                                    <span class="edit">
                                        <a
                                            href="<?= esc_url(AdminRouter::get_url_for_class(FormOperator::class, ['id' => $operator->ID])) ?>">Editar</a>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <?= esc_html(get_user_meta($operator->ID, 'phone_number', true)) ?? '—' ?>
                            </td>
                            <td>
                                <?= esc_html($operator->user_login) ?>
                            </td>
                            <td>
                                <?= esc_html($operator->get_business_plan()['counter']) ?>
                                de
                                <?= esc_html($operator->get_business_plan()['limit']) ?>
                            </td>
                        </tr>
                        <tr id="actions-container-<?= $operator->ID ?>" class="git-row-actions">
                            <td colspan="4">
                                <div id="transport-container-<?= $operator->ID ?>" class="git-item-container hidden"
                                    data-parent="#actions-container-<?= $operator->ID ?>">
                                    <?php foreach ($operator->get_transports() as $transport): ?>
                                        <div class="git-item">
                                            <?= esc_html($transport->nicename) ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div id="coupon-container-<?= $operator->ID ?>" class="git-item-container hidden"
                                    data-parent="#actions-container-<?= $operator->ID ?>">
                                    <?php foreach ($operator->get_coupons() as $coupon): ?>
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