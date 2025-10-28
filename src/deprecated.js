import { useBlockProps } from '@wordpress/block-editor';

// Function to determine if a color is light or dark.
const isColorDark = ( hexColor ) => {
	const color = hexColor.replace( '#', '' );
	const r = parseInt( color.substring( 0, 2 ), 16 );
	const g = parseInt( color.substring( 2, 4 ), 16 );
	const b = parseInt( color.substring( 4, 6 ), 16 );
	const luminance = 0.2126 * r + 0.7152 * g + 0.0722 * b;
	return luminance < 128;
};

// Function to convert HEX to CMYK
const hexToCmyk = ( hex ) => {
	const r = parseInt( hex.substring( 1, 3 ), 16 ) / 255;
	const g = parseInt( hex.substring( 3, 5 ), 16 ) / 255;
	const b = parseInt( hex.substring( 5, 7 ), 16 ) / 255;

	const k = 1 - Math.max( r, g, b );
	const c = ( 1 - r - k ) / ( 1 - k ) || 0;
	const m = ( 1 - g - k ) / ( 1 - k ) || 0;
	const y = ( 1 - b - k ) / ( 1 - k ) || 0;

	return `${ Math.round( c * 100 ) },${ Math.round( m * 100 ) },${ Math.round(
		y * 100
	) },${ Math.round( k * 100 ) }`;
};

// Version 0.1.0: Blocks without CMYK values in saved content
const v010 = {
	attributes: {
		swatches: {
			type: 'array',
			default: [],
		},
		swatchWidth: {
			type: 'number',
			default: 150,
		},
		swatchHeight: {
			type: 'number',
			default: 150,
		},
		swatchGap: {
			type: 'number',
			default: 20,
		},
		borderWidth: {
			type: 'number',
			default: 1,
		},
		borderRadius: {
			type: 'number',
			default: 0,
		},
		borderColor: {
			type: 'string',
			default: '#888888',
		},
		showCMYK: {
			type: 'boolean',
			default: false,
		},
	},
	save( { attributes } ) {
		const {
			swatches,
			swatchWidth,
			swatchHeight,
			swatchGap,
			borderWidth,
			borderRadius,
			borderColor,
		} = attributes;

		return (
			<div { ...useBlockProps.save() }>
				<div
					style={ {
						'--gapWidth': `${ swatchGap }px`,
						'--swatchWidth': `${ swatchWidth }px`,
						'--swatchHeight': `${ swatchHeight }px`,
						'--borderWidth': `${ borderWidth }px`,
						'--borderRadius': `${ borderRadius }px`,
						'--borderColor': `${ borderColor }`,
					} }
					className={ 'swatch_container' }
				>
					{ swatches.map( ( swatch, index ) => {
						return (
							<div
								key={ index }
								className={
									'swatch' +
									( isColorDark( swatch.color ) ? ' dark' : '' )
								}
								style={ { '--swatchColor': `${ swatch.color }` } }
							>
								<span className={ 'name' }>{ swatch.name }</span>
								<code>{ swatch.color.toUpperCase() }</code>
							</div>
						);
					} ) }
				</div>
			</div>
		);
	},
	migrate( attributes ) {
		// Ensure all swatches have CMYK values
		const migratedSwatches = attributes.swatches.map( ( swatch ) => {
			if ( ! swatch.cmyk && swatch.color ) {
				return {
					...swatch,
					cmyk: hexToCmyk( swatch.color ),
				};
			}
			return swatch;
		} );

		return {
			...attributes,
			swatches: migratedSwatches,
		};
	},
};

export default [ v010 ];
