<?php
/**
 * PHPUnit bootstrap file.
 */

namespace UNT;

// Fake ENV
define('KRN_HOST_API', 'test-api.krone.at');
define('WP_HOME', 'test-www.krone.at');
define('KRN_HOST_MOBIL', 'test-mobil.krone.at');
define('KRN_IS_TESTING', 1);

class bootstrap {
    public function __construct() {
        $_tests_dir = getenv('WP_TESTS_DIR');
        if (! $_tests_dir) {
            $_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
        }
        if (! file_exists($_tests_dir . '/includes/functions.php')) {
            echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?";
            exit(1);
        }
        // Give access to tests_add_filter() function.
        require_once $_tests_dir . '/includes/functions.php';

        tests_add_filter('muplugins_loaded', [$this, '_manually_load_plugin']);

        require $_tests_dir . '/includes/bootstrap.php';
    }

    public function _manually_load_plugin() {
        require dirname(dirname(__FILE__)) . '../../kmm-flattable.php';
    }
}

// Start up the WP testing environment.

$unt = new bootstrap();
