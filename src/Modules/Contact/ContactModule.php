<?php

namespace Aero\Modules\Contact;

use Aero\Contracts\AeroModuleInterface;

class ContactModule implements AeroModuleInterface {

    public static function register() {
        container()->singleton(ContactController::class, ContactController::class);
        container()->singleton(ContactService::class, ContactService::class);
    }
}