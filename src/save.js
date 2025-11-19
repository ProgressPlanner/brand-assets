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

export default function save( { attributes } ) {
	const {
		swatches,
		swatchWidth,
		swatchHeight,
		swatchGap,
		borderWidth,
		borderRadius,
		borderColor,
		showCMYK,
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
							{ showCMYK && swatch.cmyk && (
								<code className={ 'small' }>
									{ 'CMYK: ' + swatch.cmyk }
								</code>
							) }
						</div>
					);
				} ) }
			</div>
		</div>
	);
}
