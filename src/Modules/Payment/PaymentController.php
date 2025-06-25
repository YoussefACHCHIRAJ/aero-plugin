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

        AeroRouter::post('set-paypal-orderId', [$this, 'linkOrderWithPaypalId']);
        AeroRouter::post('order/paypal-email', [$this, 'savePaypalEmail']);
        AeroRouter::post('validate-payment-order', [$this, 'validatePaymentOrder']);
    }


    public function linkOrderWithPaypalId(WP_REST_Request $request)
    {
        $data = $request->get_json_params();
        $result = $this->paymentService->linkOrderWithPaypalId($data);

        return ApiResponse::build($result, 'Order has been linked with paypal');
    }


    public function validatePaymentOrder(WP_REST_Request $request)
    {
        $data = $request->get_json_params();

        $result = $this->paymentService->validatePaymentOrder($data);
        return ApiResponse::build($result, 'Order Payment Validation completed.');
    }

    public function savePaypalEmail(WP_REST_Request $request)
    {
        $data = $request->get_json_params();

        $this->paymentService->savePaypalEmail($data);

        return ApiResponse::build(['message' => 'Paypal Email handled'], 'Paypal Email handled');
    }
}
