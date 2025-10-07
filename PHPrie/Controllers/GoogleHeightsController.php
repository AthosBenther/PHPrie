<?php

namespace PHPrie\Controllers;

use DateTime;
use Exception;

class GoogleHeightsController
{
    public static $cache = null;
    public static $reqHist = null;

    static function init()
    {
        GoogleHeights::$cache = file_exists("google/cache/cache.json") ? json_decode(file_get_contents("google/cache/cache.json"), true) : [];
        GoogleHeights::$reqHist = file_exists("google/request_history.json") ? json_decode(file_get_contents("google/request_history.json"), true) : [];
    }

    static function saveCache()
    {
        file_put_contents("google/cache/cache.json", json_encode(GoogleHeights::$cache));
    }

    static function newReqHist($reqHist)
    {
        array_push(GoogleHeights::$reqHist, $reqHist);
        file_put_contents("google/request_history.json", json_encode(GoogleHeights::$reqHist));
    }

    static function getHeights($coordinates)
    {
        $req = "https://maps.googleapis.com/maps/api/elevation/json?locations=";

        $coordinates = array_map(function ($coordinate) {
            return implode("%2C", [$coordinate[1], $coordinate[0]]);
        }, $coordinates);

        $coordsTxt = implode("%7C", $coordinates);
        $req .= $coordsTxt . "&key=" . $_ENV["GOOGLE_API_KEY"];

        $response = GoogleHeights::request($req);
        //$response = json_decode(file_get_contents("google/results.json"), true);
        $results = $response["results"];

        if (count($coordinates) !== count($results)) {
            $alala = "bololo";
        }

        GoogleHeights::toCache($results);
    }

    static private function request($req)
    {
        $response = json_decode(file_get_contents($req), true);
        $reqHist = [
            "request" => $req,
            "response" => $response,
            "date" => date("Y-m-d H:i:s")
        ];

        GoogleHeights::newReqHist($reqHist);

        return $response;
    }

    static private function toCache($results)
    {
        foreach ($results as $result) {
            $lat = $result["location"]["lat"];
            $long = $result["location"]["lng"];
            $ele = $result["elevation"];

            array_push(GoogleHeights::$cache, [$lat, $long, $ele]);
        }
        GoogleHeights::saveCache();
    }

    static function cacheFindElevation(float $lat, float $long): float | null
    {
        if (GoogleHeights::$cache == null) return null;
        $elevations =  array_filter(GoogleHeights::$cache, function ($coord) use ($lat, $long) {
            return $coord[0] == $lat && $coord[1] == $long;
        });
        $elevation = $elevations == null ? null :  array_values($elevations)[0][2];
        return $elevation;
    }
}
