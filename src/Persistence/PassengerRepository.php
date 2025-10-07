<?php
namespace CentralTickets\Persistence;

use CentralTickets\Constants\LogSourceConstants;
use CentralTickets\Services\ArrayParser\PassengerArray;
use CentralTickets\Constants\WebhookTopicConstants;
use CentralTickets\Webhooks\WebhookManager;
use Exception;
use CentralTickets\Passenger;

/**
 * @extends parent<Passenger>
 */
class PassengerRepository extends BaseRepository
{
    private string $table_routes;
    private string $table_tickets;
    private string $table_transports;
    private string $table_passengers;
    private RouteRepository $route_repository;
    private TicketRepository $ticket_repository;
    private TransportRepository $transport_repository;

    public function __construct()
    {
        global $wpdb;
        $this->route_repository = new RouteRepository;
        $this->ticket_repository = new TicketRepository;
        $this->transport_repository = new TransportRepository;
        $this->table_routes = "{$wpdb->prefix}git_routes";
        $this->table_tickets = "{$wpdb->prefix}git_tickets";
        $this->table_transports = "{$wpdb->prefix}git_transports";
        $this->table_passengers = "{$wpdb->prefix}git_passengers";

        $select = "SELECT p.*
        FROM {$this->table_passengers} p
        LEFT JOIN {$this->table_routes} r ON r.id = p.id_route
        LEFT JOIN {$this->table_tickets} t ON t.id = p.id_ticket
        LEFT JOIN {$this->table_transports} tr ON tr.id = p.id_transport
        LEFT JOIN {$wpdb->prefix}posts o ON o.id = t.id_order
        LEFT JOIN {$wpdb->prefix}postmeta om ON om.post_id = o.id";

        $filters_allowed = [
            'id' => 'p.id = %d',
            'id_ticket' => 't.id = %d',
            'id_origin' => 'r.id_origin = %d',
            'id_destiny' => 'r.id_destiny = %d',
            'ticket_status' => 't.status = %s',
            'ticket_flexible' => 't.flexible = %d',
            'id_route' => 'r.id = %d',
            'departure_time' => 'r.departure_time = %s',
            'id_transport' => 'tr.id = %d',
            'name' => 'p.name LIKE %s',
            'nationality' => 'p.nationality = %s',
            'type_document' => 'p.type_document = %s',
            'data_document' => 'p.data_document LIKE %s',
            'type' => 'p.type = %s',
            'served' => 'p.served = %d',
            'date_trip' => 'p.date_trip = %s',
            'date_birth' => 'p.date_birth = %s',
            'approved' => 'p.approved = %d',
        ];

        $orders_allowed = [
            'id' => 'p.id',
            'ticket_status' => 't.status',
            'id_ticket' => 'p.id_ticket',
            'id_route' => 'p.id_route',
            'id_transport' => 'p.id_transport',
            'name' => 'p.name',
            'nationality' => 'p.nationality',
            'type_document' => 'p.type_document',
            'data_document' => 'p.data_document',
            'type' => 'p.type',
            'approved' => 'p.approved',
            'served' => 'p.served',
            'date_trip' => 'p.date_trip',
            'date_birth' => 'p.date_birth',
        ];

        parent::__construct(
            $this->table_passengers,
            $select,
            $orders_allowed,
            $filters_allowed,
            LogSourceConstants::PASSENGER,
        );
    }

    protected function build_count_query(): string
    {
        global $wpdb;
        return "SELECT COUNT(DISTINCT p.id)
        FROM {$this->table_passengers} p
        LEFT JOIN {$this->table_routes} r ON r.id = p.id_route
        LEFT JOIN {$this->table_tickets} t ON t.id = p.id_ticket
        LEFT JOIN {$this->table_transports} tr ON tr.id = p.id_transport
        LEFT JOIN {$wpdb->prefix}posts o ON o.id = t.id_order
        LEFT JOIN {$wpdb->prefix}postmeta om ON om.post_id = o.id";
    }

    protected function create_order(string $order_by, string $order)
    {
        $base_order = parent::create_order($order_by, $order);
        return " GROUP BY p.id {$base_order}";
    }

