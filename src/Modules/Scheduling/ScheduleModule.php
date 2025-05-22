<?php

namespace Aero\Modules\Scheduling;

use Aero\Contracts\AeroModuleInterface;

class ScheduleModule implements AeroModuleInterface {
    public static function register() {
        container()->singleton(Schedule::class, Schedule::class);
    }
}