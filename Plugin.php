<?php

use Aero\Api\ApiRegister;
use Aero\Api\ApiRegisterModule;
use Aero\Autoloader;
use Aero\Modules\Auth\AuthModule;
use Aero\Modules\Booking\BookingModule;
use Aero\Modules\City\CityModule;
use Aero\Modules\Contact\ContactModule;
use Aero\Modules\Dashboard\DashboardModule;
use Aero\Modules\Order\OrderModule;
use Aero\Modules\Payment\PaymentModule;
use Aero\Modules\Product\ProductModule;
use Aero\Modules\Rating\RatingModule;

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
        DashboardModule::class,
        RatingModule::class,
    ];

    public function boot()
    {
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
