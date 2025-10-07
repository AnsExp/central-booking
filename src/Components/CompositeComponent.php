<?php
namespace CentralTickets\Components;

class CompositeComponent extends BaseComponent
{
    /**
     * @var array<Component>
     */
    protected array $childs = [];

    public function __construct(string $tag = 'div')
    {
        parent::__construct($tag);
    }

    public function add_child(Component $component)
    {
        $this->childs[] = $component;
        return $this;
    }

    public function compact()
    {
        $html = parent::compact();
        foreach ($this->childs as $child) {
            $html .= $child->compact();
        }
        $html .= "</{$this->tag}>";
        return $html;
    }
}
