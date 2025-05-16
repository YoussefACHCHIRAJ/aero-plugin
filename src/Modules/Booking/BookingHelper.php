<?php

namespace Aero\Modules\Booking;

class BookingHelper
{
    function create_persons($product, $persons_data)
    {
        if ($product->has_person_types) {
            foreach ($persons_data as $id => $person) {
                $persons[$id] = $person['count'];
            }
        } else {
            if (isset($persons_data)) {
                $persons = $persons_data;
            }
        }
        return $persons;
    }

    function create_booking($start_date, $persons, $item_line, $product)
    {
        $start_date_timestamp = strtotime(date('Y-m-d', strtotime($start_date)));
        


        $booking_data = [
            'start_date' => $start_date_timestamp,
            'all_day' => 1,
            'customer_id' => 0,
            'person_counts' => $persons,
            'order_item_id' => $item_line,
        ];
        return create_wc_booking($product->get_id(), $booking_data, 'unpaid', true);
    }
}
