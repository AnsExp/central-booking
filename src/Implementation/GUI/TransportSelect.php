<?php
namespace CentralBooking\Implementation\GUI;

use CentralBooking\Data\Services\TransportService;
use CentralBooking\Data\Transport;
use CentralBooking\GUI\MultipleSelectComponent;
use CentralBooking\GUI\SelectComponent;

class TransportSelect
{
    /**
     * @var array<Transport>
     */
    private array $transports;

    public function __construct(private string $name = 'transport')
    {
        $repository = new TransportService();
        $this->transports = $repository->find(orderBy: 'nicename')?->getItems() ?? [];
    }

    public function create(bool $multiple = false)
    {
        $selectComponent = $multiple
            ? new MultipleSelectComponent($this->name)
            : new SelectComponent($this->name);

        $selectComponent->addOption('Seleccione...', '');

        foreach ($this->transports as $transport) {
            if (in_array('operator', $transport->getOperator()->getUser()->roles)) {
                $selectComponent->addOption($transport->nicename, $transport->id);
            } else {
                $selectComponent->addOption($transport->nicename . ' (Sin Operador)', $transport->id, false, ['disabled' => '']);
            }
        }

        return $selectComponent;
    }
}