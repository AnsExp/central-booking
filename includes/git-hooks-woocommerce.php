<?php

use CentralBooking\Placeholders\PlaceholderEngineCheckout;
use CentralBooking\WooCommerce\CreateOrderLineItem;
use CentralBooking\WooCommerce\ProductForm;
use CentralBooking\WooCommerce\ProductItemCart;
use CentralBooking\WooCommerce\Thankyou;
use CentralBooking\WooCommerce\SingleProduct\FormProduct;
use CentralBooking\WooCommerce\SingleProduct\FormProductNotAvailable;
use CentralBooking\WooCommerce\ValidateCoupon;

defined('ABSPATH') || exit;

function git_ajax_single_product_summary()
{
    global $product;
    if ($product instanceof WC_Product_Operator) {
        if ($product->is_purchasable()) {
            (new FormProduct($product))->render();
        } else {
            (new FormProductNotAvailable)->render();
        }
    }
}

function git_ajax_get_item_data($item_data, $cart_item)
{
    $product = wc_get_product($cart_item['product_id']);
    if ($product->get_type() !== 'operator') {
        return $item_data;
    }
    $product_item = new ProductItemCart();
    $item_data = array_merge($item_data, $product_item->itemCart($cart_item));
    return $item_data;
}

function git_ajax_before_calculate_totals($cart_object)
{
    foreach ($cart_object->get_cart() as $cart_item) {
        $product = wc_get_product($cart_item['product_id']);
        if ($product->get_type() !== 'operator') {
            continue;
        }
        $cart_item['data']->set_price($cart_item['cart_ticket']->calculatePrice());
    }
}

function git_ajax_validate_coupon(bool $valid, WC_Coupon $coupon)
{
    $validator = new ValidateCoupon();
    return $validator->isValid($coupon) && $valid;
}

function git_ajax_thankyou_order_received_text($thank_you_text, WC_Order $order)
{
    $message = git_get_setting('message_checkout', '');
    $engine = new PlaceholderEngineCheckout($order);
    $processed_message = $engine->process($message);
    return $processed_message;
}

function git_ajax_checkout_create_order_line_item($item, $cart_item_key, $values, $order)
{
    $create_order_line_item = new CreateOrderLineItem();
    $create_order_line_item->add_line_item($item, $values);
}

function git_ajax_thankyou($order_id)
{
    (new Thankyou)->thankyou($order_id);
}

function git_ajax_product_data_tabs($tabs)
{
    return array_merge($tabs, ProductForm::get_tabs());
}

function git_ajax_product_data_panels()
{
    ProductForm::get_general_panel();
    ProductForm::get_pricing_panel();
    ProductForm::get_inventory_panel();
}

function git_ajax_process_product_meta_operator($post_id)
{
    ProductForm::process_form($post_id);
}

add_action('woocommerce_thankyou', 'git_ajax_thankyou', 10, 1);
add_filter('woocommerce_get_item_data', 'git_ajax_get_item_data', 10, 2);
add_filter('woocommerce_coupon_is_valid', 'git_ajax_validate_coupon', 10, 2);
add_filter('woocommerce_product_data_tabs', 'git_ajax_product_data_tabs', 10, 1);
add_action('woocommerce_product_data_panels', 'git_ajax_product_data_panels', 10, 0);
add_action('woocommerce_single_product_summary', 'git_ajax_single_product_summary', 25, 0);
add_action('woocommerce_before_calculate_totals', 'git_ajax_before_calculate_totals', 10, 1);
add_filter('woocommerce_thankyou_order_received_text', 'git_ajax_thankyou_order_received_text', 10, 2);
add_action('woocommerce_process_product_meta_operator', 'git_ajax_process_product_meta_operator', 10, 1);
add_action('woocommerce_checkout_create_order_line_item', 'git_ajax_checkout_create_order_line_item', 10, 4);
