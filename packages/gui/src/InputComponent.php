<?php
namespace CentralBooking\GUI;

class InputComponent extends FormControlComponent
{
    public function __construct(string $name, string $type = 'text')
    {
        parent::__construct($name);
        $this->id = "input-$name-" . rand();
        $this->attributes->set('type', $type);
    }

    public function setValue($value)
    {
        $this->attributes->set('value', $value);
    }

    public function setPlaceholder(string $placeholder)
    {
        $this->attributes->set('placeholder', $placeholder);
    }
}
