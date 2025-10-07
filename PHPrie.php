<?php

namespace PHPrie;

require __DIR__ . '/vendor/autoload.php';

use PHPrie\Routes\Routes;
use PHPrie\Helpers\Console;

$routes = new Routes();

$controller = $argv[1];
//$params = getArg("p", null, true);
$fn = getArg("fn");
//$fnParams = getArg("fnp", null, true);

$params = array_filter(getArg("p", null, true), fn($v) => $v !== '' && $v !== null);
$fnParams = array_filter(getArg("fnp", null, true), fn($v) => $v !== '' && $v !== null);

// var_dump([
//     'controller' => $controller,
//     'params' => $params,
//     'fn' => $fn,
//     'fnParams' => $fnParams
// ]);
//die();

try {
    $routes->Get($controller, $params, $fn, $fnParams);
} catch (\Exception $e) {
    echo $e->getMessage();
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