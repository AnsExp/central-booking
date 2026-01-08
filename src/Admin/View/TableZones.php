<?php
namespace CentralBooking\Admin\View;

use CentralBooking\Admin\AdminRouter;
use CentralBooking\Admin\Form\FormZone;
use CentralBooking\Data\Services\ZoneService;
use CentralBooking\GUI\DisplayerInterface;

final class TableZones implements DisplayerInterface
{
    private ZoneService $zoneService;

    public function __construct()
    {
        $this->zoneService = new ZoneService();
    }

    public function render()
    {
        ?>
        <div style="max-width: 200px;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 200px;" scope="col">Nombre</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->getZones() as $zone): ?>
                        <tr>
                            <td>
                                <span><?= esc_html($zone->name) ?></span>
                                <div class="row-actions visible">
                                    <span class="edit">
                                        ID: <?= $zone->id ?>
                                    </span>
                                    <span> | </span>
                                    <span class="edit">
                                        <a
                                            href="<?= AdminRouter::get_url_for_class(FormZone::class, ['id' => $zone->id]) ?>">Editar</a>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    private function getZones()
    {
        return git_zones();
    }
}