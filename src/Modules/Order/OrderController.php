<?php

namespace Aero\Modules\Order;


use Aero\Config\ApiConfig;
use WP_Error;
use WP_REST_Request;

class OrderController
{
    protected $orderServices;

    public function __construct(OrderService $orderServices)
    {
        $this->orderServices = $orderServices;
    }

    public function register_routes() {
        register_rest_route(ApiConfig::AERO_NAMESPACE, 'order/update-status', array(
            'methods' => 'PUT',
            'callback' => [$this, 'update_order_status'],
            'permission_callback' => function () {
                return current_user_can('administrator');
            },
        ));
        register_rest_route(ApiConfig::AERO_NAMESPACE, 'order-meta', array(
            'methods' => 'POST',
            'callback' => [$this, 'get_order_meta_by_id'],
            'permission_callback' => function () {
                return current_user_can('administrator');
            },
        ));


        register_rest_route(ApiConfig::AERO_NAMESPACE, 'orders/insights', array(
            'methods' => 'GET',
            'callback' => [$this, 'fetch_orders_insights'],
            'permission_callback' => function () {
                return current_user_can('administrator');
            },
        ));

        register_rest_route(ApiConfig::AERO_NAMESPACE, 'orders/billing', array(
            'methods' => 'PUT',
            'callback' => [$this, 'save_order_billing'],
            'permission_callback' => function () {
                return current_user_can('administrator');
            },
        ));

        register_rest_route(ApiConfig::AERO_NAMESPACE, 'orders/find-my-order/(?P<orderId>[0-9-]+)', array(
            'methods' => 'GET',
            'callback' => [$this, 'find_my_order'],
            'args' => [
                'email' => [
                    'required' => true,
                    'validate_callback' => function ($param) {
                        return is_string($param);
                    }
                ]
            ],
            'permission_callback' => function () {
                return current_user_can('administrator');
            },
        ));
    }

    public function get_order_meta_by_id(WP_REST_Request $request)
    {
        try {
            $data = $request->get_json_params();

            $result = $this->orderServices->get_order_meta_by_id($data);

            return create_response($result, 'The Order Meta data.', 200);
        } catch (\Throwable $th) {
            return new WP_Error('server_error', 'something is going wrong', ['status' => 500, 'details' => $th->getMessage()]);
        }
    }

    public function update_order_status(WP_REST_Request $request)
    {
        $data = $request->get_json_params();

        if (empty($data['paypal_order'])) {
            return new WP_Error('Rejected', 'paypal order is required', ['status' => 400]);
        }

        $result = $this->orderServices->update_order_status($data);

        return create_response($result, 'Order Status Updated');
    }

    public function fetch_orders_insights()
    {
        $result = $this->orderServices->fetch_orders_insights();

        return create_response($result, 'The orders insights.', 200);
    }

    public function save_order_billing(WP_REST_Request $request)
    {
        $data = $request->get_json_params();

        $result = $this->orderServices->saveOrderBilling($data);

        return create_response($result, 'The Order billing');
    }

    public function find_my_order(WP_REST_Request $request)
    {

        $orderId = sanitize_text_field($request['orderId']);

        $email = sanitize_email($request->get_param('email'));

        $result = $this->orderServices->get_order_by_id_and_email($orderId, $email);

        return create_response($result, "Order Details");
    }
}
