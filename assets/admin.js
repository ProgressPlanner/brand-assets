/**
 * Brand Assets Admin JavaScript
 *
 * Handles admin settings page functionality including:
 * - Color picker initialization
 * - CSS loading mode toggle for popover styling section
 *
 * @package BrandAssets
 * @since 0.1.0
 */

jQuery( document ).ready( function( $ ) {
	// Initialize WordPress color pickers
	$( '.brand-assets-color-picker' ).wpColorPicker();

	/**
	 * Toggle popover styling section visibility based on CSS loading mode
	 *
	 * When "Load no CSS" is selected, hide the styling options and show
	 * an informational notice instead, since CSS variables won't be output.
	 */
	function togglePopoverStyling() {
		var cssMode = $( '#css_loading_mode' ).val();

		if ( cssMode === 'none' ) {
			$( '#popover-styling-section' ).hide();
			$( '#popover-styling-notice' ).show();
		} else {
			$( '#popover-styling-section' ).show();
			$( '#popover-styling-notice' ).hide();
		}
	}

	// Run on page load
	togglePopoverStyling();

	// Run when CSS loading mode changes
	$( '#css_loading_mode' ).on( 'change', togglePopoverStyling );
} );
