<?php
namespace CentralTickets\Components;

abstract class FormControlComponent extends StandaloneComponent
{
    public function __construct(string $name = '')
    {
        parent::__construct('input');
        $this->id = "input-$name-" . rand();
        $this->class_list->add('form-control');
        $this->set_attribute('name', $name);
    }

    public function get_label(string $text)
    {
        $label = new TextComponent('label', $text);
        if ($this->get_attribute('required') !== null) {
            $label->append(git_string_to_component('<span class="required">*</span>'));
        }
        $label->class_list->add('form-label');
        if (!empty($this->id)) {
            $label->set_attribute('for', $this->id);
        }
        return $label;
    }

    public function disabled(bool $disabled)
    {
        if ($disabled) {
            $this->set_attribute('disabled', '');
        } else {
            $this->remove_attribute('disabled');
        }
    }

    public function set_required(bool $required)
    {
        if ($required) {
            $this->set_attribute('required', '');
        } else {
            $this->remove_attribute('required');
        }
    }

    /**
     * @param bool|int|string $value
     * @return void
     */
    abstract public function set_value(mixed $value);
}
