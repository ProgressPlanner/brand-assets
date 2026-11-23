<?php
/**
 * Tests for the options service.
 *
 * @package BrandAssets
 */

/**
 * Tests Brand_Assets_Options.
 *
 * @covers Brand_Assets_Options
 */
final class Brand_Assets_Options_Test extends Yoast\WPTestUtils\WPIntegration\TestCase {

	/**
	 * The options service under test.
	 *
	 * @var Brand_Assets_Options
	 */
	private $options;

	/**
	 * Sets up each test.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		// The Settings API error functions only exist in the admin.
		require_once ABSPATH . 'wp-admin/includes/template.php';

		$this->options = new Brand_Assets_Options();
	}

	/**
	 * Cleans up after each test.
	 *
	 * @return void
	 */
	public function tear_down() {
		unset( $_POST['_wpnonce'] );
		$GLOBALS['wp_settings_errors'] = array(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Resetting between tests.

		parent::tear_down();
	}

	/**
	 * Makes the current user an administrator.
	 *
	 * @return void
	 */
	private function become_admin() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
	}

	/**
	 * Returns the codes of the settings errors that were registered.
	 *
	 * @return array<int, string>
	 */
	private function get_settings_error_codes() {
		return wp_list_pluck( get_settings_errors( Brand_Assets_Options::SETTINGS_GROUP ), 'code' );
	}

	/**
	 * Without a stored option, the defaults are returned.
	 *
	 * @return void
	 */
	public function test_get_all_returns_defaults_when_nothing_is_stored() {
		$this->assertSame( $this->options->get_defaults(), $this->options->get_all() );
	}

	/**
	 * Stored values win over the defaults, missing keys are filled in.
	 *
	 * @return void
	 */
	public function test_get_all_merges_stored_values_over_defaults() {
		update_option( Brand_Assets_Options::OPTIONS_NAME, array( 'heading' => 'Our logo' ) );

		$all = $this->options->get_all();

		$this->assertSame( 'Our logo', $all['heading'] );
		$this->assertSame( '.wp-block-site-logo', $all['logo_selector'] );
		$this->assertSame( 'default', $all['css_loading_mode'] );
	}

	/**
	 * A corrupted (non-array) option falls back to the defaults.
	 *
	 * @return void
	 */
	public function test_get_all_ignores_a_non_array_option() {
		update_option( Brand_Assets_Options::OPTIONS_NAME, 'not-an-array' );

		$this->assertSame( $this->options->get_defaults(), $this->options->get_all() );
	}

	/**
	 * A single value can be read, with a fallback for unknown keys.
	 *
	 * @return void
	 */
	public function test_get_returns_value_or_fallback() {
		$this->assertSame( 400, $this->options->get( 'popover_max_width' ) );
		$this->assertNull( $this->options->get( 'does_not_exist' ) );
		$this->assertSame( 'fallback', $this->options->get( 'does_not_exist', 'fallback' ) );
	}

	/**
	 * Updating merges into the existing values rather than replacing them.
	 *
	 * @return void
	 */
	public function test_update_merges_with_existing_values() {
		$this->options->update( array( 'heading' => 'First' ) );
		$this->options->update( array( 'brand_page_id' => 12 ) );

		$this->assertSame( 'First', $this->options->get( 'heading' ) );
		$this->assertSame( 12, $this->options->get( 'brand_page_id' ) );
	}

	/**
	 * Activation stores the defaults when the option does not exist yet.
	 *
	 * @return void
	 */
	public function test_activation_stores_defaults() {
		$this->assertFalse( get_option( Brand_Assets_Options::OPTIONS_NAME ) );

		$this->options->ensure_defaults_on_activation();

		$this->assertSame( $this->options->get_defaults(), get_option( Brand_Assets_Options::OPTIONS_NAME ) );
	}

	/**
	 * Activation leaves existing settings alone.
	 *
	 * @return void
	 */
	public function test_activation_does_not_overwrite_existing_options() {
		update_option( Brand_Assets_Options::OPTIONS_NAME, array( 'heading' => 'Keep me' ) );

		$this->options->ensure_defaults_on_activation();

		$this->assertSame( array( 'heading' => 'Keep me' ), get_option( Brand_Assets_Options::OPTIONS_NAME ) );
	}

	/**
	 * The setting is registered with the sanitize callback.
	 *
	 * @return void
	 */
	public function test_register_settings_registers_the_option() {
		$this->options->register_settings();

		$registered = get_registered_settings();

		$this->assertArrayHasKey( Brand_Assets_Options::OPTIONS_NAME, $registered );
		$this->assertSame( Brand_Assets_Options::SETTINGS_GROUP, $registered[ Brand_Assets_Options::OPTIONS_NAME ]['group'] );
		$this->assertSame( array( $this->options, 'sanitize' ), $registered[ Brand_Assets_Options::OPTIONS_NAME ]['sanitize_callback'] );

		unregister_setting( Brand_Assets_Options::SETTINGS_GROUP, Brand_Assets_Options::OPTIONS_NAME );
	}

	/**
	 * Users without the manage_options capability cannot change the settings.
	 *
	 * @return void
	 */
	public function test_sanitize_rejects_users_without_capability() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$result = $this->options->sanitize( array( 'heading' => 'Hacked' ) );

