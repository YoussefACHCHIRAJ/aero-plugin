<?php

namespace Aero\Modules\City;

use Aero\Contracts\AeroModuleInterface;

class CityModule implements AeroModuleInterface
{
    public static function register()
    {
        container()->singleton(CityService::class, CityService::class);
        container()->singleton(CityController::class, CityController::class);
        container()->singleton(CityHelper::class, CityHelper::class);
    }
}
