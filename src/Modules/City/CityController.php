<?php


namespace Aero\Modules\City;

use Aero\Config\ApiConfig;
use Aero\Helpers\AeroRouter;
use Aero\Helpers\ApiResponse;
use WP_Error;
use WP_REST_Request;

class CityController
{

    protected $cityService;

    public function __construct(CityService $cityService)
    {
        $this->cityService = $cityService;
    }

    public function register_routes()
    {
        AeroRouter::get('cities', [$this, 'fetchCities']);
        AeroRouter::get('city', [$this, 'fetchCity'], null, [
            'slug' => [
                'required' => true,
                'validate_callback' => function ($param) {
                    return is_string($param);
                }
            ]
        ]);
    }

    public function fetchCities()
    {
        $cities = $this->cityService->fetchCities();

        return ApiResponse::build($cities, "Fetched cities");
    }

    public function fetchCity(WP_REST_Request $request)
    {
        $slug = $request->get_param('slug');

        if (!isset($slug)) {
            return new WP_Error('bad-request', 'The slug is required.');
        }

        $city = $this->cityService->fetchCity($slug);

        return ApiResponse::build($city, "Fetched city");
    }
}
