<?php
/**
 * Tests for the frontend output.
 *
 * @package BrandAssets
 */

/**
 * Tests Brand_Assets_Frontend.
 *
 * @covers Brand_Assets_Frontend
 */
final class Brand_Assets_Frontend_Test extends Yoast\WPTestUtils\WPIntegration\TestCase {

	/**
	 * The frontend class under test.
	 *
	 * @var Brand_Assets_Frontend
	 */
	private $frontend;

	/**
	 * Sets up each test.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		// Start every test with clean script and style registries.
		unset( $GLOBALS['wp_scripts'], $GLOBALS['wp_styles'] );

		$this->frontend = new Brand_Assets_Frontend();
	}

	/**
	 * Cleans up after each test.
	 *
	 * @return void
	 */
	public function tear_down() {
		unset( $GLOBALS['wp_scripts'], $GLOBALS['wp_styles'] );

		parent::tear_down();
	}

	/**
	 * Creates a page and selects it as the brand page.
	 *
	 * @param string               $status  Post status of the page.
	 * @param array<string, mixed> $options Additional plugin options to store.
	 * @return int The page ID.
	 */
	private function set_brand_page( $status = 'publish', array $options = array() ) {
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => $status,
			)
		);

		Brand_Assets::get_instance()->options->update( array_merge( array( 'brand_page_id' => $page_id ), $options ) );

		return $page_id;
	}

	/**
	 * Returns everything that was added inline to a style handle.
	 *
	 * @param string $handle The style handle.
	 * @return string
	 */
	private function get_inline_style( $handle ) {
		$inline = wp_styles()->get_data( $handle, 'after' );

		return is_array( $inline ) ? implode( "\n", $inline ) : '';
	}

	/**
	 * Returns everything that was added inline to a script handle.
	 *
	 * @param string $handle The script handle.
	 * @return string
	 */
	private function get_inline_script( $handle ) {
		$inline = wp_scripts()->get_data( $handle, 'after' );

		return is_array( $inline ) ? implode( "\n", array_filter( $inline ) ) : '';
	}

	/**
	 * The frontend hooks are registered.
	 *
	 * @return void
	 */
	public function test_init_hooks_registers_hooks() {
		$this->frontend->init_hooks();

		$this->assertSame( 10, has_action( 'wp_enqueue_scripts', array( $this->frontend, 'enqueue_scripts' ) ) );
		$this->assertSame( 10, has_action( 'wp_footer', array( $this->frontend, 'add_popover_html' ) ) );
		$this->assertSame( 10, has_filter( 'render_block', array( $this->frontend, 'enqueue_copy_color_script' ) ) );
	}

	/**
	 * Without a brand page there is no popover.
	 *
	 * @return void
	 */
	public function test_popover_is_not_printed_without_a_brand_page() {
		$this->expectOutputString( '' );

		$this->frontend->add_popover_html();
	}

	/**
	 * An unpublished brand page does not get a popover.
	 *
	 * @return void
	 */
	public function test_popover_is_not_printed_for_a_draft_brand_page() {
		$this->set_brand_page( 'draft' );

		$this->expectOutputString( '' );

		$this->frontend->add_popover_html();
	}

	/**
	 * A published brand page gets a popover that links to it, with escaped texts.
	 *
	 * @return void
	 */
	public function test_popover_is_printed_for_a_published_brand_page() {
		$page_id = $this->set_brand_page(
			'publish',
			array(
				'heading'   => 'Logo <script>alert(1)</script>',
				'link_text' => 'Our brand page',
			)
		);

		ob_start();
		$this->frontend->add_popover_html();
		$output = (string) ob_get_clean();

		$this->assertStringContainsString( '<dialog popover="manual" id="brand_assets_logo_popover">', $output );
		$this->assertStringContainsString( '<h1>Logo &lt;script&gt;alert(1)&lt;/script&gt;</h1>', $output );
		$this->assertStringContainsString( '<a href="' . esc_url( (string) get_permalink( $page_id ) ) . '">Our brand page</a>', $output );
		$this->assertStringNotContainsString( '<script>', $output );
	}

	/**
	 * Without a brand page only the download helper script is loaded.
	 *
	 * @return void
	 */
	public function test_enqueue_scripts_without_a_brand_page() {
		$this->frontend->enqueue_scripts();

		$this->assertTrue( wp_script_is( 'brand-assets-popover', 'enqueued' ) );
		$this->assertFalse( wp_style_is( 'brand-assets-popover', 'enqueued' ) );

		$script = $this->get_inline_script( 'brand-assets-popover' );

		$this->assertStringContainsString( '.ba-download a', $script );
		$this->assertStringNotContainsString( 'brand_assets_logo_popover', $script );
	}

	/**
	 * With a published brand page the popover script and styles are loaded.
	 *
	 * @return void
	 */
	public function test_enqueue_scripts_with_a_published_brand_page() {
		$this->set_brand_page(
			'publish',
			array(
				'logo_selector'    => '.logo"</script>',
				'popover_bg_color' => '#123456',
				'popover_padding'  => 33,
			)
		);

		$this->frontend->enqueue_scripts();

		$this->assertTrue( wp_script_is( 'brand-assets-popover', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'brand-assets-popover', 'enqueued' ) );

		$script = $this->get_inline_script( 'brand-assets-popover' );

		$this->assertStringContainsString( '#brand_assets_logo_popover', $script );
		// The selector is JSON encoded, so it cannot break out of the script.
		$this->assertStringContainsString( 'document.querySelectorAll(' . wp_json_encode( '.logo"</script>', JSON_HEX_TAG | JSON_HEX_QUOT ) . ')', $script );
		$this->assertStringNotContainsString( '</script>', $script );

		$style = $this->get_inline_style( 'brand-assets-popover' );

		$this->assertStringContainsString( '--ba-popover-bg: #123456;', $style );
		$this->assertStringContainsString( '--ba-popover-padding: 33px;', $style );
		// The default stylesheet is inlined as well.
		$this->assertStringContainsString( 'button.close', $style );
	}

	/**
	 * With the CSS loading mode set to "none" no styles are added.
	 *
	 * @return void
	 */
	public function test_enqueue_scripts_respects_css_loading_mode_none() {
		$this->set_brand_page( 'publish', array( 'css_loading_mode' => 'none' ) );

		$this->frontend->enqueue_scripts();

		$this->assertTrue( wp_style_is( 'brand-assets-popover', 'enqueued' ) );
		$this->assertSame( '', $this->get_inline_style( 'brand-assets-popover' ) );
	}

	/**
	 * The path of the default stylesheet is filterable.
	 *
	 * @return void
	 */
	public function test_frontend_css_path_is_filterable() {
		$this->set_brand_page();

		add_filter( 'brand_assets_frontend_css_path', '__return_empty_string' );
		$this->frontend->enqueue_scripts();
		remove_filter( 'brand_assets_frontend_css_path', '__return_empty_string' );

		$style = $this->get_inline_style( 'brand-assets-popover' );

		$this->assertStringNotContainsString( 'button.close', $style );
		$this->assertStringContainsString( '--ba-popover-bg:', $style );
	}

	/**
	 * The copy script is only enqueued for the Brand Assets block.
	 *
	 * @return void
	 */
	public function test_copy_script_is_only_enqueued_for_the_brand_assets_block() {
		$content = $this->frontend->enqueue_copy_color_script( '<p>Text</p>', array( 'blockName' => 'core/paragraph' ) );

		$this->assertSame( '<p>Text</p>', $content );
		$this->assertFalse( wp_script_is( 'brand-assets-copy', 'enqueued' ) );

		$content = $this->frontend->enqueue_copy_color_script( '<div>Swatches</div>', array( 'blockName' => 'brand-assets/brand-assets' ) );

		$this->assertSame( '<div>Swatches</div>', $content );
		$this->assertTrue( wp_script_is( 'brand-assets-copy', 'enqueued' ) );
		$this->assertStringEndsWith( 'assets/copy-color.js', wp_scripts()->registered['brand-assets-copy']->src );
	}
}
