<?php
namespace CentralBooking\Data\Services;

use CentralBooking\Data\Operator;
use CentralBooking\Data\Repository\CouponRepository;
use CentralBooking\Data\Repository\OperatorRepository;

class OperatorService
{
    private CouponRepository $coupon_repository;
    private OperatorRepository $operator_repository;

    public function __construct()
    {
        $this->operator_repository = new OperatorRepository();
        $this->coupon_repository = new CouponRepository();
    }

    public function save(Operator $operator)
    {
        $operatorSaved = $this->operator_repository->save($operator);
        foreach ($operator->getCoupons() as $coupon) {
            $this->coupon_repository->assignCouponToOperator($coupon, $operatorSaved);
        }
        return $operatorSaved;
    }

    public function findAll()
    {
        return $this->operator_repository->findAll();
    }
}
