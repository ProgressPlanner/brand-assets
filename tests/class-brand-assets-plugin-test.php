<?php
/**
 * Tests for the main plugin class and the plugin bootstrap.
 *
 * @package BrandAssets
 */

/**
 * Tests Brand_Assets.
 *
 * @covers Brand_Assets
 * @covers ::brand_assets_autoloader
 */
final class Brand_Assets_Plugin_Test extends Yoast\WPTestUtils\WPIntegration\TestCase {

	/**
	 * The plugin constants are defined.
	 *
	 * @return void
	 */
	public function test_constants_are_defined() {
		$plugin_dir = trailingslashit( dirname( __DIR__ ) );

		$this->assertSame( $plugin_dir . 'brand-assets.php', BRAND_ASSETS_PLUGIN_FILE );
		$this->assertSame( $plugin_dir, BRAND_ASSETS_PLUGIN_DIR );
	}

	/**
	 * The version constant matches the plugin header and the readme stable tag.
	 *
	 * @return void
	 */
	public function test_version_is_consistent() {
		$headers = get_file_data(
			BRAND_ASSETS_PLUGIN_FILE,
			array( 'version' => 'Version' )
		);

		$this->assertSame( BRAND_ASSETS_VERSION, $headers['version'] );

		$readme = get_file_data(
			BRAND_ASSETS_PLUGIN_DIR . 'readme.txt',
			array( 'stable_tag' => 'Stable tag' )
		);

		$this->assertSame( BRAND_ASSETS_VERSION, $readme['stable_tag'] );
	}

	/**
	 * The plugin is a singleton whose components are set up on init.
	 *
	 * @return void
	 */
	public function test_components_are_initialized() {
		$plugin = Brand_Assets::get_instance();

		$this->assertSame( $plugin, Brand_Assets::get_instance() );
		$this->assertInstanceOf( Brand_Assets_Options::class, $plugin->options );
		$this->assertInstanceOf( Brand_Assets_Settings::class, $plugin->settings );
		$this->assertInstanceOf( Brand_Assets_Frontend::class, $plugin->frontend );
	}

	/**
	 * The autoloader ignores classes that do not belong to the plugin.
	 *
	 * @return void
	 */
	public function test_autoloader_ignores_foreign_classes() {
		brand_assets_autoloader( 'Brand_Assets_Does_Not_Exist' );

		$this->assertFalse( class_exists( 'Brand_Assets_Does_Not_Exist', false ) );
	}

	/**
	 * The block is registered from the built block.json.
	 *
	 * @return void
	 */
	public function test_block_is_registered() {
		$block = WP_Block_Type_Registry::get_instance()->get_registered( 'brand-assets/brand-assets' );

		$this->assertInstanceOf( WP_Block_Type::class, $block );
		$this->assertArrayHasKey( 'swatches', $block->attributes );
	}

	/**
	 * The brand page pattern is registered and uses the block.
	 *
	 * @return void
	 */
	public function test_block_pattern_is_registered() {
		$registry = WP_Block_Patterns_Registry::get_instance();

		$this->assertTrue( $registry->is_registered( 'brand-assets/brand-page' ) );

		$pattern = $registry->get_registered( 'brand-assets/brand-page' );

		$this->assertStringContainsString( '<!-- wp:brand-assets/brand-assets', $pattern['content'] );
	}

	/**
	 * The settings link is added in front of the other plugin action links.
	 *
	 * @return void
	 */
	public function test_settings_link_is_added_first() {
		$links = Brand_Assets::get_instance()->add_settings_link( array( 'deactivate' => '<a href="#">Deactivate</a>' ) );

		$this->assertCount( 2, $links );
		$this->assertSame(
			'<a href="' . esc_url( admin_url( 'options-general.php?page=brand-assets-settings' ) ) . '">Settings</a>',
			reset( $links )
		);
		$this->assertSame( 10, has_filter( 'plugin_action_links_' . plugin_basename( BRAND_ASSETS_PLUGIN_FILE ), array( Brand_Assets::get_instance(), 'add_settings_link' ) ) );
	}

	/**
	 * Activating the plugin stores the default options.
	 *
	 * @return void
	 */
	public function test_activation_stores_default_options() {
		$this->assertFalse( get_option( Brand_Assets_Options::OPTIONS_NAME ) );

		Brand_Assets::get_instance()->activate();

		$this->assertSame(
			Brand_Assets::get_instance()->options->get_defaults(),
			get_option( Brand_Assets_Options::OPTIONS_NAME )
		);
	}

	/**
	 * The settings page is added to the Settings menu and renders for administrators.
	 *
	 * @covers Brand_Assets_Settings
	 *
	 * @return void
	 */
	public function test_settings_page_renders() {
		require_once ABSPATH . 'wp-admin/includes/template.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		// Outside the admin there is no current screen to take the page title from.
		$GLOBALS['title'] = 'Brand Assets settings'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Test set-up.

		ob_start();
		Brand_Assets::get_instance()->settings->render_settings_page();
		$output = (string) ob_get_clean();

		unset( $GLOBALS['title'] );

		$this->assertStringContainsString( 'name="brand_assets_options[brand_page_id]"', $output );
		$this->assertStringContainsString( 'name="brand_assets_options[logo_selector]"', $output );
		$this->assertStringContainsString( 'value=".wp-block-site-logo"', $output );
	}

	/**
	 * Uninstalling removes the stored options.
	 *
	 * @coversNothing
	 *
	 * @return void
	 */
	public function test_uninstall_removes_the_options() {
		update_option( Brand_Assets_Options::OPTIONS_NAME, array( 'heading' => 'Bye' ) );

		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			define( 'WP_UNINSTALL_PLUGIN', 'brand-assets/brand-assets.php' );
		}

		require BRAND_ASSETS_PLUGIN_DIR . 'uninstall.php';

		$this->assertFalse( get_option( Brand_Assets_Options::OPTIONS_NAME ) );
	}
}
