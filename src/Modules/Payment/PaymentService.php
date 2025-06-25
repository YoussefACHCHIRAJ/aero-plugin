<?php

namespace Aero\Modules\Payment;

use Aero\Modules\Order\OrderHelper;
use WP_Error;

class PaymentService
{
    protected $orderHelpers;

    public function __construct(OrderHelper $orderHelpers)
    {
        $this->orderHelpers = $orderHelpers;
    }

    public function linkOrderWithPaypalId(array $data)
    {
        $order = null;

        if (isset($data['paypalOrderId'])) {
            $order = wc_get_order($data['orderId']);
        }

        if (!$order) {
            return new WP_Error('failed', [
                'message' => 'This is not a valid order id.',
                'value' => $data['orderId']
            ], ['status' => 404]);
        }

        if (isset($data['paypalOrderId'])) {
            $order->update_meta_data('_paypal_order_id', $data['paypalOrderId']);
            $order->save();
        }

        return [
            'orderId' => $order->id,
            'paypalOrderId' => $data['paypalOrderId'] ?? 'not set'
        ];
    }


    public function validatePaymentOrder(array $data)
    {

        if (!isset($data['id'])) {
            return new WP_Error('Failed', 'The id of payment is required', ['status' => 400]);
        }

        try {
            $order_id = $this->orderHelpers->get_order_id_by_meta('_paypal_order_id', $data['id']);

            $order = wc_get_order($order_id);

            if (! $order) {
                return new WP_Error('Failed', 'The order does not exists', ['status' => 404]);
            }

            $order_total = $order->get_total();

            $paypal_amount = null;
            if (
                isset($data['purchase_units'][0]['amount']['value'])
            ) {
                $paypal_amount = $data['purchase_units'][0]['amount']['value'];
            }

            if ($order_total === $paypal_amount) {
                $order->update_meta_data('_valid_payment_order', true);
                $order->save();
                return true;
            }

            $order->update_status("failed", 'The order failed due to invalid payment order.');
            $order->update_meta_data('_valid_payment_order', false);
            $order->save();

            return false;
        } catch (\Throwable $th) {
            return new WP_Error('Failed', 'Something went wrong.', ['status' => 500]);
        }
    }

    public function savePaypalEmail(array $data) {
        $paypalOrderId = $data['paymentOrder']['id'] ?? null;
        $paypalEmail = $data['paypalEmail'] ?? null;

        if(!$paypalOrderId || !$paypalEmail) return;

        try {
            $order_id = $this->orderHelpers->get_order_id_by_meta('_paypal_order_id', $data['id']);

            $order = wc_get_order($order_id);

            if (! $order) {
                return;
            }

            $order->update_meta_data('__paypal_address_email', $paypalEmail);
            $order->add_order_note("Paypal address email: $paypalEmail");

            $order->save();

        } catch (\Throwable $th) {
            return;
        }
    }
}
