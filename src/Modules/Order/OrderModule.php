<?php

namespace Aero\Modules\Order;

use Aero\Contracts\AeroModuleInterface;

class OrderModule implements AeroModuleInterface {

    public static function register() {
        container()->singleton(OrderService::class, OrderService::class);
        container()->singleton(OrderController::class, OrderController::class);
        container()->singleton(OrderRepository::class, OrderRepository::class);
        container()->singleton(OrderHelper::class, OrderHelper::class);
    }
}