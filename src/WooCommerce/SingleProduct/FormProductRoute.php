<?php
namespace CentralBooking\WooCommerce\SingleProduct;

use CentralBooking\Data\Constants\TypeWayConstants;
use CentralBooking\Data\Route;
use CentralBooking\Data\Services\RouteService;
use CentralBooking\GUI\ComponentInterface;
use CentralBooking\GUI\InputFloatingLabelComponent;
use CentralBooking\GUI\SelectComponent;
use CentralBooking\Implementation\GUI\DateTripInput;
use WC_Product;

class FormProductRoute implements ComponentInterface
{
    public function __construct(private WC_Product $product)
    {
    }

    /**
     * @param array $args
     * @return array<Route>
     */
    public static function query_routes(array $args)
    {
        $zoneOrigin = git_zone_by_name($args['name_zone_origin'] ?? '');
        $zoneDestiny = git_zone_by_name($args['name_zone_destiny'] ?? '');
        if (!$zoneOrigin || !$zoneDestiny) {
            return [];
        }

        $service = new RouteService();
        $routes = [];

        foreach ($zoneOrigin->getLocations() as $locationOrigin) {
            foreach ($zoneDestiny->getLocations() as $locationDestiny) {
                $filter = [
                    'id_origin' => $locationOrigin->id,
                    'id_destiny' => $locationDestiny->id,
                ];
                $routes = array_merge($routes, $service->find($filter)->getItems());
            }
        }

        if (!isset($args['schedule'])) {
            return $routes;
        }

        $results = [];

        foreach ($routes as $route) {
            $hour = intval(substr($route->getDepartureTime()->format('H:i:s'), 0, 2));
            if (($args['schedule'] === 'morning' && $hour >= 0 && $hour < 12)) {
                $results[] = $route;
            } else if (($args['schedule'] === 'afternoon' && $hour >= 12 && $hour < 24)) {
                $results[] = $route;
            }
        }
        return $results;
    }

