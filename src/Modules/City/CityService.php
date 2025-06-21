<?php


namespace Aero\Modules\City;

use Aero\Modules\Product\ProductRepository;
use WP_Error;
use Yoast\WP\SEO\Surfaces\Meta_Surface;

class CityService
{
    protected $cityHelper;
    protected $cityRepository;
    protected $productRepository;

    public function __construct(CityHelper $cityHelper, CityRepository $cityRepository, ProductRepository $productRepository)
    {
        $this->cityHelper = $cityHelper;
        $this->cityRepository = $cityRepository;
        $this->productRepository = $productRepository;
    }

    public function fetchCities()
    {
        $terms = $this->cityRepository->fetchAll();

        if (!$terms || sizeof($terms) < 1) {
            return [];
        }

        foreach ($terms as $term) {
            $cities[] = $this->cityHelper->prepare_city_for_response($term);
        }

        return $cities;
    }

    public function fetchCity(string $slug)
    {

        $term = $this->cityRepository->fetchBySlug($slug);


        if (!$term) {
            return new WP_Error('not-found-city', "The city not supported yer.", ['status' => 404]);
        }

        // Query to fetch products associated with the category
        $products = $this->productRepository->fetchProductsByCityId($term->term_id);

        foreach ($products as $product) {
            $thumbnail_id = get_post_thumbnail_id($product->id);
            $product->image['src'] = $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : null;
            $product->image['alt'] = $thumbnail_id ? get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true) : null;
        }

        // Attach products to the category object

        $city = $this->cityHelper->prepare_city_for_response($term);

        $yoast_head_json = [];
        if (function_exists('YoastSEO')) {
            $meta_helper = YoastSEO()->classes->get(Meta_Surface::class);
            $meta = $meta_helper->for_term($city['id']);
            if ($meta) {
                $meta_head = $meta->get_head();
                $yoast_head_json = $meta_head->json;
            } else {
                $yoast_head_json = [];
            };
        }

        $city['products'] = $products;
        $city['yoast_head_json'] = $yoast_head_json;

        
        // add other cities
        $terms = $this->cityRepository->fetchAllExceptBySlug($slug);

        if (!$terms || sizeof($terms) < 1) {
            return $city;
        }

        foreach ($terms as $term) {
            $cities[] = $this->cityHelper->prepare_city_for_response($term);
        }

        $city['otherCities'] = $cities;

        return $city;
    }
}