		$this->assertSame( $this->options->get_all(), $result );
		$this->assertContains( 'capability_failed', $this->get_settings_error_codes() );
	}

	/**
	 * An invalid nonce in the request keeps the stored values.
	 *
	 * @return void
	 */
	public function test_sanitize_rejects_an_invalid_nonce() {
		$this->become_admin();
		$_POST['_wpnonce'] = 'invalid';

		$result = $this->options->sanitize( array( 'heading' => 'Changed' ) );

		$this->assertSame( $this->options->get_all(), $result );
		$this->assertContains( 'nonce_failed', $this->get_settings_error_codes() );
	}

	/**
	 * A valid nonce lets the values through.
	 *
	 * @return void
	 */
	public function test_sanitize_accepts_a_valid_nonce() {
		$this->become_admin();
		$_POST['_wpnonce'] = wp_create_nonce( Brand_Assets_Options::SETTINGS_GROUP . '-options' );

		$result = $this->options->sanitize( array( 'heading' => 'Changed' ) );

		$this->assertSame( 'Changed', $result['heading'] );
		$this->assertSame( array(), $this->get_settings_error_codes() );
	}

	/**
	 * Valid input is kept, with the expected types.
	 *
	 * @return void
	 */
	public function test_sanitize_keeps_valid_values() {
		$this->become_admin();

		$input = array(
			'brand_page_id'           => '42',
			'heading'                 => 'Need our logo?',
			'text_line1'              => 'Line one',
			'text_line2'              => 'Line two',
			'link_text'               => 'Brand page',
			'logo_selector'           => '.custom-logo',
			'css_loading_mode'        => 'none',
			'popover_bg_color'        => '#112233',
			'popover_text_color'      => '#abc',
			'popover_border_color'    => '#445566',
			'popover_border_width'    => '0',
			'popover_border_radius'   => '0',
			'popover_padding'         => '30',
			'popover_max_width'       => '500',
			'popover_font_size'       => '18',
			'popover_link_color'      => '#778899',
			'popover_close_btn_color' => '#000000',
		);

		$expected = array(
			'brand_page_id'           => 42,
			'heading'                 => 'Need our logo?',
			'text_line1'              => 'Line one',
			'text_line2'              => 'Line two',
			'link_text'               => 'Brand page',
			'logo_selector'           => '.custom-logo',
			'css_loading_mode'        => 'none',
			'popover_bg_color'        => '#112233',
			'popover_text_color'      => '#abc',
			'popover_border_color'    => '#445566',
			'popover_border_width'    => 0,
			'popover_border_radius'   => 0,
			'popover_padding'         => 30,
			'popover_max_width'       => 500,
			'popover_font_size'       => 18,
			'popover_link_color'      => '#778899',
			'popover_close_btn_color' => '#000000',
		);

		$this->assertEqualSetsWithIndex( $expected, $this->options->sanitize( $input ) );
	}

	/**
	 * Empty, missing and invalid values fall back to the defaults.
	 *
	 * @return void
	 */
	public function test_sanitize_falls_back_to_defaults() {
		$this->become_admin();

		$input = array(
			'heading'            => '',
			'logo_selector'      => '   ',
			'css_loading_mode'   => 'bogus',
			'popover_bg_color'   => 'red',
			'popover_link_color' => 'javascript:alert(1)',
			'popover_padding'    => '0',
			'popover_max_width'  => '',
			'popover_font_size'  => 'abc',
		);

		$expected = $this->options->get_defaults();
		// A missing border radius is saved as 0 (no rounding) rather than the default.
		$expected['popover_border_radius'] = 0;

		$this->assertEqualSetsWithIndex( $expected, $this->options->sanitize( $input ) );
		$this->assertEqualSetsWithIndex( $expected, $this->options->sanitize( array() ) );
		$this->assertEqualSetsWithIndex( $expected, $this->options->sanitize( 'not-an-array' ) );
	}

	/**
	 * Markup is stripped from the text fields.
	 *
	 * @return void
	 */
	public function test_sanitize_strips_markup_from_text_fields() {
		$this->become_admin();

		$result = $this->options->sanitize(
			array(
				'heading'       => '<script>alert(1)</script>Logo <b>here</b>',
				'logo_selector' => '<i>.logo</i>',
			)
		);

		$this->assertSame( 'Logo here', $result['heading'] );
		$this->assertSame( '.logo', $result['logo_selector'] );
	}

	/**
	 * The legacy "custom" CSS mode is migrated to "default".
	 *
	 * @return void
	 */
	public function test_sanitize_migrates_custom_css_mode() {
		$this->become_admin();

		$result = $this->options->sanitize( array( 'css_loading_mode' => 'custom' ) );

		$this->assertSame( 'default', $result['css_loading_mode'] );
	}

	/**
	 * The legacy subheading keys are still accepted, the new keys win.
	 *
	 * @return void
	 */
	public function test_sanitize_accepts_legacy_subheading_keys() {
		$this->become_admin();

		$result = $this->options->sanitize(
			array(
				'subheading_line1' => 'Legacy one',
				'subheading_line2' => 'Legacy two',
				'text_line2'       => 'New two',
			)
		);

		$this->assertSame( 'Legacy one', $result['text_line1'] );
		$this->assertSame( 'New two', $result['text_line2'] );
	}
}
