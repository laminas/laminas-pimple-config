<?php

namespace ZendTest\Pimple\Config;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;
use Zend\Pimple\Config\ConfigInterface;
use Zend\Pimple\Config\ContainerFactory;

class ContainerFactoryTest extends TestCase
{
    /** @var ContainerFactory */
    private $factory;

    protected function setUp()
    {
        parent::setUp();

        $this->factory = new ContainerFactory();
    }

    public function testFactoryCreatesPsr11Container()
    {
        $factory = $this->factory;
        $config = $this->prophesize(ConfigInterface::class);

        $container = $factory($config->reveal());

        self::assertInstanceOf(ContainerInterface::class, $container);
    }

    public function testContainerIsConfigured()
    {
        $factory = $this->factory;

        $config = $this->prophesize(ConfigInterface::class);
        $config->configureContainer(Argument::type(Container::class))
            ->shouldBeCalledTimes(1)
            ->willReturn(null);

        $factory($config->reveal());
    }
}
