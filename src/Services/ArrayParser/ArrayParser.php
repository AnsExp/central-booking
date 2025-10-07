<?php
namespace CentralTickets\Services\ArrayParser;

/**
 * @template T
 */
interface ArrayParser
{
    /**
     * @param T $entity
     * @return array
     */
    public function get_array($entity);
}
