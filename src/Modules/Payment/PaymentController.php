<?php

namespace Aero\Modules\Payment;

use Aero\Contracts\AeroControllerContract;
use Aero\Helpers\AeroRouter;
use Aero\Helpers\ApiResponse;
use Aero\Modules\Order\OrderHelper;
use WP_REST_Request;

class PaymentController implements AeroControllerContract
{

    protected $paymentService;
    protected $orderHelper;

    public function __construct(PaymentService $paymentService, OrderHelper $orderHelper)
    {
        $this->paymentService = $paymentService;
        $this->orderHelper =  $orderHelper;
    }

    public function registerRoutes()
    {

        AeroRouter::post('set-paypal-orderId', [$this, 'link_order_with_paypal_id']);
        AeroRouter::post('validate-payment-order', [$this, 'validate_payment_order']);
    }


    public function link_order_with_paypal_id(WP_REST_Request $request)
    {
        $data = $request->get_json_params();
        $result = $this->paymentService->link_order_with_paypal_id($data);

        return ApiResponse::build($result, 'Order has been linked with paypal');
    }


    public function validate_payment_order(WP_REST_Request $request)
    {
        $data = $request->get_json_params();

        $result = $this->paymentService->validate_payment_order($data);
        return ApiResponse::build($result, 'Order Payment Validation completed.');
    }
}
