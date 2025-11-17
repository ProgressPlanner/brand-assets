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
	 * Options instance.
	 *
	 * @since 0.1.0
	 * @var Brand_Assets_Options
	 */
	public $options;

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
		$this->options  = new Brand_Assets_Options();
		$this->settings = new Brand_Assets_Settings();
		$this->frontend = new Brand_Assets_Frontend();

		$this->options->init_hooks();
		$this->settings->init_hooks();
		$this->frontend->init_hooks();

		// Register the block.
		$this->register_block();

		// Register block patterns.
		$this->register_block_patterns();
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
	 * Register block patterns.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	private function register_block_patterns() {
		/**
		 * Filter the path to the brand page pattern file.
		 *
		 * Allows developers to customize the location of the brand page pattern file
		 * used for the block pattern registration. The pattern file should contain
		 * valid block markup for a complete brand page layout.
		 *
		 * @since 0.1.0
		 * @param string $pattern_file_path The path to the pattern file. Default: BRAND_ASSETS_PLUGIN_DIR . 'includes/brand-page-pattern.inc'
		 */
		$brand_page_pattern_file = apply_filters( 'brand_assets_pattern_file_path', BRAND_ASSETS_PLUGIN_DIR . 'includes/brand-page-pattern.inc' );

		if ( ! file_exists( $brand_page_pattern_file ) ) {
			return;
		}

		// Load the brand page pattern.
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- This is a valid use of file_get_contents.
		$pattern_content = file_get_contents( $brand_page_pattern_file );
		if ( ! $pattern_content ) {
			return;
		}

		register_block_pattern(
			'brand-assets/brand-page',
			array(
				'title'       => __( 'Brand Assets Page', 'brand-assets' ),
				'description' => __( 'A complete Brand Assets page with logo, colors, and typography examples.', 'brand-assets' ),
				'content'     => $pattern_content,
				'categories'  => array( 'featured' ),
			)
		);
	}

	/**
	 * Plugin activation.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function activate() {
		// Ensure default options exist.
		( new Brand_Assets_Options() )->ensure_defaults_on_activation();
	}

	/**
	 * Add settings link to plugins page.
	 *
	 * @since 0.1.0
	 * @param array<string> $links Existing plugin action links.
	 * @return array<string> Modified plugin action links.
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
