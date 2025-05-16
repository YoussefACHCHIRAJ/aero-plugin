<?php

namespace Aero\Modules\Payment;

use Aero\Config\ApiConfig;
use Aero\Modules\Order\OrderHelper;
use WP_REST_Request;

class PaymentController
{

    protected $paymentService;
    protected $orderHelper;

    public function __construct(PaymentService $paymentService, OrderHelper $orderHelper)
    {
        $this->paymentService = $paymentService;
        $this->orderHelper =  $orderHelper;
    }

    public function register_routes()
    {
        register_rest_route(ApiConfig::AERO_NAMESPACE, 'set-paypal-orderId', array(
            'methods' => 'POST',
            'callback' => [$this, 'link_order_with_paypal_id'],
            'permission_callback' => function () {
                return current_user_can('administrator');
            },
        ));




        register_rest_route(ApiConfig::AERO_NAMESPACE, 'validate-payment-order', array(
            'methods' => 'POST',
            'callback' => [$this, 'validate_payment_order'],
            'permission_callback' => function () {
                return current_user_can('administrator');
            },
        ));
    }


    public function link_order_with_paypal_id(WP_REST_Request $request)
    {
        $data = $request->get_json_params();
        $result = $this->paymentService->link_order_with_paypal_id($data);

        return create_response($result, 'Order has been linked with paypal');
    }


    public function validate_payment_order(WP_REST_Request $request)
    {
        $data = $request->get_json_params();

        $result = $this->paymentService->validate_payment_order($data);
        return create_response($result, 'Order Payment Validation completed.');
    }
}
