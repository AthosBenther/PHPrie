<?php

namespace PHPrie\Types\Roads\Base;

use PHPrie\Types\Roads\BaseRoads;


class Road
{
    public float $width = 0;

    /**
     * Summary of nodes
     * @var Node[]
     */
    public $nodes = [];

    public function __construct(
        public BaseRoads $parentRoads,
        public ?string $id = null,
        public ?string $osm_id = null,
        public ?string $name = null,
        public ?string $type = null,
    ) {
        $this->setProps();
    }

    protected function setProps()
    {
        $nCount = count($this->nodes);
        if ($nCount == 0) {
            return;
        } else {
            $w = array_sum(array_map(fn($n) => $n->width, $this->nodes)) / count($this->nodes);
            $this->width = $w;
        }

    }
}