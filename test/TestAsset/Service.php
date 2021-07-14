<?php

declare(strict_types=1);

namespace LaminasTest\Pimple\Config\TestAsset;

class Service
{
    /** @var array */
    public $injected = [];

    /**
     * @param mixed $a
     * @return mixed
     */
    public function __invoke($a = null)
    {
        return $a;
    }

    /**
     * @param string $name
     */
    public function inject($name): void
    {
        $this->injected[] = $name;
    }
}
