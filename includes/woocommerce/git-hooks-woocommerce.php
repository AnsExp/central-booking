<?php

use CentralTickets\Placeholders\PlaceholderEngineCheckout;
use CentralTickets\Services\ArrayParser\TransportArray;
use CentralTickets\Thankyou;
use CentralTickets\ProductForm;
use CentralTickets\PassengerForm;
use CentralTickets\Transport;
use CentralTickets\ValidateCoupon;
use CentralTickets\ProductItemCart;
use CentralTickets\CreateOrderLineItem;
use CentralTickets\CalculateTicketPrice;
use CentralTickets\ProductSinglePresentation;
use CentralTickets\Constants\PriceExtraConstants;
use CentralTickets\WooCommerce\FormProduct;
use CentralTickets\WooCommerce\FormProductTransport;
use CentralTickets\WooCommerce\FormProductNotAvailable;

add_action('wp_ajax_nopriv_git_passenger_form_html', function () {
    $passenger_count = $_POST['passengers_count'] ?? 1;
    wp_send_json_success([
        'output' => (new PassengerForm($passenger_count))->compact()
    ]);
});

add_action('wp_ajax_git_passenger_form_html', function () {
    $passenger_count = $_POST['passengers_count'] ?? 1;
    wp_send_json_success([
        'output' => (new PassengerForm($passenger_count))->compact()
    ]);
});

add_action('woocommerce_single_product_summary', function () {
    global $product;
    if ($product->get_type() === 'operator') {
        if ($product->get_meta('enable_bookeable', true) !== 'yes') {
            echo (new FormProductNotAvailable)->compact();
        } else {
            echo $product->get_description();
            echo (new FormProduct($product))->compact();
        }
    }
}, 25);

add_filter('woocommerce_get_price_html', function ($price_html, $product) {
    if ($product->get_type() !== 'operator') {
        return $price_html;
    }
    $calculator = new CalculateTicketPrice;
    $prices = $calculator->get_prices($product);
    unset($prices[PriceExtraConstants::FLEXIBLE]);
    unset($prices[PriceExtraConstants::EXTRA]);
    $max = max(array_values($prices));
    $min = min(array_values($prices));
    $price_html = git_currency_format($min, false) . ' - ' . git_currency_format($max, false);
    return $price_html;

}, 10, 2);

add_filter('woocommerce_get_item_data', function ($item_data, $cart_item) {
    $product_item = new ProductItemCart;
    $item_data = array_merge($item_data, $product_item->is_valid($cart_item));
    return $item_data;
}, 10, 2);

add_action('woocommerce_before_calculate_totals', function ($cart_object) {
    foreach ($cart_object->get_cart() as $cart_item) {
        $product = wc_get_product($cart_item['product_id']);
        if ($product->get_type() !== 'operator') {
            continue;
        }
        $cart_item['data']->set_price($cart_item['cart_ticket']->calculate_price());
    }
});

add_filter('woocommerce_coupon_is_valid', function ($valid, $coupon) {
    $validator = new ValidateCoupon;
    if ($validator->get_operator($coupon) === false || $validator->is_valid($coupon)) {
        return $valid;
    }
    return false;
}, 10, 2);

add_filter('woocommerce_thankyou_order_received_text', function ($thank_you_text, WC_Order $order) {
    $message = git_get_setting('message_checkout', '');
    $engine = new PlaceholderEngineCheckout($order);
    $processed_message = $engine->process($message);
    $thank_you_text = $processed_message;
    return $thank_you_text;
}, 10, 2);

add_action('woocommerce_checkout_create_order_line_item', function ($item, $cart_item_key, $values, $order) {
    $create_order_line_item = new CreateOrderLineItem();
    $create_order_line_item->add_line_item($item, $values);
}, 10, 4);

add_action('woocommerce_thankyou', function ($order_id) {
    $thankyou = new Thankyou();
    $thankyou->thankyou($order_id);
}, 10, 1);

add_filter('woocommerce_product_data_tabs', function ($tabs) {
    return array_merge($tabs, ProductForm::get_tabs());
});

add_action('woocommerce_product_data_panels', function () {
    echo ProductForm::get_general_panel()->compact();
    echo ProductForm::get_pricing_panel()->compact();
    echo ProductForm::get_inventory_panel()->compact();
});

add_action('woocommerce_process_product_meta_operator', function ($post_id) {
    ProductForm::process_form($post_id);
});

add_action('wp_ajax_git_product_submit', function () {
    if (!wp_verify_nonce($_POST["nonce"], "git_product_form")) {
        return;
    }
    $var = new ProductSinglePresentation;
    $var->add_to_cart($_POST);
});

add_action('wp_ajax_nopriv_git_product_submit', function () {
    if (!wp_verify_nonce($_POST["nonce"], "git_product_form")) {
        return;
    }
    $var = new ProductSinglePresentation;
    $var->add_to_cart($_POST);
});

add_action('wp_ajax_git_fetch_transports', function () {
    $response = new TransportArray();
    wp_send_json_success(array_map(
        fn(Transport $transport) => $response->get_array($transport),
        FormProductTransport::query_transports($_POST)
    ));
});

add_action('wp_ajax_nopriv_git_fetch_transports', function () {
    $response = new TransportArray;
    wp_send_json_success(array_map(
        fn($transport) => $response->get_array($transport),
        FormProductTransport::query_transports($_POST)
    ));
});
