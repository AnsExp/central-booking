<?php
namespace CentralTickets\Components\Implementation;

use CentralTickets\Components\MultipleSelectComponent;
use CentralTickets\Components\SelectComponent;
use CentralTickets\Constants\TicketConstants;

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

        $selectComponent->add_option(
            'Seleccione...',
            ''
        );

        foreach (TicketConstants::all_status() as $status) {
            $selectComponent->add_option(
                git_get_text_by_status($status),
                $status
            );
        }

        return $selectComponent;
    }
}