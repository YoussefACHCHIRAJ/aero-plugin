<?php

namespace Aero\Modules\City;

class CityRepository
{

    public function fetchBySlug(string $slug)
    {
        global $wpdb;

        $query = "
        SELECT *
        FROM {$wpdb->terms} t
        INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id
        WHERE tt.taxonomy = 'product_cat'
        AND t.slug = %s
        LIMIT 1
        ";

        return $wpdb->get_row($wpdb->prepare($query, $slug));
    }

    public function fetchAll()
    {
        global $wpdb;

        $query = "
        SELECT *
        FROM {$wpdb->terms} t
        INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id
        WHERE tt.taxonomy = 'product_cat'
        AND t.slug != %s
    ";

        return $wpdb->get_results($wpdb->prepare($query, 'uncategorized'));
    }

    public function fetchAllExceptBySlug(string $slug)
    {
        global $wpdb;

        $query = "
        SELECT *
        FROM {$wpdb->terms} t
        INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id
        WHERE tt.taxonomy = 'product_cat'
        AND t.slug != %s
        AND t.slug != %s
    ";

        return $wpdb->get_results($wpdb->prepare($query, ['uncategorized', $slug]));
    }

    public static function fetchCitiesName()
    {
        global $wpdb;

        $query = "
        SELECT t.name
        FROM {$wpdb->terms} t
        INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id
        WHERE tt.taxonomy = 'product_cat'
        AND t.slug != %s
    ";
        return $wpdb->get_col($wpdb->prepare($query, 'uncategorized'));
    }
}
