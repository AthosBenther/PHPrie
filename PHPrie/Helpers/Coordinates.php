<?php

namespace PHPrie\Helpers;

class Coordinates
{
    static function latToM($latitude)
    {
        // Earth's radius in meters
        $earthRadius = 6371000;

        // Convert latitude from degrees to radians
        $latitudeInRadians = deg2rad($latitude);

        // Calculate the distance in meters using Haversine formula
        $distanceInMeters = $earthRadius * $latitudeInRadians;

        return $distanceInMeters;
    }

    static function mToLat($distanceInMeters)
    {
        // Earth's radius in meters
        $earthRadius = 6371000;

        // Calculate latitude in radians
        $latitudeInRadians = $distanceInMeters / $earthRadius;

        // Convert radians to degrees
        $latitude = rad2deg($latitudeInRadians);

        return $latitude;
    }

    static function longToM($longitude)
    {
        // Earth's radius in meters
        $earthRadius = 6371000;

        // Convert longitude from degrees to radians
        $longitudeInRadians = deg2rad($longitude);

        // Calculate the distance in meters using Haversine formula
        $distanceInMeters = $earthRadius * $longitudeInRadians * cos(deg2rad(0));

        return $distanceInMeters;
    }

    static function mToLong($distanceInMeters)
    {
        // Earth's radius in meters
        $earthRadius = 6371000;

        // Calculate longitude in radians
        $longitudeInRadians = $distanceInMeters / ($earthRadius * cos(deg2rad(0)));

        // Convert radians to degrees
        $longitude = rad2deg($longitudeInRadians);

        return $longitude;
    }
}
