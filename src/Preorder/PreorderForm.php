<?php
namespace CentralTickets\Preorder;

use CentralTickets\CartTicket;
use CentralTickets\Components\FormComponent;
use CentralTickets\Components\Implementation\NationalitySelect;
use CentralTickets\Components\Implementation\TypeDocumentSelect;
use CentralTickets\Components\SelectComponent;
use CentralTickets\Constants\PassengerConstants;
use CentralTickets\Constants\PriceExtraConstants;
use CentralTickets\Constants\RouteConstants;
use CentralTickets\Services\TransportService;
use WP_Query;

function preorder_process()
{
    $ticket = [
        'trip' => [
            'route' => $_POST['route'],
            'transport' => $_POST['transport'],
            'date_trip' => $_POST['date_trip'],
        ],
        'pax' => [
            PriceExtraConstants::EXTRA => 0,
            PassengerConstants::KID => 0,
            PassengerConstants::RPM => 0,
            PassengerConstants::STANDARD => count($_POST['passengers']),
        ],
        'flexible' => isset($_POST['flexible']),
        'passengers' => $_POST['passengers'],
        'product' => $_POST['product'],
    ];
    $added = WC()->cart->add_to_cart(
        $_POST['product'],
        1,
        0,
        [],
        ['cart_ticket' => CartTicket::create($ticket)]
    );
    if ($added) {
        wp_safe_redirect(wc_get_cart_url());
    }
    exit;
}

add_action('wp_ajax_preorder_process', function () {
    preorder_process();
});
add_action('wp_ajax_nopriv_preorder_process', function () {
    preorder_process();
});

class PreorderForm extends FormComponent
{
    private array $data;
    private array $products;
    private PreorderFormNotData $not_data_panel;
    private PreorderFormNotProducts $not_products_panel;

    public function __construct(private readonly Preorder $preorder)
    {
        parent::__construct();
        $this->init();
    }

    private function init()
    {
        $this->products = $this->get_products();
        $this->not_data_panel = new PreorderFormNotData();
        $this->not_products_panel = new PreorderFormNotProducts();
        $this->set_method('post');
        $this->set_action(admin_url('admin-ajax.php') . '?action=preorder_process');
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
            if ($type_route === RouteConstants::BETWEEN_ZONES) {
                foreach ($this->preorder->get_routes() as $route) {
                    if (!$switch) {
                        if (
                            $route->get_origin()->get_zone()->id == $id_zone_origin &&
                            $route->get_destiny()->get_zone()->id == $id_zone_destiny
                        ) {
                            $products[] = $post;
                        }
                    } else {
                        if (
                            ($route->get_origin()->get_zone()->id == $id_zone_origin &&
                                $route->get_destiny()->get_zone()->id == $id_zone_destiny) ||
                            ($route->get_destiny()->get_zone()->id == $id_zone_origin &&
                                $route->get_origin()->get_zone()->id == $id_zone_destiny)
                        ) {
                            $products[] = $post;
                        }
                    }
                }
            } elseif ($type_route === RouteConstants::BETWEEN_LOCATIONS) {
                foreach ($this->preorder->get_routes() as $route) {
                    if (!$switch) {
                        if (
                            $route->get_origin()->id == $id_location_origin &&
                            $route->get_destiny()->id == $id_location_destiny
                        ) {
                            $products[] = $post;
                        }
                    } else {
                        if (
                            ($route->get_origin()->id == $id_location_origin &&
                                $route->get_destiny()->id == $id_location_destiny) ||
                            ($route->get_destiny()->id == $id_location_origin &&
                                $route->get_origin()->id == $id_location_destiny)
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
            $transport_select->set_value($this->preorder->get_transport()->id);
        }
        ob_start();
        ?>
        <h2 class="my-3">Número de preorden #<?= $this->preorder->get_order()->get_id() ?></h2>
        <input type="hidden" name="product" value="<?= $this->products[0]->ID ?>">
        <input type="hidden" name="route" value="<?= $this->preorder->get_routes()[0]->id ?>">
        <input type="hidden" name="preorder" value="<?= $this->preorder->get_order()->get_id() ?>">
        <input type="hidden" name="date_trip" value="<?= $this->preorder->date_trip ?>">
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <td>Trayecto</td>
                    <td><?= $this->preorder->get_routes()[0]->get_origin()->name . ' <i class="bi bi-arrow-right"></i> ' . $this->preorder->get_routes()[0]->get_destiny()->name ?>
                    </td>
                </tr>
                <tr>
                    <td>Viaje</td>
                    <td>
                        <?= git_date_format($this->preorder->date_trip) . ' ' . git_time_format($this->preorder->get_routes()[0]->departure_time) ?>
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
                            <input type="hidden" name="passengers[<?= $i ?>][type]" value="<?= PassengerConstants::STANDARD ?>">
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
                                                $nationality_select->set_attribute('name', "passengers[{$i}][nationality]");
                                                $nationality_select->set_value(isset($passegers_info[$i]) ? esc_attr($passegers_info[$i]['nationality'] ?? '') : '');
                                                $nationality_select->display();
                                                ?>
                                                <label class="form-label">Nacionalidad</label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="form-floating">
                                                <?php
                                                $type_document_select->set_attribute('name', "passengers[{$i}][type_document]");
                                                $type_document_select->set_value(isset($passegers_info[$i]) ? esc_attr($passegers_info[$i]['type_document'] ?? '') : '');
                                                $type_document_select->display();
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
        $transports = $this->preorder->get_routes()[0]->get_transports();
        $service = new TransportService();
        $select->set_required(true);
        $select->add_option('Seleccione...', '');
        foreach ($transports as $transport) {
            $is_available = $service->check_availability(
                $transport->id,
                $this->preorder->get_routes()[0]->id,
                $this->preorder->date_trip,
                $this->preorder->pax,
            );
            if ($is_available) {
                $select->add_option($transport->nicename, $transport->id);
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
        $this->add_child(git_string_to_component('<div class="container">'));
        $this->add_child(git_string_to_component($this->fields()));
        $this->add_child($this->get_submit_button('Confirmar Preorden'));
        $this->add_child(git_string_to_component('</div>'));
        return parent::compact();
    }
}
