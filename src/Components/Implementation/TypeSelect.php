<?php
namespace CentralTickets\Components\Implementation;

use CentralTickets\Components\MultipleSelectComponent;
use CentralTickets\Components\SelectComponent;
use CentralTickets\Constants\TransportConstants;

class TypeSelect
{
    public function __construct(private string $name = 'type')
    {
    }

    public function create(bool $multiple = false)
    {
        $container = $multiple ? new MultipleSelectComponent($this->name) : new SelectComponent($this->name);

        $container->add_option('Seleccione...', '');

        foreach (TransportConstants::all() as $type) {
            $container->add_option(
                git_get_text_by_type($type),
                $type
            );
        }

        return $container;
    }
}
