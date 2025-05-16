<?php

namespace Aero\Modules\Product;

use Aero\Config\ApiConfig;
use WP_REST_Request;

class ProductController
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function register_routes()
    {
        register_rest_route(ApiConfig::AERO_NAMESPACE, 'person-types', array(
            'methods' => 'GET',
            'callback' => [$this, 'get_person_types_data'],
            'args' => [
                'product_id' => [
                    'required' => true,
                    'validate_callback' => function ($param) {
                        return is_numeric($param);
                    }
                ]
            ],
            'permission_callback' => function () {
                return current_user_can('administrator');
            },
        ));

        register_rest_route(ApiConfig::AERO_NAMESPACE, 'products', array(
            'methods' => 'GET',
            'callback' => [$this, 'get_product_by_slug'],
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

        register_rest_route(ApiConfig::AERO_NAMESPACE, '/city/(?P<citySlug>[a-zA-Z0-9-]+)/(?P<serviceSlug>[a-zA-Z0-9-]+)', array(
            'methods' => 'GET',
            'callback' => [$this, 'get_city_service_by_slug'],
            'permission_callback' => function () {
                return current_user_can('administrator');
            },
        ));
    }

    public function get_person_types_data(WP_REST_Request $request)
    {
        $result = $this->productService->get_person_types_data($request->get_param('product_id'));

        return create_response($result, 'Persons type data');
    }

    public function get_product_by_slug(WP_REST_Request $request)
    {

        $slug = $request->get_param('slug');

        $result = $this->productService->get_product_by_slug($slug);

        return create_response($result, 'Product fetched by slug.');
    }

    public function get_city_service_by_slug(WP_REST_Request $request)
    {

        $citySlug = $request['citySlug'];
        $serviceSlug = $request['serviceSlug'];

        $result = $this->productService->get_city_service_by_slug($citySlug, $serviceSlug);

        return create_response($result, 'Product fetched by slug.');
    }

    public function get_popular_products()
    {
        $result = $this->productService->get_popular_products();

        return create_response($result, 'Popular product fetched.');
    }
}
