<?php
namespace CentralTickets\Components\Implementation;

use CentralTickets\Components\MultipleSelectComponent;
use CentralTickets\Components\SelectComponent;
use CentralTickets\Transport;
use CentralTickets\Persistence\TransportRepository;

class TransportSelect
{
    /**
     * @var array<Transport>
     */
    private array $transports;

    public function __construct(private string $name = 'transport')
    {
        $repository = new TransportRepository;
        $this->transports = $repository->find_by(order_by: 'nicename') ?? [];
    }

    public function create(bool $multiple = false)
    {
        $selectComponent = $multiple
            ? new MultipleSelectComponent($this->name)
            : new SelectComponent($this->name);

        $selectComponent->add_option('Seleccione...', '');

        foreach ($this->transports as $transport) {
            if (in_array('operator', $transport->get_operator()->roles)) {
                $selectComponent->add_option($transport->nicename, $transport->id);
            } else {
                $selectComponent->add_option($transport->nicename . ' (Sin Operador)', $transport->id, false, ['disabled' => '']);
            }
        }

        return $selectComponent;
    }
}