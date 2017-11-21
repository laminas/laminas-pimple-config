# zend-pimple-config

[![Build Status](https://secure.travis-ci.org/zendframework/zend-pimple-config.svg?branch=master)](https://secure.travis-ci.org/zendframework/zend-pimple-config)
[![Coverage Status](https://coveralls.io/repos/github/zendframework/zend-pimple-config/badge.svg?branch=master)](https://coveralls.io/github/zendframework/zend-pimple-config?branch=master)

This library provides utilities to configure
a [PSR-11](http://www.php-fig.org/psr/psr-11/) compatible
[Pimple container](https://github.com/silexphp/Pimple)
using zend-servicemanager configuration, for purposes of usage within
[Expressive](https://docs.zendframework.com/zend-expressive).

## Installation

Run the following to install this library:

```bash
$ composer require zendframework/zend-pimple-config
```

## Configuration

To get a configured [PSR-11](http://www.php-fig.org/psr/psr-11/)
Pimple container, do the following:

```php
<?php
use Zend\Pimple\Config\Config;
use Zend\Pimple\Config\ContainerFactory;

$factory = new ContainerFactory();

$container = $factory(
    new Config([
        'dependencies' => [
            'services'   => [],
            'invokables' => [],
            'factories'  => [],
            'aliases'    => [],
            'delegators' => [],
            'extensions' => [],
        ],
        // ... other configuration
    ])
);
```

The `dependencies` sub associative array can contain the following keys:

- `services`: an associative array that maps a key to a specific service instance.
- `invokables`: an associative array that map a key to a constructor-less
  service; i.e., for services that do not require arguments to the constructor.
  The key and service name may be the same; if they are not, the name is treated
  as an alias.
- `factories`: an associative array that maps a service name to a factory class
  name, or any callable. Factory classes must be instantiable without arguments,
  and callable once instantiated (i.e., implement the `__invoke()` method).
- `aliases`: an associative array that maps an alias to a service name (or
  another alias).
- `delegators`: an associative array that maps service names to lists of
  delegator factory keys, see the
  [Expressive delegators documentation](https://docs.zendframework.com/zend-servicemanager/delegators/)
  for more details.
- `extensions`: an associative array that maps service names to lists of
  extension factory names, see the [the section below](#extensions).

> Please note, that the whole configuration is available in the `$container`
> on `config` key:
>
> ```php
> $config = $container->get('config');
> ```

### `extensions`

> The `extensions` configuration is only available with the Pimple container.
> If you are using [Aura.Di](https://github.com/zendframework/zend-auradi-config)
> or [zend-servicemanager](https://docs.zendframework.com/zend-servicemanager/),
> you can use [`delegators`](https://docs.zendframework.com/zend-servicemanager/delegators/)
> instead. It is recommended to use `delegators` if you'd like to keep the 
> highest compatibility and might consider changing the container library you
> use in the future.

An extension factory has the following signature:

```php
use Psr\Container\ContainerInterface;

public function __invoke(
    $service,
    ContainerInterface $container,
    $name
);
```

The parameters passed to the extension factory are the following:

- `$service` is the real service instance.
- `$container` is the container that is used while creating the extension for
  the requested service.
- `$name` is the name of the service being requested.

Here is an example extension factory:

```php
use Psr\Container\ContainerInterface;

class ExtensionFactory
{
    public function __invoke($service, ContainerInterface $container, $name)
    {
        // do something with $service

        return $service;
    }
}
```

You can also return a different instance from the extension factory:

```php
use Psr\Container\ContainerInterface;

class ExtensionFactory
{
    public function __invoke($service, ContainerInterface $container, $name)
    {
        return new Decorator($service);
    }
}
```

Please note that when configuring extensions, you must provide a _list_ of
extension factories for the service, and not a single extension factory name:

```php
new Config([
    'dependencies' => [
        'invokables' => [
            'my-service' => MyInvokable\Service::class,
        ],
        'extensions' => [
            'my-service' => [
                Extension1Factory::class,
                Extension2Factory::class,
                // ...
            ],
        ],
    ],
]);
```

Service extensions are called in the same order as defined in the list.

## Using with Expressive

Replace contents of `config/container.php` with the following:

```php
<?php

use Zend\Pimple\Config\Config;
use Zend\Pimple\Config\ContainerFactory;

$config  = require __DIR__ . '/config.php';
$factory = new ContainerFactory();

return $factory(new Config($config));
```
