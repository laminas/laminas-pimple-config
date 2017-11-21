<?php
/**
 * @see       https://github.com/zendframework/zend-pimple-config for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-pimple-config/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Pimple\Config\TestAsset;

use Psr\Container\ContainerInterface;

class Decorator1Factory
{
    public function __invoke($service, ContainerInterface $container, $name)
    {
        return new Decorator1($service);
    }
}
