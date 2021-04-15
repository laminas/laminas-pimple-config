<?php

/**
 * @see       https://github.com/laminas/laminas-pimple-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-pimple-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-pimple-config/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Pimple\Config;

use Pimple\Container;
use Pimple\Exception\ExpectedInvokableException;
use Pimple\Psr11\Container as PsrContainer;
use Psr\Container\ContainerInterface;

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

    public function configureContainer(Container $container) : ContainerInterface
    {
        $container['config'] = $this->config;
        $psrContainer = new PsrContainer($container);

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
        $this->injectFactories($psrContainer, $container, $dependencies);
        $this->injectInvokables($psrContainer, $container, $dependencies);
        $this->injectAliases($container, $dependencies);
        $this->injectExtensions($psrContainer, $container, $dependencies);

        return $psrContainer;
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

    private function injectFactories(PsrContainer $psrContainer, Container $container, array $dependencies) : void
    {
        if (empty($dependencies['factories'])
            || ! is_array($dependencies['factories'])
        ) {
            return;
        }

        foreach ($dependencies['factories'] as $name => $object) {
            $callback = function () use ($psrContainer, $container, $object, $name) {
                if (is_callable($object)) {
                    $factory = $object;
                } elseif (! is_string($object) || ! class_exists($object) || ! is_callable($factory = new $object())) {
                    throw new ExpectedInvokableException(sprintf(
                        'Factory provided to initialize service %s does not exist or is not callable',
                        $name
                    ));
                }

                return $factory($psrContainer, $name);
            };

            if (isset($dependencies['delegators'][$name])) {
                $this->injectDelegator(
                    $psrContainer,
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

    private function injectInvokables(PsrContainer $psrContainer, Container $container, array $dependencies) : void
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
                    $psrContainer,
                    $container,
                    $callback,
                    $object,
                    $dependencies['delegators'][$object]
                );
            } else {
                $this->setService($container, $dependencies, $object, $callback);
            }

            if (! is_int($alias) && $alias !== $object) {
                $this->setAlias($container, $dependencies, $alias, $object);
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
            $this->setAlias($container, $dependencies, $alias, $target);
        }
    }

    private function injectExtensions(PsrContainer $psrContainer, Container $container, array $dependencies) : void
    {
        if (empty($dependencies['extensions'])
            || ! is_array($dependencies['extensions'])
        ) {
            return;
        }

        foreach ($dependencies['extensions'] as $name => $extensions) {
            foreach ($extensions as $extension) {
                $container->extend($name, function ($service, Container $c) use ($psrContainer, $extension, $name) {
                    $factory = new $extension();
                    return $factory($service, $psrContainer, $name); // passing extra parameter $name
                });
            }
        }
    }

    private function injectDelegator(
        PsrContainer $psrContainer,
        Container $container,
        callable $callback,
        string $name,
        array $delegators
    ) {
        $container[$name] = function (Container $c) use ($psrContainer, $callback, $name, $delegators) {
            foreach ($delegators as $delegatorClass) {
                if (! class_exists($delegatorClass)) {
                    throw new ExpectedInvokableException();
                }

                $delegator = new $delegatorClass();

                if (! is_callable($delegator)) {
                    throw new ExpectedInvokableException();
                }

                $instance = $delegator($psrContainer, $name, $callback);
                $callback = function () use ($instance) {
                    return $instance;
                };
            }

            return $instance ?? $callback();
        };
    }

    private function setAlias(Container $container, array $dependencies, string $alias, string $target)
    {
        $this->setService(
            $container,
            $dependencies,
            $alias,
            function () use ($container, $dependencies, $alias, $target) {
                $instance = $container->offsetGet($target);

                if (! $this->isShared($dependencies, $alias)) {
                    return clone $instance;
                }

                return $instance;
            }
        );
    }

    private function setService(Container $container, array $dependencies, string $name, callable $callback)
    {
        $container[$name] = $this->isShared($dependencies, $name)
            ? $callback
            : $container->factory($callback);
    }

    private function isShared(array $dependencies, string $name)
    {
        return ($dependencies['shared_by_default'] === true && ! isset($dependencies['shared'][$name]))
            || (isset($dependencies['shared'][$name]) && $dependencies['shared'][$name] === true);
    }
}
