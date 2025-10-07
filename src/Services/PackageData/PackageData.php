<?php
namespace CentralTickets\Services\PackageData;

/**
 * @template T
 */
interface PackageData
{
    /**
     * @return T
     */
    public function get_data();
}
