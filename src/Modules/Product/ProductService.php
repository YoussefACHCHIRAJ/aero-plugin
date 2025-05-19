<?php

namespace Aero\Modules\Product;

use Aero\Modules\City\CityRepository;
use Aero\Modules\City\CityHelper;
use WC_Product;
use WP_Error;
use Yoast\WP\SEO\Surfaces\Meta_Surface;

class ProductService
{
    protected $productHelper;
    protected $cityRepository;
    protected $cityHelper;
    protected $productRepository;

    public function __construct(CityHelper $cityHelper, ProductHelper $productHelper, CityRepository $cityRepository, ProductRepository $productRepository)
    {
        $this->cityHelper =  $cityHelper;
        $this->productHelper = $productHelper;
        $this->cityRepository = $cityRepository;
        $this->productRepository = $productRepository;
    }


    public function get_product_by_slug($slug)
    {

        if (!$slug) {
            return new WP_Error('Bad Request', 'The product slug param is required.', ['status' => 400]);
        }

        $productId = $this->productRepository->fetchProductIdBySlug($slug);
        $product = wc_get_product($productId);

        if (!$product) {
            return new WP_Error('Bad Request', 'There is no product with the given slug.', ['status' => 404]);
        }

        return $this->prepare_product_for_response($product);
    }

    public function get_city_service_by_slug($citySlug, $serviceSlug)
    {

        if (!$citySlug) {
            return new WP_Error('Bad Request', 'The city slug slug param is required.', ['status' => 400]);
        }

        if (!$serviceSlug) {
            return new WP_Error('Bad Request', 'The serviceSlug slug param is required.', ['status' => 400]);
        }

        $productId = $this->productRepository->fetchProductIdBySlug($serviceSlug);
        $service = wc_get_product($productId);

        if (!$service) {
            return new WP_Error('Not Found', 'The service not exist or you may misspelled.', ['status' => 404]);
        }

        $cityTerm = $this->cityRepository->fetchBySlug($citySlug);

        if (!$cityTerm) {
            return new WP_Error('Not Found', 'The city not exist or you may misspelled.', ['status' => 404]);
        }

        $city = $this->cityHelper->prepare_city_for_response($cityTerm);

        $preparedService = $this->prepare_product_for_response($service);

        $preparedService['city'] = $city;
        return $preparedService;
    }


    private function prepare_product_for_response(WC_Product $product)
    {
        //* Get product image:
        $image_id = $product->get_image_id();
        if ($image_id) {
            $attachment = wp_get_attachment_image_src($image_id, 'full');
        }

        if ($attachment) {
            $image_src = current($attachment);
        }

        //* get product categories: 
        $category_ids = $product->get_category_ids();
        $categories = [];

        foreach ($category_ids as $id) {
            $category = get_term($id);
            $categories[] = strtolower($category->name);
        }

        $yoast_head_json = [];
        if (function_exists('YoastSEO')) {
            $meta_helper = YoastSEO()->classes->get(Meta_Surface::class);
            $meta = $meta_helper->for_post($product->id);
            if ($meta) {
                $meta_head = $meta->get_head();
                $yoast_head_json = $meta_head->json;
            } else {
                $yoast_head_json = [];
            }
        }

        //* prepare product for response:
        $product_data = [
            'id' => $product->get_id(),
            'name' => $product->get_name(),
            'slug' => $product->get_slug(),
            'price' => $product->get_price(),
            'image_src' => $image_src ?? null,
            'description' => $product->get_description(),
            'short_description' => $product->get_short_description(),
            'stock_status' => $product->get_stock_status(),
            'has_person_types' => $product->has_person_types,
            'addons' => $product->get_meta('_product_addons'),
            'pricing' => $product->pricing,
            'person_types' => $this->get_person_types_for_product($product),
            'min_date' => [
                'unit' => $product->min_date_unit,
                'value' => $product->min_date_value,
            ],
            'max_date' => [
                'unit' => $product->max_date_unit,
                'value' => $product->max_date_value,
            ],
            'categories' => $categories,
            'yoast_head_json' => $yoast_head_json
        ];

        return $product_data;
    }

    private function get_person_types_for_product(WC_Product $product)
    {

        $cache_key = 'person_types_' . $product->get_id();
        $cached_person_types = wp_cache_get($cache_key, 'person_types_group');

        if ($cached_person_types) {
            return $cached_person_types;
        }

        if (!$product->has_person_types) {
            return null;
        }

        $all_person_types = $this->productHelper->extractPersonsFromProduct($product->person_types);


        wp_cache_set($cache_key, $all_person_types, 'person_types_group', 3600);
        return $all_person_types;
    }
}
