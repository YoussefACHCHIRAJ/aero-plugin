<?php

namespace Aero\Modules\Scheduling;

use Aero\Contracts\AeroControllerContract;
use Aero\Helpers\AeroRouter;

class ScheduleController  implements AeroControllerContract
{

    protected $schedule;

    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
    }

    public function registerRoutes()
    {
        AeroRouter::get("schedule/trigger", [$this, 'triggerScheduledJobs']);
    }

    /**
     * Trigger schedule manually using HTTP
     */
    public function triggerScheduledJobs()
    {
        $this->schedule->run();

        return "Scheduled Jobs has been trigged.";
    }
}
