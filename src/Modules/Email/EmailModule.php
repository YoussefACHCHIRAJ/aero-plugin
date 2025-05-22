<?php

namespace Aero\Modules\Email;

use Aero\Contracts\AeroModuleInterface;

class EmailModule implements AeroModuleInterface {

    public static function register() {
        container()->singleton(EmailBuilder::class, EmailBuilder::class);
        container()->singleton(EmailService::class, EmailService::class);
    }
}