<?php

declare(strict_types=1);

namespace LaminasTest\Pimple\Config\TestAsset;

use Psr\Container\ContainerInterface;

class Factory
{
    public function __invoke(ContainerInterface $container)
    {
        return new Service();
    }
}
