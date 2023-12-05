# laminas-pimple-config

> ## Abandoned
>
> This package is **abandoned** and will receive no further development!
>
> See the Technical Steering Committee [meeting minutes](https://github.com/laminas/technical-steering-committee/blob/main/meetings/minutes/2023-12-04-TSC-Minutes.md)

> ## 🇷🇺 Русским гражданам
>
> Мы, участники Laminas, родились и живем в разных странах. У многих из нас есть друзья, родственники и коллеги как в России, так и в Украине. Некоторые из нас родились в России. Некоторые из нас живут в России. У некоторых бабушки и дедушки сражались с фашистами во Второй мировой войне. Здесь никто не поддерживает фашизм.
>
> У одного из нас есть украинская родственница, которая спаслась из дома вместе с сыном. Поезд задержался из-за бомбежки на дороге впереди. У нас есть друзья, которые прячутся в бомбоубежищах. Мы с тревогой ждем весточки от них после воздушных налетов, которые беспорядочно наносят удары и попадают по больницам, школам, детским садам и домам. Мы не берем это из каких-либо СМИ. Мы наблюдаем это напрямую.
>
> Вы доверяете нам достаточно, чтоб использовать наши программы, и мы просим вас довериться нам вновь. Мы нуждаемся в помощи. Выходите и протестуйте против этой бесполезной войны. Остановите кровопролитие. Скажите "Нет войне!"
>
> ## 🇺🇸 To Citizens of Russia
>
> We at Laminas come from all over the world. Many of us have friends, family and colleagues in both Russia and Ukraine. Some of us were born in Russia. Some of us currently live in Russia. Some have grandparents who fought Nazis in World War II. Nobody here supports fascism.
>
> One team member has a Ukrainian relative who fled her home with her son. The train was delayed due to bombing on the road ahead. We have friends who are hiding in bomb shelters. We anxiously follow up on them after the air raids, which indiscriminately fire at hospitals, schools, kindergartens and houses. We're not taking this from any media. These are our actual experiences.
>
> You trust us enough to use our software. We ask that you trust us to say the truth on this. We need your help. Go out and protest this unnecessary war. Stop the bloodshed. Say "stop the war!"

This library provides utilities to configure
a [PSR-11](http://www.php-fig.org/psr/psr-11/) compatible
[Pimple container](https://github.com/silexphp/Pimple)
using laminas-servicemanager configuration, for purposes of usage within
[Mezzio](https://docs.mezzio.dev/mezzio).

## Installation

Run the following to install this library:

```bash
$ composer require laminas/laminas-pimple-config
```

## Configuration

To get a configured [PSR-11](http://www.php-fig.org/psr/psr-11/)
Pimple container, do the following:

```php
<?php
use Laminas\Pimple\Config\Config;
use Laminas\Pimple\Config\ContainerFactory;

$factory = new ContainerFactory();

$container = $factory(
    new Config([
        'dependencies' => [
            'services'          => [],
            'invokables'        => [],
            'factories'         => [],
            'aliases'           => [],
            'delegators'        => [],
            'extensions'        => [],
            'shared'            => [],
            'shared_by_default' => true,
        ],
        // ... other configuration
    ])
);
```

The `dependencies` sub associative array can contain the following keys:

- `services`: an associative array that maps a key to a specific service instance.
- `invokables`: an associative array that map a key to a constructor-less
  service; i.e., for services that do not require arguments to the constructor.
  The key and service name usually are the same; if they are not, the key is
  treated as an alias.
- `factories`: an associative array that maps a service name to a factory class
  name, or any callable. Factory classes must be instantiable without arguments,
  and callable once instantiated (i.e., implement the `__invoke()` method).
- `aliases`: an associative array that maps an alias to a service name (or
  another alias).
- `delegators`: an associative array that maps service names to lists of
  delegator factory keys, see the
  [Mezzio delegators documentation](https://docs.laminas.dev/laminas-servicemanager/delegators/)
  for more details.
- `extensions`: an associative array that maps service names to lists of
  extension factory names, see the [the section below](#extensions).
- `shared`: associative array that map a service name to a boolean, in order to
  indicate the service manager if it should cache or not a service created
  through the get method, independant of the shared_by_default setting.
- `shared_by_default`: boolean that indicates whether services created through
  the `get` method should be cached. This is `true` by default.

> Please note, that the whole configuration is available in the `$container`
> on `config` key:
>
> ```php
> $config = $container->get('config');
> ```

### `extensions`

> The `extensions` configuration is only available with the Pimple container.
> If you are using [Aura.Di](https://github.com/laminas/laminas-auradi-config)
> or [laminas-servicemanager](https://docs.laminas.dev/laminas-servicemanager/),
> you can use [`delegators`](https://docs.laminas.dev/laminas-servicemanager/delegators/)
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

## Using with Mezzio

Replace contents of `config/container.php` with the following:

```php
<?php

use Laminas\Pimple\Config\Config;
use Laminas\Pimple\Config\ContainerFactory;

$config  = require __DIR__ . '/config.php';
$factory = new ContainerFactory();

return $factory(new Config($config));
```
