<?php
namespace CentralBooking\Data;

use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\Data\Repository\LazyLoader;
use WP_Post;
use WC_Order;
use WP_User;

/**
 * Class Ticket
 *
 * Representa un boleto dentro del sistema CentralBooking.
 *
 * @package CentralBooking\Data
 */
final class Ticket
{
    /** @var int ID único del ticket. */
    public int $id = 0;

    /** @var int Monto total del ticket. */
    public int $total_amount = 0;

    /** @var bool Indica si el ticket es flexible. */
    public bool $flexible = false;

    /** @var TicketStatus Estado actual del ticket. */
    public TicketStatus $status = TicketStatus::PENDING;

    /** @var array Metadatos asociados al ticket. */
    private array $metadata = [];

    /** @var WC_Order Pedido asociado al ticket. */
    private ?WC_Order $order = null;

    /** @var WP_User|null Cliente asociado al ticket. */
    private ?WP_User $client = null;

    /** @var WP_Post|null Cupón aplicado al ticket. */
    private ?WP_Post $coupon = null;

    /** @var Passenger[] Lista de pasajeros asociados al ticket. */
    private array $passengers;

    /**
     * Obtiene el cliente asociado al ticket.
     *
     * @return WP_User|null
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Asigna un cliente al ticket.
     *
     * @param WP_User|null $client
     * @return void
     */
    public function setClient(?WP_User $client)
    {
        $this->client = $client;
    }

    /**
     * Obtiene el cupón aplicado al ticket.
     *
     * @return WP_Post|null
     */
    public function getCoupon()
    {
        return $this->coupon;
    }

    /**
     * Asigna un cupón al ticket.
     *
     * @param WP_Post|null $coupon
     * @return void
     */
    public function setCoupon(?WP_Post $coupon)
    {
        $this->coupon = $coupon;
    }

    /**
     * Obtiene el pedido asociado al ticket.
     * Si no está cargado, lo obtiene mediante LazyLoader.
     *
     * @return WC_Order|null
     */
    public function getOrder()
    {
        if (!isset($this->order)) {
            $this->order = LazyLoader::loadOrderByTicket($this);
        }
        return $this->order;
    }

    /**
     * Asigna un pedido al ticket.
     *
     * @param WC_Order|null $order
     * @return void
     */
    public function setOrder(?WC_Order $order)
    {
        $this->order = $order;
    }

    /**
     * Obtiene un metadato del ticket.
     *
     * @param string $key
     * @return mixed
     */
    public function getMeta(string $key)
    {
        if (isset($this->metadata[$key])) {
            return $this->metadata[$key];
        }
        return MetaManager::getMeta(MetaManager::TICKET, $this->id, $key);
    }

    /**
     * Establece un metadato en memoria.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setMeta(string $key, mixed $value)
    {
        $this->metadata[$key] = $value;
    }

    /**
     * Persiste los metadatos en la base de datos.
     *
     * @return void
     */
    public function saveMeta()
    {
        foreach ($this->metadata as $key => $value) {
            MetaManager::setMeta(MetaManager::TICKET, $this->id, $key, $value);
        }
    }

    public function getProofPayment()
    {
        $proof = $this->getMeta('proof_payment') ?? [
            'name' => '',
            'path' => '',
            'date' => '',
            'code' => '',
            'amount' => '',
        ];
        $proof_payment = new ProofPayment(
            $proof['name'],
            $proof['path'],
            $proof['code'],
            $proof['amount'],
            $proof['date'],
        );
        return $proof_payment;
    }

    /**
     * Establece los datos del comprobante de pago.
     *
     * @param string $name
     * @param string $url
     * @param Date $date
     * @param string $code
     * @param int $amount
     * @return void
     */
    public function setProofPayment(
        string $name,
        string $url,
        Date $date,
        string $code,
        int $amount
    ) {
        $this->setMeta('proof_payment', [
            'name' => $name,
            'path' => $url,
            'date' => $date,
            'code' => $code,
            'amount' => $amount,
        ]);
    }

    /**
     * Obtiene la lista de pasajeros asociados al ticket.
     *
     * @return Passenger[]
     */
    public function getPassengers()
    {
        if (!isset($this->passengers)) {
            $this->passengers = LazyLoader::loadPassengersByTicket($this);
        }
        return $this->passengers;
    }

    /**
     * Asigna la lista de pasajeros al ticket.
     *
     * @param Passenger[] $passengers
     * @return void
     */
    public function setPassengers(array $passengers)
    {
        $this->passengers = $passengers;
    }

    public function save()
    {
        git_ticket_save($this);
    }
}