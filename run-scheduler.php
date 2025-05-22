<?php

use Aero\Modules\Scheduling\Schedule;

require __DIR__ . '/../../../wp-load.php';

$scheduler = new Schedule();

$result = $scheduler->run();

if(is_wp_error($result)) {
    echo "Error " . $result->get_error_message() . PHP_EOL;
    exit(1);
}

echo $result . PHP_EOL;
exit(0);