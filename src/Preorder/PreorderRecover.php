<?php
namespace CentralTickets\Preorder;

use CentralTickets\Persistence\RouteRepository;
class PreorderRecover
{
    private function __construct(private int $id_preorder)
    {
    }

    private function get()
    {
        $order = wc_get_order($this->id_preorder);
        if (is_bool($order)) {
            return null;
        }
        $order_id = $order->get_id();
        $order_flag = get_post_meta($order_id, 'git_order', true);
        if (!$order_flag === 'preorder') {
            return null;
        }
        $preorder = new Preorder();
        $transport = git_get_transport_by_id((int) get_post_meta($order_id, 'transport_id', true));
        $routes = $this->get_routes(get_post_meta($order_id, 'routes_id'));
        $preorder->pax = get_post_meta($order_id, 'pax', true);
        $preorder->date_trip = get_post_meta($order_id, 'date_trip', true);
        $preorder->passengers_info = get_post_meta($order_id, 'passengers_info', true);
        $preorder->set_routes($routes);
        $preorder->set_order($order);
        if ($transport) {
            $preorder->set_transport($transport);
        }
        if ($preorder->is_served()) {
            return null;
        }
        return $preorder;
    }

    private function get_routes(array $ids)
    {
        $repository = new RouteRepository;
        $routes = [];
        foreach ($ids as $id) {
            $route = $repository->find((int) $id);
            if ($route !== null) {
                $routes[] = $route;
            }
        }
        return $routes;
    }

    public static function recover(int $id_preorder)
    {
        $instance = new self($id_preorder);
        $recovered = $instance->get();
        if ($recovered !== null) {
            return $recovered;
        }
        return null;
    }
}
