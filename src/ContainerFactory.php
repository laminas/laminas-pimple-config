<?php

namespace Laminas\Pimple\Config;

use Pimple\Container;
use Pimple\Psr11\Container as PsrContainer;

class ContainerFactory
{
    public function __invoke(ConfigInterface $config)
    {
        $container = new Container();
        $config->configureContainer($container);

        return new PsrContainer($container);
    }
}
