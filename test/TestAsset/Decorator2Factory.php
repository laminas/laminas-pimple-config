<?php

declare(strict_types=1);

namespace LaminasTest\Pimple\Config\TestAsset;

use Psr\Container\ContainerInterface;

class Decorator2Factory
{
    /**
     * @param object $service
     * @param string $name
     * @return object
     */
    public function __invoke($service, ContainerInterface $container, $name)
    {
        return new Decorator2($service);
    }
}
