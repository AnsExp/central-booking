<?php
namespace CentralTickets\Persistence;

use CentralTickets\Operator;
use WP_Post;

class CouponRepository
{
    public function assign_coupon_to_operator(WP_Post $coupon, Operator $operator)
    {
        update_post_meta($coupon->ID, 'coupon_assigned_operator', $operator->ID);
        return $coupon;
    }

    public function unassign_coupon_to_operator(WP_Post $coupon, Operator $operator)
    {
        return delete_post_meta($coupon->ID, 'coupon_assigned_operator', $operator->ID);
    }

    /**
     * @param Operator $operator
     * @return array<WP_Post>
     */
    public function get_coupons_by_operator(Operator $operator)
    {
        $args = [
            'post_type' => 'shop_coupon',
            'posts_per_page' => -1,
            'meta_key' => 'coupon_assigned_operator',
            'meta_value' => $operator->ID,
            'orderby' => 'title',
            'order' => 'ASC'
        ];
        return get_posts($args);
    }

    public function find_all()
    {
        $args = [
            'post_type' => 'shop_coupon',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ];
        return get_posts($args);
    }

    public function find(int $id)
    {
        $coupon = get_post($id);
        if ($coupon && $coupon->post_type === 'shop_coupon') {
            return $coupon;
        }
        return null;
    }
}
