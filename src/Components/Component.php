<?php
namespace CentralTickets\Components;
/**
 * A generic interface for components that can generate compact representations.
 *
 * This interface ensures that any implementing class provides a mechanism to
 * produce a compact representation, typically used for rendering or serialization.
 */
interface Component
{
    /**
     * Generates a compact representation of the component.
     *
     * This method is intended to return a string representation of the component,
     * such as an HTML element or other serialized format. The exact implementation
     * depends on the concrete class that implements this interface.
     *
     * @return string The compact representation of the component.
     */
    public function compact();
}

