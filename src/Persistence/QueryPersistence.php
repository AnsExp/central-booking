<?php
namespace CentralTickets\Persistence;

class QueryPersistence
{
    private static ?QueryPersistence $instance = null;
    public const ZONE = 'zone';
    public const ROUTE = 'route';
    public const TICKET = 'ticket';
    public const COUPON = 'coupon';
    public const SERVICE = 'service';
    public const LOCATION = 'location';
    public const OPERATOR = 'operator';
    public const PASSENGER = 'passenger';
    public const TRANSPORT = 'transport';

    private ZoneRepository $zoneRepository;
    private RouteRepository $routeRepository;
    private TicketRepository $ticketRepository;
    private CouponRepository $couponRepository;
    private ServiceRepository $serviceRepository;
    private OperatorRepository $operatorRepository;
    private LocationRepository $locationRepository;
    private PassengerRepository $passengerRepository;
    private TransportRepository $transportRepository;

    public function __construct()
    {
        $this->zoneRepository = new ZoneRepository();
        $this->routeRepository = new RouteRepository();
        $this->ticketRepository = new TicketRepository();
        $this->couponRepository = new CouponRepository();
        $this->serviceRepository = new ServiceRepository();
        $this->locationRepository = new LocationRepository();
        $this->operatorRepository = new OperatorRepository();
        $this->passengerRepository = new PassengerRepository();
        $this->transportRepository = new TransportRepository();
    }

    public function get_zone_repository()
    {
        return $this->zoneRepository;
    }

    public function get_route_repository()
    {
        return $this->routeRepository;
    }

    public function get_ticket_repository()
    {
        return $this->ticketRepository;
    }

    public function get_coupon_repository()
    {
        return $this->couponRepository;
    }

    public function get_service_repository()
    {
        return $this->serviceRepository;
    }

    public function get_location_repository()
    {
        return $this->locationRepository;
    }

    public function get_operator_repository()
    {
        return $this->operatorRepository;
    }

    public function get_passenger_repository()
    {
        return $this->passengerRepository;
    }

    public function get_transport_repository()
    {
        return $this->transportRepository;
    }

    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}