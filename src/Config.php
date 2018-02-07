<?php
/**
 * @see       https://github.com/zendframework/zend-pimple-config for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-pimple-config/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Pimple\Config;

use Pimple\Container;
use Pimple\Psr11\Container as PsrContainer;

class Config implements ConfigInterface
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function configureContainer(Container $container)
    {
        $container['config'] = $this->config;

        $dependencies = [];
        if (isset($this->config['dependencies'])
            && is_array($this->config['dependencies'])
        ) {
            $dependencies = $this->config['dependencies'];
        }

        $this->injectServices($container, $dependencies);
        $this->injectFactories($container, $dependencies);
        $this->injectInvokables($container, $dependencies);
        $this->injectAliases($container, $dependencies);
        $this->injectExtensions($container, $dependencies);
        $this->injectDelegators($container, $dependencies);
    }

    private function injectServices(Container $container, array $dependencies)
    {
        if (empty($dependencies['services'])
            || ! is_array($dependencies['services'])
        ) {
            return;
        }

        foreach ($dependencies['services'] as $name => $service) {
            $container[$name] = function (Container $c) use ($service) {
                return $service;
            };
        }
    }

    private function injectFactories(Container $container, array $dependencies)
    {
        if (empty($dependencies['factories'])
            || ! is_array($dependencies['factories'])
        ) {
            return;
        }

        foreach ($dependencies['factories'] as $name => $object) {
            $container[$name] = function (Container $c) use ($object, $name) {
                if ($c->offsetExists($object)) {
                    $factory = $c->offsetGet($object);
                } else {
                    $factory = new $object();
                    $c[$object] = $c->protect($factory);
                }
                return $factory(new PsrContainer($c), $name);
            };
        }
    }

    private function injectInvokables(Container $container, array $dependencies)
    {
        if (empty($dependencies['invokables'])
            || ! is_array($dependencies['invokables'])
        ) {
            return;
        }

        foreach ($dependencies['invokables'] as $name => $object) {
            if (is_int($name)) {
                $name = $object;
            }

            $container[$name] = function (Container $c) use ($object) {
                return new $object();
            };
        }
    }

    private function injectAliases(Container $container, array $dependencies)
    {
        if (empty($dependencies['aliases'])
            || ! is_array($dependencies['aliases'])
        ) {
            return;
        }

        foreach ($dependencies['aliases'] as $alias => $target) {
            $container[$alias] = function (Container $c) use ($target) {
                return $c->offsetGet($target);
            };
        }
    }

    private function injectExtensions(Container $container, array $dependencies)
    {
        if (empty($dependencies['extensions'])
            || ! is_array($dependencies['extensions'])
        ) {
            return;
        }

        foreach ($dependencies['extensions'] as $name => $extensions) {
            foreach ($extensions as $extension) {
                $container->extend($name, function ($service, Container $c) use ($extension, $name) {
                    $factory = new $extension();
                    return $factory($service, new PsrContainer($c), $name); // passing extra parameter $name
                });
            }
        }
    }

    private function injectDelegators(Container $container, array $dependencies)
    {
        if (empty($dependencies['delegators'])
            || ! is_array($dependencies['delegators'])
        ) {
            return;
        }

        foreach ($dependencies['delegators'] as $name => $delegators) {
            foreach ($delegators as $delegator) {
                $container->extend($name, function ($service, Container $c) use ($delegator, $name) {
                    $factory = new $delegator();
                    $callback = function () use ($service) {
                        return $service;
                    };
                    return $factory(new PsrContainer($c), $name, $callback);
                });
            }
        }
    }
}
