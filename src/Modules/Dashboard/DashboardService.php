<?php

namespace Aero\Modules\Dashboard;

use Aero\Helpers\RequestHelper;
use WP_Query;

class DashboardService
{
    protected $dashboardHelper;

    public function fetchDashboardOrders($page, $limit, $platform = null)
    {
        $cms_list = [
            'casa' => [
                'url'    => 'https://api.fasttrack-casablanca.com/wp-json/wc/v3/orders',
                'key'    => 'ck_653ae135d2d507e0a981106e95959815447d91f1',
                'secret' => 'cs_3acc75930db3027b5bc31e65fc3ef098ae02427f',
            ],
            'marrakech' => [
                'url'    => 'https://fasttrack-marrakech.com/wp-json/wc/v3/orders',
                'key'    => 'ck_642188ed9d2563863d6ce522c856f43f33c0f926',
                'secret' => 'cs_943882e8ffdf627dc6b8eb95ff596f512e83fdea',
            ],
            'marrakech_fr' => [
                'url'    => 'https://fasttrack-marrakech.com/fr/wp-json/wc/v3/orders',
                'key'    => 'ck_ef99e2485e572a26d107e3d1db122b5dce53d20e',
                'secret' => 'cs_28a9d4a79592847dc9f61d65703fb1dd8e30c3ac',
            ],
        ];

        if ($platform) {
            $cms_list = [$platform => $cms_list[$platform]];
        }

        $all_orders = [];

        foreach ($cms_list as $key => $cms) {
            $params = [
                'page' => $page,
                'limit' => $limit,
            ];
            $url = $cms['url'] . '?consumer_key=' . $cms['key'] . '&consumer_secret=' . $cms['secret'];
            $response = RequestHelper::get($url, $params);

            if ($response && is_array($response)) {
                $all_orders[$key] = $response;
            }
        }

        if (!$platform || $platform === 'aero') {
            $local_orders = $this->get_local_orders($page, $limit);
            $all_orders['aero'] = $local_orders;
        }

        return [
            'success' => true,
            'page' => (int) $page,
            'limit' => (int) $limit,
            'data' => $all_orders
        ];
    }

    private function get_local_orders($page, $limit)
    {
        $args = [
            'post_type'      => 'shop_order',
            'post_status'    => ['wc-completed'], // adjust as needed
            'posts_per_page' => $limit,
            'paged'          => $page,
        ];

        $query = new WP_Query($args);
        $orders = [];

        foreach ($query->posts as $post) {
            $order = wc_get_order($post->ID);
            if ($order) {
                $orders[] = [
                    'id'         => $order->get_id(),
                    'date'       => $order->get_date_created()->date('Y-m-d H:i:s'),
                    'total'      => $order->get_total(),
                    'status'     => $order->get_status(),
                    'customer'   => $order->get_billing_email(),
                    'billing' => [
                        'first_name' => $order->get_billing_first_name()
                    ]
                ];
            }
        }

        return $orders;
    }
}
