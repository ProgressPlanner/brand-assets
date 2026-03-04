<?php

/**
 * Uninstall Brand Assets.
 *
 * Fired when the plugin is uninstalled.
 *
 * @package Brand_Assets
 * @since   1.0.1
 */

// If uninstall not called from WordPress, then exit.
if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('brand_assets_options');
