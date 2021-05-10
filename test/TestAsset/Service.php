<?php

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
