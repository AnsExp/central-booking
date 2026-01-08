<?php
namespace CentralBooking\Implementation\GUI;

use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\GUI\MultipleSelectComponent;
use CentralBooking\GUI\SelectComponent;

class TicketStatusSelect
{
    public function __construct(private string $name = 'status')
    {
    }

    public function create(bool $multiple = false)
    {
        $selectComponent = $multiple
            ? new MultipleSelectComponent($this->name)
            : new SelectComponent($this->name);

        $selectComponent->addOption(
            'Seleccione...',
            ''
        );

        foreach (TicketStatus::cases() as $status) {
            $selectComponent->addOption(
                $status->label(),
                $status->value
            );
        }

        return $selectComponent;
    }
}