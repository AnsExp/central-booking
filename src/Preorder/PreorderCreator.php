<?php
namespace CentralBooking\Preorder;

use CentralBooking\Data\Constants\TransportConstants;
use CentralBooking\Data\Services\Actions\DateTrip;
use CentralBooking\Data\Zone;
use WP_Error;

class PreorderCreator
{
    private function __construct(
        private int $pax,
        private int $transport_id,
        private string $origin,
        private string $destiny,
        private string $date_trip,
        TransportConstants $type,
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
            'post_title' => "Preorder - {$this->origin} a {$this->destiny}",
        ]);
        $origin = $this->get_zone($this->origin);
        $destiny = $this->get_zone($this->destiny);
        if ($origin === null || $destiny === null) {
            return new WP_Error('route_not_found', 'Route not found');
        }
        $routes = $this->get_routes(
            $origin,
            $destiny,
            $this->departure_time,
            $this->type
        );
        if (empty($routes)) {
            return new WP_Error('route_not_found', 'Route not found');
        }
        update_post_meta($order_id, 'git_order', 'preorder');
        update_post_meta($order_id, 'pax', $this->pax, true);
        update_post_meta($order_id, 'transport_id', $this->transport_id, true);
        update_post_meta($order_id, 'routes_id', array_map(fn($r) => $r->id, $routes), true);
        update_post_meta($order_id, 'date_trip', $this->date_trip, true);
        update_post_meta($order_id, 'passengers_info', $this->passengers_info, true);
        $order->save();
        return $order;
    }

    private function get_routes(Zone $origin, Zone $destiny, string $departure_time, TransportConstants $type)
    {
        return git_routes([
            'departure_time' => $departure_time,
            'type' => $type->value,
            'id_zone_origin' => $origin->id,
            'id_zone_destiny' => $destiny->id,
        ]);
    }

    private function get_zone(string $location)
    {
        return git_zone_by_name($location);
    }

    public static function create(
        int $pax,
        int $transport_id,
        string $origin,
        string $destiny,
        string $date_trip,
        TransportConstants $type = TransportConstants::MARINE,
        string $departure_time = '00:00:00',
        array $passengers_info = []
    ) {
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
