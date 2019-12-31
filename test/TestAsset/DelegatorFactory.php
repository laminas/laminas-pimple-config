<?php

/**
 * @see       https://github.com/laminas/laminas-pimple-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-pimple-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-pimple-config/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Pimple\Config\TestAsset;

use Psr\Container\ContainerInterface;

class DelegatorFactory
{
    public function __invoke(ContainerInterface $container, $name, callable $callback)
    {
        return new Delegator($name, $callback);
    }
}
