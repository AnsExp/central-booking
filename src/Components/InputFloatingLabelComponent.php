<?php
namespace CentralTickets\Components;

class InputFloatingLabelComponent extends BaseComponent
{
    public function __construct(
        private readonly FormControlComponent $input,
        private readonly string $text
    ) {
        parent::__construct('div');
        $this->class_list->add('form-floating');
    }

    public function compact()
    {
        $html = parent::compact();
        $html .= $this->input->compact();
        $html .= $this->input->get_label($this->text)->compact();
        $html .= "</{$this->tag}>";
        return $html;
    }
}
