<?php

namespace FrankaMini;

require __DIR__ . '/vendor/autoload.php';

use FrankaMini\GeoJson;
use FrankaMini\GoogleHeights;

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

GoogleHeights::init();

$geoJson = new GeoJson("QGIS/gtt_export/roads.geojson",2048,2048,2048,2048);
$geoJson->cacheGoogleHeights();
$geoJson->applyGoogleHeights();