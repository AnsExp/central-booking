<?php
namespace CentralBooking\Data\Repository;

use CentralBooking\Data\Constants\UserConstants;
use CentralBooking\Data\MetaManager;
use CentralBooking\Data\Operator;
use WP_Post;
use WP_User;
use WP_User_Query;

class OperatorRepository
{
    private function userToOperator(WP_User $user): Operator
    {
        $operator = new Operator();
        $operator->setUser($user);
        $phone = MetaManager::getMeta(
            MetaManager::OPERATOR,
            $user->ID,
            'phone_number',
        );
        $brand_media = MetaManager::getMeta(
            MetaManager::OPERATOR,
            $user->ID,
            'brand_media',
        );
        $business_plan = [
            'limit' => MetaManager::getMapMeta(
                MetaManager::OPERATOR,
                $user->ID,
                'business_plan.limit',
            ),
            'counter' => MetaManager::getMapMeta(
                MetaManager::OPERATOR,
                $user->ID,
                'business_plan.counter',
            ),
        ];
        $operator->setPhone($phone ?? '');
        $operator->setBrandMedia($brand_media ?? '');
        $operator->setBusinessPlan(
            $business_plan['limit'] ?? 0,
            $business_plan['counter'] ?? 0
        );
        return $operator;
    }

    public function findById(int $id)
    {
        $user = get_user($id);
        if (!$user) {
            return null;
        }
        $operator = $this->userToOperator($user);
        return $operator;
    }

    public function findByCoupon(WP_Post $coupon)
    {
        $operator_id = get_post_meta($coupon->ID, 'coupon_assigned_operator', true);
        if ($operator_id) {
        }
        return null;
    }

    public function findAll()
    {
        $user_query = new WP_User_Query([
            'role' => UserConstants::OPERATOR->value,
            'orderby' => 'ID',
            'order' => 'ASC',
            'fields' => 'all_with_meta',
        ]);
        $operators = [];
        foreach ($user_query->get_results() as $user) {
            $operators[] = $this->userToOperator($user);
        }
        return $operators;
    }

    public function save(Operator $operator)
    {
        wp_update_user($operator->getUser());
        MetaManager::setMetadata(
            MetaManager::OPERATOR,
            $operator->getUser()->ID,
            [
                'phone_number' => $operator->getPhone(),
                'brand_media' => $operator->brand_media,
                'business_plan' => [
                    'limit' => $operator->getBusinessPlan()['limit'],
                    'counter' => $operator->getBusinessPlan()['counter'],
                ],
            ]
        );
        return $operator;
    }
}
