<?php

declare(strict_types=1);

namespace LaminasTest\Pimple\Config;

use Laminas\Pimple\Config\ConfigInterface;
use Laminas\Pimple\Config\ContainerFactory;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Psr\Container\ContainerInterface;

class ContainerFactoryTest extends TestCase
{
    /** @var ContainerFactory */
    private $factory;

    protected function setUp(): void
    {
        $this->factory = new ContainerFactory();
    }

    public function testFactoryCreatesPsr11Container()
    {
        $factory = $this->factory;
        $config  = $this->createMock(ConfigInterface::class);

        $container = $factory($config);

        self::assertInstanceOf(ContainerInterface::class, $container);
    }

    public function testContainerIsConfigured()
    {
        $factory = $this->factory;

        $config = $this->createMock(ConfigInterface::class);
        $config
            ->expects($this->once())
            ->method('configureContainer')
            ->with($this->isInstanceOf(Container::class));

        $factory($config);
    }
}
