<?php

/*
* Plugin name: Fast Track Aero Plugin
* Description: Expose Custom APIs for Fast Track Aero website.
* Version:     2.0.3
* Author:      Youssef ACHCHIRAJ
* Author URI:  youssef-achchiraj.vercel.app
*/


require __DIR__ . '/src/Autoloader.php';
require __DIR__ . '/Plugin.php';
require __DIR__ . '/helpers.php';

if (! \Aero\Autoloader::init()) {
    return;
}


$plugin = new Plugin();

$plugin->boot();
