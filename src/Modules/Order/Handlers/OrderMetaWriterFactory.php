<?php

namespace Aero\Modules\Order\Handlers;

use Aero\Modules\Booking\Enums\BookingTypeEnum;

class OrderMetaWriterFactory {

    
    public static function make(string $type): OrderMetaWriterConcert {
        return match (BookingTypeEnum::tryFrom($type)) {
            BookingTypeEnum::ArrivalDeparture => new ArrivalDepartureMetaWriter(),
            BookingTypeEnum::Connection => new ConnectionMetaWriter(),
            default => throw new \InvalidArgumentException("Unsupported Service type: $type")
        };
    }
}