    protected function create_filter(array $args)
    {
        if (isset($args['served'])) {
            $args['served'] = $args['served'] === 'true' || $args['served'] === true ? 1 : 0;
        }
        if (isset($args['name'])) {
            $args['name'] = "<percent>{$args['name']}<percent>";
        }
        if (isset($args['approved'])) {
            $args['approved'] = $args['approved'] === 'true' || $args['approved'] === true ? 1 : 0;
        }
        if (isset($args['data_document'])) {
            $args['data_document'] = "<percent>{$args['data_document']}<percent>";
        }
        if (isset($args['ticket_flexible'])) {
            $args['ticket_flexible'] = $args['ticket_flexible'] === 'true' || $args['ticket_flexible'] === true ? 1 : 0;
        }
        global $wpdb;
        $result = parent::create_filter($args);
        $result = str_replace('<percent>', '%', $result);
        if (isset($args['date_trip'])) {
            return $result;
        } else if (
            isset($args['date_trip_from']) &&
            isset($args['date_trip_to'])
        ) {
            return $result . $wpdb->prepare(
                " AND p.date_trip BETWEEN %s AND %s",
                $args['date_trip_from'],
                $args['date_trip_to'],
            );
        } else if (
            isset($args['date_trip_from']) &&
            !isset($args['date_trip_to'])
        ) {
            return $result . $wpdb->prepare(
                " AND p.date_trip >= %s",
                $args['date_trip_from'],
            );
        } else if (
            !isset($args['date_trip_from']) &&
            isset($args['date_trip_to'])
        ) {
            return $result . $wpdb->prepare(
                " AND p.date_trip <= %s",
                $args['date_trip_to'],
            );
        } else {
            return $result;
        }
    }

    /**
     * @param Passenger $passenger
     * @return Passenger
     */
    protected function process_save($passenger)
    {
        global $wpdb;
        $data = [
            'id_ticket' => $passenger->get_ticket()->id,
            'name' => $passenger->name,
            'nationality' => $passenger->nationality,
            'type_document' => $passenger->type_document,
            'data_document' => $passenger->data_document,
            'birthday' => $passenger->birthday,
            'type' => $passenger->type,
            'served' => $passenger->served ? 1 : 0,
            'approved' => $passenger->approved ? 1 : 0,
            'date_trip' => $passenger->date_trip,
            'id_route' => $passenger->get_route()->id,
            'id_transport' => $passenger->get_transport()->id,
        ];
        $format = [
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%d',
            '%s',
            '%d',
            '%d',
        ];
        if ($this->exists($passenger->id)) {
            $wpdb->update(
                $this->table_passengers,
                $data,
                ['id' => $passenger->id],
                $format,
                ['%d']
            );
        } else {
            $wpdb->insert(
                $this->table_passengers,
                $data,
                $format,
            );
            $passenger->id = $wpdb->insert_id;
        }
        $parser = new PassengerArray;
        if ($passenger->served) {
            WebhookManager::get_instance()->trigger(
                WebhookTopicConstants::PASSENGER_SERVED,
                $parser->get_array($passenger)
            );
        }
        if ($passenger->approved) {
            WebhookManager::get_instance()->trigger(
                WebhookTopicConstants::PASSENGER_APPROVED,
                $parser->get_array($passenger)
            );
        }
        return $passenger;
    }

    protected function verify($entity)
    {
        $ticket = $entity->get_ticket();
        if (!isset($ticket)) {
            throw new Exception('Se intentó guardar un pasajero sin antes asignarle un ticket.');
        }
        $ticket = $this->ticket_repository->find($ticket->id);
        if ($ticket === null) {
            throw new Exception("El ticket con el ID {$entity->get_ticket()->id} asociado al pasajero no existe.");
        }
        $route = $entity->get_route();
        if (!isset($route)) {
            throw new Exception('Se intentó guardar un pasajero sin antes asignarle una ruta.');
        }
        $route = $this->route_repository->find($route->id);
        if ($route === null) {
            throw new Exception('La ruta asociado al pasajero no existe.');
        }
        $transport = $entity->get_transport();
        if (!isset($transport)) {
            throw new Exception('Se intentó guardar un pasajero sin antes asignarle un transporte.');
        }
        $transport = $this->transport_repository->find($transport->id);
        if ($transport === null) {
            throw new Exception('El transporte asociado al pasajero no existe.');
        }
        $entity->set_route($route);
        $entity->set_ticket($ticket);
        $entity->set_transport($transport);
    }

    protected function result_to_entity(mixed $result)
    {
        $ticket_detail = new Passenger;
        $ticket_detail->id = $result->id;
        $ticket_detail->approved = $result->approved == 1;
        $ticket_detail->name = $result->name;
        $ticket_detail->type = $result->type;
        $ticket_detail->served = $result->served == 1;
        $ticket_detail->nationality = $result->nationality;
        $ticket_detail->type_document = $result->type_document;
        $ticket_detail->data_document = $result->data_document;
        $ticket_detail->date_trip = $result->date_trip;
        $ticket_detail->birthday = $result->birthday;
        return $ticket_detail;
    }
}
