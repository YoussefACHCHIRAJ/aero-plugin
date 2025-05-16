<?php

namespace Aero\Modules\Product;

class ProductDao
{
    public function fetchProductsByCityId($cityId)
    {
        global $wpdb;


        $products_query = "
        SELECT p.ID as id, p.post_title as name, p.post_excerpt as excerpt, p.post_name as slug, pm_price.meta_value as price
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
        INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        LEFT JOIN {$wpdb->postmeta} pm_price ON p.ID = pm_price.post_id AND pm_price.meta_key = '_price'
        WHERE tt.term_id = %d
        AND p.post_type = 'product'
        AND p.post_status = 'publish'
    ";

        // Fetch products for the category
        return $wpdb->get_results($wpdb->prepare($products_query, $cityId));
    }
}
