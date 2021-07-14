<?php

declare(strict_types=1);

namespace LaminasTest\Pimple\Config\TestAsset;

class Extension
{
    /** @var object */
    public $service;

    /** @var string */
    public $name;

    /**
     * @param object $service
     * @param string $name
     */
    public function __construct($service, $name)
    {
        $this->service = $service;
        $this->name    = $name;
    }
}
