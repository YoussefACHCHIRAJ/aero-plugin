<?php

namespace Aero\Modules\Booking;

use Aero\Modules\Booking\Handlers\ArrivalDepartureHandler;
use Aero\Modules\Booking\Handlers\BookingHandlerConcert;
use Aero\Modules\Booking\Handlers\ConnectionHandler;

class BookingFactory
{

    protected $bookingService;
    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * return the correspond booking service based on service type
     * 
     * @return \BookingHandleConcert
     */
    public function make(string $serviceType): BookingHandlerConcert
    {
        return match ($serviceType) {
            'arrivalDeparture' => new ArrivalDepartureHandler($this->bookingService),
            'connection' => new ConnectionHandler($this->bookingService),
            default => throw new \InvalidArgumentException("Unsupported Service type: $serviceType")
        };
    }
}
