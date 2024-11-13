<?php
/**
 * Plugin Name:       Color Scheme
 * Plugin URI:        https://joost.blog/plugins/color-scheme/
 * Description:       Easily show your company&#39;s color scheme.
 * Requires at least: 6.6
 * Requires PHP:      7.2
 * Version:           0.1.0
 * Author:            Joost de Valk
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       color-scheme
 *
 * @package Joost
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function joost_color_scheme_block_init() {
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'joost_color_scheme_block_init' );
