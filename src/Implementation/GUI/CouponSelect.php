<?php
namespace CentralBooking\Implementation\GUI;

use CentralBooking\Data\Repository\CouponRepository;
use CentralBooking\GUI\MultipleSelectComponent;
use CentralBooking\GUI\SelectComponent;
use WP_Post;

class CouponSelect
{
    /**
     * @var array<WP_Post>
     */
    private array $coupons;

    public function __construct(private string $name = 'coupon', ?int $operator = null)
    {
        if ($operator === null) {
            $this->coupons = (new CouponRepository)->findAll();
        } else {
            $operator = git_operator_by_id($operator);
            if ($operator === null) {
                $this->coupons = [];
            } else {
                $this->coupons = (new CouponRepository)->findCouponsByOperator($operator);
            }
        }
    }

    public function create(bool $multiple = false)
    {

        $selectComponent = $multiple
            ? new MultipleSelectComponent($this->name)
            : new SelectComponent($this->name);

        $selectComponent->addOption('Seleccione...', '');

        foreach ($this->coupons as $coupon) {
            $selectComponent->addOption(
                $coupon->post_title,
                $coupon->ID
            );
        }

        return $selectComponent;
    }
}