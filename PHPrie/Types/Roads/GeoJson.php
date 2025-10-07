<?php

namespace PHPrie\Types\Roads;

use PHPrie\Controllers\GoogleHeightsController;
use PHPrie\Helpers\Coordinates;

class GeoJson extends BaseRoads
{
    public $geoArray;

    //West
    public $minLat      = 90;
    //East
    public $maxLat      = -90;
    //North
    public $minLong     = 180;
    //South
    public $maxLong     = -180;


    public $longCenter  = 0;
    public $latCenter  = 0;

    public $longDiff  = 0;
    public $latDiff  = 0;

    public $heightDiff  = 0;
    public $widthDiff  = 0;

    public $fileHeight = 0;
    public $fileWidth = 0;
    public $constraintsHeight = 0;
    public $constraintsWidth = 0;

    public $heightNormalScale = 1;
    public $widthNormalScale = 1;

    public $heightFileScale = 1;
    public $widthFileScale = 1;

    public $roadsWidths = [];
    public $ignoreIds = [];

    private $svg, $gameDecalJson, $gameMeshJson;



    function __construct(
        string $fileName,
        int $fileHeight,
        int $fileWidth,
        int $contraintsHeight,
        int $contraintsWidth,
        array $ignoreIds = null,
        array $roadsWidths = null
    ) {
        $this->geoArray = json_decode(file_get_contents($fileName), true);

        $this->fileHeight = $fileHeight;
        $this->fileWidth  = $fileWidth;
        $this->constraintsHeight = $contraintsHeight;
        $this->constraintsWidth  = $contraintsWidth;

        $this->ignoreIds = $ignoreIds ?? [-1, 5, 6];
        $this->roadsWidths = $roadsWidths ?? [
            "motorway_link" => 6,
            "primary" => 8,
            "primary_link" => 8,
            "secondary" => 6,
            "raceway" => 32,
            "road" => 6,
        ];


        //The first two elements are LONGITUDE and LATITUDE
        //long == X == WIDTH!
        //lat == Y == HEIGHT!


        $this->minMax();

        $this->longDiff = $this->maxLong - $this->minLong;
        $this->latDiff = $this->maxLat - $this->minLat;

        $this->longCenter = $this->minLong + ($this->longDiff / 2);
        $this->latCenter = $this->minLat + ($this->latDiff / 2);
        $this->widthDiff = Coordinates::longToM($this->longDiff);
        $this->heightDiff = Coordinates::latToM($this->latDiff);

        $this->widthNormalScale =  $this->constraintsWidth / $this->widthDiff;
        $this->heightNormalScale = $this->constraintsHeight / $this->heightDiff;

        $this->widthFileScale =  $this->fileWidth / $this->constraintsWidth;
        $this->heightFileScale = $this->fileHeight / $this->constraintsHeight;
    }

    function cacheGoogleHeights()
    {
        $chunk = 0;
        $chunkSize = 500;
        $coordsChunk = [];
        $chunkHistory = [];

        foreach ($this->geoArray['features'] as $featID => $feature) {
            foreach ($feature['geometry']['coordinates'] as $coordinates) {

                foreach ($coordinates as $coordID => $coord) {
                    $lat = $coord[1];
                    $long = $coord[0];
                    $elevation = GoogleHeightsController::cacheFindElevation($lat, $long);

                    if (isset($elevation)) {
                        continue;
                    } else if (count($coordsChunk) < $chunkSize) {

                        array_push($coordsChunk, $coord);
                    } else {
                        array_push($coordsChunk, $coord);
                        $coordsChunk = array_unique($coordsChunk, SORT_REGULAR);

                        if (count($coordsChunk) < $chunkSize) {
                            continue;
                        } else {
                            GoogleHeightsController::getHeights($coordsChunk);
                            $coordsChunk = [];
                            $chunk++;
                        }
                    }
                }
            }
        }
        if (count($coordsChunk) > 0) {
            GoogleHeightsController::getHeights($coordsChunk);
            $coordsChunk = [];
            $chunk++;
        }
        echo $chunk;
        return $this->geoArray;
    }

    function applyGoogleHeights()
    {
        $errors = [];
        foreach ($this->geoArray['features'] as $featID => $feature) {
            foreach ($feature['geometry']['coordinates'] as $coordGroupID => $coordGroup) {
                foreach ($coordGroup as $coordID => $coord) {
                    $lat = $coord[1];
                    $long = $coord[0];
                    $elevation = GoogleHeights::cacheFindElevation($lat, $long);

                    if (isset($elevation)) {
                        $this->geoArray['features'][$featID]['geometry']['coordinates'][$coordGroupID][$coordID] =
                            [$coord[0], $coord[1], $elevation];
                    } else {
                        echo "Elevation not found for lat $coord[1] long $coord[0]" . PHP_EOL;
                        array_push($errors, $coord);
                    }
                }
            }
        }
        file_put_contents("google/applyErrors.geojson", json_encode($errors));
        file_put_contents("google/RoadsWithHeigth.geojson", json_encode($this->geoArray));
    }

