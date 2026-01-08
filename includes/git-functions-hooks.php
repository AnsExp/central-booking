<?php

use CentralBooking\Data\Constants\PassengerConstants;
use CentralBooking\Data\Constants\PriceExtraConstants;
use CentralBooking\WooCommerce\CalculateTicketPrice;
use CentralBooking\WooCommerce\PassengerForm;
use CentralBooking\WooCommerce\ProductSinglePresentation;
use CentralBooking\WooCommerce\ValidateCoupon;

function git_preorder_process(array $data)
{
    // $ticket = [
    //     'trip' => [
    //         'route' => $_POST['route'],
    //         'transport' => $_POST['transport'],
    //         'date_trip' => $_POST['date_trip'],
    //     ],
    //     'pax' => [
    //         PriceExtraConstants::EXTRA->value => 0,
    //         PassengerConstants::KID->value => 0,
    //         PassengerConstants::RPM->value => 0,
    //         PassengerConstants::STANDARD->value => count($_POST['passengers']),
    //     ],
    //     'flexible' => isset($_POST['flexible']),
    //     'passengers' => $_POST['passengers'],
    //     'product' => $_POST['product'],
    // ];
    // $added = WC()->cart->add_to_cart(
    //     $_POST['product'],
    //     1,
    //     0,
    //     [],
    //     ['cart_ticket' => CartItem::create($ticket)]
    // );
    // if ($added) {
    //     wp_safe_redirect(wc_get_cart_url());
    // }
    // exit;
}

function git_validate_coupon(WC_Coupon $coupon)
{
    $validator = new ValidateCoupon();
    return $validator->isValid($coupon);
}

function git_get_price_html_product(WC_Product $product)
{
    if ($product->get_type() !== 'operator') {
        return git_currency_format(0);
    }
    $calculator = new CalculateTicketPrice();
    $prices = $calculator->getPrices($product);
    unset($prices[PriceExtraConstants::FLEXIBLE->value]);
    unset($prices[PriceExtraConstants::EXTRA->value]);
    $max = max(array_values($prices));
    $min = min(array_values($prices));
    $price_html = git_currency_format($min, false) . ' - ' . git_currency_format($max, false);
    return $price_html;
}

function git_get_passenger_form(int $passenger_count): string
{
    $output = '';
    $passenger_form = new PassengerForm();
    for ($i = 0; $i < $passenger_count; $i++) {
        $output .= '<div class="form_passenger mb-5" data-passenger-index="' . $i . '">';
        $output .= $passenger_form->compact();
        $output .= '</div>';
    }
    return $output;
}

/**
 * @param array{
 * flexible:bool,
 * round_trip:bool,
 * passengers:array{type:string,name:string,nationality:string,type_document:string,data_document:string,birthday:string}[],
 * pax:array{kid:int,rpm:int,extra:int,standard:int},
 * goes:array{id_transport:string,id_route:string,date_trip:string},
 * returns:array{id_transport:string,id_route:string,date_trip:string},
 * product:int
 * } $data information submitted from product form
 * @return void
 */
function git_proccess_submit_product_form(array $data)
{
    $flexible = $data['flexible'] ?? false;
    $round_trip = $data['round_trip'] ?? false;
    $productSinglePresentation = new ProductSinglePresentation();
    $passengers = $data['passengers'] ?? [];

    foreach ($passengers as &$passenger) {
        $passenger['type'] = PassengerConstants::STANDARD->value;
    }

    $add1 = true;
    $add2 = true;

    $add1 = $productSinglePresentation->addToCart(
        (int) $data['goes']['id_transport'],
        (int) $data['goes']['id_route'],
        $data['goes']['date_trip'],
        (int) $data['product'],
        $flexible,
        $passengers,
        $data['pax'],
    );

    if ($round_trip) {
        $add2 = $productSinglePresentation->addToCart(
            (int) $data['returns']['id_transport'],
            (int) $data['returns']['id_route'],
            $data['returns']['date_trip'],
            (int) $data['product'],
            $flexible,
            $passengers,
            $data['pax'],
        );
    }

    if ($add1 && $add2) {
        wp_safe_redirect(wc_get_cart_url());
        exit;
    }
}