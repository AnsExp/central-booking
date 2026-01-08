<?php
namespace CentralBooking\Data\Repository;

final class ImportData
{
    private array $zones_ids_map = [];
    private array $routes_ids_map = [];
    private array $services_ids_map = [];
    private array $locations_ids_map = [];
    private array $operators_ids_map = [];
    private array $transports_ids_map = [];

    public function import(array $data)
    {
        $zones = $data['zones'] ?? [];
        $routes = $data['routes'] ?? [];
        $coupons = $data['coupons'] ?? [];
        $settings = $data['settings'] ?? [];
        $products = $data['products'] ?? [];
        $services = $data['services'] ?? [];
        $operators = $data['operators'] ?? [];
        $locations = $data['locations'] ?? [];
        $transports = $data['transports'] ?? [];

        // $this->import_settings($settings);
        // $this->import_operators($operators);
        // $this->import_coupons($coupons);
        // $this->import_zones($zones);
        // $this->import_services($services);
        // $this->import_locations($locations);
        // $this->import_routes($routes);
        // $this->import_transports($transports);
        // $this->import_products($products);
    }

    // private function import_settings(array $settings)
    // {
    //     foreach ($settings as $key => $value) {
    //         Configurations::set($key, $value);
    //     }
    // }

    // private function import_operators(array $operators)
    // {
    //     foreach ($operators as $operator) {
    //         $id_operator = wp_insert_user([
    //             'user_login' => $operator['username'] ?? '',
    //             'user_pass' => $operator['username'] ?? wp_generate_password(12, false),
    //             'user_email' => $operator['email'] ?? '',
    //             'first_name' => $operator['firstname'] ?? '',
    //             'last_name' => $operator['lastname'] ?? '',
    //             'role' => UserConstants::OPERATOR,
    //         ]);
    //         if (is_numeric($id_operator)) {
    //             update_user_meta($id_operator, 'phone_number', $operator['phone'] ?? '');
    //             update_user_meta($id_operator, 'business_plan_limit', $operator['business_plan']['limit'] ?? 0);
    //             update_user_meta($id_operator, 'business_plan_counter', $operator['business_plan']['counter'] ?? 0);
    //             $this->operators_ids_map[$operator['id']] = $id_operator;
    //         }
    //     }
    // }

    // private function import_coupons(array $coupons)
    // {
    //     foreach ($coupons as $coupon) {
    //         $defaults = [
    //             'post_title' => $coupon['code'] ?? '',
    //             'post_status' => 'publish',
    //             'post_author' => get_current_user_id(),
    //             'post_type' => 'shop_coupon',
    //         ];

    //         $coupon_id = wp_insert_post($defaults);

    //         if (is_numeric($coupon_id)) {
    //             update_post_meta($coupon_id, 'discount_type', 'percent');
    //             update_post_meta($coupon_id, 'coupon_amount', '100');
    //             $operator = git_get_operator_by_id($this->operators_ids_map[$coupon['operator_id']] ?? -1);
    //             if ($operator) {
    //                 git_get_query_persistence()
    //                     ->get_coupon_repository()
    //                     ->assign_coupon_to_operator(
    //                         get_post($coupon_id),
    //                         $operator
    //                     );
    //             }
    //         }
    //     }
    // }

    // private function import_zones(array $zones)
    // {
    //     foreach ($zones as $zone) {
    //         $zone_obj = new Zone();
    //         $zone_obj->name = $zone['name'] ?? '';
    //         $zone_saved = git_get_query_persistence()->get_zone_repository()->save($zone_obj);
    //         if ($zone_saved) {
    //             $this->zones_ids_map[$zone['id']] = $zone_obj->id;
    //         }
    //     }
    // }

    // private function import_services(array $services)
    // {
    //     foreach ($services as $service) {
    //         $service_obj = new Service();

    //         $service_obj->name = $service['name'] ?? '';
    //         $service_obj->icon = $service['icon'] ?? '';
    //         $service_obj->price = $service['price'] ?? '';

    //         $repository = git_get_query_persistence()->get_service_repository();
    //         $service_saved = $repository->save($service_obj);
    //         if ($service_saved) {
    //             $this->services_ids_map[$service['id']] = $service_saved->id;
    //         }
    //     }
    // }

    // private function import_locations(array $locations)
    // {
    //     foreach ($locations as $location) {
    //         $zone_obj = git_get_zone_by_id($this->zones_ids_map[$location['zone_id']] ?? -1);

    //         if (!$zone_obj) {
    //             continue;
    //         }

