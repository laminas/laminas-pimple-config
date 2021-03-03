<?php

/**
 * @see       https://github.com/laminas/laminas-pimple-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-pimple-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-pimple-config/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Pimple\Config;

use Laminas\Pimple\Config\ConfigInterface;
use Laminas\Pimple\Config\ContainerFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Pimple\Container;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;

class ContainerFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ContainerFactory */
    private $factory;

    protected function setUp() : void
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
        $config
            ->configureContainer(Argument::type(Container::class))
            ->shouldBeCalledTimes(1);

        $factory($config->reveal());
    }
}
