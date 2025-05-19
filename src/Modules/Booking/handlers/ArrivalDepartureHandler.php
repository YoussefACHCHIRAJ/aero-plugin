<?php

namespace Aero\Modules\Booking\Handlers;

use Aero\Modules\Booking\BookingService;

class ArrivalDepartureHandler implements BookingHandlerConcert {
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }
    
    public function handle(array $data) {
        return $this->bookingService->create($data);
    }
}