<?php

/**
 * @see       https://github.com/laminas/laminas-pimple-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-pimple-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-pimple-config/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Pimple\Config;

use Laminas\Pimple\Config\Config;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Pimple\Psr11\Container as Psr11Container;

class ConfigTest extends TestCase
{
    /** @var Container */
    private $container;

    protected function setUp()
    {
        $this->container = new Container();
    }

    public function testConfigureContainerReturnsPsrContainer()
    {
        $container = (new Config([]))->configureContainer($this->container);

        self::assertInstanceOf(Psr11Container::class, $container);
    }

    public function testInjectConfiguration()
    {
        $config = [
            'foo' => 'bar',
        ];

        (new Config($config))->configureContainer($this->container);

        self::assertTrue($this->container->offsetExists('config'));
        self::assertSame($config, $this->container->offsetGet('config'));
    }

    public function testInjectExtensionForInvokable()
    {
        $dependencies = [
            'invokables' => [
                'foo-bar' => TestAsset\Service::class,
            ],
            'extensions' => [
                'foo-bar' => [
                    TestAsset\ExtensionFactory::class,
                ],
            ],
        ];

        (new Config(['dependencies' => $dependencies]))->configureContainer($this->container);

        self::assertTrue($this->container->offsetExists('foo-bar'));
        $service = $this->container->offsetGet('foo-bar');
        self::assertInstanceOf(TestAsset\Extension::class, $service);
        self::assertInstanceOf(TestAsset\Service::class, $service->service);
        self::assertSame('foo-bar', $service->name);
    }

    public function testInjectExtensionForService()
    {
        $myService = new TestAsset\Service();
        $dependencies = [
            'services' => [
                'foo-bar' => $myService,
            ],
            'extensions' => [
                'foo-bar' => [
                    TestAsset\ExtensionFactory::class,
                ],
            ],
        ];

        (new Config(['dependencies' => $dependencies]))->configureContainer($this->container);

        self::assertTrue($this->container->offsetExists('foo-bar'));
        $service = $this->container->offsetGet('foo-bar');
        self::assertInstanceOf(TestAsset\Extension::class, $service);
        self::assertSame($myService, $service->service);
        self::assertSame('foo-bar', $service->name);
    }

    public function testInjectExtensionForFactory()
    {
        $dependencies = [
            'factories' => [
                'foo-bar' => TestAsset\Factory::class,
            ],
            'extensions' => [
                'foo-bar' => [
                    TestAsset\ExtensionFactory::class,
                ],
            ],
        ];

        (new Config(['dependencies' => $dependencies]))->configureContainer($this->container);

        self::assertTrue($this->container->offsetExists('foo-bar'));
        $service = $this->container->offsetGet('foo-bar');
        self::assertInstanceOf(TestAsset\Extension::class, $service);
        self::assertInstanceOf(TestAsset\Service::class, $service->service);
        self::assertSame('foo-bar', $service->name);
    }

    public function testInjectMultipleExtensions()
    {
        $dependencies = [
            'invokables' => [
                'foo-bar' => TestAsset\Service::class,
            ],
            'extensions' => [
                'foo-bar' => [
                    TestAsset\Extension1Factory::class,
                    TestAsset\Extension2Factory::class,
                ],
            ],
        ];

        (new Config(['dependencies' => $dependencies]))->configureContainer($this->container);

        self::assertTrue($this->container->offsetExists('foo-bar'));
        $service = $this->container->offsetGet('foo-bar');
        self::assertInstanceOf(TestAsset\Service::class, $service);
        self::assertEquals(
            [
                TestAsset\Extension1Factory::class,
                TestAsset\Extension2Factory::class,
            ],
            $service->injected
        );
    }

    public function testInjectMultipleExtensionsAsDecorators()
    {
        $myService = new TestAsset\Service();
        $dependencies = [
            'services' => [
                'foo-bar' => $myService,
            ],
            'extensions' => [
                'foo-bar' => [
                    TestAsset\Decorator1Factory::class,
                    TestAsset\Decorator2Factory::class,
                ],
            ],
        ];

        (new Config(['dependencies' => $dependencies]))->configureContainer($this->container);

        self::assertTrue($this->container->offsetExists('foo-bar'));
        $service = $this->container->offsetGet('foo-bar');
        self::assertInstanceOf(TestAsset\Decorator2::class, $service);
        self::assertInstanceOf(TestAsset\Decorator1::class, $service->originService);
        self::assertSame($myService, $service->originService->originService);
    }
}
