<?php

namespace Laminas\Pimple\Config;

use Pimple\Container;

interface ConfigInterface
{
    public function configureContainer(Container $container);
}
