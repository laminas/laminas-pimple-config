<?php

declare(strict_types=1);

namespace LaminasTest\Pimple\Config\TestAsset;

use Psr\Container\ContainerInterface;

class Extension1Factory
{
    /**
     * @param object $service
     * @param string $name
     * @return object
     */
    public function __invoke($service, ContainerInterface $container, $name)
    {
        $service->inject(static::class);

        return $service;
    }
}
