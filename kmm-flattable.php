<?php
/**
 * Plugin Name: KMM Flattable
 * Description: This is a short description of what the plugin does.
 * Plugin URI:  http://github.com/KroneMultiMedia/plugin-flattable
 * Version:     v9.9.9
 * Author:      Krone.at
 * Author URI:  http://www.krone.at
 * Licence:     MIT
 * Text Domain: kmm-flattable
 * Domain Path: /languages
 */

namespace KMM\Flattable;

use KMM\Flattable\Core;

if ( ! function_exists( 'add_filter' ) ) {
	return;
}


/**
 * Ensure that autoload is already loaded or loads it if available.
 *
 * @param string $class_name
 *
 * @return bool
 */
function ensure_class_loaded( $class_name ) {

	$class_exists   = class_exists( $class_name );
	$autoload_found = file_exists( __DIR__ . '/vendor/autoload.php' );

	// If the class does not exist, and the vendor file is there
	// maybe the plugin was installed separately via Composer, let's try to load autoload
	if ( ! $class_exists && $autoload_found ) {
		@require_once __DIR__ . '/vendor/autoload.php';
	}

	return $class_exists || ( $autoload_found && class_exists( $class_name ) );
}

// Exit if classes are not available
if ( ! ensure_class_loaded( __NAMESPACE__ . '\Core' ) ) {
	return;
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\main' );


/**
 * @wp-hook plugins_loaded
 */
function main() {
	new Core();

	if ( is_admin() ) {
		// Backend

		// TODO: register your backend-code here...

	} else {
		// Frontend

		// TODO: register frontend-code here...
	}

}
