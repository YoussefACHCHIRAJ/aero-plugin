<?php

namespace Aero\Modules\Booking\Enums;
// src/Modules/Booking/Enums/BookingTypeEnum.php

enum BookingTypeEnum: string
{
    case ArrivalDeparture = 'arrivalDeparture';
    case Connection = 'connection';

    public static function isConnection(string $type) {
        return BookingTypeEnum::tryFrom($type) === self::Connection;
    }
}
