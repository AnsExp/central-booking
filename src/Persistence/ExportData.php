<?php
namespace CentralTickets\Persistence;

use CentralTickets\Configurations;
use CentralTickets\Location;
use CentralTickets\MetaManager;
use CentralTickets\Operator;
use CentralTickets\Route;
use CentralTickets\Service;
use CentralTickets\Transport;
use CentralTickets\Zone;
use WC_Product;
use WP_Post;

final class ExportData
{
    public function export(array $data)
    {
        $coupons = $data['coupons'] ?? false;
        $settings = $data['settings'] ?? false;
        $entities = $data['entities'] ?? false;
        $products = $data['products'] ?? false;
        $operators = $data['operators'] ?? false;

        return [
            'zones' => $entities ? $this->export_zones() : [],
            'routes' => $entities ? $this->export_routes() : [],
            'coupons' => $coupons ? $this->export_coupons() : [],
            'settings' => $settings ? $this->export_settings() : [],
            'services' => $entities ? $this->export_services() : [],
            'products' => $products ? $this->export_products() : [],
            'locations' => $entities ? $this->export_locations() : [],
            'operators' => $operators ? $this->export_operators() : [],
            'transports' => $entities ? $this->export_transports() : [],
        ];
    }

    private function export_settings()
    {
        return Configurations::get_all();
    }

    private function export_transports()
    {
        return array_map(fn(Transport $transport) => [
            'id' => $transport->id,
            'type' => $transport->type,
            'code' => $transport->code,
            'nicename' => $transport->nicename,
            'operator_id' => $transport->get_operator()->ID,
            'routes_ids' => array_map(fn(Route $route) => $route->id, $transport->get_routes()),
            'services_ids' => array_map(fn(Service $service) => $service->id, $transport->get_services()),
            'meta' => MetaManager::get_metadata(MetaManager::TRANSPORT, $transport->id),
        ], git_get_transports());
    }

    private function export_locations()
    {
        return array_map(fn(Location $location) => [
            'id' => $location->id,
            'name' => $location->name,
            'zone_id' => $location->get_zone()->id,
        ], git_get_locations());
    }

    private function export_zones()
    {
        return array_map(fn(Zone $zone) => [
            'id' => $zone->id,
            'name' => $zone->name,
        ], git_get_zones());
    }

    private function export_services()
    {
        return array_map(fn(Service $service) => [
            'id' => $service->id,
            'icon' => $service->icon,
            'name' => $service->name,
            'price' => $service->price,
        ], git_get_services());
    }

    private function export_routes()
    {
        return array_map(fn(Route $route) => [
            'id' => $route->id,
            'type' => $route->type,
            'origin_id' => $route->get_origin()->id,
            'destiny_id' => $route->get_destiny()->id,
            'distance_km' => $route->distance_km,
            'duration_trip' => $route->duration_trip,
            'departure_time' => $route->departure_time,
        ], git_get_routes());
    }

    private function export_products()
    {
        return array_map(fn(WC_Product $p) => [
            'id' => $p->get_id(),
            'name' => $p->get_name(),
            'type' => $p->get_type(),
            'type_way' => $p->get_meta('type_way'),
            'type_transport' => $p->get_meta('type_transport'),
            'type_route' => $p->get_meta('type_route'),
            'status' => method_exists($p, 'get_status') ? $p->get_status() : get_post_status($p->get_id()),
            'prices' => [
                'sale' => $p->get_sale_price(),
                'regular' => $p->get_regular_price(),
                'kid' => $p->get_meta('price_kid'),
                'rpm' => $p->get_meta('price_rpm'),
                'extra' => $p->get_meta('price_extra'),
                'standar' => $p->get_meta('price_standar'),
                'flexible' => $p->get_meta('price_flexible'),
            ],
            'maximums' => [
                'extra' => $p->get_meta('maximum_extra'),
                'person' => $p->get_meta('maximum_person'),
            ],
            'zones' => [
                'origin' => $p->get_meta('zone_origin'),
                'destiny' => $p->get_meta('zone_destiny'),
            ],
            'enable_bookeable' => $p->get_meta('enable_bookeable') === 'yes',
            'enable_switch_route' => $p->get_meta('enable_switch_route') === 'yes',
        ], wc_get_products([
                'limit' => -1,
                'type' => 'operator',
                'status' => ['publish', 'pending', 'draft', 'future', 'private', 'trash', 'auto-draft'],
            ]));
    }

    private function export_coupons()
    {
        return array_map(fn(WP_Post $coupon) => [
            'id' => $coupon->ID,
            'code' => $coupon->post_title,
            'operator_id' => git_get_operator_by_coupon($coupon)?->ID ?? null,
        ], git_get_all_coupons());
    }

    private function export_operators()
    {
        return array_map(fn(Operator $operator) => [
            'id' => $operator->ID,
            'firstname' => $operator->first_name,
            'lastname' => $operator->last_name,
            'email' => $operator->user_email,
            'username' => $operator->user_login,
            'phone' => get_user_meta($operator->ID, 'phone_number', true),
            'business_plan' => $operator->get_business_plan(),
        ], git_get_query_persistence()->get_operator_repository()->find_by());
    }
}
