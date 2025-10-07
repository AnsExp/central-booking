<?php
namespace CentralTickets;

use WC_Coupon;

class ValidateCoupon
{
    private $operator;

    public function is_valid(WC_Coupon $coupon)
    {
        $operator = $this->get_operator($coupon);
        if ($operator === false) {
            return true;
        }
        $plan = $operator->get_business_plan();
        return $plan['counter'] < $plan['limit'];
    }

    public function get_operator(WC_Coupon $coupon)
    {
        if (!isset($this->operator)) {
            $coupon_post = get_post($coupon->get_id());
            $operator = git_get_query_persistence()->get_operator_repository()->find_by_coupon($coupon_post);
            $this->operator = $operator ?? false;
        }
        return $this->operator;
    }
}