    public function compact()
    {
        $origin = '';
        $destiny = '';
        $type_way = $this->product->get_meta('type_way', true);
        $switch = $this->product->get_meta('enable_switch_route', true) == 'yes';

        $date_trip_goes = (new DateTripInput('date_trip_goes'))->create();
        $date_trip_returns = (new DateTripInput('date_trip_returns'))->create();
        $schedule_goes = new SelectComponent('schedule_goes');
        $schedule_returns = new SelectComponent('schedule_returns');

        $id_radio_group = 'radio_group_' . rand();
        $id_button_next = 'button_next_' . rand();
        $id_switch_button = 'switch_button_' . rand();
        $id_radio_one_way = 'radio_one_way_' . rand();
        $id_origin_label = 'location_label_' . rand();
        $id_destiny_label = 'location_label_' . rand();
        $id_radio_double_way = 'radio_double_way_' . rand();
        $id_container_trip_goes = 'container_trip_goes_' . rand();
        $id_container_trip_returns = 'container_trip_returns_' . rand();

        $zone_origin = $this->product->get_meta('zone_origin', true);
        $zone_destiny = $this->product->get_meta('zone_destiny', true);

        $origin = git_zone_by_id((int) $zone_origin)->name;
        $destiny = git_zone_by_id((int) $zone_destiny)->name;

        foreach ([$schedule_goes, $schedule_returns] as $schedule_select) {
            $schedule_select->addOption('Mañana', 'morning');
            $schedule_select->addOption('Tarde', 'afternoon');
        }

        wp_enqueue_script(
            'pane-form-route',
            CENTRAL_BOOKING_URL . '/assets/js/client/product/pane-form-route.js',
            [],
            null
        );

        wp_localize_script(
            'pane-form-route',
            'dataRoute',
            [
                'origin' => $origin,
                'destiny' => $destiny,
                'typeWay' => $type_way === TypeWayConstants::DOUBLE_WAY->value ? TypeWayConstants::DOUBLE_WAY->value : TypeWayConstants::ONE_WAY->value,
                'elements' => [
                        'idButtonNext' => $id_button_next,
                        'idOriginLabel' => $id_origin_label,
                        'idRadioOneWay' => $id_radio_one_way,
                        'idSwitchButton' => $id_switch_button,
                        'idDestinyLabel' => $id_destiny_label,
                        'idDateTripGoes' => $date_trip_goes->id,
                        'idScheduleGoes' => $schedule_goes->id,
                        'idRadioDoubleWay' => $id_radio_double_way,
                        'idDateTripReturns' => $date_trip_returns->id,
                        'idScheduleReturns' => $schedule_returns->id,
                        'idContainerTripGoes' => $id_container_trip_goes,
                        'idContainerTripReturns' => $id_container_trip_returns,
                    ],
            ]
        );
        ob_start();
        ?>
        <div id="git-form-product-route">
            <div id="<?= $id_radio_group ?>">
                <?php if ($type_way === TypeWayConstants::ONE_WAY->value): ?>
                    <input id="<?= $id_radio_one_way ?>" class="form-control btn-check" name="type_way" type="radio"
                        value="<?= TypeWayConstants::ONE_WAY->value ?>" checked>
                    <label class="btn btn-outline-primary m-1"
                        for="<?= $id_radio_one_way ?>"><?= TypeWayConstants::ONE_WAY->label() ?></label>
                <?php elseif ($type_way === TypeWayConstants::DOUBLE_WAY->value): ?>
                    <input id="<?= $id_radio_double_way ?>" class="form-control btn-check" name="type_way" type="radio"
                        value="<?= TypeWayConstants::DOUBLE_WAY->value ?>" checked>
                    <label class="btn btn-outline-primary m-1" for="<?= $id_radio_double_way ?>"
                        checked><?= TypeWayConstants::DOUBLE_WAY->label() ?></label>
                <?php elseif ($type_way === TypeWayConstants::ANY_WAY->value): ?>
                    <input id="<?= $id_radio_one_way ?>" class="form-control btn-check" name="type_way" type="radio"
                        value="<?= TypeWayConstants::ONE_WAY->value ?>" checked>
                    <label class="btn btn-outline-primary m-1"
                        for="<?= $id_radio_one_way ?>"><?= TypeWayConstants::ONE_WAY->label() ?></label>
                    <input id="<?= $id_radio_double_way ?>" class="form-control btn-check" name="type_way" type="radio"
                        value="<?= TypeWayConstants::DOUBLE_WAY->value ?>">
                    <label class="btn btn-outline-primary m-1"
                        for="<?= $id_radio_double_way ?>"><?= TypeWayConstants::DOUBLE_WAY->label() ?></label>
                <?php endif; ?>
            </div>
            <div class="my-3" style="display: flex">
                <div class="w-50 text-start">
                    <span id="<?= $id_origin_label ?>" class="fs-5 px-5"><?= $origin ?></span>
                </div>
                <?php if ($switch): ?>
                    <button id="<?= $id_switch_button ?>" class="btn btn-outline-primary" type="button" data-bs-toggle="tooltip"
                        data-bs-placement="top" data-bs-title="Cambiar ruta" style="cursor: pointer;">
                        <i class="bi bi-arrow-left-right"></i>
                    </button>
                <?php endif; ?>
                <div class="w-50 text-end">
                    <span id="<?= $id_destiny_label ?>" class="fs-5 px-5"><?= $destiny ?></span>
                </div>
            </div>
            <div id="<?= $id_container_trip_goes ?>" class="p-2">
                <p class="fs-5">Fecha y hora de ida</p>
                <?php
                $floating_date_trip_goes = new InputFloatingLabelComponent($date_trip_goes, 'Fecha');
                $floating_schedule_goes = new InputFloatingLabelComponent($schedule_goes, 'Hora');
                $floating_date_trip_goes->render();
                $floating_schedule_goes->render();
                ?>
            </div>
            <?php if ($type_way !== TypeWayConstants::ONE_WAY): ?>
                <div id="<?= $id_container_trip_returns ?>" class="p-2"
                    style="display: <?= $type_way === TypeWayConstants::ANY_WAY ? 'none' : '' ?>;">
                    <p class="fs-5">Fecha y hora de vuelta</p>
                    <?php
                    $floating_date_trip_returns = new InputFloatingLabelComponent($date_trip_returns, 'Fecha');
                    $floating_schedule_returns = new InputFloatingLabelComponent($schedule_returns, 'Hora');
                    $floating_date_trip_returns->render();
                    $floating_schedule_returns->render();
                    ?>
                </div>
            <?php endif; ?>
            <button id="<?= $id_button_next ?>" class="btn btn-primary" type="button">Continuar Reserva</button>
        </div>
        <?php
        return ob_get_clean();
    }
}
