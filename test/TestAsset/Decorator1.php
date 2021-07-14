<?php

declare(strict_types=1);

namespace LaminasTest\Pimple\Config\TestAsset;

class Decorator1
{
    /** @var object */
    public $originService;

    /** @param object $service */
    public function __construct($service)
    {
        $this->originService = $service;
    }
}
