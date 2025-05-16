<?php

/**
 * fast track Aero Custom endpoints.
 *
 */

namespace Aero\Api;

use Aero\Modules\Auth\AuthController;
use Aero\Modules\Booking\BookingController;
use Aero\Modules\City\CityController;
use Aero\Modules\Contact\ContactController;
use Aero\Modules\Dashboard\DashboardController;
use Aero\Modules\Order\OrderController;
use Aero\Modules\Payment\PaymentController;
use Aero\Modules\Product\ProductController;
use Aero\Modules\Rating\RatingController;

class ApiRegister
{

    private $controllers = [
        BookingController::class,
        CityController::class,
        OrderController::class,
        PaymentController::class,
        ProductController::class,
        AuthController::class,
        ContactController::class,
        DashboardController::class,
        RatingController::class
    ];


    /**
     * Hook into wordpress ready to init the REST API as needed
     * 
     * Also disable woocommerce rest check permission in local environment
     */
    public function init()
    {
        add_action('rest_api_init', [$this, 'register_endpoints'], 10);

        if (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'local') {
            add_filter('woocommerce_rest_check_permissions', '__return_true');
        }
    }

    public function register_endpoints()
    {


        foreach ($this->controllers as $controller) {
            container($controller)->register_routes();
        }
    }
}
