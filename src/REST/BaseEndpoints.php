<?php
namespace CentralTickets\REST;

use CentralTickets\REST\Controllers\BaseController;

/**
 * @template T
 */
abstract class BaseEndpoints
{
    protected string $root;
    /**
     * @var BaseController<T>
     */
    protected BaseController $controller;

    /**
     * @param string $route
     * @param BaseController<T> $controller
     */
    protected function __construct(string $route, BaseController $controller)
    {
        $this->root = $route;
        $this->controller = $controller;
    }

    public function init_endpoints()
    {
        RegisterRoute::register(
            $this->root,
            'GET',
            [$this->controller, 'get_all']
        );
        RegisterRoute::register(
            $this->root,
            'POST',
            [$this->controller, 'post']
        );
        RegisterRoute::register(
            "{$this->root}/(?P<id>\d+)",
            'GET',
            [$this->controller, 'get']
        );
        RegisterRoute::register(
            "{$this->root}/(?P<id>\d+)",
            'PUT',
            [$this->controller, 'put'],
            [
                'id' => [
                    'validate_callback' => fn($param) => is_numeric($param)
                ]
            ],
        );
        RegisterRoute::register(
            "{$this->root}/page/(?P<page_number>\d+)",
            'GET',
            [$this->controller, 'page'],
            [
                'page' => [
                    'validate_callback' => fn($param) => (is_numeric($param) && $param > 0)
                ]
            ]
        );
    }
}
