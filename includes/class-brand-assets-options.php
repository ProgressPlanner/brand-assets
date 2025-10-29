<?php
/**
 * Brand Assets Options Service
 *
 * @since 0.1.0
 * @package BrandAssets
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Options service that owns defaults, sanitization and registration.
 *
 * @since 0.1.0
 */
final class Brand_Assets_Options {

	/**
	 * Settings group name.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	public const SETTINGS_GROUP = 'brand_assets_settings';

	/**
	 * Options name stored in wp_options.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	public const OPTIONS_NAME = 'brand_assets_options';

	/**
	 * Hook into WP for registering settings.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register settings for the plugin.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			self::SETTINGS_GROUP,
			self::OPTIONS_NAME,
			array(
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => $this->get_defaults(),
			)
		);
	}

	/**
	 * Get default options.
	 *
	 * @since 0.1.0
	 * @return array
	 */
	public function get_defaults() {
		return array(
			'brand_page_id'           => 0,
			'heading'                 => __( 'Looking for our logo?', 'brand-assets' ),
			'text_line1'              => __( "You're in the right spot!", 'brand-assets' ),
			'text_line2'              => __( 'Check out our', 'brand-assets' ),
			'link_text'               => __( 'Brand Assets page', 'brand-assets' ),
			'logo_selector'           => '.wp-block-site-logo',
			'css_loading_mode'        => 'default',
			// Popover styling options.
			'popover_bg_color'        => '#ffffff',
			'popover_text_color'      => '#000000',
			'popover_border_color'    => '#cccccc',
			'popover_border_width'    => 1,
			'popover_border_radius'   => 8,
			'popover_padding'         => 20,
			'popover_max_width'       => 400,
			'popover_font_size'       => 16,
			'popover_link_color'      => '#0073aa',
			'popover_close_btn_color' => '#666666',
		);
	}

