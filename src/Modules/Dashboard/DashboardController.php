<?php

namespace Aero\Modules\Dashboard;

use Aero\Config\ApiConfig;
use WP_REST_Request;

class DashboardController {
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService =  $dashboardService;
    }

    public function register_routes() {
        register_rest_route(ApiConfig::AERO_NAMESPACE, 'dashboard/orders', array(
            'methods' => 'GET',
            'callback' => [$this, 'fetchDashboardOrders'],
            'permission_callback' => function () {
                return current_user_can('administrator');
            },
        ));
    }

    public function fetchDashboardOrders(WP_REST_Request $request) {
        $page = $request->get_param('page') ?? 1;
        $limit = $request->get_param('limit') ?? 20;
        $platform = $request->get_param('platform');

        $result = $this->dashboardService->fetchDashboardOrders($page, $limit, $platform);

        return $result;
    }
}