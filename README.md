# zend-pimple-config

[![Build Status](https://secure.travis-ci.org/zendframework/zend-pimple-config.svg?branch=master)](https://secure.travis-ci.org/zendframework/zend-pimple-config)
[![Coverage Status](https://coveralls.io/repos/github/zendframework/zend-pimple-config/badge.svg?branch=master)](https://coveralls.io/github/zendframework/zend-pimple-config?branch=master)

This library provides utilities to configure
[PSR-11](http://www.php-fig.org/psr/psr-11/)
[Pimple container](https://github.com/silexphp/Pimple)
using ZendFramework ServiceManager configuration.

## Installation

Run the following to install this library:

```bash
$ composer require zendframework/zend-pimple-config
```

## Configuration

To get configured [PSR-11 Container](http://www.php-fig.org/psr/psr-11/)
Pimple Container do the following:

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

- `services`: an associative array that maps a key to a service instance.
- `invokables`: an associative array that map a key to a constructor-less
  services, or services that do not require arguments to the constructor.
- `factories`: an associative array that map a key to a factory name, or any
  callable.
- `aliases`: an associative array that map a key to a service key (or another
  alias).
- `delegators`: an associative array that maps service keys to lists of
  delegator factory keys, see the
  [delegators documentation](https://docs.zendframework.com/zend-servicemanager/delegators/)
  for more details.
- `extensions`: an associative array that maps service keys to lists of
  extension factory keys, see the [the section below](#extensions).

> Please note, that the whole configuration is available in the `$container`
> on `config` key:
>
> ```php
> $config = $container->get('config');
> ```

### `extensions`

> The `extensions` configuration is only available with Pimple container.
> If you are using [Aura.Di container](https://github.com/zendframework/zend-auradi-config)
> or [Zend\ServiceManager](https://github.com/zendframework/zend-servicemanager)
> you can use [`delegators`](https://docs.zendframework.com/zend-servicemanager/delegators/).
> It is recommended to use `delegators` if you'd like to keep the highest
> compatibility and you would consider changing container library in the
> future.

A extension factory has the following signature:
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
<?php

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
<?php

use Psr\Container\ContainerInterface;

class ExtensionFactory
{
    public function __invoke($service, ContainerInterface $container, $name)
    {
        return new Decorator($service);
    }
}
```

Please note, that in the configuration you have to provide list of extension
factories for a service, for example:
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

Service extensions are called in the same order as defined on the list,
so the final service will be like:
```php
$finalService = $extension2Factory(
    $extension1Factory(
        $originService,
        $container,
        $name
    ),
    $container,
    $name
);
```

## Using with Expressive

First you have to install the library:
```bash
$ composer require zendframework/zend-pimple-config
```

Then replace contents of `config/container.php` with the following:
```php
<?php

use Zend\Pimple\Config\Config;
use Zend\Pimple\Config\ContainerFactory;

$config  = require __DIR__ . '/config.php';
$factory = new ContainerFactory();

return $factory(new Config($config));
```
