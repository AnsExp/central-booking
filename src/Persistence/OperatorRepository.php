<?php
namespace CentralTickets\Persistence;

use CentralTickets\Constants\LogSourceConstants;
use CentralTickets\Operator;
use WP_Post;

/**
 * @extends parent<Operator>
 */
class OperatorRepository extends BaseRepository
{
    private $table_users;
    private $table_users_meta;
    private $table_transports;
    public function __construct()
    {
        global $wpdb;
        $this->table_users = "{$wpdb->prefix}users";
        $this->table_users_meta = "{$wpdb->prefix}usermeta";
        $this->table_transports = "{$wpdb->prefix}git_transports";

        $query_select = "SELECT u.* FROM {$this->table_users} u
        LEFT JOIN {$this->table_users_meta} um ON um.user_id = u.id
        LEFT JOIN {$this->table_transports} t ON t.id_operator = u.id";

        $filters_allowed = [
            'id' => 'u.id = %d',
            'first_name' => '',
            'last_name' => '',
            'username' => 'u.user_login = %s',
            'id_transport' => 't.id = %d',
            'id_coupon' => '',
            'code_coupon' => '',
        ];

        $orders_allowed = [
            'id' => 'u.id',
            'firstname' => '',
            'lastname' => '',
            'id_transport' => 't.id',
            'id_coupon' => '',
            'code_coupon' => '',
        ];

        parent::__construct(
            $this->table_users,
            $query_select,
            $orders_allowed,
            $filters_allowed,
            LogSourceConstants::OPERATOR,
        );
    }

    protected function build_count_query(): string
    {
        return "SELECT COUNT(DISTINCT u.id)
        FROM {$this->table_users} u
        LEFT JOIN {$this->table_users_meta} um ON um.user_id = u.id
        LEFT JOIN {$this->table_transports} t ON t.id_operator = u.id";
    }

    public function find_by_coupon(WP_Post $coupon)
    {
        $operator_id = get_post_meta($coupon->ID, 'coupon_assigned_operator', true);
        if ($operator_id) {
            return $this->find(intval($operator_id));
        }
        return null;
    }

    protected function create_filter(array $args)
    {
        $filter_base = parent::create_filter($args);
        return "AND um.meta_key = 'wp_capabilities' AND um.meta_value LIKE '%operator%' $filter_base";
    }

    protected function create_order(string $order_by, string $order)
    {
        $base_order = parent::create_order($order_by, $order);
        return "GROUP BY u.ID $base_order";
    }

    /**
     * @param Operator $entity
     * @return ?Operator
     */
    protected function process_save($entity)
    {
        $update_data = [
            'ID' => $entity->ID,
            'first_name' => $entity->first_name,
            'last_name' => $entity->last_name,
        ];

        wp_update_user($update_data);
        update_user_meta($entity->ID, 'phone_number', $entity->phone);
        update_user_meta($entity->ID, 'business_plan_limit', $entity->get_business_plan()['limit']);
        update_user_meta($entity->ID, 'business_plan_counter', $entity->get_business_plan()['counter']);
        update_user_meta($entity->ID, 'brand_media', $entity->brand_media);

        return $entity;
    }

    protected function result_to_entity(mixed $result)
    {
        $operator = new Operator($result->ID);
        $operator->phone = get_user_meta($operator->ID, 'phone_number', true);
        $operator->brand_media = get_user_meta($operator->ID, 'brand_media', true);

        $bussines_plan_limit = get_user_meta($operator->ID, 'business_plan_limit', true);
        $bussines_plan_counter = get_user_meta($operator->ID, 'business_plan_counter', true);
        $operator->set_business_plan(
            $bussines_plan_limit ? intval($bussines_plan_limit) : 0,
            $bussines_plan_counter ? intval($bussines_plan_counter) : 0,
        );

        return $operator;
    }

    protected function verify($entity)
    {
    }
}
