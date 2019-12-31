<?php

/**
 * @see       https://github.com/laminas/laminas-pimple-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-pimple-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-pimple-config/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Pimple\Config\TestAsset;

class Decorator1
{
    public $originService;

    public function __construct($service)
    {
        $this->originService = $service;
    }
}
