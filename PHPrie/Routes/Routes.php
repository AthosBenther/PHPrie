<?php

namespace PHPrie\Routes;

use PHPrie\Controllers\ArchRoadsController;
use PHPrie\Controllers\GoogleHeightsController;

class Routes
{
    private $routes = [
        'google' => GoogleHeightsController::class,
        'arch' => ArchRoadsController::class
    ];
    function Get(string $route, array $params, string $fn, array $fnParams)
    {
        if (array_key_exists($route, $this->routes)) {
            $className = $this->routes[$route];
            return new $className(...$params)->$fn(...$fnParams);
        } else {
            throw new \Exception("\n\nRoute not found: " . $route . "\nAvailable routes: " . implode(", ", array_keys($this->routes)) . "\n\n");
        }
    }
}