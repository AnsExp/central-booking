<?php
namespace CentralTickets\REST;

class RegisterRoute
{
    private function __construct()
    {
    }

    public const prefix = 'api_git_central/';

    public static function register(string $route, string $methods, callable $callback, array $args = [])
    {
        register_rest_route(
            RegisterRoute::prefix,
            $route,
            [
                'methods' => $methods,
                'callback' => $callback,
                'args' => $args,
                'permission_callback' => '__return_true'
            ]
        );
    }

    public static function secure(string $route, string $methods, callable $callback, array $args = [], array $roles = [])
    {
        register_rest_route(
            RegisterRoute::prefix,
            $route,
            [
                'methods' => $methods,
                'callback' => $callback,
                'args' => $args,
                'permission_callback' => fn() => self::user_has_role($roles)
            ]
        );
    }

    private static function user_has_role(array $allowed_roles)
    {
        $user = wp_get_current_user();
        foreach ($allowed_roles as $role)
            if (in_array($role, $user->roles))
                return true;
        return false;
    }
}
