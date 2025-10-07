<?php

namespace PHPrie\Types\Roads\ArchRoads;

use PHPrie\Types\Roads\Base\Node as BaseNode;
use PHPrie\Types\Roads\Base\Road as BaseRoad;

class Node extends BaseNode
{

    public function __construct(
        public BaseRoad $parentRoad,
        public array $archNode
    ) {
        parent::__construct(
            $this->parentRoad,
            $this->archNode['posX'],
            $this->archNode['posY'],
            $this->archNode['posZ'] ?? null,
            array_sum($this->archNode['widths']) ?? null
        );
    }
}