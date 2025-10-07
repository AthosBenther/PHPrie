<?php

namespace PHPrie\Types\Roads;

use PHPrie\Types\Roads\Base\Road as BaseRoad;
use PHPrie\Types\Roads\Base\Node as BaseNode;

class BaseRoads
{
    //West
    public $minLat = 90;
    //East
    public $maxLat = -90;
    //North
    public $minLong = 180;
    //South
    public $maxLong = -180;


    public $longCenter = 0;
    public $latCenter = 0;

    public $longDiff = 0;
    public $latDiff = 0;

    public $heightDiff = 0;
    public $widthDiff = 0;

    public ?int $fileHeight = null;
    public ?int $fileWidth = null;
    public $constraintsHeight = 0;
    public $constraintsWidth = 0;

    public $heightNormalScale = 1;
    public $widthNormalScale = 1;

    public $heightFileScale = 1;
    public $widthFileScale = 1;

    public array $fileArray;

    /**
     * Summary of roads
     * @var BaseRoad[]
     */
    public $roads = [];


    public function addRoad(BaseRoad $road)
    {
        array_push($this->roads, $road);
    }

    protected function minMax()
    {

        /**
         * @var BaseRoad $road
         */
        foreach ($this->roads as $road) {

            /**
             * @var BaseNode $node
             */
            foreach ($road->nodes as $node) {
                $this->minLat = ($node->lat < $this->minLat) ? $node->lat : $this->minLat;
                $this->minLat = ($node->lat < $this->minLat) ? $node->lat : $this->minLat;
                $this->maxLat = ($node->lat > $this->maxLat) ? $node->lat : $this->maxLat;
                $this->minLong = ($node->long < $this->minLong) ? $node->long : $this->minLong;
                $this->maxLong = ($node->long > $this->maxLong) ? $node->long : $this->maxLong;

            }
        }
    }

    public function toSvg(): string
    {

        // Create an SVG string
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . ($this->fileWidth ?? 2048) . 'px" height="' . ($this->fileHeight ?? 2048) . 'px">';
        $coords = [];
        $roads = "";
        $meshRoads = "";
        $coordsCount = 0;

        /**
         * @var BaseRoad $road
         */
        foreach ($this->roads as $road) {

            $osmId = $road->osm_id ?? null;
            $height = 1000;

            $svg .= '<polyline points="';
            $coords = [];

            /**
             * @var BaseNode $node
             */
            foreach ($road->nodes as $node) {

                // $height = $node->normalLong;
                // //The first two elements are LONGITUDE and LATITUDE
                // //long == X == WIDTH!
                // //lat == Y == HEIGHT!
                // $normalLong = $coord[0] - $this->minLong;

                // $metersX = Coordinates::longToM($normalLong);
                // $metersX *= $this->widthNormalScale;
                // $normalX = $metersX;
                // //$normalX = $this->limitDecimals($normalX);

                // $svgX = $normalX; // * $this->widthFileScale;
                // $ggjsX = $metersX - ($this->fileWidth / 2);


                // //lat == Y == HEIGHT!
                // $normalLat = $coord[1] - $this->minLat;

                // $metersY = Coordinates::latToM(abs($normalLat));
                // $metersY *= $this->heightNormalScale;
                // $normalY = ($metersY - $this->constraintsHeight) * -1;

                // //$normalY = $this->limitDecimals($normalY);

                // $svgY = $normalY; // * $this->heightFileScale;
                // $ggjsY = $metersY - ($this->fileHeight / 2);





                $svg .= "$node->x,$node->y ";
                //array_push($coords, [$ggjsX, $ggjsY, $height, $roadWidth, 1, 0, 0, 1]);
                //echo $normalX - 4096 . ', ' . $normalY - 4096 . PHP_EOL;
                $coordsCount++;

            }
            $svg .= '" style="fill:none;stroke:black;stroke-width:' . $road->width . 'px" />';
        }

        $svg .= '</svg>';

        return $svg;
    }

    protected function loadFile(string $filePath)
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \Exception("Failed to read file: $filePath");
        }
        $array = json_decode($content, true);
        if ($array === null) {
            throw new \Exception("Failed to decode JSON from file: $filePath");
        }
        $this->fileArray = $array;
    }
}