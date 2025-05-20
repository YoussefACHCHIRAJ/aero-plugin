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
        $autoloader = dirname(__DIR__) . '/vendor/autoload.php';

        if (!is_readable($autoloader)) {
            error_log("Not found File: $autoloader");
            self::missingAutoloader();
            return false;
        }

        $autoloaderResult = require $autoloader;

        if (!$autoloaderResult) {
            return false;
        }

        return $autoloaderResult;
    }

    /**
     * If the autoloader is missing, add an admin notice.
     */
    protected static function missingAutoloader()
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Your installation of Aero Plugin is incomplete.');
        }
        add_action(
            'admin_notices',
            function () {
?>
            <div class="notice notice-error">
                <p>
                    Your installation of Aero Plugin is incomplete. failed to load the autoloader. If you installed this plugin from github, please set your development environment and run `composer dump-autoload` to generate the vendor folder.
                </p>
            </div>
<?php
            }
        );
    }
}
