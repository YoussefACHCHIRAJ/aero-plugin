<?php

/*
* Plugin name: Fast Track Aero Plugin
* Description: Expose Custom APIs for Fast Track Aero website.
* Version:     2.0.1
* Author:      Youssef ACHCHIRAJ
* Author URL:  youssef-achchiraj.vercel.com
*/


if (!defined("AERO_PLUGIN_FILE")) {
    define("AERO_PLUGIN_FILE", __FILE__);
}

require __DIR__ . '/src/Autoloader.php';
require __DIR__ . '/src/Helpers/helpers.php';
require __DIR__ . '/Plugin.php';
require __DIR__ . '/src/Modules/Booking/Enums/BookingTypeEnum.php';



$plugin = new Plugin();

$plugin->boot();
