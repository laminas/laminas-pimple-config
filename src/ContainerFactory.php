<?php

/**
 * @see       https://github.com/laminas/laminas-pimple-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-pimple-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-pimple-config/blob/master/LICENSE.md New BSD License
 */

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
