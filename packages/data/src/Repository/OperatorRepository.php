<?php
namespace CentralBooking\Data\Repository;

use CentralBooking\Data\Constants\UserRole;
use CentralBooking\Data\Operator;
use WP_User;
use WP_User_Query;

class OperatorRepository
{
    private function userToOperator(WP_User $user): Operator
    {
        $operator = new Operator();
        $operator->setUser($user);
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

    public function findAll()
    {
        $user_query = new WP_User_Query([
            'role' => UserRole::OPERATOR->slug(),
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
}
