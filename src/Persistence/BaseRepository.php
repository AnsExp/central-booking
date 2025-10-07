<?php
namespace CentralTickets\Persistence;

use Exception;

/**
 * @template T
 */
abstract class BaseRepository implements Repository
{
    public string $error_message;

    protected function __construct(
        private readonly string $table_name,
        private readonly string $query_select,
        private readonly array $orders_allowed,
        private readonly array $filters_allowed,
        private readonly string $git_source,
    ) {
    }

    /**
     * @param T $entity
     * @return ?T
     */
    public function save($entity)
    {
        try {
            $this->verify($entity);
            return $this->process_save($entity);
        } catch (Exception $e) {
            $this->error_message = $e->getMessage();
            return null;
        }
    }

    public function find_by(array $args = [], string $order_by = 'id', string $order = 'ASC', int $limit = -1, int $offset = 0)
    {
        return array_map(
            [$this, 'result_to_entity'],
            $this->_find_by($args, $order_by, $order, $limit, $offset)
        );
    }

    private function _find_by(array $args = [], string $order_by = 'id', string $order = 'ASC', int $limit = -1, int $offset = 0)
    {
        global $wpdb;
        $filter = $this->create_filter($args);
        $order = $this->create_order($order_by, $order);
        $limit_offset = $this->create_limit_offset($limit, $offset);

        $sql = "{$this->query_select} WHERE 1 = 1 $filter $order $limit_offset";

        return $wpdb->get_results($sql);
    }

    public function count(array $args = [])
    {
        global $wpdb;
        $filter = $this->create_filter($args);

        $count_query = $this->build_count_query();
        $sql = "{$count_query} WHERE 1 = 1 $filter";

        return (int) $wpdb->get_var($sql);
    }

    protected function build_count_query(): string
    {
        return "SELECT COUNT(*) FROM {$this->table_name}";
    }

    public function exists(int $id)
    {
        global $wpdb;
        return (bool) $wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->table_name} WHERE id = %d", $id));
    }

    public function remove(int $id)
    {
        global $wpdb;
        return (bool) $wpdb->delete($this->table_name, ['id' => $id], ['%d']);
    }

    public function remove_by(array $args = [])
    {
        global $wpdb;

        $where_clauses = [];
        $params = [];

        foreach ($args as $key => $value) {
            if (is_bool($value)) {
                $where_clauses[] = "`$key` = %d";
                $params[] = $value ? 1 : 0;
            } elseif (is_int($value)) {
                $where_clauses[] = "`$key` = %d";
                $params[] = $value;
            } elseif (is_float($value)) {
                $where_clauses[] = "`$key` = %f";
                $params[] = $value;
            } else {
                $where_clauses[] = "`$key` = %s";
                $params[] = (string) $value;
            }
        }

        $sql = sprintf(
            "DELETE FROM `%s` WHERE %s",
            esc_sql($this->table_name),
            implode(' AND ', $where_clauses)
        );

        $query = $wpdb->prepare($sql, $params);
        $result = $wpdb->query($query);

        if ($result === false) {
            return false;
        }

        return $result > 0;
    }

    /**
     * @return T|null
     */
    public function find(int $id)
    {
        return $this->find_first(['id' => $id]);
    }

    /**
     * @param array $args
     * @return T|null
     */
    public function find_first(array $args = [])
    {
        $matches = $this->_find_by(args: $args, limit: 1);
        if (empty($matches)) {
            return null;
        }
        return $this->result_to_entity($matches[0]);
    }

    /**
     * @param string $order_by
     * @param string $order
     * @return string
     */
    protected function create_order(string $order_by, string $order)
    {
        $order = strtoupper($order);
        $order_by = strtolower($order_by);

        $orders = ['ASC', 'DESC'];

        if (!key_exists($order_by, $this->orders_allowed)) {
            return '';
        }

        $result_order = "ORDER BY {$this->orders_allowed[$order_by]}";

        return in_array($order, $orders, true) ? "$result_order $order" : $result_order;
    }

    protected function create_limit_offset(int $limit, int $offset)
    {
        $parts = [];
        if ($limit > 0) {
            $parts[] = "LIMIT $limit";
        }
        if ($offset > 0) {
            $parts[] = "OFFSET $offset";
        }
        return implode(' ', $parts);
    }

    /**
     * @param array $args
     * @return string
     */
    protected function create_filter(array $args)
    {
        global $wpdb;
        $result_filter = '';

        foreach ($args as $key => $value) {
            if (isset($this->filters_allowed[$key])) {
                $result_filter .= $wpdb->prepare(" AND {$this->filters_allowed[$key]}", $value);
            }
        }

        $result_filter = str_replace('#', '%', $result_filter);

        return $result_filter;
    }

    private function is_valid_time_format(string $time): bool
    {
        return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $time) === 1;
    }

    /**
     * @param T $entity
     * @return T
     */
    abstract protected function process_save($entity);

    /**
     * @param T $entity
     * @return void
     */
    abstract protected function verify($entity);

    /**
     * @param mixed $result
     * @return T
     */
    abstract protected function result_to_entity(mixed $result);
}
