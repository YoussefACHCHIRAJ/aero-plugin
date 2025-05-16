<?php

namespace Aero\Modules\Auth;

use Aero\Config\ApiConfig;
use WP_Error;
use WP_REST_Request;

class AuthController
{
    protected $authService;

    public function __construct(AuthService $authService) {
        $this->authService = $authService;
    }

    public function register_routes() {
        register_rest_route(ApiConfig::AERO_NAMESPACE, 'auth/register', array(
            'methods' => 'POST',
            'callback' => [$this, 'wc_register_customer'],
            'permission_callback' => function () {
                return current_user_can('administrator');
            },
        ));

        register_rest_route(ApiConfig::AERO_NAMESPACE, 'auth/login', array(
            'methods' => 'POST',
            'callback' => [$this, 'wc_login_customer'],
            'permission_callback' => function () {
                return current_user_can('administrator');
            },
        ));
    }

    public function wc_login_customer(WP_REST_Request $request)
    {
        $data = $request->get_json_params();

        $result = $this->authService->login($data);

        return create_response($result, "Authentication successfully");
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

        return create_response($result, 'Register successfully', 201);
    }
}
