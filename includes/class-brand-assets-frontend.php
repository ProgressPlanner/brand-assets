<?php
/**
 * Brand Assets Frontend Class
 *
 * @since 0.1.0
 * @package BrandAssets
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Brand Assets Frontend Class
 *
 * @since 0.1.0
 */
final class Brand_Assets_Frontend {

	/**
	 * Popover options
	 *
	 * @since 0.1.0
	 * @var array
	 */
	private $popover_options = array();

	/**
	 * Constructor
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks
	 *
	 * @since 0.1.0
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_footer', array( $this, 'add_popover_html' ) );
	}

	/**
	 * Enqueue frontend scripts and styles for the logo popover functionality
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function enqueue_scripts() {
		$settings = new Brand_Assets_Settings();
		$options  = $settings->get_options();

		// Only enqueue if we have a brand page selected.
		if ( empty( $options['brand_page_id'] ) ) {
			return;
		}

		$brand_page_id            = $options['brand_page_id'];
		$popover_heading          = $options['popover_heading'] ?: __( 'Looking for our logo?', 'brand-assets' );
		$popover_subheading_line1 = $options['popover_subheading_line1'] ?: __( "You're in the right spot!", 'brand-assets' );
		$popover_subheading_line2 = $options['popover_subheading_line2'] ?: __( 'Go to our', 'brand-assets' );
		$popover_link_text        = $options['popover_link_text'] ?: __( 'logo & style page', 'brand-assets' );
		$logo_selector            = $options['logo_selector'] ?: '.wp-block-site-logo';

		// Get the page URL and title.
		$page_url   = get_permalink( $brand_page_id );
		$page_title = get_the_title( $brand_page_id );

		if ( ! $page_url || ! $page_title ) {
			return;
		}

		// Store the options for use in add_popover_html.
		$this->popover_options = array(
			'heading'          => $popover_heading,
			'subheading_line1' => $popover_subheading_line1,
			'subheading_line2' => $popover_subheading_line2,
			'link_text'        => $popover_link_text,
			'page_url'         => $page_url,
			'logo_selector'    => $logo_selector,
		);

		// Register and enqueue our own style handle.
		wp_register_style( 'brand-assets-popover', false, array(), BRAND_ASSETS_VERSION );
		wp_enqueue_style( 'brand-assets-popover' );

		// Register and enqueue our own script handle.
		wp_register_script( 'brand-assets-popover', false, array(), BRAND_ASSETS_VERSION, true );
		wp_enqueue_script( 'brand-assets-popover' );

		// Create the CSS.
		$css = $this->get_popover_css();

		// Create the JavaScript.
		$js = sprintf(
			'document.addEventListener("DOMContentLoaded", function() {
				const logoElement = document.querySelector("%s");
				if (logoElement) {
					logoElement.addEventListener("contextmenu", function(event) {
						event.preventDefault();
						document.querySelector("#logo_popover").showPopover();
					}, false);
				}
			});',
			esc_js( $logo_selector )
		);

		// Add inline styles and scripts to our own handles.
		wp_add_inline_style( 'brand-assets-popover', $css );
		wp_add_inline_script( 'brand-assets-popover', $js );
	}

	/**
	 * Add popover HTML to footer
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function add_popover_html() {
		// Only add HTML if we have popover options (meaning enqueue_scripts ran).
		if ( empty( $this->popover_options ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the printf.
		printf(
			'<div popover="manual" id="logo_popover">
				<h3>%s</h3>
				<p>%s<br>%s <a href="%s">%s</a>.</p>
				<button class="close" popovertarget="logo_popover" popovertargetaction="hide">X</button>
			</div>',
			esc_html( $this->popover_options['heading'] ),
			esc_html( $this->popover_options['subheading_line1'] ),
			esc_html( $this->popover_options['subheading_line2'] ),
			esc_url( $this->popover_options['page_url'] ),
			esc_html( $this->popover_options['link_text'] )
		);
	}

	/**
	 * Get popover CSS styles
	 *
	 * @since 0.1.0
	 * @return string CSS styles for the popover.
	 */
	private function get_popover_css() {
		return '
			#logo_popover::backdrop {
				background: rgb(0 0 0 / 75%);
			}
			#logo_popover {
				position: relative;
				padding: 30px 45px 20px 30px;
				text-align: left;
			}
			#logo_popover p {
				margin: 20px 0;
				line-height: 1.5;
			}
			#logo_popover a {
				font-weight: bold;
				text-decoration: underline;
			}
			#logo_popover button.close {
				border: none;
				background-color: #fff;
				position: absolute;
				right: 5px;
				top: 5px;
			}
			#logo_popover button.close:hover {
				cursor: pointer;
				background-color: #CCE1D7;
			}
		';
	}
}
