<?php
namespace CentralBooking\WooCommerce\SingleProduct;

use CentralBooking\GUI\ModalComponent;
use CentralBooking\GUI\ComponentInterface;
use CentralBooking\GUI\Constants\ButtonStyleConstants;
use WC_Product;

class FormProduct implements ComponentInterface
{

    public function __construct(private readonly WC_Product $product)
    {
    }

    public function compact()
    {
        $modal = new ModalComponent('Información');
        $modal->add_body(git_string_to_component('<div id="message_form_modal" class="mb-3"></div>'));
        $button_dimiss = $modal->create_button_dimiss('Entendido');
        $button_dimiss->set_style(ButtonStyleConstants::WARNING);
        $button_launch = $modal->create_button_launch();
        $modal->add_body($button_dimiss);
        $button_launch->id = 'button_launch_modal_form';
        $button_launch->styles->set('display', 'none');
        wp_enqueue_style(
            'operator-product-styles',
            CENTRAL_BOOKING_URL . '/assets/css/operator-product-styles.css',
            [],
            null
        );
        wp_enqueue_script(
            'git-form-product',
            CENTRAL_BOOKING_URL . '/assets/js/client/product/product-form.js',
            [],
            null,
            true
        );
        ob_start();
        ?>
        <script> window.CentralTickets = window.CentralTickets || {}; </script>
        <?= $modal->compact() ?>
        <?= $button_launch->compact() ?>
        <div id="overlay_loading" class="overlay">
            <div class="spinner-border text-secondary" role="status"><span class="visually-hidden">Loading...</span></div>
        </div>
        <form id="product_form" class="p-3" method="post" action="<?= admin_url('admin-ajax.php?action=git_product_submit') ?>">
            <input type="hidden" name="nonce" value="<?= wp_create_nonce('git_product_form') ?>">
            <input type="hidden" name="product" value="<?= $this->product->get_id() ?>">
            <?php
            echo (new FormProductRoute($this->product))->compact();
            echo (new FormProductTransport($this->product))->compact();
            echo (new FormProductPassenger())->compact();
            ?>
        </form>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
            crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
        <?php
        return ob_get_clean();
    }
}
