<?php

use Aero\Core\Container;

function container($abstract = null) {
    static $container;

    if(!$container) {
        $container = new Container();
    }

    return $abstract ? $container->get($abstract) : $container;
}