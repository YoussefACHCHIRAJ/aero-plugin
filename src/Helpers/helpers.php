<?php

use Aero\Core\Container;

function container($abstract = null)
{
    static $container;

    if (!$container) {
        $container = new Container();
    }

    return $abstract ? $container->get($abstract) : $container;
}


/**
 * Check if the environment is a dev or prod
 * 
 * @return boolean wither it's dev env or no.
 */
function is_development()
{
    return defined("WP_ENVIRONMENT_TYPE") && (WP_ENVIRONMENT_TYPE === 'local' || WP_ENVIRONMENT_TYPE === 'development');
}
