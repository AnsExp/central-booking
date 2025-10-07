<?php
namespace CentralTickets\Components;

class InputComponent extends FormControlComponent
{
    public function __construct(string $name, string $type = 'text')
    {
        parent::__construct($name);
        $this->id = "input-$name-" . rand();
        $this->set_attribute('type', $type);
    }

    public function set_value(mixed $value)
    {
        $this->set_attribute('value', $value);
    }

    public function set_placeholder(string $placeholder)
    {
        $this->set_attribute('placeholder', $placeholder);
    }
}
