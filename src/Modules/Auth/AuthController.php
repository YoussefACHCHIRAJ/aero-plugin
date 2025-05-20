<?php

namespace Aero\Modules\Auth;

use Aero\Helpers\AeroRouter;
use Aero\Helpers\ApiResponse;
use WP_Error;
use WP_REST_Request;

class AuthController
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register_routes()
    {
        AeroRouter::post('auth/register', [$this, 'wc_register_customer']);

        AeroRouter::post('auth/login', [$this, 'wc_login_customer']);
    }

    public function wc_login_customer(WP_REST_Request $request)
    {
        $data = $request->get_json_params();

        $result = $this->authService->login($data);

        return ApiResponse::build($result, "Authentication successfully");
    }

    public function wc_register_customer(WP_REST_Request $request)
    {
        $data = $request->get_json_params();

        $required_fields = [
            'first_name',
            'last_name',
            'email',
            'password',
        ];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return new WP_Error(400, ucfirst($field) . ' is required');
            }
        }

        $result = $this->authService->register($data);

        return ApiResponse::build($result, 'Register successfully', 201);
    }
}
