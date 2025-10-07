<?php
namespace CentralTickets\Services;

use CentralTickets\Constants\LogLevelConstants;
use CentralTickets\Constants\LogSourceConstants;
use CentralTickets\Services\LogService;
use CentralTickets\Ticket;
use CentralTickets\Constants\TicketConstants;
use CentralTickets\Persistence\OperatorRepository;
use CentralTickets\Persistence\PassengerRepository;
use CentralTickets\Persistence\TicketRepository;

/**
 * @extends parent<Ticket>
 */
class TicketService extends BaseService
{
    private TicketNotifier $ticket_notifier;
    private PassengerService $passenger_service;
    private OperatorRepository $operator_repository;
    private PassengerRepository $passenger_repository;

    public function __construct()
    {
        parent::__construct(new TicketRepository);
        $this->ticket_notifier = new TicketNotifier;
        $this->passenger_service = new PassengerService;
        $this->operator_repository = new OperatorRepository;
        $this->passenger_repository = new PassengerRepository;
    }

    public function save_proof_payment(int $id_ticket, float $amount, string $code, mixed $file = null)
    {
        $this->error_stack = [];
        $ticket = $this->repository->find($id_ticket);
        if ($ticket === null) {
            $this->error_stack[] = "El ticket con el ID $id_ticket no existe.";
            return null;
        }
        $proof_payment = $ticket->get_meta('proof_payment') ?? [];

        if ($file !== null && $file['name'] !== '' && $file['path'] !== '' && $file['tmp_name'] !== '') {
            $original_name = $file['name'];
            $unique_name = $this->generate_unique_filename($original_name);
            $upload_dir = wp_upload_dir();
            $destination = $upload_dir['path'] . '/' . $unique_name;

            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                $this->error_stack[] = 'Error a la hora de almacenar el archivo.';
                return null;
            }
            $proof_payment['date'] = date('Y-m-d');
            $proof_payment['name'] = $original_name;
            $proof_payment['path'] = get_site_url() . '/wp-content/' . explode('/wp-content/', $destination)[1];
        }

        $proof_payment['code'] = $code;
        $proof_payment['amount'] = intval($amount * 100);

        $ticket->set_meta('proof_payment', $proof_payment);

        $ticket_saved = $this->repository->save($ticket);

        if ($ticket_saved === null) {
            $this->error_stack[] = $this->repository->error_message;
            return null;
        }

        $ticket_saved = $this->repository->save($ticket);