    protected function minMax()
    {

        foreach ($this->geoArray['features'] as $feature) {
            foreach ($feature['geometry']['coordinates'] as $coordinates) {
                foreach ($coordinates as $coord) {
                    $long = $coord[0];
                    $lat = $coord[1];

                    // Update min and max values
                    $this->minLat = ($lat < $this->minLat) ? $lat : $this->minLat;
                    $this->maxLat = ($lat > $this->maxLat) ? $lat : $this->maxLat;
                    $this->minLong = ($long < $this->minLong) ? $long : $this->minLong;
                    $this->maxLong = ($long > $this->maxLong) ? $long : $this->maxLong;
                }
            }
        }

        return [
            $this->minLat,
            $this->maxLat,
            $this->minLong,
            $this->maxLong
        ];
    }

    private function genFiles()
    {
        // Create an SVG string
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $this->fileWidth . 'px" height="' . $this->fileHeight . 'px">';
        $coords = [];
        $roads = "";
        $meshRoads = "";
        $coordsCount = 0;

        $allcoords = [];

        foreach ($this->geoArray['features'] as $featID => $feature) {
            $roadId = $feature["properties"]["ID"] ?? null;
            $hwy = $feature["properties"]["highway"] ?? null;
            if (in_array($roadId, $this->ignoreIds) || $hwy == null) continue;

            $name = $feature["properties"]["name"] ?? "null";
            $name = remove_accents($name);
            $name = mb_ereg_replace("([^\w\d\-_])", '', $name);

            $osmId = $feature["properties"]["osm_id"] ?? null;
            $height = 1000;

            $svg .= '<polyline points="';
            $coords = [];
            $roadWidth = isset($hwy) ? $this->roadsWidths[$hwy] ?? 8 : 5;

            $coordinates = $feature['geometry']['coordinates'];

            array_push($allcoords, $coordinates);

            foreach ($feature['geometry']['coordinates'] as $coordinates) {


                foreach ($coordinates as $coord) {
                    $height = $coord[2];
                    //The first two elements are LONGITUDE and LATITUDE
                    //long == X == WIDTH!
                    //lat == Y == HEIGHT!
                    $normalLong = $coord[0] - $this->minLong;

                    $metersX = Coordinates::longToM($normalLong);
                    $metersX *= $this->widthNormalScale;
                    $normalX = $metersX;
                    //$normalX = $this->limitDecimals($normalX);

                    $svgX = $normalX; // * $this->widthFileScale;
                    $ggjsX = $metersX - ($this->fileWidth / 2);


                    //lat == Y == HEIGHT!
                    $normalLat = $coord[1] - $this->minLat;

                    $metersY = Coordinates::latToM(abs($normalLat));
                    $metersY *= $this->heightNormalScale;
                    $normalY = ($metersY - $this->constraintsHeight) * -1;

                    //$normalY = $this->limitDecimals($normalY);

                    $svgY = $normalY; // * $this->heightFileScale;
                    $ggjsY = $metersY - ($this->fileHeight / 2);





                    $svg .= "$svgX,$svgY ";
                    array_push($coords, [$ggjsX, $ggjsY, $height, $roadWidth, 1, 0, 0, 1]);
                    //echo $normalX - 4096 . ', ' . $normalY - 4096 . PHP_EOL;
                    $coordsCount++;
                }
            }
            $svg .= '" style="fill:none;stroke:black;stroke-width:' . $roadWidth . 'px" />';

            $road = [
                "name" => "road_$name" . "_$osmId",
                "internalName" => "road_$name" . "_$osmId",
                "class" => "DecalRoad",
                "persistentId" => "",
                "__parent" => "Asphalt-Decal",
                "position" => [$coords[0][0], $coords[0][1], $coords[0][2]],
                "improvedSpline" => true,
                "material" => "asphalt",
                "nodes" => $coords,
                "overObjects" => true,
                "startEndFade" => [0, 10]
            ];

            $meshRoad = [
                "name" => "road_$name" . "_$osmId",
                "internalName" => "road_$name" . "_$osmId",
                "class" => "MeshRoad",
                "persistentId" => "",
                "__parent" => "Asphalt-Mesh",
                "position" => [$coords[0][0], $coords[0][1], $coords[0][2]],
                "nodes" => $coords,
                "widthSubdivisions" => 1
            ];

            $roads .= json_encode($road) . PHP_EOL;
            $meshRoads .= json_encode($meshRoad) . PHP_EOL;
        }

        file_put_contents("debug.json", json_encode($allcoords));

        $svg .= '</svg>';

        $this->svg = $svg;
        $this->gameDecalJson = $roads;
        $this->gameMeshJson = $meshRoads;
    }

    function toDecalJson($forceNew = false)
    {

        if ($forceNew || !$this->gameDecalJson) {
            $this->genFiles();
        }

        return $this->gameDecalJson;
    }

    function toMeshJson($forceNew = false)
    {

        if ($forceNew || !$this->gameMeshJson) {
            $this->genFiles();
        }

        return $this->gameMeshJson;
    }

    function limitDecimals($number, $decimals = 2)
    {
        return (float)number_format($number, $decimals, '.', '');
    }
}
