<?php


enum BookingTypeEnum: string
{
    case ArrivalDeparture = 'arrivalDeparture';
    case Connection = 'connection';

    public static function isConnection(string $type) {
        return BookingTypeEnum::tryFrom($type) === self::Connection;
    }
}
