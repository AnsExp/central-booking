<?php
namespace CentralTickets\Components;

class SpinnerComponent implements Component
{
    private CompositeComponent $container;

    public function __construct(string $type = SpinnerConstants::PRIMARY)
    {
        $this->container = new CompositeComponent;
        $content = new TextComponent('span', 'Loading...');

        $this->container->class_list->add('spinner-border');
        $this->container->class_list->add("text-{$type}");
        $this->container->set_attribute('role', 'status');

        $content->class_list->add('visually-hidden');
        $this->container->add_child($content);
    }

    public function compact()
    {
        return $this->container->compact();
    }
}
