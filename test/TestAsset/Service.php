<?php

/**
 * @see       https://github.com/laminas/laminas-pimple-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-pimple-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-pimple-config/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Pimple\Config\TestAsset;

class Service
{
    public $injected = [];

    public function __invoke($a = null)
    {
        return $a;
    }

    public function inject($name)
    {
        $this->injected[] = $name;
    }
}
