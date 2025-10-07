<?php

namespace PHPrie\Types\Roads\Base;

use PHPrie\Types\Roads\Base\Road as BaseRoad;

class Node
{
    //long == X == WIDTH!
    //lat == Y == HEIGHT!
    //height == Z == DEPTH!
    public float $x {
        get {
            return $this->x ??= $this->long - $this->parentRoad->parentRoads->minLong;
        }
    }
    public float $y {
        get {
            return $this->y ??= $this->lat - $this->parentRoad->parentRoads->minLat;
        }
    }
    public ?float $z = null;

    public float $normalLong {
        get {
            return $this->normalLong ??= $this->long - $this->parentRoad->parentRoads->minLong;
        }
    }

    public float $normalLat {
        get {
            return $this->normalLat ??= $this->lat - $this->parentRoad->parentRoads->minLat;
        }
    }

    public function __construct(
        public BaseRoad $parentRoad,
        public float $lat,
        public float $long,
        public ?float $height = null,
        public ?float $width = null
    ) {
    }
}