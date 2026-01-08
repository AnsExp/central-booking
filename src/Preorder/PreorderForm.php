<?php
namespace CentralBooking\Preorder;

use CentralBooking\Data\Constants\PassengerConstants;
use CentralBooking\Data\Constants\RouteConstants;
use CentralBooking\Data\Services\TransportService;
use CentralBooking\GUI\ButtonComponent;
use CentralBooking\GUI\CompositeComponent;
use CentralBooking\GUI\SelectComponent;
use CentralBooking\Implementation\GUI\NationalitySelect;
use CentralBooking\Implementation\GUI\TypeDocumentSelect;
use CentralTickets\Preorder\PreorderFormNotData;
use CentralTickets\Preorder\PreorderFormNotProducts;
use WP_Query;

final class PreorderForm extends CompositeComponent
{
    private array $data;
    private array $products;
    private PreorderFormNotData $not_data_panel;
    private PreorderFormNotProducts $not_products_panel;

    public function __construct(private readonly Preorder $preorder)
    {
        parent::__construct('form');
        $this->init();
    }

    private function init()
    {
        $this->products = $this->get_products();
        $this->not_data_panel = new PreorderFormNotData();
        $this->not_products_panel = new PreorderFormNotProducts();
        $this->attributes->set('method', 'post');
        $this->attributes->set('action', admin_url('admin-ajax.php') . '?action=preorder_process');
    }

