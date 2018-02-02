<?php
/**
 * @see       https://github.com/zendframework/zend-pimple-config for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-pimple-config/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Pimple\Config;

use PHPUnit\Framework\TestCase;
use Zend\Pimple\Config\Config;
use Zend\Pimple\Config\ContainerFactory;

class ContainerTest extends TestCase
{
    public function config()
    {
        yield 'factories' => [['factories' => ['service' => TestAsset\Factory::class]]];
        yield 'invokables' => [['invokables' => ['service' => TestAsset\Service::class]]];
        yield 'aliases-invokables' => [
            [
                'aliases' => ['service' => TestAsset\Service::class],
                'invokables' => [TestAsset\Service::class => TestAsset\Service::class],
            ],
        ];
        yield 'aliases-factories' => [
            [
                'aliases' => ['service' => TestAsset\Service::class],
                'factories' => [TestAsset\Service::class => TestAsset\Factory::class],
            ],
        ];
    }

    /**
     * @dataProvider config
     */
    public function testIsSharedByDefault(array $config)
    {
        $container = $this->createContainer($config);

        $service1 = $container->get('service');
        $service2 = $container->get('service');

        $this->assertSame($service1, $service2);
    }

    /**
     * @dataProvider config
     */
    public function testCanDisableSharedByDefault(array $config)
    {
        $container = $this->createContainer(array_merge($config, [
            'shared_by_default' => false,
        ]));

        $service1 = $container->get('service');
        $service2 = $container->get('service');

        $this->assertNotSame($service1, $service2);
    }

    /**
     * @dataProvider config
     */
    public function testCanDisableSharedForSingleService(array $config)
    {
        $container = $this->createContainer(array_merge($config, [
            'shared' => [
                'service' => false,
            ],
        ]));

        $service1 = $container->get('service');
        $service2 = $container->get('service');

        $this->assertNotSame($service1, $service2);
    }

    /**
     * @dataProvider config
     */
    public function testCanEnableSharedForSingleService(array $config)
    {
        $container = $this->createContainer(array_merge($config, [
            'shared_by_default' => false,
            'shared' => [
                'service' => true,
            ],
        ]));

        $service1 = $container->get('service');
        $service2 = $container->get('service');

        $this->assertSame($service1, $service2);
    }

    private function createContainer(array $config)
    {
        $factory = new ContainerFactory();

        return $factory(new Config(['dependencies' => $config]));
    }
}
