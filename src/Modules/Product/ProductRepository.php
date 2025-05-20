<?php

namespace Aero\Modules\Product;

class ProductRepository
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

    public function fetchProductIdBySlug(string $slug)
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

    public function fetchProductsIdByTags(array|string $tags)
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
}
