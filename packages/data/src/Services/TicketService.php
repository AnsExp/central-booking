<?php
namespace CentralBooking\Data\Services;

use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\Data\Date;
use CentralBooking\Data\ORM\ORMInterface;
use CentralBooking\Data\ORM\TicketORM;
use CentralBooking\Data\Passenger;
use CentralBooking\Data\Repository\PassengerRepository;
use CentralBooking\Data\Repository\TicketRepository;
use CentralBooking\Data\Ticket;
use Exception;

final class TicketService
{
    private PassengerRepository $passengerRepository;
    private TicketRepository $ticketRepository;
    private ORMInterface $ormTicket;
    private static ?TicketService $instance = null;

    /**
     * @return TicketService
     */
    public static function getInstance(): TicketService
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $wpdb = $GLOBALS['wpdb'];
        if ($wpdb) {
            $this->ticketRepository = new TicketRepository(
                $wpdb
            );
            $this->passengerRepository = new PassengerRepository(
                $wpdb
            );
            $this->ormTicket = new TicketORM();
        } else {
            throw new Exception('Error en la variable global wpdb');
        }
    }

    public function saveProofPayment(Ticket $ticket, float $amount, string $code, mixed $file = null)
    {
        $results = $this->find(['id' => $ticket->id]);
        if (!$results->hasItems()) {
            throw new Exception("El ticket con el ID {$ticket->id} no existe.");
        }

        $proofPayment = $ticket->getProofPayment();

        if ($file !== null && $file['name'] !== '' && $file['path'] !== '' && $file['tmp_name'] !== '') {
            $original_name = $file['name'];
            $unique_name = $this->generateUniqueFilename($original_name);
            $upload_dir = wp_upload_dir();
            $destination = $upload_dir['path'] . '/' . $unique_name;

            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                throw new Exception('Error al mover el archivo subido.');
            }
            $proofPayment->date = new Date('Y-m-d');
            $proofPayment->filename = $original_name;
            $proofPayment->url = get_site_url() . '/wp-content/' . explode('/wp-content/', $destination)[1];
        }

        $proofPayment->code = $code;
        $proofPayment->amount = $amount;
        $ticket->setProofPayment(
            $proofPayment->filename,
            $proofPayment->url,
            $proofPayment->date,
            $proofPayment->code,
            $proofPayment->amount,
        );

        $ticketSaved = $this->ticketRepository->save($ticket);

        if ($ticketSaved === null) {
            throw new Exception('Error al guardar el ticket con el comprobante de pago.');
        }

        $ticketSaved = $this->ticketRepository->save($ticket);

        return $ticketSaved;
    }

    /**
     * @param Ticket $ticket
     * @param array<Passenger> $passengers
     * @return Ticket
     */
    public function approvePassengers(Ticket $ticket, array $passengers)
    {
        $ticketFound = $this->ticketRepository->findById($this->ormTicket, $ticket->id);

        if ($ticketFound === null) {
            throw new Exception("El ticket con el ID {$ticket->id} no existe.");
        }

        $passengersIds = array_map(
            fn(Passenger $passenger) => $passenger->id,
            $passengers
        );

        foreach ($ticket->getPassengers() as $passenger) {
            $passenger->approved = in_array($passenger->id, $passengersIds);
            $this->passengerRepository->save($passenger);
        }

        return $ticket;
    }

    private function generateUniqueFilename(string $original_name)
    {
        $extension = pathinfo($original_name, PATHINFO_EXTENSION);
        $microtime = microtime(true);
        $timestamp = date('dHis', intval($microtime));
        $milliseconds = sprintf('%03d', ($microtime - intval($microtime)) * 1000);
        return "$timestamp$milliseconds.$extension";
    }

    public function save(Ticket $ticket)
    {
        if ($ticket->total_amount < 0) {
            throw new Exception('El monto total del ticket debe ser mayor o igual que cero');
        }
        $ticketSaved = $this->ticketRepository->save($ticket);
        if ($ticketSaved !== null) {
            foreach ($ticket->getPassengers() as $passenger) {
                $passenger->setTicket($ticketSaved);
                $this->passengerRepository->save($passenger);
            }
        }
        return $ticketSaved;
    }

    public function find(
        array $args = [],
        string $orderBy = 'id',
        string $order = 'ASC',
        int $limit = -1,
        int $offset = 0,
    ) {
        return $this->ticketRepository->find(
            $this->ormTicket,
            $args,
            $orderBy,
            $order,
            $limit,
            $offset
        );
    }
}
