<?php

declare(strict_types=1);

namespace Laminas\Pimple\Config;

use Pimple\Container;

interface ConfigInterface
{
    public function configureContainer(Container $container): void;
}
