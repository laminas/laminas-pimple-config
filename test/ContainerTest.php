<?php

declare(strict_types=1);

namespace LaminasTest\Pimple\Config;

use Laminas\ContainerConfigTest\AbstractMezzioContainerConfigTest;
use Laminas\ContainerConfigTest\SharedTestTrait;
use Laminas\Pimple\Config\Config;
use Laminas\Pimple\Config\ContainerFactory;
use Psr\Container\ContainerInterface;

class ContainerTest extends AbstractMezzioContainerConfigTest
{
    use SharedTestTrait;

    protected function createContainer(array $config) : ContainerInterface
    {
        $factory = new ContainerFactory();

        return $factory(new Config(['dependencies' => $config]));
    }
}
