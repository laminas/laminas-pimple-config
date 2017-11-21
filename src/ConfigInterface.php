<?php
/**
 * @see       https://github.com/zendframework/zend-pimple-config for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-pimple-config/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Pimple\Config;

use Pimple\Container;

interface ConfigInterface
{
    public function configureContainer(Container $container);
}
