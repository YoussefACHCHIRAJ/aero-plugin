<?php

namespace Aero\Helpers;

use WP_REST_Response;

class ApiResponse
{
    public static function error($message, $status = 400): WP_REST_Response
    {
        return new WP_REST_Response([
            'message' => $message,
            'status' => $status,
        ], $status);
    }

    public static function build($data, string $message, int $statusCode = 200): WP_REST_Response
    {
        if (is_wp_error($data)) {
            return $data;
        }

        return new WP_REST_Response([
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }
}
