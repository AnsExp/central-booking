<?php
namespace CentralTickets\Components\Implementation;

use CentralTickets\Components\MultipleSelectComponent;
use CentralTickets\Components\SelectComponent;

class CouponSelect
{
    private array $coupons;
    public function __construct(private string $name = 'coupon', ?int $operator = null)
    {
        $args = [
            'post_type' => 'shop_coupon',
            'posts_per_page' => -1,
        ];

        if ($operator !== null) {
            $args['meta_query'] = [
                [
                    'key' => 'coupon_assigned_operator',
                    'value' => $operator,
                    'compare' => '='
                ]
            ];
        }

        $this->coupons = get_posts($args);
    }

    public function create(bool $multiple = false)
    {

        $selectComponent = $multiple
            ? new MultipleSelectComponent($this->name)
            : new SelectComponent($this->name);

        $selectComponent->add_option('Seleccione...', '');

        foreach ($this->coupons as $service) {
            $selectComponent->add_option(
                $service->post_title,
                $service->ID
            );
        }

        return $selectComponent;
    }
}