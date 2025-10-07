<?php

$path = 'archRoads.json';

$data = file_get_contents($path);

$data = json_decode($data, true);

foreach ($data['data']['roads'] as &$road) {
    $road['profile']['fadeE'] = 100;
    $road['profile']['fadeS'] = 100;
}

file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));