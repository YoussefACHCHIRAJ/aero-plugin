<?php

namespace Aero\Modules\Booking;

use Aero\Modules\City\CityHelper;
use Aero\Modules\Order\OrderService;
use WP_Error;

class BookingService
{

    protected $orderService;
    protected $bookingHelper;
    protected $cityHelper;

    public function __construct(OrderService $orderService, BookingHelper $bookingHelper, CityHelper $cityHelper)
    {
        if (!did_action('woocommerce_init') && function_exists("WC")) {
            WC()->initialize_session();
            WC()->initialize_cart();
        }

        $this->orderService = $orderService;
        $this->bookingHelper = $bookingHelper;
        $this->cityHelper = $cityHelper;
    }


    public function createBooking(array $data): array | WP_Error
    {
        try {

            $error_response  = validate_required_fields($data);

            if ($error_response) {
                return $error_response;
            }

            if (!$this->cityHelper->validateCity($data['city'])) {
                return new WP_Error('invalid_city', 'The given city is not yet supported', ['status' => 400]);
            }

            if (!$this->validateAvailabilityDate($data['date'])) {
                return new WP_Error('invalid_date', 'The Date is unavailable', ['status' => 400]);
            }

            $product = wc_get_product($data['productId']);

            if (! $product) {
                return new WP_Error('Failed', 'The Product Not Founded', ['status' => 400]);
            }

            $persons = $this->bookingHelper->create_persons($product, $data['persons']);

            $order = $this->orderService->createOrder($data);

            $item_line = $this->orderService->createOrderItem($order, $product, $data);


            $booking_item = $this->bookingHelper->create_booking($data['date'], $persons, $item_line, $product);
            if (!$booking_item) {
                wp_delete_post($order->get_id(), true);
                return new WP_Error("Failed", 'Product with id ' . $product->get_id() . ' does not saved. Order has been cancelled', ['status' => '500']);
            }

            $wc_booking_item = [
                'id' => $booking_item->id,
                'start' => $booking_item->start,
                'end' => $booking_item->end,
                'status' => $booking_item->status,
            ];

            $order->update_meta_data('_wc_booking_item', $wc_booking_item);
            $order->update_meta_data('_order_platform', $data['orderPlatform'] ?? "Web site");
            $order->update_meta_data("_booking_city", $data['city']);

            wp_update_post(array(
                'ID' => $booking_item->id,
                'post_parent' => $order->id
            ));

            $order->set_payment_method_title("Paypal");
            $order->calculate_totals();
            $order->save();


            return [
                'orderId' => $order->id,
            ];
        } catch (\Throwable $th) {
            return new WP_Error('Failed', 'Unable to create the booking order.', ['status' => 500, 'details' => $th->getMessage()]);
        }
    }

    public function createConnectionBooking(array $data): array | WP_Error
    {
        try {

            if (!$this->cityHelper->validateCity($data['city'])) {
                return new WP_Error('invalid_city', 'The given city is not yet supported', ['status' => 400]);
            }

            if (!$this->validateAvailabilityDate($data['arrival']['date'])) {
                return new WP_Error('invalid_date', 'The arrival Date is unavailable', ['status' => 400]);
            }

            if (!$this->validateAvailabilityDate($data['departure']['date'])) {
                return new WP_Error('invalid_date', 'The departure Date is unavailable', ['status' => 400]);
            }



            $product = wc_get_product($data['productId']);

            if (! $product) {
                return new WP_Error('Failed', 'The Product Not Founded', ['status' => 400]);
            }

            $persons = $this->bookingHelper->create_persons($product, $data['persons']);

            $order = $this->orderService->createOrder($data);

            $item_line = $this->orderService->createOrderItem($order, $product, $data, true);

            $arrival_booking_item = $this->bookingHelper->create_booking($data['arrival']['date'], $persons, $item_line, $product);

            if (!$arrival_booking_item) {
                wp_delete_post($order->get_id(), true);
                return new WP_Error("Failed", 'The booking can not be created. Order has been cancelled', ['status' => '500']);
            }

            $wc_arrival_booking_item = [
                'id' => $arrival_booking_item->id,
                'start' => $arrival_booking_item->start,
                'end' => $arrival_booking_item->end,
            ];

            $order->update_meta_data('_wc_arrival_booking_item', $wc_arrival_booking_item);
            $order->update_meta_data('_order_platform', $data['orderPlatform'] ?? "Web site");
            $order->update_meta_data("_booking_city", $data['city']);


            wp_update_post(array(
                'ID' => $arrival_booking_item->id,
                'post_parent' => $order->id
            ));

            $order->set_payment_method_title("Paypal");
            $order->calculate_totals();
            $order->save();


            return [
                'orderId' => $order->id,
                'paypalOrderId' => $data['paypalOrderId'] ?? 'not set'
            ];
        } catch (\Throwable $th) {
            return new WP_Error('server_error', 'Unable to create the booking order.', ['status' => 500, 'details' => $th->getMessage()]);
        }
    }

    private function validateAvailabilityDate($date)
    {
        $start_date_timestamp = strtotime(date('Y-m-d', strtotime($date)));
        $now = strtotime(date('Y-m-d')); // Today at 00:00:00

        // Compare full days
        if ($start_date_timestamp < strtotime('+2 days', $now)) {
            return false;
        }

        return true;
    }
}
