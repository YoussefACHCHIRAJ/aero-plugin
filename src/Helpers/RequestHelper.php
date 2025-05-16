<?php

namespace Aero\Helpers;

class RequestHelper
{
    static public function get($url, $params)
    {
        $url_with_query = add_query_arg($params, $url);
        $response = wp_remote_get($url_with_query);
        if (is_wp_error($response)) {
            return null;
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
}
