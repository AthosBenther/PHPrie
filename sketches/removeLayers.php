<?php


$path = 'archRoads copy.json';
$savePath = 'archRoads.json';

$roads = json_decode(file_get_contents($path), true);

$names = [
    'Edge Line L',
    'Edge Line R',
    'Center Line',
    'Edge Blend L',
    'Edge Blend R',
];

foreach ($roads['data']['roads'] as &$road) {
    foreach ($road['profile']['layers'] as &$layer) {
        if (in_array($layer['name'], $names)) {
            $layer = null;
        }
    }
}

file_put_contents($savePath, json_encode($roads, JSON_PRETTY_PRINT));