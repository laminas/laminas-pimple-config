<?php
/**
 * @see       https://github.com/zendframework/zend-pimple-config for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-pimple-config/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Pimple\Config;

use Pimple\Container;
use Pimple\Psr11\Container as PsrContainer;

class ContainerFactory
{
    public function __invoke(ConfigInterface $config) : PsrContainer
    {
        $container = new Container();
        $config->configureContainer($container);

        return new PsrContainer($container);
    }
}