    private function get_products()
    {
        if (empty($this->preorder->get_routes())) {
            return [];
        }
        $args = [
            'post_type' => 'product',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'type_transport',
                    'value' => $this->preorder->get_routes()[0]->type,
                ],
                [
                    'key' => 'enable_bookeable',
                    'value' => 'yes',
                ],
            ],
        ];
        $query = new WP_Query($args);
        $posts = $query->posts;
        $products = [];
        foreach ($posts as $post) {
            $switch = get_post_meta($post->ID, 'enable_switch_route', true) == 'yes';
            $type_route = get_post_meta($post->ID, 'type_route', true);
            $id_zone_origin = get_post_meta($post->ID, 'zone_origin', true);
            $id_zone_destiny = get_post_meta($post->ID, 'zone_destiny', true);
            $id_location_origin = get_post_meta($post->ID, 'location_origin', true);
            $id_location_destiny = get_post_meta($post->ID, 'location_destiny', true);
            if ($type_route === RouteConstants::BETWEEN_ZONES->value) {
                foreach ($this->preorder->get_routes() as $route) {
                    if (!$switch) {
                        if (
                            $route->getOrigin()->getZone()->id == $id_zone_origin &&
                            $route->getDestiny()->getZone()->id == $id_zone_destiny
                        ) {
                            $products[] = $post;
                        }
                    } else {
                        if (
                            ($route->getOrigin()->getZone()->id == $id_zone_origin &&
                                $route->getDestiny()->getZone()->id == $id_zone_destiny) ||
                            ($route->getDestiny()->getZone()->id == $id_zone_origin &&
                                $route->getOrigin()->getZone()->id == $id_zone_destiny)
                        ) {
                            $products[] = $post;
                        }
                    }
                }
            } elseif ($type_route === RouteConstants::BETWEEN_LOCATIONS) {
                foreach ($this->preorder->get_routes() as $route) {
                    if (!$switch) {
                        if (
                            $route->getOrigin()->id == $id_location_origin &&
                            $route->getDestiny()->id == $id_location_destiny
                        ) {
                            $products[] = $post;
                        }
                    } else {
                        if (
                            ($route->getOrigin()->id == $id_location_origin &&
                                $route->getDestiny()->id == $id_location_destiny) ||
                            ($route->getDestiny()->id == $id_location_origin &&
                                $route->getOrigin()->id == $id_location_destiny)
                        ) {
                            $products[] = $post;
                        }
                    }
                }
            }
        }
        return $products;
    }

    private function fields()
    {
        $transport_select = $this->get_transport_select();
        $nationality_select = (new NationalitySelect('passenger_nationality'))->create();
        $type_document_select = (new TypeDocumentSelect('passenger_type_document'))->create();
        $passegers_info = $this->preorder->passengers_info;
        if ($this->preorder->get_transport()) {
            $transport_select->setValue($this->preorder->get_transport()->id);
        }
        ob_start();
        ?>
        <h2 class="my-3">Número de preorden #<?= $this->preorder->get_order()->get_id() ?></h2>
        <input type="hidden" name="product" value="<?= $this->products[0]->ID ?>">
        <input type="hidden" name="route" value="<?= $this->preorder->get_routes()[0]->id ?>">
        <input type="hidden" name="preorder" value="<?= $this->preorder->get_order()->get_id() ?>">
        <input type="hidden" name="date_trip" value="<?= $this->preorder->date_trip->format('Y-m-d') ?>">
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <td>Trayecto</td>
                    <td><?= $this->preorder->get_routes()[0]->getOrigin()->name . ' <i class="bi bi-arrow-right"></i> ' . $this->preorder->get_routes()[0]->getDestiny()->name ?>
                    </td>
                </tr>
                <tr>
                    <td>Viaje</td>
                    <td>
                        <?= git_date_format($this->preorder->date_trip->format('Y-m-d')) . ' ' . git_time_format($this->preorder->get_routes()[0]->getDepartureTime()->format()) ?>
                    </td>
                </tr>
                <tr>
                    <td>Transporte</td>
                    <td>
                        <?= $transport_select->compact() ?>
                    </td>
                </tr>
                <tr>
                    <td>Pasajeros</td>
                    <td>
                        <?php for ($i = 0; $i < $this->preorder->pax; $i++): ?>
                            <?= $i === 0 ? '' : '<hr>' ?>
                            <p class="my-2 fw-bold">Pasajero <?= $i + 1 ?></p>
                            <input type="hidden" name="passengers[<?= $i ?>][type]"
                                value="<?= PassengerConstants::STANDARD->value ?>">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="form-floating">
                                                <input class="form-control" name="passengers[<?= $i ?>][name]"
                                                    placeholder="Nombre del pasajero" type="text"
                                                    value="<?= isset($passegers_info[$i]) ? esc_attr($passegers_info[$i]['name'] ?? '') : '' ?>"
                                                    required>
                                                <label class="form-label">Nombre del pasajero</label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="form-floating">
                                                <?php
                                                $nationality_select->attributes->set('name', "passengers[{$i}][nationality]");
                                                $nationality_select->setValue(isset($passegers_info[$i]) ? esc_attr($passegers_info[$i]['nationality'] ?? '') : '');
                                                $nationality_select->render();
                                                ?>
                                                <label class="form-label">Nacionalidad</label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="form-floating">
                                                <?php
                                                $type_document_select->attributes->set('name', "passengers[{$i}][type_document]");
                                                $type_document_select->setValue(isset($passegers_info[$i]) ? esc_attr($passegers_info[$i]['type_document'] ?? '') : '');
                                                $type_document_select->render();
                                                ?>
                                                <label class="form-label">Tipo de documento</label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="form-floating">
                                                <input class="form-control" name="passengers[<?= $i ?>][data_document]"
                                                    placeholder="Número de documento" type="text"
                                                    value="<?= isset($passegers_info[$i]) ? esc_attr($passegers_info[$i]['data_document'] ?? '') : '' ?>"
                                                    required>
                                                <label class="form-label">Número de documento</label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="form-floating">
                                                <input class="form-control" name="passengers[<?= $i ?>][birthday]"
                                                    placeholder="Fecha de nacimiento" type="date"
                                                    value="<?= isset($passegers_info[$i]) ? esc_attr($passegers_info[$i]['birthday'] ?? '') : '' ?>"
                                                    required>
                                                <label class="form-label">Fecha de nacimiento</label>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php endfor; ?>
                    </td>
                </tr>
                <tr>
                    <td>Servicios extras</td>
                    <td>
                        <label for="checkbox-flexible">Flexible</label>
                        <input id="checkbox-flexible" type="checkbox" name="flexible" checked>
                    </td>
                </tr>
                <tr>
                    <td>Productos similares</td>
                    <td>
                        <ul>
                            <?php foreach ($this->products as $product): ?>
                                <li><a href="<?= get_permalink($product->ID) ?>"><?= $product->post_title ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }

    public function get_transport_select()
    {
        $select = new SelectComponent('transport');
        $transports = $this->preorder->get_routes()[0]->getTransports();
        $select->setRequired(true);
        $select->addOption('Seleccione...', '');
        foreach ($transports as $transport) {
            $is_available = git_transport_check_availability(
                $transport->id,
                $this->preorder->get_routes()[0]->id,
                git_date_create($this->preorder->date_trip->format('Y-m-d')),
                $this->preorder->pax,
            );
            if ($is_available) {
                $select->addOption($transport->nicename, $transport->id);
            }
        }
        return $select;
    }

    public function compact()
    {
        if ($this->preorder === null) {
            return $this->not_data_panel->compact();
        } elseif (empty($this->products)) {
            return $this->not_products_panel->compact();
        }
        $this->addChild(git_string_to_component('<div class="container">'));
        $this->addChild(git_string_to_component($this->fields()));
        $this->addChild($this->get_submit_button('Confirmar Preorden'));
        $this->addChild(git_string_to_component('</div>'));
        return parent::compact();
    }

    private function get_submit_button(string $text)
    {
        $button = new ButtonComponent($text);
        $button->class_list->add('btn btn-primary my-3');
        return $button;
    }
}
