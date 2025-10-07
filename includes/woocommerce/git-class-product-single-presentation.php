<?php
namespace CentralTickets;

use CentralTickets\Constants\TypeWayConstants;

final class ProductSinglePresentation
{
    public function add_to_cart(array $data)
    {
        $product = [
            'trip' => [
                'route' => intval($data['trip']['goes']['route']),
                'transport' => intval($data['trip']['goes']['transport']),
                'date_trip' => $data['trip']['goes']['date'],
            ],
            'pax' => $data['pax'],
            'passengers' => $data['passengers'],
            'flexible' => isset($data['flexible']),
            'product' => $data['product'],
        ];

        $added = WC()->cart->add_to_cart(
            $data['product'],
            1,
            0,
            [],
            [
                'cart_ticket' => CartTicket::create($product),
                'direction' => 'goes'
            ],
        );

        if ($data['type_way'] === TypeWayConstants::DOUBLE_WAY) {
            $product['trip']['route'] = intval($data['trip']['returns']['route']);
            $product['trip']['transport'] = intval($data['trip']['returns']['transport']);
            $product['trip']['date_trip'] = $data['trip']['returns']['date'];

            $added = WC()->cart->add_to_cart(
                $data['product'],
                1,
                0,
                [],
                [
                    'cart_ticket' => CartTicket::create($product),
                    'direction' => 'returns'
                ],
            );
        }

        // echo git_serialize($data);
        if ($added) {
            wp_safe_redirect(wc_get_cart_url());
            exit;
        }
    }
}