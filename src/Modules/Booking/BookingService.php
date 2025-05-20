<?php

namespace Aero\Modules\Booking;

use Aero\Modules\City\CityHelper;
use Aero\Modules\Order\OrderService;
use BookingTypeEnum;
use InvalidArgumentException;
use WC_Booking;
use WC_Order;
use WP_Error;


class BookingService
{

    protected array $dataToValidate = ['productId', 'productPrice', 'persons', 'city', 'passengerName', 'mobileNumber', 'serviceType'];
    protected $orderService;
    protected $bookingHelper;
    protected $cityHelper;
    protected $orderId = null;

    public function __construct(OrderService $orderService, BookingHelper $bookingHelper, CityHelper $cityHelper)
    {
        $this->orderService = $orderService;
        $this->bookingHelper = $bookingHelper;
        $this->cityHelper = $cityHelper;
    }


    public function create(array $data): array | WP_Error
    {
        try {

            self::initializeWoocommerce();

            BookingValidator::validate($data, $this->dataToValidate);

            $product = wc_get_product($data['productId']);

            if (! $product) {
                return new WP_Error('Failed', 'The Product Not Founded', ['status' => 400]);
            }

            $persons = $this->bookingHelper->createPersons($product, $data['persons']);

            $order = $this->orderService->createOrder($data['passengerName']);

            $this->setOrderId($order->get_id());

            $orderItem = $this->orderService->createOrderItem($order, $product, $data);


            $bookingDate = $this->getBookingDate($data);

            $bookingItem = $this->bookingHelper->createBooking($bookingDate, $persons, $orderItem, $product);

            self::writeBookingItemMeta($bookingItem, $order);
            $order->update_meta_data("_booking_city", $data['city']);

            wp_update_post(array(
                'ID' => $bookingItem->id,
                'post_parent' => $order->id
            ));

            $order->set_payment_method_title("Paypal");
            $order->calculate_totals();
            $order->save();


            return [
                'orderId' => $order->get_id(),
            ];
        } catch (\Throwable $th) {
            wp_delete_post($this->orderId, true);
            return new WP_Error('Failed', 'Unable to create the booking order.', ['status' => $th->getCode(), 'details' => $th->getMessage()]);
        }
    }

    private static function initializeWoocommerce()
    {
        if (!did_action('woocommerce_init') && function_exists("WC")) {
            WC()->initialize_session();
            WC()->initialize_cart();
        }
    }

    private static function writeBookingItemMeta(WC_Booking $bookingItem, WC_Order $order)
    {
        $wcBookingItem = [
            'id' => $bookingItem->id,
            'start' => $bookingItem->start,
            'end' => $bookingItem->end,
            'status' => $bookingItem->status,
        ];

        $order->update_meta_data('_wc_booking_item', $wcBookingItem);
    }

    private function getBookingDate(array $data)
    {
        $date = !BookingTypeEnum::isConnection($data['serviceType']) ? $data['date'] : $data['arrival']['date'];

        if (!$date) {
            throw new InvalidArgumentException("Failed to get the booking start date", 400);
        }

        return $date;
    }


    public function setOrderId(int $id)
    {
        $this->orderId = $id;
    }
}
