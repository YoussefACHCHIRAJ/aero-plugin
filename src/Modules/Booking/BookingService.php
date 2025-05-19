<?php

namespace Aero\Modules\Booking;

use Aero\Modules\City\CityHelper;
use Aero\Modules\Order\OrderService;
use BookingTypeEnum;
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


    public function create(array $data): array | WP_Error
    {
        try {

            /**
             * 1.validate shared data: Done
             * 2. get the woo_product: Done
             * 3. create persons: Done
             * 4. create order: Done
             * 5. create order Item: Done
             * 6. create item line for the order: Done
             * 7. if creating item line failed delete the order and return an error and log the error: Done
             * 9. save meta data for the order "_wc_booking_item" and "_booking_city": Done
             * 10. link order with the booking item: Done
             * 11. set the payment method: Done
             * 12. recalculate order totals: Done
             * 13. save order: Done
             * 14. return order id in an array: Done 
             */

            $isConnectionType = $data['serviceType'] === BookingTypeEnum::Connection;
            $dataToValidate = ['productId', 'productPrice', 'persons', 'city', 'passengerName', 'mobileNumber', 'serviceType'];
            $bookingDate = !$isConnectionType ? $data['date'] : $data['arrival']['date'];

            $error_response  = BookingValidator::validate($data, $dataToValidate);

            if ($error_response) {
                return $error_response;
            }

            $product = wc_get_product($data['productId']);

            if (! $product) {
                return new WP_Error('Failed', 'The Product Not Founded', ['status' => 400]);
            }

            $persons = $this->bookingHelper->create_persons($product, $data['persons']);

            $order = $this->orderService->createOrder($data);

            $item_line = $this->orderService->createOrderItem($order, $product, $data, $isConnectionType);


            $booking_item = $this->bookingHelper->create_booking($bookingDate, $persons, $item_line, $product);

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

}
