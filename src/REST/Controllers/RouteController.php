<?php
namespace CentralTickets\REST\Controllers;

use CentralTickets\Route;
use CentralTickets\Services\ArrayParser\RouteArray;
use CentralTickets\Services\PackageData\RouteData;
use CentralTickets\Services\RouteService;

/**
 * @extends parent<Route>
 */
class RouteController extends BaseController
{

    public function __construct()
    {
        parent::__construct(
            new RouteService,
            new RouteArray()
        );
    }

    protected function parse_payload(array $payload)
    {
        return new RouteData(
            $payload['origin'] ?? -1,
            $payload['destiny'] ?? -1,
            $payload['type'] ?? '',
            $payload['departure_time'] ?? '',
            $payload['duration_trip'] ?? '',
            $payload['distance'] ?? -1,
            $payload['transports'] ?? [],
        );
    }

    /**
     * @param RouteData $route
     * @return bool
     */
    protected function validate($route)
    {
        $pass = true;
        $this->issues = [];
        if (!$this->is_valid_time_format($route->duration)) {
            $this->issues[] = "Formato de hora incorrecta en la duración: $route->duration";
            $pass = false;
        }
        if (!$this->is_valid_time_format($route->departure_time)) {
            $this->issues[] = "El horario no tienen el formato asecuado";
            $pass = false;
        }
        if ($route->type === '') {
            $this->issues[] = "Tipo de ruta no válida: $route->type. Rutas permitidas: aero, land, marine";
            $pass = false;
        }
        return $pass;
    }

    private function is_valid_time_format(string $time): bool
    {
        return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $time) === 1;
    }
}
