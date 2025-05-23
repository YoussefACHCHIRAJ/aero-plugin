<?php

namespace Aero\Helpers;

use Aero\Config\ApiConfig;

class AeroRouter
{

    public static function get(string $route, callable $callBack, ?string $permission = 'administrator', ?array $args = [])
    {
        self::register('GET', $route, $callBack, $permission, $args);
    }

    public static function post(string $route, callable $callBack, ?string $permission = 'administrator', ?array $args = [])
    {
        self::register('POST', $route, $callBack, $permission, $args);
    }

    public static function put(string $route, callable $callBack, ?string $permission = 'administrator', ?array $args = [])
    {
        self::register('PUT', $route, $callBack, $permission, $args);
    }

    public static function delete(string $route, callable $callBack, ?string $permission = 'administrator', ?array $args = [])
    {
        self::register('DELETE', $route, $callBack, $permission, $args);
    }

    private static function register(string $method, string $route, callable $callBack, ?string $permission = 'administrator', ?array $args = [])
    {
        if(!$permission) $permission = 'administrator';
        
        register_rest_route(ApiConfig::AERO_NAMESPACE, $route, array(
            'methods' => $method,
            'callback' => $callBack,
            'args' => $args,
            'permission_callback' => function () use ($permission) {
                return current_user_can($permission);
            },
        ));
    }
}
