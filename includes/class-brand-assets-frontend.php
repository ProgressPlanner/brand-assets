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
	 * Flag to check if the copy script has been enqueued.
	 *
	 * @since 0.1.0
	 * @var bool
	 */
	private $copy_script_enqueued = false;

	/**
	 * Initialize WordPress hooks
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_footer', array( $this, 'add_popover_html' ) );
		add_filter( 'render_block', array( $this, 'enqueue_copy_color_script' ), 10, 2 );
	}

	/**
	 * Enqueue frontend scripts and styles for the logo popover functionality
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function enqueue_scripts() {
		$options = Brand_Assets::get_instance()->options->get_all();

		// Register and enqueue our own script handle.
		wp_register_script( 'brand-assets-popover', false, array(), BRAND_ASSETS_VERSION, true );
		wp_enqueue_script( 'brand-assets-popover' );

		$js = 'document.addEventListener("DOMContentLoaded", function() {
				// Add download attribute to all download links.
				const downloadElements = document.querySelectorAll( ".ba-download a" );
				if ( 0 < downloadElements.length ) {
					downloadElements.forEach( element => {
						element.setAttribute( "download", "" );
					} );
				}
			});';

		// Only enqueue if we have a brand page selected.
		if ( empty( $options['brand_page_id'] ) ) {
			// But inline JS can be used without the page being selected.
			wp_add_inline_script( 'brand-assets-popover', $js );
			return;
		}

		// Register and enqueue our own style handle.
		wp_register_style( 'brand-assets-popover', false, array(), BRAND_ASSETS_VERSION );
		wp_enqueue_style( 'brand-assets-popover' );

		// Create the JavaScript.
		$js .= PHP_EOL;
		$js .= sprintf(
			'document.addEventListener("DOMContentLoaded", function() {
				const logoElements = document.querySelectorAll("%s");
				const popover = document.querySelector("#brand_assets_logo_popover");

				if (logoElements.length > 0) {
					logoElements.forEach(logoElement => {
						logoElement.addEventListener("contextmenu", function(event) {
							event.preventDefault();
							popover.showPopover();
						}, false);
					});
				}

				// Close popover on escape key
				document.addEventListener("keydown", function(event) {
					if (event.key === "Escape" && popover && popover.matches(":popover-open")) {
						popover.hidePopover();
					}
				}, false);
			});',
			esc_js( $options['logo_selector'] )
		);

		// Add inline scripts to our own handle.
		wp_add_inline_script( 'brand-assets-popover', $js );

		// Handle CSS based on loading mode.
		$css_to_load = '';
		switch ( $options['css_loading_mode'] ) {
			case 'default':
				$css_to_load = $this->get_default_css();
				break;
			case 'custom':
				$css_to_load = $options['css'];
				break;
			case 'none':
			default:
				break;
		}

		// Add inline styles if we have CSS to load.
		if ( ! empty( $css_to_load ) ) {
			wp_add_inline_style( 'brand-assets-popover', $css_to_load );
		}
	}

	/**
	 * Print inline script to copy color values to clipboard.
	 *
	 * @since 0.1.0
	 * @param string               $block_content The block content.
	 * @param array<string, mixed> $block The block.
	 * @return string The block content.
	 */
	public function enqueue_copy_color_script( $block_content, $block ) {
		// Only enqueue the script if we're on a brand assets block.
		if ( 'brand-assets/brand-assets' === $block['blockName'] ) {
			// And do it only once.
			if ( ! $this->copy_script_enqueued ) {
				$js = 'document.addEventListener("DOMContentLoaded", function() {
						// Copy the color value to clipboard when clicking the color swatch.
						const colorElements = document.querySelectorAll( ".wp-block-brand-assets-brand-assets .swatch code" );
						if ( 0 < colorElements.length ) {
							colorElements.forEach( element => {
								element.addEventListener( "click", async function(event) {
									event.preventDefault();
									let color = element.textContent;

									// Remove the "CMYK: " prefix if it exists.
									if ( color.startsWith( "CMYK:" ) ) {
										color = color.substring( 5 );
									}

									color = color.trim();

									// Try modern Clipboard API first
									try {
										await navigator.clipboard.writeText( color );

										// Add visual feedback
										element.classList.add( "copied" );
										setTimeout( () => {
											element.classList.remove( "copied" );
										}, 500 );
									} catch ( err ) {

										// Fallback to execCommand
										const textArea = document.createElement( "textarea" );
										textArea.value = color;
										textArea.style.position = "fixed";
										textArea.style.left = "-999999px";
										document.body.appendChild( textArea );
										textArea.select();

										try {
											document.execCommand( "copy" );

											// Add visual feedback
											element.classList.add( "copied" );
											setTimeout( () => {
												element.classList.remove( "copied" );
											}, 500 );
										} catch ( fallbackErr ) {
											console.error( "Fallback copy failed:", fallbackErr );
										}

										document.body.removeChild( textArea );
									}
								} );
							} );
						}
					});';

				// Print inline script.
				wp_register_script( 'brand-assets-copy', false, array(), BRAND_ASSETS_VERSION, true );
				wp_enqueue_script( 'brand-assets-copy' );
				wp_add_inline_script( 'brand-assets-copy', $js );
				$this->copy_script_enqueued = true;
			}
		}

		return $block_content;
	}

	/**
	 * Add popover HTML to footer
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function add_popover_html() {
		$options = Brand_Assets::get_instance()->options->get_all();

		if ( empty( $options['brand_page_id'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the printf.
		printf(
			'<dialog popover="manual" id="brand_assets_logo_popover">
	<h1>%s</h1>
	<p class="text_line1">%s</p>
	<p class="text_line2">%s <a href="%s">%s</a>.</p>
	<button class="close" popovertarget="brand_assets_logo_popover" popovertargetaction="hide">X</button>
</dialog>' . PHP_EOL,
			esc_html( $options['heading'] ),
			esc_html( $options['text_line1'] ),
			esc_html( $options['text_line2'] ),
			esc_url( (string) get_permalink( $options['brand_page_id'] ) ),
			esc_html( $options['link_text'] )
		);
	}

	/**
	 * Get default CSS from the frontend.css file.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	private function get_default_css() {
		/**
		 * Filter the path to the frontend CSS file.
		 *
		 * Allows developers to customize the location of the frontend CSS file
		 * used for styling the logo popover. The CSS file should contain styles
		 * for the #brand_assets_logo_popover element and its children.
		 *
		 * @since 0.1.0
		 * @param string $css_file_path The path to the CSS file. Default: BRAND_ASSETS_PLUGIN_DIR . 'assets/frontend.css'
		 */
		$css_file = apply_filters( 'brand_assets_frontend_css_path', BRAND_ASSETS_PLUGIN_DIR . 'assets/frontend.css' );

		if ( file_exists( $css_file ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- This is a valid use of file_get_contents.
			$css_content = file_get_contents( $css_file );
			return $css_content ? $css_content : '';
		}

		return '';
	}
}
