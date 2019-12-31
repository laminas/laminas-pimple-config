<?php

namespace LaminasTest\Pimple\Config;

use Laminas\Pimple\Config\Config;
use PHPUnit\Framework\TestCase;
use Pimple\Container;

class ConfigTest extends TestCase
{
    /** @var Container */
    private $container;

    protected function setUp()
    {
        parent::setUp();

        $this->container = new Container();
    }

    public function testXYZ()
    {
        self::assertTrue(true);
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

    public function testInjectService()
    {
        $myService = new TestAsset\Service();

        $dependencies = [
            'services' => [
                'foo-bar' => $myService,
            ],
        ];

        (new Config(['dependencies' => $dependencies]))->configureContainer($this->container);

        self::assertTrue($this->container->offsetExists('foo-bar'));
        self::assertSame($myService, $this->container->offsetGet('foo-bar'));
    }

    public function testInjectServiceFactory()
    {
        $factory = new TestAsset\Factory();

        $dependencies = [
            'services'  => [
                'factory' => $factory,
            ],
            'factories' => [
                'foo-bar' => 'factory',
            ],
        ];

        (new Config(['dependencies' => $dependencies]))->configureContainer($this->container);

        self::assertTrue($this->container->offsetExists('factory'));
        self::assertTrue($this->container->offsetExists('foo-bar'));
        self::assertInstanceOf(TestAsset\Service::class, $this->container->offsetGet('foo-bar'));
    }

    public function testInjectInvokableFactory()
    {
        $dependencies = [
            'factories' => [
                'foo-bar' => TestAsset\Factory::class,
            ],
        ];

        (new Config(['dependencies' => $dependencies]))->configureContainer($this->container);

        self::assertTrue($this->container->offsetExists('foo-bar'));
        self::assertInstanceOf(TestAsset\Service::class, $this->container->offsetGet('foo-bar'));
    }

    public function testInjectInvokable()
    {
        $dependencies = [
            'invokables' => [
                'foo-bar' => TestAsset\Service::class,
            ],
        ];

        (new Config(['dependencies' => $dependencies]))->configureContainer($this->container);

        self::assertTrue($this->container->offsetExists('foo-bar'));
        self::assertInstanceOf(TestAsset\Service::class, $this->container->offsetGet('foo-bar'));
    }

    public function testInjectAlias()
    {
        $myService = new TestAsset\Service();

        $dependencies = [
            'services' => [
                'foo-bar' => $myService,
            ],
            'aliases'  => [
                'alias' => 'foo-bar',
            ],
        ];

        (new Config(['dependencies' => $dependencies]))->configureContainer($this->container);

        self::assertTrue($this->container->offsetExists('alias'));
        self::assertSame($myService, $this->container->offsetGet('alias'));
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

    public function testInjectDelegatorForInvokable()
    {
        $dependencies = [
            'invokables' => [
                'foo-bar' => TestAsset\Service::class,
            ],
            'delegators' => [
                'foo-bar' => [
                    TestAsset\DelegatorFactory::class,
                ],
            ],
        ];

        (new Config(['dependencies' => $dependencies]))->configureContainer($this->container);

        self::assertTrue($this->container->offsetExists('foo-bar'));
        $delegator = $this->container->offsetGet('foo-bar');
        self::assertInstanceOf(TestAsset\Delegator::class, $delegator);
        $callback = $delegator->callback;
        self::assertInstanceOf(TestAsset\Service::class, $callback());
    }

    public function testInjectDelegatorForService()
    {
        $myService = new TestAsset\Service();
        $dependencies = [
            'services' => [
                'foo-bar' => $myService,
            ],
            'delegators' => [
                'foo-bar' => [
                    TestAsset\DelegatorFactory::class,
                ],
            ],
        ];

        (new Config(['dependencies' => $dependencies]))->configureContainer($this->container);

        self::assertTrue($this->container->offsetExists('foo-bar'));
        $delegator = $this->container->offsetGet('foo-bar');
        self::assertInstanceOf(TestAsset\Delegator::class, $delegator);
        $callback = $delegator->callback;
        self::assertSame($myService, $callback());
    }

    public function testInjectDelegatorForFactory()
    {
        $dependencies = [
            'factories' => [
                'foo-bar' => TestAsset\Factory::class,
            ],
            'delegators' => [
                'foo-bar' => [
                    TestAsset\DelegatorFactory::class,
                ],
            ],
        ];

        (new Config(['dependencies' => $dependencies]))->configureContainer($this->container);

        self::assertTrue($this->container->offsetExists('foo-bar'));
        $delegator = $this->container->offsetGet('foo-bar');
        self::assertInstanceOf(TestAsset\Delegator::class, $delegator);
        $callback = $delegator->callback;
        self::assertInstanceOf(TestAsset\Service::class, $callback());
    }

    public function testInjectMultipleDelegators()
    {
        $dependencies = [
            'invokables' => [
                'foo-bar' => TestAsset\Service::class,
            ],
            'delegators' => [
                'foo-bar' => [
                    TestAsset\Delegator1Factory::class,
                    TestAsset\Delegator2Factory::class,
                ],
            ],
        ];

        (new Config(['dependencies' => $dependencies]))->configureContainer($this->container);

        self::assertTrue($this->container->offsetExists('foo-bar'));
        $service = $this->container->offsetGet('foo-bar');
        self::assertInstanceOf(TestAsset\Service::class, $service);
        self::assertEquals(
            [
                TestAsset\Delegator1Factory::class,
                TestAsset\Delegator2Factory::class,
            ],
            $service->injected
        );
    }
}
