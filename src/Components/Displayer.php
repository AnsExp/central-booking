<?php
namespace CentralTickets\Components;

/**
 * Interface Displayer
 *
 * Provides a contract for components that can be displayed.
 * Implementing classes should define the display logic for rendering output.
 */
interface Displayer
{
    /**
     * Display the component output.
     *
     * @return void
     */
    public function display();
}
    