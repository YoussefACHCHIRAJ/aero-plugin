<?php


namespace Aero\Modules\Booking;

use Aero\Interfaces\AeroModuleInterface;

class BookingModule implements AeroModuleInterface
{
    public static function register()
    {
        // todo: define later interfaces and bind on them
        container()->singleton(BookingService::class, BookingService::class);
        container()->singleton(BookingController::class, BookingController::class);
        container()->singleton(BookingHelper::class, BookingHelper::class);
    }
}
