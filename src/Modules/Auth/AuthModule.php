<?php

namespace Aero\Modules\Auth;

use Aero\Interfaces\AeroModuleInterface;

class AuthModule implements AeroModuleInterface {

    public static function register() {
        container()->singleton(AuthController::class, AuthController::class);
        container()->singleton(AuthService::class, AuthService::class);
    }
}