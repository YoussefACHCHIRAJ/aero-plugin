<?php


namespace Aero\Modules\City;

use Aero\Config\ApiConfig;
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
        register_rest_route(ApiConfig::AERO_NAMESPACE, 'cities', array(
            'methods' => 'GET',
            'callback' => [$this, 'fetchCities'],
            'permission_callback' => function () {
                return current_user_can('administrator');
            },
        ));

        register_rest_route(ApiConfig::AERO_NAMESPACE, 'city', array(
            'methods' => 'GET',
            'callback' => [$this, 'fetchCity'],
            'args' => [
                'slug' => [
                    'required' => true,
                    'validate_callback' => function ($param) {
                        return is_string($param);
                    }
                ]
            ],
            'permission_callback' => function () {
                return current_user_can('administrator');
            },
        ));
    }

    public function fetchCities()
    {
        $cities = $this->cityService->fetchCities();

        return create_response($cities, "Fetched cities");
    }

    public function fetchCity(WP_REST_Request $request)
    {
        $slug = $request->get_param('slug');

        if (!isset($slug)) {
            return new WP_Error('bad-request', 'The slug is required.');
        }

        $city = $this->cityService->fetchCity($slug);

        return create_response($city, "Fetched city");
    }
}
