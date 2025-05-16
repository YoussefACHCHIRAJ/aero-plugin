<?php

namespace Aero\Modules\Order;

use WP_Error;

class OrderHelper
{
    private function create_order_meta_section($prefix, $data, $includeTransfer = false)
    {
        $meta_data = [
            "{$prefix} Time" => $data['time'],
            "{$prefix} Date" => $data['date'],
            "{$prefix} Airline" => $data['airline'],
            "{$prefix} Flight Number" => $data['flight'],
        ];

        if (isset($data['porter']) && isset($data['porter']['label']) && isset($data['porter']['price'])) {
            $meta_data["{$prefix} VIP porter: ($ {$data['porter']['price']} )"] = $data['porter']['label'];
        }

        if ($includeTransfer && !empty($data['transfer']['label']) && isset($data['transfer']['price'])) {
            $meta_data["{$prefix} VIP Transfer: ($ {$data['transfer']['price']})"] = $data['transfer']['label'];

            if (!empty($data['address'])) {
                $meta_data["{$prefix} Pick up / Drop off"] = $data['address'];
            }
        }

        return $meta_data;
    }

    public function create_order_item_meta($item_line, $data)
    {
        $meta_data = array_merge(
            ["Service Type" => $data['fastTrackService']],
            $this->create_order_meta_section("", $data, true),
            [
                "city" => $data['city'],
                'Passenger Name' => $data['passengerName'],
                'Mobile Number' => $data['mobileNumber'],
            ]
        );

        foreach ($meta_data as $meta_key => $meta_value) {
            wc_add_order_item_meta($item_line, $meta_key, $meta_value);
        }
    }

    public function create_connection_order_item_meta($item_line, $data)
    {
        $meta_data = array_merge(
            [
                "Service Type" => $data['fastTrackService'],
                "city" => $data['city'],
                'Passenger Name' => $data['passengerName'],
                'Mobile Number' => $data['mobileNumber'],
            ],
            $this->create_order_meta_section('Arrival', $data['arrival'], true),
            $this->create_order_meta_section('Departure', $data['departure'], true),
        );

        foreach ($meta_data as $meta_key => $meta_value) {
            wc_add_order_item_meta($item_line, $meta_key, $meta_value);
        }
    }

    public function get_order_id_by_meta($meta_key, $meta_value)
    {
        $cache_key = 'order_by_meta_' . $meta_key . '_' . md5($meta_value);
        $order_id = wp_cache_get($cache_key, 'orders');

        if (!$order_id) {
            $args = [
                'post_type' => 'shop_order',
                'posts_per_page' => 1,
                'post_status' => 'any',
                'meta_key' => $meta_key,
                'meta_value' => $meta_value,
                'fields' => 'ids',
            ];

            $orders = get_posts($args);
            if (!empty($orders)) {
                $order_id = $orders[0];
                wp_cache_set($cache_key, $order_id, 'orders');
            }
        }

        return $order_id ? $order_id : false;
    }

    public function update_order_status_by_paypal_order_helper($paypal_order, $status)
    {
        try {
            $order_id = $this->get_order_id_by_meta('_paypal_order_id', $paypal_order);

            if (! $order_id) {
                return new WP_Error("failed", 'No order founded with the given id.', ['status' => 400]);
            }

            $order = wc_get_order($order_id);

            if ($order->get_status() === $status) {
                return ['orderId' => $order->id, 'status' => $order->status, 'order_key' => $order->get_order_key(), 'customer_email' => $order->get_billing_email()];
            }

            $messages = [
                'processing' => 'Payment received and order is now processing.',
                'completed' => 'Payment received and order is completed.',
                'cancelled' => 'Payment was cancelled.'
            ];

            $valid_payment_order = $order->get_meta('_valid_payment_order');

            if (! $valid_payment_order && $status === 'processing') {
                $order->update_status("failed", 'The order failed due to invalid payment order.');
                $order->save();
                return ['orderId' => $order->id, 'status' => $order->status, 'order_key' => $order->get_order_key(), 'customer_email' => $order->get_billing_email()];
            }

            $order->update_status($status, $messages[$status]);
            $order->save();

            return ['orderId' => $order->id, 'status' => $status, 'order_key' => $order->get_order_key(), 'customer_email' => $order->get_billing_email()];
        } catch (\Throwable $th) {
            return new WP_Error("failed", 'Something went wrong while capture the payment', ['status' => 500]);
        }
    }

    public function calculate_booking_order_total($persons, $product_price, $transfer_cost, $has_persons, $porterPrice = 0, $order = null)
    {
        $amount = 0;

        // Validate transfer cost
        if (!is_numeric($transfer_cost)) {
            $transfer_cost = 0;
            if ($order) {
                $order->add_order_note("Invalid transfer cost provided. Defaulting to 0.");
            }
        }

        // Handle case where persons are an array (record of objects)
        if ($has_persons && is_array($persons)) {
            foreach ($persons as $key => $person) {
                if (isset($person['count']) && isset($person['cost'])) {
                    $cost = is_numeric($person['cost']) ? $person['cost'] : $product_price;
                    $count = is_numeric($person['count']) ? $person['count'] : 1;

                    if ($count < 0) {
                        if ($order) {
                            $order->add_order_note("Person $key has an invalid count of $count. Skipping.");
                        }
                        continue;
                    }

                    $amount += $count * $cost;
                } else {
                    if ($order) {
                        $order->add_order_note("Person $key is missing required fields (count or cost). Skipping.");
                    }
                    continue;
                }
            }
        } elseif (!$has_persons && is_numeric($persons)) {
            if ($persons >= 1) {
                $amount = $persons * $product_price;
            } else {
                if ($order) {
                    $order->add_order_note("Invalid number of persons: $persons. defaulting to 1.");
                }
                $amount = $product_price;
            }
        } else {
            if ($order) {
                $order->add_order_note("Invalid persons data structure.");
            }
            return 0;
        }

        return $amount + $transfer_cost + $porterPrice;
    }

    public function get_orders_count_by_platform($platform_value = 'Web Site', $include_orders_without_meta = false)
    {
        global $wpdb;

        // Base query to count orders
        $query = "
        SELECT COUNT(DISTINCT p.ID)
        FROM {$wpdb->posts} p
    ";

        if (!$include_orders_without_meta) {
            // Include only orders with the meta key and value
            $query .= $wpdb->prepare(
                " INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
              WHERE pm.meta_key = '_order_platform'
              AND pm.meta_value = %s
              AND p.post_type = 'shop_order'",
                $platform_value
            );
        } else {
            // Include orders with the meta key and value OR without the meta key
            $query .= $wpdb->prepare(
                " LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_order_platform'
              WHERE (pm.meta_value = %s OR pm.meta_key IS NULL)
              AND p.post_type = 'shop_order'",
                $platform_value
            );
        }

        // Execute the query and get the count
        $order_count = $wpdb->get_var($query);

        return $order_count;
    }
}
