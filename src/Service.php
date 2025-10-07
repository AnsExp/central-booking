<?php
namespace CentralTickets;

use CentralTickets\Persistence\TransportRepository;

class Service
{
    public int $id = 0;
    public string $name = '';
    public string $icon = '';
    public int $price = 0;
    /**
     * @var array<Transport>
     */
    private array $transports;

    public function get_transports()
    {
        if (!isset($this->transports)) {
            $repository = new TransportRepository;
            $this->transports = $repository->find_by(
                ['id_service' => $this->id],
                order_by: 'nicename',
                order: 'ASC'
            );
        }
        return $this->transports;
    }

    /**
     * @param array<Transport> $transports
     * @return void
     */
    public function set_transports(array $transports)
    {
        $this->transports = $transports;
    }

    public function add_transport(Transport $transport)
    {
        $this->transports[] = $transport;
    }
}
