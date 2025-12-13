<?php
/**
 * Plugin Name:       Brand Assets
 * Plugin URI:        https://progressplanner.com/plugins/brand-assets/
 * Description:       Easily display your company's brand assets including color schemes, logos, and brand guidelines.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           0.1.0
 * Author:            Team Progress Planner
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       brand-assets
 * Plugin ID:         did:plc:jgkahf6mwbkzw4k2cqity72y
 * Security: 		  security@progressplanner.com
 * GitHub Plugin URI: https://github.com/progressplanner/brand-assets
 *
 * @package BrandAssets
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'BRAND_ASSETS_VERSION', '0.1.0' );
define( 'BRAND_ASSETS_PLUGIN_FILE', __FILE__ );
define( 'BRAND_ASSETS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BRAND_ASSETS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Autoloader for Brand Assets plugin classes.
 *
 * @since 0.1.0
 * @param string $class_name The class name to load.
 * @return void
 */
function brand_assets_autoloader( $class_name ) {
		// Only handle Brand_Assets classes.
	if ( strpos( $class_name, 'Brand_Assets' ) !== 0 ) {
		return;
	}

		// Convert class name to file name.
		$file_name = 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';
		$file_path = BRAND_ASSETS_PLUGIN_DIR . 'includes/' . $file_name;

		// Load the file if it exists.
	if ( file_exists( $file_path ) ) {
		require_once $file_path;
	}
}

// Register the autoloader.
spl_autoload_register( 'brand_assets_autoloader' );


// Initialize the plugin.
Brand_Assets::get_instance();