    //         $location_obj = new Location();
    //         $location_obj->name = $location['name'] ?? '';
    //         $location_obj->set_zone($zone_obj);

    //         $repository = git_get_query_persistence()->get_location_repository();
    //         $location_saved = $repository->save($location_obj);
    //         if ($location_saved) {
    //             $this->locations_ids_map[$location['id']] = $location_saved->id;
    //         }
    //     }
    // }

    // private function import_routes(array $routes)
    // {
    //     foreach ($routes as $route) {
    //         $origin = git_get_location_by_id($this->locations_ids_map[$route['origin_id']] ?? -1);
    //         $destiny = git_get_location_by_id($this->locations_ids_map[$route['destiny_id']] ?? -1);

    //         if (!$origin || !$destiny) {
    //             continue;
    //         }

    //         $route_obj = new Route();
    //         $route_obj->type = $route['type'] ?? '';
    //         $route_obj->set_origin($origin);
    //         $route_obj->set_destiny($destiny);
    //         $route_obj->distance_km = $route['distance_km'] ?? 0;
    //         $route_obj->duration_trip = $route['duration_trip'] ?? '';
    //         $route_obj->departure_time = $route['departure_time'] ?? '';

    //         $route_saved = git_get_query_persistence()->get_route_repository()->save($route_obj);

    //         if ($route_saved) {
    //             $this->routes_ids_map[$route['id']] = $route_saved->id;
    //         }
    //     }
    // }

    // private function import_transports(array $transports)
    // {
    //     foreach ($transports as $transport) {
    //         $transport_obj = new Transport();
    //         $transport_obj->type = $transport['type'] ?? '';
    //         $transport_obj->code = $transport['code'] ?? '';
    //         $transport_obj->nicename = $transport['nicename'] ?? '';

    //         $operator = git_get_operator_by_id($this->operators_ids_map[$transport['operator_id']] ?? -1);
    //         if ($operator) {
    //             $transport_obj->set_operator($operator);
    //         }

    //         $routes_objs = [];
    //         $services_objs = [];
    //         foreach ($transport['routes_ids'] ?? [] as $route_id) {
    //             $route = git_get_route_by_id($this->routes_ids_map[$route_id] ?? -1);
    //             if ($route) {
    //                 $routes_objs[] = $route;
    //             }
    //         }
    //         foreach ($transport['services_ids'] ?? [] as $service_id) {
    //             $service = git_get_service_by_id($this->services_ids_map[$service_id] ?? -1);
    //             if ($service) {
    //                 $services_objs[] = $service;
    //             }
    //         }
    //         $transport_obj->set_routes($routes_objs);
    //         $transport_obj->set_services($services_objs);

    //         $transport_saved = git_get_query_persistence()->get_transport_repository()->save($transport_obj);
    //         if ($transport_saved) {
    //             $this->transports_ids_map[$transport['id']] = $transport_saved->id;
    //             MetaManager::set_metadata(
    //                 MetaManager::TRANSPORT,
    //                 $transport_saved->id,
    //                 $transport['meta'] ?? [],
    //             );
    //         }
    //     }
    // }

    // private function import_products(array $products)
    // {
    //     foreach ($products as $product_data) {
    //         $product = new WC_Product_Operator(0);
    //         $product->set_name($product_data['name'] ?? '');
    //         $product->set_price($product_data['prices']['sale'] ?? 0);
    //         $product->set_regular_price($product_data['prices']['regular'] ?? 0);
    //         $product->set_meta_data([
    //             'price_kid' => $product_data['prices']['kid'] ?? 0,
    //             'price_rpm' => $product_data['prices']['rpm'] ?? 0,
    //             'price_extra' => $product_data['prices']['extra'] ?? 0,
    //             'price_standar' => $product_data['prices']['standar'] ?? 0,
    //             'price_flexible' => $product_data['prices']['flexible'] ?? 0,
    //             'maximum_extra' => $product_data['maximums']['extra'] ?? 0,
    //             'maximum_person' => $product_data['maximums']['person'] ?? 0,
    //             'zone_origin' => $product_data['zones']['origin'] ?? 0,
    //             'zone_destiny' => $product_data['zones']['destiny'] ?? 0,
    //             'enable_bookeable' => $product_data['enable_bookeable'] ? 'yes' : 'no',
    //             'enable_switch_route' => $product_data['enable_switch_route'] ? 'yes' : 'no',
    //         ]);
    //         $product->save();
    //     }
    // }
}
