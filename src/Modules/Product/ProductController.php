<?php

namespace Aero\Modules\Product;

use Aero\Contracts\AeroControllerContract;
use Aero\Helpers\AeroRouter;
use Aero\Helpers\ApiResponse;
use WP_REST_Request;

class ProductController implements AeroControllerContract
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function registerRoutes()
    {
        AeroRouter::get('products', [$this, 'get_product_by_slug'], null, [
            'slug' => [
                'required' => true,
                'validate_callback' => function ($param) {
                    return is_string($param);
                }
            ]
        ]);

        AeroRouter::get('/city/(?P<citySlug>[a-zA-Z0-9-]+)/(?P<serviceSlug>[a-zA-Z0-9-]+)', [$this, 'get_city_service_by_slug']);
    }

    public function get_product_by_slug(WP_REST_Request $request)
    {

        $slug = $request->get_param('slug');

        $result = $this->productService->get_product_by_slug($slug);

        return ApiResponse::build($result, 'Product fetched by slug.');
    }

    public function get_city_service_by_slug(WP_REST_Request $request)
    {

        $citySlug = $request['citySlug'];
        $serviceSlug = $request['serviceSlug'];

        $result = $this->productService->get_city_service_by_slug($citySlug, $serviceSlug);

        return ApiResponse::build($result, 'Product fetched by slug.');
    }
}
