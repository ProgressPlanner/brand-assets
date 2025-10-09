<?php
/**
 * Main Brand Assets Plugin Class
 *
 * @package BrandAssets
 * @since 0.1.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Brand Assets Plugin Class
 *
 * @since 0.1.0
 */
final class Brand_Assets {

	/**
	 * Plugin instance.
	 *
	 * @since 0.1.0
	 * @var Brand_Assets
	 */
	private static $instance = null;

	/**
	 * Settings instance.
	 *
	 * @since 0.1.0
	 * @var Brand_Assets_Settings
	 */
	public $settings;

	/**
	 * Frontend instance.
	 *
	 * @since 0.1.0
	 * @var Brand_Assets_Frontend
	 */
	public $frontend;

	/**
	 * Get plugin instance.
	 *
	 * @since 0.1.0
	 * @return Brand_Assets
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		// Register settings link hook immediately.
		add_filter( 'plugin_action_links_' . plugin_basename( BRAND_ASSETS_PLUGIN_FILE ), array( $this, 'add_settings_link' ) );
		register_activation_hook( BRAND_ASSETS_PLUGIN_FILE, array( $this, 'activate' ) );
	}

	/**
	 * Initialize the plugin.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function init() {
		// Initialize components.
		$this->settings = new Brand_Assets_Settings();
		$this->frontend = new Brand_Assets_Frontend();

		// Register the block.
		$this->register_block();
	}

	/**
	 * Load plugin textdomain.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'brand-assets',
			false,
			dirname( plugin_basename( BRAND_ASSETS_PLUGIN_FILE ) ) . '/languages'
		);
	}

	/**
	 * Register the Brand Assets block.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	private function register_block() {
		register_block_type( BRAND_ASSETS_PLUGIN_DIR . 'build' );
	}

	/**
	 * Plugin activation.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function activate() {
		// Set default options.
		$default_options = array(
			'brand_page_id'            => 0,
			'popover_heading'          => __( 'Looking for our logo?', 'brand-assets' ),
			'popover_subheading_line1' => __( "You're in the right spot!", 'brand-assets' ),
			'popover_subheading_line2' => __( 'Go to our', 'brand-assets' ),
			'popover_link_text'        => __( 'logo & style page', 'brand-assets' ),
			'logo_selector'            => '.wp-block-site-logo',
		);

		add_option( 'brand_assets_options', $default_options );
	}

	/**
	 * Add settings link to plugins page.
	 *
	 * @since 0.1.0
	 * @param array $links Existing plugin action links.
	 * @return array Modified plugin action links.
	 */
	public function add_settings_link( array $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'options-general.php?page=brand-assets-settings' ),
			__( 'Settings', 'brand-assets' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}
}
