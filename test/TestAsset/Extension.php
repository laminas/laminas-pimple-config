<?php

namespace LaminasTest\Pimple\Config\TestAsset;

class Extension
{
    public $service;

    public $name;

    public function __construct($service, $name)
    {
        $this->service = $service;
        $this->name = $name;
    }
}
