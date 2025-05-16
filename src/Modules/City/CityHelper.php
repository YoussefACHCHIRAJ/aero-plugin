<?php


namespace Aero\Modules\City;

class CityHelper
{

    public function prepare_city_for_response($term)
    {
        $data = [
            'id' => (int) $term->term_id,
            'name'        => $term->name,
            'slug'        => $term->slug,
            'description' => $term->description,
            'image'       => null,
        ];

        $image_id = get_term_meta($term->term_id, 'thumbnail_id', true);
        if ($image_id) {
            $data['image'] = array(
                'src'               => wp_get_attachment_url($image_id),
                'alt'               => get_post_meta($image_id, '_wp_attachment_image_alt', true),
            );
        }

        return $data;
    }

    public function validateCity(string | null $city)
    {
        global $wpdb;

        $query = "
        SELECT t.name
        FROM {$wpdb->terms} t
        INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id
        WHERE tt.taxonomy = 'product_cat'
        AND t.slug != %s
    ";
        $availableCities = $wpdb->get_col($wpdb->prepare($query, 'uncategorized'));

        $availableCities = array_map('strtolower', $availableCities);

        if (!in_array(strtolower($city), $availableCities)) {
            return false;
        }

        return true;
    }
}
