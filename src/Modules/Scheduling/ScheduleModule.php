<?php

namespace Aero\Modules\Scheduling;

use Aero\Contracts\AeroModuleInterface;

class ScheduleModule implements AeroModuleInterface
{
    public static function register()
    {
        container()->singleton(Schedule::class, Schedule::class);
        container()->singleton(ScheduleController::class, ScheduleController::class);
        self::registerScheduledJobs();
    }

    public static function registerScheduledJobs()
    {
        add_action('aero_run_my_job', [Schedule::class, 'run']);
    }
}
