<?php

namespace LaminasTest\Pimple\Config\TestAsset;

use Psr\Container\ContainerInterface;

class ExtensionFactory
{
    public function __invoke($service, ContainerInterface $container, $name)
    {
        return new Extension($service, $name);
    }
}
