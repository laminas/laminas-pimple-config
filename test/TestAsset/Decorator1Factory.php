<?php

declare(strict_types=1);

namespace LaminasTest\Pimple\Config\TestAsset;

use Psr\Container\ContainerInterface;

class Decorator1Factory
{
    public function __invoke($service, ContainerInterface $container, $name)
    {
        return new Decorator1($service);
    }
}
