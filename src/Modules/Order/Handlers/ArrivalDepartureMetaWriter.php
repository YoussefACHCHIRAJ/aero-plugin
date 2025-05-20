<?php

namespace Aero\Modules\Order\Handlers;

use Aero\Modules\Order\OrderHelper;

class ArrivalDepartureMetaWriter implements OrderMetaWriterConcert {

    

    public function writeMetaItem(int $orderItem, array $data) {
        $meta_data = array_merge(
            ["Service Type" => $data['fastTrackService']],
            OrderHelper::create_order_meta_section("", $data, true),
            [
                "city" => $data['city'],
                'Passenger Name' => $data['passengerName'],
                'Mobile Number' => $data['mobileNumber'],
            ]
        );

        foreach ($meta_data as $meta_key => $meta_value) {
            wc_add_order_item_meta($orderItem, $meta_key, $meta_value);
        }
    }

    

}