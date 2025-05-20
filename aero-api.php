<?php

/*
* Plugin name: Fast Track Aero Plugin
* Description: Expose Custom APIs for Fast Track Aero website.
* Version:     2.0.1
* Author:      Youssef ACHCHIRAJ
* Author URI:  youssef-achchiraj.vercel.app
*/


if (!defined("AERO_PLUGIN_FILE")) {
    define("AERO_PLUGIN_FILE", __FILE__);
}

require __DIR__ . '/src/Autoloader.php';

if (! \Aero\Autoloader::init()) {
    return;
}


$plugin = new Plugin();

$plugin->boot();
