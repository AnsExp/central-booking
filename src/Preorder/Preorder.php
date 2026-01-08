<?php
namespace CentralBooking\Preorder;

use CentralBooking\Data\Route;
use CentralBooking\Data\Time;
use CentralBooking\Data\Transport;
use DateTime;
use WC_Order;

class Preorder
{
    public Time $departure_time;
    public DateTime $date_trip;
    public int $pax = 0;
    public array $passengers_info = [];
    /**
     * @var array<Route>
     */
    private array $routes = [];
    private WC_Order $order;
    private ?Transport $transport = null;

    public function __construct()
    {
        $this->date_trip = new DateTime('now');
        $this->departure_time = new Time('00:00:00');
    }

    public function set_order(WC_Order $order)
    {
        $this->order = $order;
    }

    public function get_order()
    {
        return $this->order;
    }

    public function set_transport(Transport $transport)
    {
        $this->transport = $transport;
    }

    public function get_transport()
    {
        return $this->transport;
    }

    /**
     * @param array<Route> $routes
     * @return void
     */
    public function set_routes(array $routes)
    {
        $this->routes = $routes;
    }

    public function get_routes()
    {
        return $this->routes;
    }

    public function is_served()
    {
        $result = get_post_meta($this->order->get_id(), 'served', true);
        if ($result === '') {
            return false;
        }
        return $result === 'true';
    }

    public function set_served(bool $served)
    {
        update_post_meta($this->order->get_id(), 'served', $served ? 'true' : 'false');
    }
}
