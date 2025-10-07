<?php
namespace CentralTickets\Preorder;

use CentralTickets\Zone;
use CentralTickets\Location;
use CentralTickets\Constants\TransportConstants;
use CentralTickets\Persistence\LocationRepository;
use CentralTickets\Persistence\RouteRepository;
use CentralTickets\Persistence\ZoneRepository;
use CentralTickets\Services\Actions\DateTrip;
use WP_Error;
class PreorderCreator
{
    private function __construct(
        private int $pax,
        private int $transport_id,
        private array $origin,
        private array $destiny,
        private string $date_trip,
        private string $type,
        private string $departure_time,
        private array $passengers_info = []
    ) {
    }

    private function get()
    {
        $order = wc_create_order();
        $order->set_billing_first_name('Central Tickets (Preorder)');
        $order_id = $order->get_id();
        wp_update_post([
            'ID' => $order_id,
            'post_title' => "Preorder - {$this->origin['name']} a {$this->destiny['name']}",
        ]);
        $origin = $this->get_location($this->origin);
        $destiny = $this->get_location($this->destiny);
        if ($origin === null || $destiny === null) {
            return new WP_Error('route_not_found', 'Route not found');
        }
        $route = $this->get_route(
            $origin,
            $destiny,
            $this->departure_time,
            $this->type
        );
        if (empty($route)) {
            return new WP_Error('route_not_found', 'Route not found');
        }
        update_post_meta($order_id, 'git_order', 'preorder');
        update_post_meta($order_id, 'pax', $this->pax, true);
        update_post_meta($order_id, 'transport_id', $this->transport_id, true);
        update_post_meta($order_id, 'routes_id', array_map(fn($r) => $r->id, $route), true);
        update_post_meta($order_id, 'date_trip', $this->date_trip, true);
        update_post_meta($order_id, 'passengers_info', $this->passengers_info, true);
        $order->save();
        return $order;
    }

    private function get_route(
        Location|Zone $origin,
        Location|Zone $destiny,
        string $departure_time,
        string $type,
    ) {
        $repository = new RouteRepository();
        $args = [
            'departure_time' => $departure_time,
            'type' => $type
        ];
        if ($origin instanceof Location) {
            $args['id_origin'] = $origin->id;
        } elseif ($origin instanceof Zone) {
            $args['id_zone_origin'] = $origin->id;
        }
        if ($destiny instanceof Location) {
            $args['id_destiny'] = $destiny->id;
        } elseif ($destiny instanceof Zone) {
            $args['id_zone_destiny'] = $destiny->id;
        }
        return $repository->find_by($args);
    }

    private function get_location(array $location)
    {
        if (!isset($location['type']) || !in_array($location['type'], ['location', 'zone'])) {
            return null;
        }
        if ($location['type'] === 'zone') {
            return (new ZoneRepository)->find_first(['name' => $location['name']]);
        }
        if ($location['type'] === 'location') {
            return (new LocationRepository)->find_first(['name' => $location['name']]);
        }
    }

    public static function create(
        int $pax,
        int $transport_id,
        array $origin,
        array $destiny,
        string $date_trip,
        string $type = TransportConstants::MARINE,
        string $departure_time = '00:00:00',
        array $passengers_info = []
    ) {
        if (!TransportConstants::is_valid($type)) {
            return null;
        }
        if (!DateTrip::valid($date_trip)) {
            return null;
        }
        $creator = new self(
            $pax,
            $transport_id,
            $origin,
            $destiny,
            $date_trip,
            $type,
            $departure_time,
            $passengers_info
        );
        $order = $creator->get();
        if ($order instanceof WP_Error) {
            return null;
        }
        return PreorderRecover::recover($order->get_id());
    }
}
