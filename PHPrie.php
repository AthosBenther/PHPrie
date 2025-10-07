<?php

namespace PHPrie;

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use PHPrie\Routes\Routes;
use PHPrie\Helpers\Console;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$routes = new Routes();

$controller = $argv[1];
$fn = getArg("fn");

$params = array_filter(getArg("p", null, true), fn($v) => $v !== '' && $v !== null);
$fnParams = array_filter(getArg("fnp", null, true), fn($v) => $v !== '' && $v !== null);

// dd([
//     'controller' => $controller,
//     'params' => $params,
//     'fn' => $fn,
//     'fnParams' => $fnParams
// ]);

try {
    $routes->Get($controller, $params, $fn, $fnParams);
} catch (\Exception $e) {
    Console::log($e->getMessage());
}

function getArg(string $argName, $default = null, $toArray = false)
{
    global $argv;
    $result = $default;
    foreach ($argv as $arg) {
        if (str_starts_with($arg, "--$argName=")) {
            $result = substr($arg, strlen($argName) + 3);
            break;
        }
    }
    if ($toArray) {
        return $result ? explode(',', $result) : [];
    }
    return $result;
}