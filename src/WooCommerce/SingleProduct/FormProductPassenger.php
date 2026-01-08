<?php
namespace CentralBooking\WooCommerce\SingleProduct;

use CentralBooking\GUI\ComponentInterface;

class FormProductPassenger implements ComponentInterface
{
    public function __construct()
    {
    }

    public function compact()
    {
        $id_prev_button = uniqid();
        $id_submit_button = uniqid();
        $id_passengers_form_container = uniqid();
        wp_enqueue_script(
            'pane-form-passenger',
            CENTRAL_BOOKING_URL . '/assets/js/client/product/pane-form-passengers.js',
            ['jquery'],
        );
        wp_localize_script('pane-form-passenger', 'dataPassenger', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'hook' => 'git_passenger_form_html',
            'elements' => [
                'prevButton' => $id_prev_button,
                'submitButton' => $id_submit_button,
                'passengersFormContainer' => $id_passengers_form_container,
            ],
        ]);
        ob_start();
        ?>
        <div id="git-form-product-passengers" style="display: none;">
            <div id="<?= $id_passengers_form_container ?>"></div>
            <div class="mt-2">
                <button id="<?= $id_prev_button ?>" class="me-2 btn btn-secondary" type="button">Atrás</button>
                <button id="<?= $id_submit_button ?>" class="btn btn-primary" type="submit">Reservar</button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
