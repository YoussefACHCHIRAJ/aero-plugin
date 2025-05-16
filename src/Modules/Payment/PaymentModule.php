<?php

namespace Aero\Modules\Payment;

use Aero\Interfaces\AeroModuleInterface;

class PaymentModule implements AeroModuleInterface
{

    public static function register()
    {
        container()->singleton(PaymentService::class, PaymentService::class);
        container()->singleton(PaymentController::class, PaymentController::class);
        container()->singleton(PaymentHelper::class, PaymentHelper::class);
    }
}
