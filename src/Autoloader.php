<?php


namespace Aero;

/**
 * Autoloader class.
 */

class Autoloader
{
    private function __construct() {}

    /**
     * Require the autoloader and return the result.
     * 
     * If the autoloader in not represent, let's log the failure and display a nice admin notice.
     * 
     * @return void
     */
    public static function init()
    {
        spl_autoload_register(function ($class) {
            
            $prefix = 'Aero\\';

            $base_dir = __DIR__ . '/';

            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                
                return;
            }

            $relative_class = substr($class, $len);

            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

            if (file_exists($file)) {
                require $file;
            }
        });
    }

}
