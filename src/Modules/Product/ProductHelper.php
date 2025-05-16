<?php

namespace Aero\Modules\Product;


class ProductHelper
{
    public function wc_get_product_id_by_slug($slug)
    {
        global $wpdb;
        $query = $wpdb->prepare("
        SELECT ID
        FROM {$wpdb->posts}
        WHERE post_name = %s
        AND post_type = 'product'
        AND post_status = 'publish'
    ", $slug);

        return $wpdb->get_var($query);
    }

    public function wc_get_product_ids_by_tags($tags)
    {
        global $wpdb;

        if (!is_array($tags)) {
            $tags = [$tags];
        }

        $placeholders = implode(',', array_fill(0, count($tags), '%s'));

        $query = $wpdb->prepare("
        SELECT DISTINCT p.ID
        FROM {$wpdb->posts} AS p
        INNER JOIN {$wpdb->term_relationships} AS tr ON p.ID = tr.object_id
        INNER JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        INNER JOIN {$wpdb->terms} AS t ON tt.term_id = t.term_id
        WHERE p.post_type = 'product'
        AND p.post_status = 'publish'
        AND tt.taxonomy = 'product_tag'
        AND t.name IN ($placeholders)
    ", $tags);

        // Get results
        return $wpdb->get_col($query);
    }


    public function prepare_base_product($product)
    {

        $image_id = $product->get_image_id();
        $image = null;
        if ($image_id) {
            $attachment = wp_get_attachment_image_src($image_id, 'full');
        }

        if ($attachment) {
            $image_src = array(current($attachment));
            $image = array_map(function ($item) {
                return ["src" => $item];
            }, $image_src);
        }

        $product_data = [
            'id' => $product->get_id(),
            'name' => $product->get_name(),
            'slug' => $product->get_slug(),
            'price' => $product->get_price(),
            'images' => $image,
            'short_description' => $product->get_short_description(),
            'stock_status' => $product->get_stock_status(),
        ];

        return $product_data;
    }

    public function get_related_products($exclude_ids = [], $limit = 10, $categories = [], $city = null)
    {
        if (!empty($city)) {
            $category = get_term_by('name', $city, 'product_cat');
        }

        $query_args = [
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'post__not_in'   => $exclude_ids,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'tax_query'      => [
                [
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => isset($category) ? $category->term_id : $categories,
                    'operator' => 'IN',
                ],
            ],
        ];

        $product_ids = get_posts($query_args);

        $products = [];
        foreach ($product_ids as $product_id) {
            $product = wc_get_product($product_id);
            if ($product) {
                $featured_image_id = $product->get_image_id(); // Get the featured image ID

                $images = [];
                if ($featured_image_id) {
                    $attachment = wp_get_attachment_image_src($featured_image_id, 'full');
                    $images[] = [
                        'src' => $attachment ? $attachment[0] : '', // Image URL
                        'alt' => get_post_meta($featured_image_id, '_wp_attachment_image_alt', true) ?: '', // Alt text
                    ];
                }

                //* get product categories: 
                $category_ids = $product->get_category_ids();
                $categories = [];

                foreach ($category_ids as $id) {
                    $category = get_term($id);
                    $categories[] = strtolower($category->name);
                }

                //* prepare product for response:

                $products[] = [
                    'id'    => $product->get_id(),
                    'name'  => $product->get_name(),
                    'price' => $product->get_price(),
                    'slug' => $product->get_slug(),
                    'price' => $product->get_price(),
                    'short_description' => $product->get_short_description(),
                    'html_price' => $product->get_price_html(),
                    'images' => $images,
                    'categories' => $categories
                ];
            }
        }

        return $products;
    }
}