        return $ticket_saved;
    }

    public function change_ticket_status(int $id_ticket, string $status)
    {
        $this->error_stack = [];
        if (!TicketConstants::is_valid_status($status)) {
            $this->error_stack[] = "El estado $status no es válido.";
            return null;
        }
        $ticket = $this->repository->find($id_ticket);
        if ($ticket === null) {
            $this->error_stack[] = "El ticket con el ID $id_ticket no existe.";
            return null;
        }
        $clone = $ticket->__clone();
        $ticket->status = $status;
        $ticket_saved = $this->repository->save($ticket);
        if ($ticket_saved === null) {
            $this->error_stack[] = $this->repository->error_message;
            return null;
        }
        $this->notify_change_status($clone, $ticket_saved);
        return $ticket_saved;
    }

    /**
     * Summary of approve_passengers
     * @param int $id_ticket
     * @param array<int> $passengers
     * @return Ticket|null
     */
    public function approve_passengers(int $id_ticket, array $passengers)
    {
        $this->error_stack = [];
        $ticket = $this->repository->find($id_ticket);

        if ($ticket === null) {
            $this->error_stack[] = "El ticket con el ID $id_ticket no existe.";
            return null;
        }

        foreach ($ticket->get_passengers() as $passenger) {
            $passenger->approved = in_array($passenger->id, $passengers);
            $this->passenger_repository->save($passenger);
        }

        if ($ticket->status === TicketConstants::PAYMENT || $ticket->status === TicketConstants::PARTIAL) {
            $this->ticket_notifier->notify($ticket);
        }

        return $ticket;
    }

    private function generate_unique_filename(string $original_name)
    {
        $extension = pathinfo($original_name, PATHINFO_EXTENSION);
        $microtime = microtime(true);
        $timestamp = date('dHis', intval($microtime));
        $milliseconds = sprintf('%03d', ($microtime - intval($microtime)) * 1000);
        return "$timestamp$milliseconds.$extension";
    }

    public function toggle_flexible(int $id, ?bool $force = null)
    {
        $this->error_stack = [];
        $ticket = $this->repository->find($id);
        if ($ticket === null) {
            $this->error_stack[] = "El ticket con el ID $id no existe.";
            return null;
        }
        $clone = clone $ticket;
        $ticket->flexible = $force === null ? !$ticket->flexible : $force;
        $result = $this->repository->save($ticket);
        if ($result === null) {
            $this->error_stack[] = $this->repository->error_message;
            return null;
        }
        $this->notify_change_flexible($clone, $ticket);
        return $result;
    }

    public function save($request, int $id = 0)
    {
        $ticket = parent::save($request, $id);
        if ($ticket !== null) {
            foreach ($ticket->get_passengers() as $passenger) {
                $passenger->set_ticket($ticket);
                $passenger->approved = $ticket->status === TicketConstants::PAYMENT;
                $this->passenger_repository->save($passenger);
            }
            if ($ticket->get_coupon() !== null) {
                $operator = $this->operator_repository->find_by_coupon($ticket->get_coupon());
                if ($operator !== null) {
                    $plan = $operator->get_business_plan();
                    $operator->set_business_plan(
                        $plan['limit'],
                        $plan['counter'] + 1
                    );
                    $this->operator_repository->save($operator);
                }
            }
            if ($ticket->status === TicketConstants::PAYMENT) {
                if (
                    $ticket->get_meta('qr_sent') === null ||
                    !$ticket->get_meta('qr_sent')
                ) {
                    $this->ticket_notifier->notify($ticket);
                    $ticket->set_meta('qr_sent', true);
                    $this->repository->save($ticket);
                }
            }
        }
        return $ticket;
    }

    /**
     * @param Ticket $ticket
     * @return bool
     */
    protected function verify($ticket)
    {
        $pass = true;
        $this->error_stack = [];
        if (!wc_get_order($ticket->get_order()->get_id())) {
            $this->error_stack[] = 'El pedido asociado al ticket no existe.';
            $pass = false;
        }
        if (!TicketConstants::is_valid_status($ticket->status)) {
            $this->error_stack[] = 'El status asociado al ticket no es válido.';
            $pass = false;
        }
        foreach ($ticket->get_passengers() as $index => $passenger) {
            if (!$this->passenger_service->verify_ignore_ticket($passenger)) {
                $this->error_stack[] = [
                    'passenger_index' => $index + 1,
                    'issues' => $this->passenger_service->error_stack,
                ];
                $pass = false;
            }
        }
        return $pass;
    }

    private function notify_change_status(Ticket $older_ticket, Ticket $new_ticket)
    {
        ob_start();
        ?>
        <p>
            Se ha cambiado el estado del ticket de <?= git_get_text_by_status($older_ticket->status); ?> a
            <?= git_get_text_by_status($new_ticket->status); ?>.
            <br>
            Responsable: <code><?= wp_get_current_user()->user_login; ?></code>.
        </p>
        <?php
        LogService::create_git_log(
            source: LogSourceConstants::TICKET,
            id_source: $new_ticket->id,
            message: ob_get_clean(),
            level: LogLevelConstants::INFO
        );
    }

    private function notify_change_flexible(Ticket $older_ticket, Ticket $new_ticket)
    {
        ob_start();
        ?>
        <p>
            Se ha cambiado la flexibilidad del ticket de <?= $older_ticket->flexible ? 'Sí' : 'No'; ?> a
            <?= $new_ticket->flexible ? 'Sí' : 'No'; ?>.
            <br>
            Responsable: <code><?= wp_get_current_user()->user_login; ?></code>.
        </p>
        <?php
        LogService::create_git_log(
            source: LogSourceConstants::TICKET,
            id_source: $new_ticket->id,
            message: ob_get_clean(),
            level: LogLevelConstants::INFO
        );
    }
}
