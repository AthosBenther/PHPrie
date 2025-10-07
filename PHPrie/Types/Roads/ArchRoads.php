<?php

namespace PHPrie\Types\Roads;

use PHPrie\Types\Roads\ArchRoads\Road;

class ArchRoads extends BaseRoads
{
    function __construct(
        private string $filePath
    ) {
        $this->loadFile($this->filePath);
        $this->parse();
    }

    private function parse()
    {
        $archRoads = $this->fileArray['data']['roads'];
        foreach ($archRoads as $archRoad) {
            $road = new Road(
                $this,
                $archRoad
            );
            $this->roads[] = $road;
        }

        $this->minMax();
        //$this->calcScale();
    }
}