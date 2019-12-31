<?php

namespace LaminasTest\Pimple\Config\TestAsset;

use Psr\Container\ContainerInterface;

class Extension1Factory
{
    public function __invoke($service, ContainerInterface $container, $name)
    {
        $service->inject(static::class);

        return $service;
    }
}
