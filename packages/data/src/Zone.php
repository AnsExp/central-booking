<?php
namespace CentralBooking\Data;

use CentralBooking\Data\Repository\LazyLoader;

/**
 * Class Zone
 *
 * Representa una zona geográfica o lógica dentro del sistema CentralBooking.
 * Cada zona puede contener múltiples ubicaciones (Location).
 *
 * @package CentralBooking\Data
 */
final class Zone
{
    /**
     * ID único de la zona.
     *
     * @var int
     */
    public int $id = 0;

    /**
     * Nombre descriptivo de la zona.
     *
     * @var string
     */
    public string $name = '';

    /**
     * Lista de ubicaciones asociadas a esta zona.
     *
     * @var Location[]
     */
    private array $locations = [];

    /**
     * Obtiene las ubicaciones asociadas a esta zona.
     * Si no están cargadas, se obtienen mediante LazyLoader.
     *
     * @return Location[]
     */
    public function getLocations()
    {
        if (empty($this->locations)) {
            $this->locations = LazyLoader::loadLocationsByZone($this);
        }
        return $this->locations;
    }

    /**
     * Asigna una lista de ubicaciones a esta zona.
     *
     * @param Location[] $locations
     * @return void
     */
    public function setLocations(array $locations)
    {
        $this->locations = $locations;
    }
}