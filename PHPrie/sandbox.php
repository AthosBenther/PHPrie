<?php

class Sandbox
{
    public ?string $alala { get {
            return $this->alala ??= "alala " . $this->bololo; }
    }

    public function __construct(private ?string $bololo)
    {
    }
}

$sdbx = new Sandbox("bololo");
echo $sdbx->alala;