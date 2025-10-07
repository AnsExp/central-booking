<?php
namespace CentralTickets\Components\Implementation;

use CentralTickets\Components\MultipleSelectComponent;
use CentralTickets\Components\SelectComponent;
use CentralTickets\Persistence\OperatorRepository;

class OperatorSelect
{
    private string $name;
    private OperatorRepository $operator_repository;

    public function __construct(string $name = 'operator')
    {
        $this->operator_repository = new OperatorRepository();
        $this->name = $name;
    }

    public function create(bool $multiple = false)
    {
        $operators = $this->operator_repository->find_by();

        $selectComponent = $multiple
            ? new MultipleSelectComponent($this->name)
            : new SelectComponent($this->name);

        $selectComponent->add_option('Seleccione...', '');

        foreach ($operators as $operator) {
            $selectComponent->add_option(
                $operator->user_login,
                $operator->ID
            );
        }

        return $selectComponent;
    }
}