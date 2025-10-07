<?php
namespace CentralTickets\Persistence;

use CentralTickets\Constants\LogSourceConstants;
use CentralTickets\Constants\WebhookTopicConstants;
use CentralTickets\Services\ArrayParser\TicketArray;
use CentralTickets\Ticket;
use CentralTickets\MetaManager;
use CentralTickets\Webhooks\WebhookManager;
use Exception;

/**
 * @extends parent<Ticket>
 */
class TicketRepository extends BaseRepository
{
    private string $table_ticket;
    private string $table_meta;
    private string $table_passengers;

    public function __construct()
    {
        global $wpdb;
        $this->table_ticket = "{$wpdb->prefix}git_tickets";
        $this->table_meta = "{$wpdb->prefix}git_meta";
        $this->table_passengers = "{$wpdb->prefix}git_passengers";

        $select = "SELECT t.*
        FROM {$this->table_ticket} t
        LEFT JOIN {$this->table_meta} tm ON (tm.meta_id = t.id AND tm.meta_type = '" . MetaManager::TICKET . "')
        LEFT JOIN {$this->table_passengers} p ON p.id_ticket = t.id
        LEFT JOIN {$wpdb->prefix}posts o ON o.id = t.id_order
        LEFT JOIN {$wpdb->prefix}posts c ON c.id = t.id_coupon
        LEFT JOIN {$wpdb->prefix}postmeta om ON om.post_id = o.id";

        $orders_allowed = [
            'price' => 't.total_amount',
            'status' => 't.status',
            'flexible' => 't.flexible',
            'passenger' => 't.passenger',
            'name_buyer' => "om.meta_key",
            'code_coupon' => "om.meta_key",
            'date_creation' => 'o.post_date',
        ];

        $filters_allowed = [
            'id' => 't.id = %d',
            'id_passenger' => 'p.id = %d',
            'id_order' => 'o.id = %d',
            'status' => 't.status = %s',
            'flexible' => 't.flexible = %d',
            'name_buyer' => 't.name_buyer = %s',
            'code_coupon' => "c.post_title = %s",
            'total_amount' => 't.total_amount = %d',
            'date_creation' => 'DATE(o.post_date_gmt) = %s',
            'id_coupon' => "c.post_type = 'shop_coupon' AND c.id = %d",
            'coupon_code' => "o.post_type = 'shop_coupon' AND om.meta_value = %s",
            'phone_buyer' => "om.meta_key = 'billing_phone' AND om.meta_value = %s",
        ];

        parent::__construct(
            $this->table_ticket,
            $select,
            $orders_allowed,
            $filters_allowed,
            LogSourceConstants::TICKET,
        );
    }

    protected function create_order(string $order_by, string $order)
    {
        $base_order = parent::create_order($order_by, $order);
        return " GROUP BY t.id {$base_order}";
    }

    protected function create_filter(array $args)
    {
        if (isset($args['flexible'])) {
            $args['flexible'] = ($args['flexible'] === 'true' || $args['flexible'] === true) ? 1 : 0;
        }
        global $wpdb;
        if (isset($args['date_creation'])) {
            return parent::create_filter($args);
        } else if (
            isset($args['date_creation_from']) &&
            isset($args['date_creation_to'])
        ) {
            return parent::create_filter($args) . $wpdb->prepare(
                " AND o.post_date BETWEEN %s AND %s",
                $args['date_creation_from'],
                $args['date_creation_to'],
            );
        } else if (
            isset($args['date_creation_from']) &&
            !isset($args['date_creation_to'])
        ) {
            return parent::create_filter($args) . $wpdb->prepare(
                " AND o.post_date >= %s",
                $args['date_creation_from'],
            );
        } else if (
            !isset($args['date_creation_from']) &&
            isset($args['date_creation_to'])
        ) {
            return parent::create_filter($args) . $wpdb->prepare(
                " AND o.post_date <= %s",
                $args['date_creation_to'],
            );
        } else {
            return parent::create_filter($args);
        }
    }

    /**
     * @param Ticket $ticket
     * @return void
     */
    protected function verify($ticket)
    {
        if (empty($ticket->get_order()) || $ticket->get_order()->get_id() === 0) {
            throw new Exception("Se intentó almacenar un ticket sin un pedido.");
        }

        if (!is_array($ticket->metadata)) {
            throw new Exception("El formato de los metadatos del ticket no es correcto.");
        }

        if ($ticket->total_amount < 0) {
            throw new Exception("Se intentó almacenar un ticket con precio negativo.");
        }

        foreach ($ticket->get_passengers() as $passenger) {
            $passenger->set_ticket($ticket);
        }
    }

    /**
     * @param Ticket $ticket
     * @return Ticket
     */
    protected function process_save($ticket)
    {
        global $wpdb;

        $data = [
            'id_order' => $ticket->get_order()->get_id(),
            'status' => $ticket->status,
            'flexible' => $ticket->flexible ? 1 : 0,
            'total_amount' => $ticket->total_amount,
        ];
        $format = ['%d', '%s', '%d', '%d'];

        if ($ticket->get_coupon() !== null) {
            $data['id_coupon'] = $ticket->get_coupon()->ID;
            $format[] = '%d';
        }
        $parser = new TicketArray();
        if ($this->exists($ticket->id)) {
            $wpdb->update(
                $this->table_ticket,
                $data,
                ['id' => $ticket->id],
                $format,
                ['%d']
            );
            WebhookManager::get_instance()->trigger(
                WebhookTopicConstants::TICKET_UPDATE,
                $parser->get_array($ticket)
            );
        } else {
            $wpdb->insert(
                $this->table_ticket,
                $data,
                $format
            );
            $ticket->id = $wpdb->insert_id;
            if ($ticket->get_coupon() !== null) {
                WebhookManager::get_instance()->trigger(
                    WebhookTopicConstants::COUPON_USED,
                    $parser->get_array($ticket)
                );
            }
            WebhookManager::get_instance()->trigger(
                WebhookTopicConstants::TICKET_CREATE,
                $parser->get_array($ticket)
            );
        }

        MetaManager::set_metadata(
            MetaManager::TICKET,
            $ticket->id,
            $ticket->metadata,
        );

        return $ticket;
    }

    protected function build_count_query(): string
    {
        global $wpdb;
        return "SELECT COUNT(DISTINCT t.id)
        FROM {$this->table_ticket} t
        LEFT JOIN {$this->table_meta} tm ON (tm.meta_id = t.id AND tm.meta_type = '" . MetaManager::TICKET . "')
        LEFT JOIN {$this->table_passengers} p ON p.id_ticket = t.id
        LEFT JOIN {$wpdb->prefix}posts o ON o.id = t.id_order
        LEFT JOIN {$wpdb->prefix}posts c ON c.id = t.id_coupon
        LEFT JOIN {$wpdb->prefix}postmeta om ON om.post_id = o.id";
    }

    protected function result_to_entity(mixed $result)
    {
        $ticket = new Ticket();
        $ticket->id = $result->id;
        $ticket->status = $result->status;
        $ticket->total_amount = $result->total_amount;
        $ticket->flexible = $result->flexible == 1;
        $order = wc_get_order(intval($result->id_order));
        $ticket->set_order($order);
        $ticket->metadata = MetaManager::get_metadata(MetaManager::TICKET, $ticket->id);
        if ($result->id_coupon) {
            $ticket->set_coupon(get_post($result->id_coupon));
        }
        return $ticket;
    }
}
