<?php
namespace CentralTickets\Services;

use CentralTickets\Operator;
use CentralTickets\Persistence\CouponRepository;
use CentralTickets\Persistence\OperatorRepository;

/**
 * @extends parent<Operator>
 */
class OperatorService extends BaseService
{
    private CouponRepository $coupon_repository;
    public function __construct()
    {
        parent::__construct(new OperatorRepository);
        $this->coupon_repository = new CouponRepository;
    }

    public function save($request, int $id = 0)
    {
        $result = parent::save($request, $id);
        if ($result !== null) {
            foreach ($this->coupon_repository->get_coupons_by_operator($result) as $coupon) {
                $this->coupon_repository->unassign_coupon_to_operator($coupon, $result);
            }
            foreach ($result->get_coupons() as $coupon) {
                $this->coupon_repository->assign_coupon_to_operator($coupon, $result);
            }
        }
        return $result;
    }

    /**
     * @param Operator $entity
     * @return bool
     */
    protected function verify($entity)
    {
        return true;
    }
}
