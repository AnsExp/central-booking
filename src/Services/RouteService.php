<?php
namespace CentralTickets\Services;

use CentralTickets\Route;
use CentralTickets\Constants\TransportConstants;
use CentralTickets\Persistence\RouteRepository;

/**
 * @extends parent<Route>
 */
class RouteService extends BaseService
{

    public function __construct()
    {
        parent::__construct(new RouteRepository);
    }

    /**
     * @param Route $route
     * @return bool
     */
    protected function verify($route)
    {
        $this->error_stack = [];
        $pass = true;
        if ($route->distance_km <= 0) {
            $this->error_stack[] = 'La distancia de la ruta no puede ser negativa o igual a cero.';
            $pass = false;
        }
        if (!TransportConstants::is_valid($route->type)) {
            $this->error_stack[] = 'El tipo de ruta no está disponible.';
            $pass = false;
        }
        return $pass;
    }
}