	/**
	 * Sanitize and validate input values.
	 * Ensures empty strings fall back to defaults so frontend does not need inline fallbacks.
	 *
	 * @since 0.1.0
	 * @param array $input Raw input values.
	 * @return array Sanitized values.
	 */
	public function sanitize( $input ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.inputFound
		$defaults = $this->get_defaults();

		// Verify nonce if present; when called via register_setting this may not be necessary,
		// but keep parity with previous behavior.
		if ( isset( $_POST['_wpnonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), self::SETTINGS_GROUP . '-options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			add_settings_error( self::SETTINGS_GROUP, 'nonce_failed', __( 'Security check failed. Please try again.', 'brand-assets' ) );
			return $this->get_all();
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			add_settings_error( self::SETTINGS_GROUP, 'capability_failed', __( 'You do not have sufficient permissions to save these settings.', 'brand-assets' ) );
			return $this->get_all();
		}

		$sanitized = array();
		$input     = is_array( $input ) ? $input : array();

		$sanitized['brand_page_id'] = absint( $input['brand_page_id'] ?? $defaults['brand_page_id'] );

		$heading = sanitize_text_field( $input['heading'] ?? '' );
		// Accept new keys, fall back to legacy names if present.
		$text_line1_input = $input['text_line1'] ?? ( $input['subheading_line1'] ?? '' );
		$text_line2_input = $input['text_line2'] ?? ( $input['subheading_line2'] ?? '' );
		$text_line1       = sanitize_text_field( $text_line1_input );
		$text_line2       = sanitize_text_field( $text_line2_input );
		$link_text        = sanitize_text_field( $input['link_text'] ?? '' );
		$logo_selector    = sanitize_text_field( $input['logo_selector'] ?? '' );
		$css_loading_mode = sanitize_text_field( $input['css_loading_mode'] ?? '' );

		// Migrate 'custom' mode to 'default' for backward compatibility.
		if ( 'custom' === $css_loading_mode ) {
			$css_loading_mode = 'default';
		}

		// Sanitize popover styling options.
		$popover_bg_color        = sanitize_hex_color( $input['popover_bg_color'] ?? '' );
		$popover_text_color      = sanitize_hex_color( $input['popover_text_color'] ?? '' );
		$popover_border_color    = sanitize_hex_color( $input['popover_border_color'] ?? '' );
		$popover_border_width    = absint( $input['popover_border_width'] ?? 0 );
		$popover_border_radius   = absint( $input['popover_border_radius'] ?? 0 );
		$popover_padding         = absint( $input['popover_padding'] ?? 0 );
		$popover_max_width       = absint( $input['popover_max_width'] ?? 0 );
		$popover_font_size       = absint( $input['popover_font_size'] ?? 0 );
		$popover_link_color      = sanitize_hex_color( $input['popover_link_color'] ?? '' );
		$popover_close_btn_color = sanitize_hex_color( $input['popover_close_btn_color'] ?? '' );

		$sanitized['heading']          = $heading !== '' ? $heading : $defaults['heading'];
		$sanitized['text_line1']       = $text_line1 !== '' ? $text_line1 : $defaults['text_line1'];
		$sanitized['text_line2']       = $text_line2 !== '' ? $text_line2 : $defaults['text_line2'];
		$sanitized['link_text']        = $link_text !== '' ? $link_text : $defaults['link_text'];
		$sanitized['logo_selector']    = $logo_selector !== '' ? $logo_selector : $defaults['logo_selector'];
		$sanitized['css_loading_mode'] = in_array( $css_loading_mode, array( 'default', 'none' ), true ) ? $css_loading_mode : $defaults['css_loading_mode'];

		// Add popover styling to sanitized array with fallback to defaults.
		$sanitized['popover_bg_color']        = $popover_bg_color !== '' && $popover_bg_color !== null ? $popover_bg_color : $defaults['popover_bg_color'];
		$sanitized['popover_text_color']      = $popover_text_color !== '' && $popover_text_color !== null ? $popover_text_color : $defaults['popover_text_color'];
		$sanitized['popover_border_color']    = $popover_border_color !== '' && $popover_border_color !== null ? $popover_border_color : $defaults['popover_border_color'];
		$sanitized['popover_border_width']    = $popover_border_width > 0 ? $popover_border_width : $defaults['popover_border_width'];
		$sanitized['popover_border_radius']   = $popover_border_radius >= 0 ? $popover_border_radius : $defaults['popover_border_radius'];
		$sanitized['popover_padding']         = $popover_padding > 0 ? $popover_padding : $defaults['popover_padding'];
		$sanitized['popover_max_width']       = $popover_max_width > 0 ? $popover_max_width : $defaults['popover_max_width'];
		$sanitized['popover_font_size']       = $popover_font_size > 0 ? $popover_font_size : $defaults['popover_font_size'];
		$sanitized['popover_link_color']      = $popover_link_color !== '' && $popover_link_color !== null ? $popover_link_color : $defaults['popover_link_color'];
		$sanitized['popover_close_btn_color'] = $popover_close_btn_color !== '' && $popover_close_btn_color !== null ? $popover_close_btn_color : $defaults['popover_close_btn_color'];

		return $sanitized;
	}

	/**
	 * Get all options merged with defaults. Stored values take precedence.
	 *
	 * @since 0.1.0
	 * @return array
	 */
	public function get_all() {
		$stored = get_option( self::OPTIONS_NAME, array() );
		$stored = is_array( $stored ) ? $stored : array();

		return wp_parse_args( $stored, $this->get_defaults() );
	}

	/**
	 * Get a single option value with an optional fallback.
	 *
	 * @since 0.1.0
	 * @param string $key Option key.
	 * @param mixed  $fallback Fallback when not set.
	 * @return mixed
	 */
	public function get( $key, $fallback = null ) {
		$options = $this->get_all();
		return array_key_exists( $key, $options ) ? $options[ $key ] : $fallback;
	}

	/**
	 * Update multiple options at once.
	 *
	 * @since 0.1.0
	 * @param array $values Values to update.
	 * @return void
	 */
	public function update( array $values ) {
		$current = $this->get_all();
		update_option( self::OPTIONS_NAME, array_merge( $current, $values ) );
	}

	/**
	 * Ensure defaults are stored on activation.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function ensure_defaults_on_activation() {
		if ( get_option( self::OPTIONS_NAME, null ) === null ) {
			add_option( self::OPTIONS_NAME, $this->get_defaults() );
		}
	}
}
