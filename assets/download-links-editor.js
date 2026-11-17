/**
 * Toggle downloads for individual rich-text links.
 */

( function () {
	const { createElement } = wp.element;
	const { __ } = wp.i18n;
	const { applyFormat, getActiveFormat, registerFormatType } = wp.richText;
	const { BlockControls } = wp.blockEditor;
	const { ToolbarButton } = wp.components;
	const { useSelect } = wp.data;
	const label = __( 'This link is a download', 'brand-assets' );

	/**
	 * Find the entire link, including text outside a partial selection.
	 *
	 * @param {Object} value Rich-text value.
	 * @return {?Object} Link format and its boundaries, or null for mixed selections.
	 */
	function getLinkRange( value ) {
		const link = getActiveFormat( value, 'core/link' );
		if ( ! link || value.start === undefined || value.end === undefined ) {
			return null;
		}

		const linkAt = ( index ) =>
			value.formats[ index ]?.find(
				( format ) => format.type === 'core/link'
			);
		let start = value.start;
		let end = value.end;

		// A caret at the end of a link can still have that link active.
		if ( start === end && linkAt( start ) !== link ) {
			start--;
		}
		if ( linkAt( start ) !== link ) {
			return null;
		}
		for ( let index = start; index < end; index++ ) {
			if ( linkAt( index ) !== link ) {
				return null;
			}
		}
		while ( start > 0 && linkAt( start - 1 ) === link ) {
			start--;
		}
		while ( end < value.text.length && linkAt( end ) === link ) {
			end++;
		}

		return { link, start, end };
	}

	/**
	 * Render the control only when a single existing link is selected.
	 *
	 * @param {Object}   props           Rich-text editing props.
	 * @param {Object}   props.value     Current rich-text value.
	 * @param {Function} props.onChange  Update the rich-text value.
	 * @param {boolean}  props.isVisible Whether the formatting toolbar is visible.
	 * @return {?Object} Toolbar control.
	 */
	function DownloadLinkControl( { value, onChange, isVisible = true } ) {
		const inheritedDownload = useSelect( ( select ) => {
			const editor = select( 'core/block-editor' );
			const clientId = editor.getSelectedBlockClientId();
			if ( ! clientId ) {
				return false;
			}
			return [ clientId, ...editor.getBlockParents( clientId ) ].some(
				( id ) =>
					( editor.getBlockAttributes( id )?.className || '' )
						.split( /\s+/ )
						.includes( 'ba-download' )
			);
		}, [] );
		const range = getLinkRange( value );
		if ( ! isVisible || ! range ) {
			return null;
		}

		const { link, start, end } = range;
		const classes = ( link.attributes.class || '' )
			.split( /\s+/ )
			.filter( Boolean );
		const nativeDownload =
			link.attributes.download !== undefined ||
			link.unregisteredAttributes?.download !== undefined;
		const isDownload =
			! classes.includes( 'ba-no-download' ) &&
			( classes.includes( 'ba-download' ) ||
				inheritedDownload ||
				nativeDownload );

		return createElement(
			BlockControls,
			{ group: 'inline' },
			createElement( ToolbarButton, {
				label,
				icon: 'download',
				isPressed: isDownload,
				onClick: () => {
					const nextClasses = classes.filter(
						( name ) =>
							name !== 'ba-download' && name !== 'ba-no-download'
					);
					if ( ! isDownload ) {
						nextClasses.push( 'ba-download' );
					} else if ( inheritedDownload ) {
						// Override an existing block-wide download setting for this link.
						nextClasses.push( 'ba-no-download' );
					}
					const attributes = { ...link.attributes };
					const unregisteredAttributes = {
						...link.unregisteredAttributes,
					};
					if ( nextClasses.length ) {
						attributes.class = nextClasses.join( ' ' );
					} else {
						delete attributes.class;
					}
					if ( isDownload ) {
						delete attributes.download;
						delete unregisteredAttributes.download;
					}
					onChange(
						applyFormat(
							value,
							{ ...link, attributes, unregisteredAttributes },
							start,
							end
						)
					);
				},
			} )
		);
	}

	// Register an editing control; only core/link is modified, so no wrapper is saved.
	registerFormatType( 'brand-assets/download-link', {
		title: label,
		tagName: 'span',
		className: 'ba-download-control',
		edit: DownloadLinkControl,
	} );
} )();
