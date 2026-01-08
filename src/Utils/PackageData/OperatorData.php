<?php
namespace CentralBooking\Utils\PackageData;

use CentralBooking\Data\Operator;
use WP_Post;

/**
 * @implements parent<Operator>
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

    public function get_data(): Operator
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

        $operator->setCoupons($coupons);
        $operator->setBusinessPlan($this->coupon_limit, $this->coupon_counter);

        return $operator;
    }
}
