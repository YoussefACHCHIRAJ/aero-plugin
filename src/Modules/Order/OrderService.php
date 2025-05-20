<?php

namespace Aero\Modules\Order;

use Aero\Modules\Contact\ContactService;
use Aero\Modules\Order\Handlers\OrderMetaWriterFactory;
use Automattic\WooCommerce\Utilities\NumberUtil;
use WC_Order;
use WC_Order_Item_Coupon;
use WC_Order_Refund;
use WC_Product;
use WP_Error;
use WPMailSMTP\Vendor\Aws\Arn\Exception\InvalidArnException;

class OrderService
{
    protected $orderHelpers;
    protected $contactService;

    public function __construct(OrderHelper $orderHelpers, ContactService $contactService)
    {
        $this->orderHelpers = $orderHelpers;
        $this->contactService = $contactService;
    }

    /**
     * Create Woo Order for the booking
     * @param string passengerName
     * 
     * @return \WC_Order|\WP_Error $order
     */
    public function createOrder(string $passengerName)
    {
        $order = wc_create_order([
            'customer_id' => 0,
            'created_via' => 'admin',
        ]);

        $address = [
            'first_name' => $passengerName,
        ];

        $order->set_billing($address);

        $order->add_order_note('Order saved, waiting the billing info and the payment');

        return $order;
    }

    /**
     * Create the order Item for the booking
     */
    public function createOrderItem(WC_Order $order, WC_Product $product, array $data)
    {
        $total = $this->orderHelpers->calculateTotalPrice(
            $data,
            $product->has_person_types,
            $order
        );

        $orderItem = $order->add_product($product, 1, [
            'total' => $total,
        ]);

        if (!$orderItem) {
            wp_delete_post($order->get_id(), true);
            throw new InvalidArnException('Failed to add product with the id: ' . $product->get_id() . ' to the order.', '400');
        }

        $handler = OrderMetaWriterFactory::make($data['serviceType']);

        $handler->writeMetaItem($orderItem, $data);

        return $orderItem;
    }

    public function get_order_meta_by_id(array $data): mixed
    {

        $order = wc_get_order($data['orderId']);

        $meta = $order->get_meta();

        return $meta;
    }

    public function update_order_status($data)
    {

        $result = $this->orderHelpers->update_order_status_by_paypal_order_helper($data['paypal_order'], $data['status']);

        if (is_wp_error($result)) {
            return new WP_Error("server_error", "Something went  wrong.", ['status' => 500]);
        }
        $booking_status = $result['status'] ?? 'failed to get the status';

        if (defined('DEVELOPER_CONTACT')) {
            $this->contactService->notify_receiving_order(
                DEVELOPER_CONTACT,
                'New order Received',
                $booking_status,
                $result['customer_email'],
                $result['orderId'],
            );
        }
        return $result;
    }

    public function fetch_orders_insights()
    {
        $web_order_count = $this->orderHelpers->get_orders_count_by_platform('Web site', true);
        $app_order_count = $this->orderHelpers->get_orders_count_by_platform('Mobile App');

        return [
            'webOrdersCounts' => $web_order_count,
            'appOrdersCounts' => $app_order_count,
        ];
    }


    public function saveOrderBilling(array $data)
    {
        try {
            $orderId = $data['orderId'];

            $order = wc_get_order($orderId);

            if (!$order) {
                return new WP_Error('bad_request', 'the order is invalid', ['status' => 400]);
            }

            //* Billing and shipping
            $address = [
                'first_name' => $data['firstName'],
                'last_name' => $data['lastName'],
                'email' => $data['email'],
                'phone' => $data['mobileNumber'],
            ];

            $order->set_address($address, "billing");
            $order->set_address($address, "shipping");
            $order->add_order_note('Order Billing saved, waiting the payment');
            $order->save();

            return [
                'message' => 'The billing has been saved.',
                'success' => true,
            ];
        } catch (\Throwable $th) {
            return new WP_Error('server_error', 'Something went wrong in our side.', ['status' => 500]);
        }
    }


    public function get_order_by_id_and_email(string $orderId, string $email)
    {
        $order = wc_get_order($orderId);

        if (!$order) {
            return new WP_Error('no_order', 'Order not found', ['status' => 404]);
        }

        if (strtolower($order->get_billing_email()) !== strtolower($email)) {
            return new WP_Error('unauthorized', 'Invalid email.', ['status' => 403]);
        }

        return ['orderId' => $order->get_id(), 'order_key' => $order->get_order_key()];
    }

