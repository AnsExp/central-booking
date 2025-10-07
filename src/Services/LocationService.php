<?php
namespace CentralTickets\Services;

use CentralTickets\Location;
use CentralTickets\Persistence\LocationRepository;
use CentralTickets\Persistence\RouteRepository;
use CentralTickets\Persistence\ZoneRepository;

/**
 * @extends parent<Location>
 */
class LocationService extends BaseService
{
    private ZoneRepository $zone_repository;
    private RouteRepository $route_repository;

    public function __construct()
    {
        parent::__construct(new LocationRepository);
        $this->zone_repository = new ZoneRepository;
        $this->route_repository = new RouteRepository;
    }

    public function remove(int $id)
    {
        return parent::remove($id);
    }

    /**
     * @param Location $location
     * @return bool
     */
    protected function verify($location)
    {
        $pass = true;
        if (!$this->verify_field($location, 'name')) {
            $this->error_stack = ['El nombre ya está ocupado.'];
            $pass = false;
        }
        if (!$this->zone_repository->exists($location->get_zone()->id)) {
            $this->error_stack = ['Se asigno una zona no existente a una locación.'];
            $pass = false;
        }
        return $pass;
    }
}
