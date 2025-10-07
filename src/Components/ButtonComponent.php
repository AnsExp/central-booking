<?php
namespace CentralTickets\Components;

use CentralTickets\Components\Constants\ButtonActionConstants;
use CentralTickets\Components\Constants\ButtonStyleConstants;

class ButtonComponent extends BaseComponent
{
    private string $text;
    private string $type = ButtonActionConstants::BUTTON;
    private string $style = ButtonStyleConstants::NONE;

    /**
     * @param string|Component $text
     * @param string $type
     * @param string $style
     */
    public function __construct(
        $text,
        string $type = ButtonActionConstants::BUTTON,
        string $style = ButtonStyleConstants::NONE,
    ) {
        parent::__construct('button');
        $this->set_text($text);
        $this->set_type($type);
        $this->set_style($style);
    }

    public function compact()
    {
        $this->set_attribute('type', $this->type);
        $this->class_list->add(...explode(' ', $this->style));
        $html = parent::compact();
        $html .= $this->text;
        $html .= "</{$this->tag}>";
        return $html;
    }

    /**
     * @param string|Component $text
     * @return void
     */
    public function set_text($text)
    {
        // $this->text = $text instanceof Component ? $text->compact() : $text;
        $this->text = $text instanceof Component ? $text->compact() : htmlspecialchars($text);
    }

    public function set_type(string $type)
    {
        $this->type = htmlspecialchars($type);
    }

    public function set_style(string $style)
    {
        $this->style = htmlspecialchars($style);
    }
}
