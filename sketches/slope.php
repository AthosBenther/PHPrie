<?php

$path = 'archRoads.json';

$data = file_get_contents($path);

$date = date('Y-m-d');
$backupName = "{$date}-backup.json";

file_put_contents($backupName, $data);

$data = json_decode($data, true);

$roads = &$data['data']['roads'];

$psmsRoads = [];

foreach ($roads as &$road) {
    if (str_starts_with($road['displayName'], 'PSMS ')) {
        $psmsRoads[] = &$road;
    }
}


foreach ($psmsRoads as &$psmsRoad) {
    $nodes = &$psmsRoad['nodes'];

    slopeMeDaddy($psmsRoad);
}


function slopeMeDaddy(&$road)
{
    $nodes = &$road['nodes'];
    $cNodes = count($nodes);

    $roadLength = 0;
    $elevationDif = $nodes[count($nodes) - 1]['posZ'] - $nodes[0]['posZ'];
    $inclination = $elevationDif / $roadLength;


    // Calculate total road length
    for ($i = 0; $i < $cNodes - 1; $i++) {
        $currNode = $nodes[$i];
        if ($i + 1 < $cNodes - 1) {
            $nextNode = $nodes[$i + 1];

            $dist = sqrt(pow($currNode['posX'] - $nextNode['posX'], 2) + pow($currNode['posY'] - $nextNode['posY'], 2));
            $roadLength += $dist;
        }
    }

    // Adjust elevations
    for ($i = 0; $i < $cNodes; $i++) {
        $currNode = &$nodes[$i];

        $distFromLast = 0;
        
        if ($i > 0) {
            $lastNode = $nodes[$i - 1];
            $lastHeight = $lastNode['posZ'];
            $distFromLast = sqrt(pow($currNode['posX'] - $lastNode['posX'], 2) + pow($currNode['posY'] - $lastNode['posY'], 2));
        }
    }

}

file_put_contents('slopeTest' . $path, json_encode($data, JSON_PRETTY_PRINT));