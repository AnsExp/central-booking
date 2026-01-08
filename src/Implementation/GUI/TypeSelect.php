<?php
namespace CentralBooking\Implementation\GUI;

use CentralBooking\Data\Constants\TransportConstants;
use CentralBooking\GUI\MultipleSelectComponent;
use CentralBooking\GUI\SelectComponent;

class TypeSelect
{
    public function __construct(private string $name = 'type')
    {
    }

    public function create(bool $multiple = false)
    {
        $container = $multiple ? new MultipleSelectComponent($this->name) : new SelectComponent($this->name);

        $container->addOption('Seleccione...', '');

        foreach (TransportConstants::cases() as $type) {
            $container->addOption(
                $type->label(),
                $type->value,
            );
        }

        return $container;
    }
}
