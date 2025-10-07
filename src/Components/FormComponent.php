<?php
namespace CentralTickets\Components;

use CentralTickets\Components\Constants\ButtonActionConstants;
use CentralTickets\Components\Constants\ButtonStyleConstants;

class FormComponent extends CompositeComponent
{
    public function __construct()
    {
        parent::__construct('form');
        $this->id = 'form-' . uniqid();
        $this->set_attribute('enctype', 'multipart/form-data');
    }

    public function set_method(string $method)
    {
        $this->set_attribute('method', $method);
    }

    public function set_action(string $action)
    {
        $this->set_attribute('action', $action);
    }

    public function get_submit_button(string $text = 'Submit')
    {
        $button = new ButtonComponent($text, ButtonActionConstants::SUBMIT, ButtonStyleConstants::PRIMARY);
        $button->set_attribute('form', $this->id);
        return $button;
    }
}
