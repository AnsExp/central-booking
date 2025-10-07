<?php
namespace CentralTickets\WooCommerce;

use CentralTickets\Components\Component;
use CentralTickets\Components\Implementation\DateTripInput;
use CentralTickets\Components\InputFloatingLabelComponent;
use CentralTickets\Components\SelectComponent;
use CentralTickets\Constants\TypeWayConstants;
use CentralTickets\Persistence\RouteRepository;
use WC_Product;

class FormProductRoute implements Component
{
    public function __construct(private WC_Product $product)
    {
    }

    public static function query_routes(array $args)
    {
        $filter = [];
        if (isset($args['name_zone_origin'])) {
            $filter['name_zone_origin'] = $args['name_zone_origin'];
        }
        if (isset($args['name_zone_destiny'])) {
            $filter['name_zone_destiny'] = $args['name_zone_destiny'];
        }
        $repository = new RouteRepository();
        $routes = $repository->find_by($filter);

        if (!isset($args['schedule'])) {
            return $routes;
        }

        $results = [];
        foreach ($routes as $route) {
            $hour = intval(substr($route->departure_time, 0, 2));
            if (
                ($args['schedule'] === 'morning' && $hour >= 0 && $hour < 12) ||
                ($args['schedule'] === 'afternoon' && $hour >= 12 && $hour < 24)
            ) {
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

        $origin = git_get_zone_by_id((int) $zone_origin)->name;
        $destiny = git_get_zone_by_id((int) $zone_destiny)->name;

        foreach ([$schedule_goes, $schedule_returns] as $schedule_select) {
            $schedule_select->add_option('Mañana', 'morning');
            $schedule_select->add_option('Tarde', 'afternoon');
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
                'typeWay' => $type_way === TypeWayConstants::DOUBLE_WAY ? TypeWayConstants::DOUBLE_WAY : TypeWayConstants::ONE_WAY,
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
                <?php if ($type_way === TypeWayConstants::ONE_WAY): ?>
                    <input id="<?= $id_radio_one_way ?>" class="form-control btn-check" name="type_way" type="radio"
                        value="<?= TypeWayConstants::ONE_WAY ?>" checked>
                    <label class="btn btn-outline-primary m-1"
                        for="<?= $id_radio_one_way ?>"><?= git_get_text_by_way(TypeWayConstants::ONE_WAY) ?></label>
                <?php elseif ($type_way === TypeWayConstants::DOUBLE_WAY): ?>
                    <input id="<?= $id_radio_double_way ?>" class="form-control btn-check" name="type_way" type="radio"
                        value="<?= TypeWayConstants::DOUBLE_WAY ?>">
                    <label class="btn btn-outline-primary m-1" for="<?= $id_radio_double_way ?>"
                        checked><?= git_get_text_by_way(TypeWayConstants::DOUBLE_WAY) ?></label>
                <?php elseif ($type_way === TypeWayConstants::ANY_WAY): ?>
                    <input id="<?= $id_radio_one_way ?>" class="form-control btn-check" name="type_way" type="radio"
                        value="<?= TypeWayConstants::ONE_WAY ?>" checked>
                    <label class="btn btn-outline-primary m-1"
                        for="<?= $id_radio_one_way ?>"><?= git_get_text_by_way(TypeWayConstants::ONE_WAY) ?></label>
                    <input id="<?= $id_radio_double_way ?>" class="form-control btn-check" name="type_way" type="radio"
                        value="<?= TypeWayConstants::DOUBLE_WAY ?>">
                    <label class="btn btn-outline-primary m-1"
                        for="<?= $id_radio_double_way ?>"><?= git_get_text_by_way(TypeWayConstants::DOUBLE_WAY) ?></label>
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
                <p class="fs-5">Horario de Ida</p>
                <?php
                $floating_date_trip_goes = new InputFloatingLabelComponent($date_trip_goes, 'Fecha');
                $floating_schedule_goes = new InputFloatingLabelComponent($schedule_goes, 'Hora');
                $floating_date_trip_goes->display();
                $floating_schedule_goes->display();
                ?>
            </div>
            <?php if ($type_way !== TypeWayConstants::ONE_WAY): ?>
                <div id="<?= $id_container_trip_returns ?>" class="p-2"
                    style="display: <?= $type_way === TypeWayConstants::ANY_WAY ? 'none' : '' ?>;">
                    <p class="fs-5">Horario de Vuelta</p>
                    <?php
                    $floating_date_trip_returns = new InputFloatingLabelComponent($date_trip_returns, 'Fecha');
                    $floating_schedule_returns = new InputFloatingLabelComponent($schedule_returns, 'Hora');
                    $floating_date_trip_returns->display();
                    $floating_schedule_returns->display();
                    ?>
                </div>
            <?php endif; ?>
            <button id="<?= $id_button_next ?>" class="btn btn-primary" type="button">Continuar Reserva</button>
        </div>
        <?php
        return ob_get_clean();
    }
}
