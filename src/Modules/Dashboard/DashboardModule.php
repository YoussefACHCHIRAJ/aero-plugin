<?php

namespace Aero\Modules\Dashboard;

use Aero\Interfaces\AeroModuleInterface;

class DashboardModule implements AeroModuleInterface
{

    public static function register()
    {
        container()->singleton(DashboardService::class, DashboardService::class);
        container()->singleton(DashboardController::class, DashboardController::class);
    }
}
