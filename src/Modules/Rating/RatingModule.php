<?php

namespace Aero\Modules\Rating;

use Aero\Contracts\AeroModuleInterface;

class RatingModule implements AeroModuleInterface
{

    public static function register()
    {
        container()->singleton(RatingController::class, RatingController::class);
    }
}
