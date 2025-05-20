<?php

namespace Aero\Modules\Product;

use Aero\Contracts\AeroModuleInterface;

class ProductModule implements AeroModuleInterface {

    public static function register() {
        container()->singleton(ProductController::class, ProductController::class);
        container()->singleton(ProductService::class, ProductService::class);
        container()->singleton(ProductHelper::class, ProductHelper::class);
        container()->singleton(ProductRepository::class, ProductRepository::class);
    }
}