    public function prepare_item_for_response(WC_Order| WC_Order_Refund $order)
    {
        $dp    = wc_get_price_decimals();

        $data = array(
            'id'                   => $order->get_id(),
            'parent_id'            => $order->get_parent_id(),
            'status'               => $order->get_status(),
            'order_key'            => $order->get_order_key(),
            'number'               => $order->get_order_number(),
            'currency'             => $order->get_currency(),
            'version'              => $order->get_version(),
            'prices_include_tax'   => $order->get_prices_include_tax(),
            'date_created'         => wc_rest_prepare_date_response($order->get_date_created()),  // v1 API used UTC.
            'date_modified'        => wc_rest_prepare_date_response($order->get_date_modified()), // v1 API used UTC.
            'customer_id'          => $order->get_customer_id(),
            'discount_total'       => wc_format_decimal($order->get_total_discount(), $dp),
            'discount_tax'         => wc_format_decimal($order->get_discount_tax(), $dp),
            'shipping_total'       => wc_format_decimal($order->get_shipping_total(), $dp),
            'shipping_tax'         => wc_format_decimal($order->get_shipping_tax(), $dp),
            'cart_tax'             => wc_format_decimal($order->get_cart_tax(), $dp),
            'total'                => wc_format_decimal($order->get_total(), $dp),
            'total_tax'            => wc_format_decimal($order->get_total_tax(), $dp),
            'billing'              => array(),
            'shipping'             => array(),
            'payment_method'       => $order->get_payment_method(),
            'payment_method_title' => $order->get_payment_method_title(),
            'transaction_id'       => $order->get_transaction_id(),
            'customer_ip_address'  => $order->get_customer_ip_address(),
            'customer_user_agent'  => $order->get_customer_user_agent(),
            'created_via'          => $order->get_created_via(),
            'customer_note'        => $order->get_customer_note(),
            'date_completed'       => wc_rest_prepare_date_response($order->get_date_completed(), false), // v1 API used local time.
            'date_paid'            => wc_rest_prepare_date_response($order->get_date_paid(), false), // v1 API used local time.
            'cart_hash'            => $order->get_cart_hash(),
            'line_items'           => array(),
            'tax_lines'            => array(),
            'shipping_lines'       => array(),
            'fee_lines'            => array(),
            'coupon_lines'         => array(),
            'refunds'              => array(),
        );

        // Add addresses.
        $data['billing']  = $order->get_address('billing');
        $data['shipping'] = $order->get_address('shipping');

        // Add line items.
        foreach ($order->get_items() as $item_id => $item) {
            /** @var WC_Order_Item_Product $item */
            $product      = $item->get_product();
            $product_id   = 0;
            $variation_id = 0;
            $product_sku  = null;

            // Check if the product exists.
            if (is_object($product)) {
                $product_id   = $item->get_product_id();
                $variation_id = $item->get_variation_id();
                $product_sku  = $product->get_sku();
            }

            $item_meta = array();

            $hideprefix = '_';

            foreach ($item->get_all_formatted_meta_data($hideprefix) as $meta_key => $formatted_meta) {
                $item_meta[] = array(
                    'key'   => $formatted_meta->key,
                    'label' => $formatted_meta->display_key,
                    'value' => wc_clean($formatted_meta->display_value),
                );
            }

            $line_item = array(
                'id'           => $item_id,
                'name'         => $item['name'],
                'sku'          => $product_sku,
                'product_id'   => (int) $product_id,
                'variation_id' => (int) $variation_id,
                'quantity'     => wc_stock_amount($item['qty']),
                'tax_class'    => ! empty($item['tax_class']) ? $item['tax_class'] : '',
                'price'        => wc_format_decimal($order->get_item_total($item, false, false), $dp),
                'subtotal'     => wc_format_decimal($order->get_line_subtotal($item, false, false), $dp),
                'subtotal_tax' => wc_format_decimal($item['line_subtotal_tax'], $dp),
                'total'        => wc_format_decimal($order->get_line_total($item, false, false), $dp),
                'total_tax'    => wc_format_decimal($item['line_tax'], $dp),
                'taxes'        => array(),
                'meta'         => $item_meta,
            );

            $item_line_taxes = maybe_unserialize($item['line_tax_data']);
            if (isset($item_line_taxes['total'])) {
                $line_tax = array();

                foreach ($item_line_taxes['total'] as $tax_rate_id => $tax) {
                    $line_tax[$tax_rate_id] = array(
                        'id'       => $tax_rate_id,
                        'total'    => $tax,
                        'subtotal' => '',
                    );
                }

                foreach ($item_line_taxes['subtotal'] as $tax_rate_id => $tax) {
                    $line_tax[$tax_rate_id]['subtotal'] = $tax;
                }

                $line_item['taxes'] = array_values($line_tax);
            }

            $data['line_items'][] = $line_item;
        }

        // Add taxes.
        foreach ($order->get_items('tax') as $key => $tax) {
            $tax_line = array(
                'id'                 => $key,
                'rate_code'          => $tax['name'],
                'rate_id'            => $tax['rate_id'],
                'label'              => isset($tax['label']) ? $tax['label'] : $tax['name'],
                'compound'           => (bool) $tax['compound'],
                'tax_total'          => wc_format_decimal($tax['tax_amount'], $dp),
                'shipping_tax_total' => wc_format_decimal($tax['shipping_tax_amount'], $dp),
            );

            $data['tax_lines'][] = $tax_line;
        }

        // Add shipping.
        foreach ($order->get_shipping_methods() as $shipping_item_id => $shipping_item) {
            $shipping_line = array(
                'id'           => $shipping_item_id,
                'method_title' => $shipping_item['name'],
                'method_id'    => $shipping_item['method_id'],
                'total'        => wc_format_decimal($shipping_item['cost'], $dp),
                'total_tax'    => wc_format_decimal('', $dp),
                'taxes'        => array(),
            );

            $shipping_taxes = $shipping_item->get_taxes();

            if (! empty($shipping_taxes['total'])) {
                $total_tax = NumberUtil::array_sum($shipping_taxes['total']);

                $shipping_line['total_tax'] = wc_format_decimal($total_tax, $dp);

                foreach ($shipping_taxes['total'] as $tax_rate_id => $tax) {
                    $shipping_line['taxes'][] = array(
                        'id'       => $tax_rate_id,
                        'total'    => $tax,
                    );
                }
            }

            $data['shipping_lines'][] = $shipping_line;
        }

        // Add fees.
        foreach ($order->get_fees() as $fee_item_id => $fee_item) {
            $fee_line = array(
                'id'         => $fee_item_id,
                'name'       => $fee_item['name'],
                'tax_class'  => ! empty($fee_item['tax_class']) ? $fee_item['tax_class'] : '',
                'tax_status' => 'taxable',
                'total'      => wc_format_decimal($order->get_line_total($fee_item), $dp),
                'total_tax'  => wc_format_decimal($order->get_line_tax($fee_item), $dp),
                'taxes'      => array(),
            );

            $fee_line_taxes = maybe_unserialize($fee_item['line_tax_data']);
            if (isset($fee_line_taxes['total'])) {
                $fee_tax = array();

                foreach ($fee_line_taxes['total'] as $tax_rate_id => $tax) {
                    $fee_tax[$tax_rate_id] = array(
                        'id'       => $tax_rate_id,
                        'total'    => $tax,
                        'subtotal' => '',
                    );
                }

                if (isset($fee_line_taxes['subtotal'])) {
                    foreach ($fee_line_taxes['subtotal'] as $tax_rate_id => $tax) {
                        $fee_tax[$tax_rate_id]['subtotal'] = $tax;
                    }
                }

                $fee_line['taxes'] = array_values($fee_tax);
            }

            $data['fee_lines'][] = $fee_line;
        }

        // Add coupons.
        /** @var WC_Order_Item_Coupon $coupon_item */
        foreach ($order->get_items('coupon') as $coupon_item_id => $coupon_item) {
            $coupon_line = array(
                'id'           => $coupon_item_id,
                'code'         => $coupon_item->get_name(),
                'discount'     => wc_format_decimal($coupon_item->get_discount(), $dp),
                'discount_tax' => wc_format_decimal($coupon_item->get_discount_tax(), $dp),
            );

            $data['coupon_lines'][] = $coupon_line;
        }

        // Add refunds.
        foreach ($order->get_refunds() as $refund) {
            $data['refunds'][] = array(
                'id'     => $refund->get_id(),
                'refund' => $refund->get_reason() ? $refund->get_reason() : '',
                'total'  => '-' . wc_format_decimal($refund->get_amount(), $dp),
            );
        }

        return $data;
    }
}
