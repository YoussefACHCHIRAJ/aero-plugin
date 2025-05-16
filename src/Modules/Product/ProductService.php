<?php

namespace Aero\Modules\Product;

use Aero\Modules\City\CityDao;
use Aero\Modules\City\CityHelper;
use WC_Product;
use WP_Error;
use Yoast\WP\SEO\Surfaces\Meta_Surface;

class ProductService
{
    protected $productHelper;
    protected $cityDao;
    protected $cityHelper;

    public function __construct(CityHelper $cityHelper, ProductHelper $productHelper, CityDao $cityDao)
    {
        $this->cityHelper =  $cityHelper;
        $this->productHelper = $productHelper;
        $this->cityDao = $cityDao;
    }


    public function get_person_types_data($product_id)
    {
        $all_person_types = [];
        if (!$product_id) {
            return new WP_Error('Bad Request', 'The product id param is required.', ['status' => 400]);
        }

        $product = wc_get_product($product_id);

        if (!$product) {
            return new WP_Error('Bad Request', 'There is no product with the given id.', ['status' => 400]);
        }

        if (!$product->has_person_types) {
            return new WP_Error('Bad Request', 'This product has no person types specified.', ['status' => 400]);
        }

        $person_types_ids = array_keys($product->person_types);

        foreach ($person_types_ids as $person_type_id) {
            $details = get_post($person_type_id);
            $meta_data = get_post_meta($person_type_id);


            if ($details) {
                $all_person_types[$person_type_id] = (array) $details;

                if ($meta_data) {
                    $all_person_types[$person_type_id]['meta_data'] = $meta_data;
                }
            }
        }

        // Return the array of person types as a JSON response
        return $all_person_types;
    }


    public function get_product_by_slug($slug)
    {

        if (!$slug) {
            return new WP_Error('Bad Request', 'The product slug param is required.', ['status' => 400]);
        }

        $product = wc_get_product($this->productHelper->wc_get_product_id_by_slug($slug));

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

        $service = wc_get_product($this->productHelper->wc_get_product_id_by_slug($serviceSlug));

        if (!$service) {
            return new WP_Error('Not Found', 'The service not exist or you may misspelled.', ['status' => 404]);
        }

        $cityTerm = $this->cityDao->fetchBySlug($citySlug);

        if (!$cityTerm) {
            return new WP_Error('Not Found', 'The city not exist or you may misspelled.', ['status' => 404]);
        }

        $city = $this->cityHelper->prepare_city_for_response($cityTerm);

        $preparedService = $this->prepare_product_for_response($service);

        $preparedService['city'] = $city;
        return $preparedService;
    }


    public function get_popular_products()
    {
        $product_ids = $this->productHelper->wc_get_product_ids_by_tags("popular-product");
        $products = [];

        foreach ($product_ids as $id) {
            $product = wc_get_product($id);
            $products[] = $this->productHelper->prepare_base_product($product);
        }

        return $product;
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

    private function prepare_service_for_response(WC_Product $product)
    {
        //* Get product image:
        $image_id = $product->get_image_id();
        if ($image_id) {
            $attachment = wp_get_attachment_image_src($image_id, 'full');
        }

        if ($attachment) {
            $image_src = current($attachment);
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
            'yoast_head_json' => $yoast_head_json
        ];

        return $product_data;
    }

    private function get_person_types_for_product($product)
    {

        $cache_key = 'person_types_' . $product->get_id();
        $cached_person_types = wp_cache_get($cache_key, 'person_types_group');

        if ($cached_person_types) {
            return $cached_person_types;
        }

        $all_person_types = [];

        if (!$product->has_person_types) {
            return null;
        }

        $person_types_ids = array_keys($product->person_types);

        foreach ($person_types_ids as $person_type_id) {
            $details = get_post($person_type_id);
            $meta_data = get_post_meta($person_type_id);

            if ($details) {
                $all_person_types[$person_type_id] = (array) $details;

                if ($meta_data) {
                    $all_person_types[$person_type_id]['meta_data'] = $meta_data;
                }
            }
        }

        wp_cache_set($cache_key, $all_person_types, 'person_types_group', 3600);
        return $all_person_types;
    }
}
