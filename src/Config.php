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

use function class_exists;
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
            $callback = function () use ($container, $object, $name) {
                if (is_callable($object)) {
                    $factory = $object;
                } elseif (! is_string($object) || ! class_exists($object) || ! is_callable($factory = new $object())) {
                    throw new ExpectedInvokableException(sprintf(
                        'Factory provided to initialize service %s does not exist or is not callable',
                        $name
                    ));
                }

                return $factory(new PsrContainer($container), $name);
            };

            if (isset($dependencies['delegators'][$name])) {
                $this->injectDelegator(
                    $container,
                    $callback,
                    $name,
                    $dependencies['delegators'][$name]
                );
                continue;
            }

            $this->setService($container, $dependencies, $name, $callback);
        }
    }

    private function injectInvokables(Container $container, array $dependencies) : void
    {
        if (empty($dependencies['invokables'])
            || ! is_array($dependencies['invokables'])
        ) {
            return;
        }

        foreach ($dependencies['invokables'] as $alias => $object) {
            $callback = function () use ($object) {
                if (! class_exists($object)) {
                    throw new ExpectedInvokableException(sprintf(
                        'Class %s does not exist',
                        $object
                    ));
                }

                return new $object();
            };

            if (isset($dependencies['delegators'][$object])) {
                $this->injectDelegator(
                    $container,
                    $callback,
                    $object,
                    $dependencies['delegators'][$object]
                );
            } else {
                $this->setService($container, $dependencies, $object, $callback);
            }

            if (! is_int($alias) && $alias !== $object) {
                $container[$alias] = function (Container $c) use ($object) {
                    return $c->offsetGet($object);
                };
            }
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

    private function injectDelegator(Container $container, callable $callback, string $name, array $delegators)
    {
        $container[$name] = function (Container $c) use ($callback, $name, $delegators) {
            foreach ($delegators as $delegatorClass) {
                if (! class_exists($delegatorClass)) {
                    throw new ExpectedInvokableException();
                }

                $delegator = new $delegatorClass();

                if (! is_callable($delegator)) {
                    throw new ExpectedInvokableException();
                }

                $instance = $delegator(new PsrContainer($c), $name, $callback);
                $callback = function () use ($instance) {
                    return $instance;
                };
            }

            return $instance ?? $callback();
        };
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
