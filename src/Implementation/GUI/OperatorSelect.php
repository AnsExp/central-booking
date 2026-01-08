<?php
namespace CentralBooking\Implementation\GUI;

use CentralBooking\Data\Services\OperatorService;
use CentralBooking\GUI\MultipleSelectComponent;
use CentralBooking\GUI\SelectComponent;

class OperatorSelect
{
    private OperatorService $service;

    public function __construct(private string $name = 'operator')
    {
        $this->service = new OperatorService();
    }

    public function create(bool $multiple = false)
    {
        $operators = $this->service->findAll();

        $selectComponent = $multiple
            ? new MultipleSelectComponent($this->name)
            : new SelectComponent($this->name);

        $selectComponent->addOption('Seleccione...', '');

        foreach ($operators as $operator) {
            $selectComponent->addOption(
                $operator->getUser()->user_login,
                $operator->getUser()->ID
            );
        }

        return $selectComponent;
    }
}