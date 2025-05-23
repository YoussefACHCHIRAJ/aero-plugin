<?php

use Aero\Api\ApiRegister;
use Aero\Api\ApiRegisterModule;
use Aero\Autoloader;
use Aero\Modules\Auth\AuthModule;
use Aero\Modules\Booking\BookingModule;
use Aero\Modules\City\CityModule;
use Aero\Modules\Contact\ContactModule;
use Aero\Modules\Email\EmailModule;
use Aero\Modules\Order\OrderModule;
use Aero\Modules\Payment\PaymentModule;
use Aero\Modules\Product\ProductModule;
use Aero\Modules\Rating\RatingModule;
use Aero\Modules\Scheduling\ScheduleModule;

class Plugin
{

    private $modules = [
        BookingModule::class,
        ApiRegisterModule::class,
        CityModule::class,
        OrderModule::class,
        PaymentModule::class,
        ProductModule::class,
        AuthModule::class,
        ContactModule::class,
        RatingModule::class,
        EmailModule::class,
        ScheduleModule::class,
    ];

    public function boot()
    {
        if (!wp_next_scheduled('aero_run_my_job')) {
            wp_schedule_event(strtotime("09:00:00"), 'daily', 'aero_run_my_job');
        } else {
            $next = wp_next_scheduled("aero_run_my_job");

            if($next < time()) {
                do_action('aero_run_my_job');
            }
        }

        add_filter('woocommerce_bookings_get_end_date_with_time', function ($end, $booking) {
            return $booking->get_start(); // Same as start
        }, 10, 2);

        Autoloader::init();

        $this->registerModules();

        container(ApiRegister::class)->init();
    }


    protected function registerModules()
    {
        foreach ($this->modules as $module) {
            $module::register();
        }
    }
}
