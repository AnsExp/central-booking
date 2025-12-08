<?php
namespace CentralTickets\Services\PackageData;

use CentralTickets\Operator;
use WP_Post;

/**
 * @extends parent<Operator>
 */
class OperatorData implements PackageData
{
    public function __construct(
        public readonly string $firstname = '',
        public readonly string $lastname = '',
        public readonly string $phone = '',
        public readonly array $coupons = [],
        public readonly int $coupon_counter = 0,
        public readonly int $coupon_limit = 0,
        public readonly string $brand_media = '',
    ) {
    }

    public function get_data()
    {
        $operator = new Operator;
        $coupons = [];

        foreach ($this->coupons as $id_coupon) {
            $coupon = WP_Post::get_instance($id_coupon);
            if (!$coupon) {
                continue;
            }
            $coupons[] = $coupon;
        }

        $operator->phone = $this->phone;
        $operator->last_name = $this->lastname;
        $operator->first_name = $this->firstname;
        $operator->brand_media = $this->brand_media;

        $operator->set_coupons($coupons);
        $operator->set_business_plan($this->coupon_limit, $this->coupon_counter);

        return $operator;
    }
}
