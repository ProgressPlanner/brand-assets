/**
 * Copy color values to clipboard when clicking color swatches
 *
 * @package
 * @since 0.1.0
 */

/* global navigator */

document.addEventListener( 'DOMContentLoaded', function () {
	// Copy the color value to clipboard when clicking the color swatch.
	const colorElements = document.querySelectorAll(
		'.wp-block-brand-assets-brand-assets .swatch code'
	);

	if ( 0 < colorElements.length ) {
		colorElements.forEach( ( element ) => {
			element.addEventListener( 'click', async function ( event ) {
				event.preventDefault();
				let color = element.textContent;

				// Remove the "CMYK: " prefix if it exists.
				if ( color.startsWith( 'CMYK:' ) ) {
					color = color.substring( 5 );
				}

				color = color.trim();

				// Try modern Clipboard API first
				try {
					await navigator.clipboard.writeText( color );

					// Add visual feedback
					element.classList.add( 'copied' );
					setTimeout( () => {
						element.classList.remove( 'copied' );
					}, 500 );
				} catch ( err ) {
					// Fallback to execCommand
					const textArea = document.createElement( 'textarea' );
					textArea.value = color;
					textArea.style.position = 'fixed';
					textArea.style.left = '-999999px';
					document.body.appendChild( textArea );
					textArea.select();

					try {
						document.execCommand( 'copy' );

						// Add visual feedback
						element.classList.add( 'copied' );
						setTimeout( () => {
							element.classList.remove( 'copied' );
						}, 500 );
					} catch ( fallbackErr ) {
						console.error( 'Fallback copy failed:', fallbackErr ); // eslint-disable-line no-console
					}

					document.body.removeChild( textArea );
				}
			} );
		} );
	}
} );
