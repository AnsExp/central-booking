<?php
namespace CentralTickets;

use CentralTickets\Constants\TicketConstants;

use CentralTickets\Persistence\PassengerRepository;

use WP_Post;
use WC_Order;

class Ticket
{
    public int $id = 0;
    public string $status = TicketConstants::PENDING;
    public int $total_amount = 0;
    public bool $flexible = false;
    public array $metadata = [];

    private WC_Order $order;
    private ?WP_Post $coupon = null;
    /**
     * @var array<Passenger>
     */
    private array $passengers;

    public function get_coupon()
    {
        return $this->coupon;
    }

    public function set_coupon(WP_Post $coupon)
    {
        $this->coupon = $coupon;
    }

    public function get_order()
    {
        return $this->order;
    }

    public function set_order(WC_Order $order)
    {
        $this->order = $order;
    }

    public function get_meta(string $key)
    {
        return $this->metadata[$key] ?? null;
    }

    public function set_meta(string $key, mixed $value)
    {
        $this->metadata[$key] = $value;
    }

    /**
     * @return array<Passenger>
     */
    public function get_passsengers_approved()
    {
        if (!isset($this->metadata['passengers_approved'])) {
            return [];
        }

        $passenger_repository = new PassengerRepository;
        return array_filter(
            array_map(
                fn(int $id) => $passenger_repository->find($id),
                $this->metadata['passengers_approved']
            ),
            fn($x) => $x !== null
        );
    }

    public function passenger_is_approved(Passenger $passenger)
    {
        return isset($this->metadata['passengers_approved']) &&
            in_array($passenger->id, $this->metadata['passengers_approved']);
    }

    public function get_passengers()
    {
        if (!isset($this->passengers)) {
            $repository = new PassengerRepository;
            $this->passengers = $repository->find_by(['id_ticket' => $this->id]);
        }
        return $this->passengers;
    }

    public function set_passengers(array $passengers)
    {
        $this->passengers = $passengers;
    }

    public function __clone()
    {
        $clone = new self();
        $clone->id = $this->id;
        $clone->status = $this->status;
        $clone->flexible = $this->flexible;
        $clone->metadata = $this->metadata;
        $clone->total_amount = $this->total_amount;
        return $clone;
    }

    public function get_brand_logo()
    {
        $custom_field = $this->get_meta('custome_field');
        return 'https://img.freepik.com/vector-gratis/plantilla-diseno-logotipo-barco_23-2150391518.jpg';
    }
}
