<?php

namespace FrankaMini;

require __DIR__ . '/vendor/autoload.php';

use FrankaMini\Coordinates;
use FrankaMini\GeoJson;

$geoJson = new GeoJson("google/RoadsWithHeigth.geojson",2048,2048,2048,2048);
$svg = $geoJson->toSVG();
//$decalJson = $geoJson->toDecalJson();
$meshJson = $geoJson->toMeshJson();

file_put_contents("import/roads.svg",$svg);
//file_put_contents("main/MissionGroup/Roads/Decal/Asphalt-Decal/items.level.json",$decalJson);
file_put_contents("main/MissionGroup/Roads/Mesh/Asphalt-Mesh/items.level.json",$meshJson);