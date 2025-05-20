<?php

namespace Aero\Modules\Booking;

use InvalidArgumentException;
use WC_Booking;
use WC_Product;

class BookingHelper
{
    function createPersons(WC_Product $product, array|int $persons)
    {
        if ($product->has_person_types) {
            foreach ($persons as $id => $person) {
                $persons[$id] = $person['count'];
            }

            return $persons;
        }

        if (is_numeric($persons)) {
            return $persons;
        }

        throw new \InvalidArgumentException("Persons are missing or in incorrect format.", 400);
    }

    function createBooking(string $start_date, array|int $persons, int $orderItem, WC_Product $product): WC_Booking
    {
        $start_date_timestamp = strtotime(date('Y-m-d', strtotime($start_date)));



        $booking_data = [
            'start_date' => $start_date_timestamp,
            'all_day' => 1,
            'customer_id' => 0,
            'person_counts' => $persons,
            'order_item_id' => $orderItem,
        ];
        $bookingItem = create_wc_booking($product->get_id(), $booking_data, 'unpaid', true);

        if (!$bookingItem) {
            throw new InvalidArgumentException('Product with id ' . $product->get_id() . ' does not saved. Order has been cancelled',  '500');
        }

        return $bookingItem;
    }
}
