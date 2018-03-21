<?php
/**
 * @see       https://github.com/zendframework/zend-pimple-config for the canonical source repository
 * @copyright Copyright (c) 2017-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-pimple-config/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Pimple\Config;

use Pimple\Container;
use Pimple\Exception\ExpectedInvokableException;
use Pimple\Psr11\Container as PsrContainer;
use Psr\Container\NotFoundExceptionInterface;

use function is_array;
use function is_callable;
use function is_int;
use function is_string;

class Config implements ConfigInterface
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function configureContainer(Container $container) : void
    {
        $container['config'] = $this->config;

        $dependencies = [];
        if (isset($this->config['dependencies'])
            && is_array($this->config['dependencies'])
        ) {
            $dependencies = $this->config['dependencies'];
        }
        $dependencies['shared_by_default'] = isset($dependencies['shared_by_default'])
            ? (bool) $dependencies['shared_by_default']
            : true;

        $this->injectServices($container, $dependencies);
        $this->injectFactories($container, $dependencies);
        $this->injectInvokables($container, $dependencies);
        $this->injectAliases($container, $dependencies);
        $this->injectExtensions($container, $dependencies);
        $this->injectDelegators($container, $dependencies);
    }

    private function injectServices(Container $container, array $dependencies) : void
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

    private function injectFactories(Container $container, array $dependencies) : void
    {
        if (empty($dependencies['factories'])
            || ! is_array($dependencies['factories'])
        ) {
            return;
        }

        foreach ($dependencies['factories'] as $name => $object) {
            $this->setService($container, $dependencies, $name, function (Container $c) use ($object, $name) {
                if (is_string($object) && class_exists($object)) {
                    $factory = new $object();
                } else {
                    $factory = $object;
                }

                if (! is_callable($factory)) {
                    // todo: this is very tricky way, probably we should define here another exception
                    // if we need to throw instance of NotFoundExceptionInterface
                    throw new class (sprintf(
                        'Factory provided to initialize service %s is not invokable',
                        $name
                    )) extends ExpectedInvokableException implements NotFoundExceptionInterface {};
                }

                return $factory(new PsrContainer($c), $name);
            });
        }
    }

    private function injectInvokables(Container $container, array $dependencies) : void
    {
        if (empty($dependencies['invokables'])
            || ! is_array($dependencies['invokables'])
        ) {
            return;
        }

        foreach ($dependencies['invokables'] as $name => $object) {
            if (! is_int($name) && $name !== $object) {
                $this->setService($container, $dependencies, $name, function (Container $c) use ($object) {
                    return new $object();
                });
            }

            $this->setService($container, $dependencies, $object, function (Container $c) use ($object) {
                return new $object();
            });
        }
    }

    private function injectAliases(Container $container, array $dependencies) : void
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

    private function injectExtensions(Container $container, array $dependencies) : void
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

    private function injectDelegators(Container $container, array $dependencies) : void
    {
        if (empty($dependencies['delegators'])
            || ! is_array($dependencies['delegators'])
        ) {
            return;
        }

        foreach ($dependencies['delegators'] as $name => $delegators) {
            foreach ($delegators as $delegator) {
                if (isset($dependencies['services'][$name])) {
                    continue;
                }

                if (isset($dependencies['aliases'][$name])) {
                    continue;
                }

                // todo: probably we are missing some test case as we shouldn't allow delegators on invokable aliases:
                // if (isset($dependencies['invokables'][$name]) && $name !== $dependencies['invokables'][$name]) {
                //     continue;
                // }

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

    private function setService(Container $container, array $dependencies, string $name, callable $callback)
    {
        if (($dependencies['shared_by_default'] === true && ! isset($dependencies['shared'][$name]))
            || (isset($dependencies['shared'][$name]) && $dependencies['shared'][$name] === true)
        ) {
            $container[$name] = $callback;
        } else {
            $container[$name] = $container->factory($callback);
        }
    }
}
