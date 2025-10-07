<?php

namespace PHPrie\Types\Roads\ArchRoads;

use PHPrie\Types\Roads\Base\Road as BaseRoad;
use PHPrie\Types\Roads\BaseRoads;

class Road extends BaseRoad
{
    public function __construct(
        public BaseRoads $parentRoads,
        public ?array $archRoad = null,
    ) {
        parent::__construct(
            $parentRoads,
            $archRoad['name'] ?? null,
            $archRoad['osm_id'] ?? null,
            $archRoad['displayName'] ?? null,
            $archRoad['type'] ?? null
        );

        if ($this->archRoad) {
            $nodes = $this->archRoad['nodes'];

            foreach ($nodes as $nodeData) {
                $node = new Node($this, $nodeData);
                $this->nodes[] = $node;
            }
        }

        $this->setProps();
    }
}