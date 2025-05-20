<?php

namespace Aero\Modules\Order;

class OrderRepository
{

    public function fetchOrderByIdAndEmail(string $id, string $email)
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

        // return $wpdb->get_row($wpdb->prepare($query, $slug));
    }

    
}
