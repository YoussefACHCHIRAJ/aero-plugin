<?php

namespace Aero\Modules\Booking;

use Aero\Helpers\AeroRouter;
use Aero\Modules\Booking\BookingService;
use WP_Error;
use WP_REST_Request;

class BookingController
{
    protected $bookingService;


    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function register_routes()
    {
        AeroRouter::post('booking', [$this, 'create']);
    }

    public function create(WP_REST_Request $request)
    {
        try {
            $data = $request->get_json_params();

            $result = $this->bookingService->create($data);

            return create_response($result, 'The booking has been saved and the order has been created', 201);
        } catch (\Throwable $th) {
            return new WP_Error('server_error', 'something is going wrong', ['status' => 500, 'details' => $th->getMessage()]);
        }
    }
}
