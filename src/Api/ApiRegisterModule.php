<?php

namespace Aero\Api;

use Aero\Api\ApiRegister;

class ApiRegisterModule
{
    public static function register()
    {
        container()->singleton(ApiRegister::class, ApiRegister::class);
    }
}
