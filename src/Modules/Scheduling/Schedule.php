<?php

namespace Aero\Modules\Scheduling;

use Aero\Modules\Booking\BookingReview;
use WP_Error;

class Schedule
{

    /**
     * Run Schedule Jobs
     */
    public function run()
    {
        try {
            BookingReview::sendRequestReview();
        } catch (\Throwable $th) {
            return new WP_Error('error', "Failed scheduling. Error details: " . $th->getMessage());
        }
    }
}
