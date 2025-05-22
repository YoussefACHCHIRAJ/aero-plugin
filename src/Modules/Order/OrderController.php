<?php

namespace Aero\Modules\Order;

use Aero\Contracts\AeroControllerContract;
use Aero\Helpers\AeroRouter;
use Aero\Helpers\ApiResponse;
use WP_Error;
use WP_REST_Request;

class OrderController implements AeroControllerContract
{
    protected $orderServices;

    public function __construct(OrderService $orderServices)
    {
        $this->orderServices = $orderServices;
    }

    public function registerRoutes()
    {
        AeroRouter::put('order/update-status', [$this, 'update_order_status']);

        AeroRouter::post('order-meta', [$this, 'get_order_meta_by_id']);

        AeroRouter::put('orders/billing', [$this, 'save_order_billing']);

        AeroRouter::get('orders/find-my-order/(?P<orderId>[0-9-]+)', [$this, 'find_my_order'], null, [
            'email' => [
                'required' => true,
                'validate_callback' => function ($param) {
                    return is_string($param);
                }
            ]
        ]);
    }

    public function get_order_meta_by_id(WP_REST_Request $request)
    {
        try {
            $data = $request->get_json_params();

            $result = $this->orderServices->get_order_meta_by_id($data);

            return ApiResponse::build($result, 'The Order Meta data.', 200);
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

        return ApiResponse::build($result, 'Order Status Updated');
    }

    public function save_order_billing(WP_REST_Request $request)
    {
        $data = $request->get_json_params();

        $result = $this->orderServices->saveOrderBilling($data);

        return ApiResponse::build($result, 'The Order billing');
    }

    public function find_my_order(WP_REST_Request $request)
    {

        $orderId = sanitize_text_field($request['orderId']);

        $email = sanitize_email($request->get_param('email'));

        $result = $this->orderServices->get_order_by_id_and_email($orderId, $email);

        return ApiResponse::build($result, "Order Details");
    }
}
