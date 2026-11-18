<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package BrandAssets
 */

// Load the helper functions that locate and boot the WordPress test suite.
require_once dirname( __DIR__ ) . '/vendor/yoast/wp-test-utils/src/WPIntegration/bootstrap-functions.php';

$brand_assets_tests_dir = rtrim( Yoast\WPTestUtils\WPIntegration\get_path_to_wp_test_dir(), '/' );

if ( ! file_exists( $brand_assets_tests_dir . '/includes/functions.php' ) ) {
	echo 'Could not find the WordPress test suite, have you run tests/bin/install-wp-tests.sh?' . PHP_EOL;
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $brand_assets_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 *
 * @return void
 */
function brand_assets_tests_load_plugin() {
	require dirname( __DIR__ ) . '/brand-assets.php';
}

// Add plugin to active mu-plugins - to make sure it gets loaded.
tests_add_filter( 'muplugins_loaded', 'brand_assets_tests_load_plugin' );

/*
 * Load WordPress, which will load the Composer autoload file, and load the MockObject autoloader after that.
 */
Yoast\WPTestUtils\WPIntegration\bootstrap_it();
