import { useState, useEffect } from '@wordpress/element';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	ColorPalette,
	Button,
	TextControl,
	RangeControl,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

// Function to determine if a color is light or dark
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
	let r = parseInt( hex.substring( 1, 3 ), 16 ) / 255;
	let g = parseInt( hex.substring( 3, 5 ), 16 ) / 255;
	let b = parseInt( hex.substring( 5, 7 ), 16 ) / 255;

	let k = 1 - Math.max( r, g, b );
	let c = ( 1 - r - k ) / ( 1 - k ) || 0;
	let m = ( 1 - g - k ) / ( 1 - k ) || 0;
	let y = ( 1 - b - k ) / ( 1 - k ) || 0;

	return `${ Math.round( c * 100 ) },${ Math.round( m * 100 ) },${ Math.round(
		y * 100
	) },${ Math.round( k * 100 ) }`;
};

// Function to get default swatches from theme colors or fallback to defaults
const getDefaultSwatches = ( themeColors ) => {
	if ( themeColors && themeColors.length > 0 ) {
		return themeColors.map( ( color, index ) => ({
			name: color.name || `Color ${ index + 1 }`,
			color: color.color,
			cmyk: hexToCmyk( color.color )
		}));
	}

	// Fallback to default colors
	return [
		{
			name: 'Red',
			color: '#E63027',
			cmyk: '0,79,83,10'
		},
		{
			name: 'Green',
			color: '#008000',
			cmyk: '100,0,100,50'
		},
		{
			name: 'Black',
			color: '#000000',
			cmyk: '0,0,0,100'
		}
	];
};

export default function Edit( { attributes, setAttributes, clientId } ) {
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
	const [ selectedSwatchIndex, setSelectedSwatchIndex ] = useState( 0 );
	const [ linked, setLinked ] = useState( true );

	// Get theme colors from WordPress
	const themeColors = useSelect( ( select ) => {
		return select( 'core/block-editor' ).getSettings()?.colors || [];
	}, [] );

	// Check if the block is selected
	const isSelected = useSelect(
		( select ) =>
			select( 'core/block-editor' ).getBlockSelectionStart() === clientId,
		[ clientId ]
	);

	// Initialize swatches with theme colors if they're empty and theme colors are available
	useEffect( () => {
		if ( swatches.length === 0 ) {
			const defaultSwatches = getDefaultSwatches( themeColors );
			setAttributes( { swatches: defaultSwatches } );
		}
	}, [ setAttributes ] );

	const updateSwatch = ( index, newSwatch ) => {
		const updatedSwatches = [ ...swatches ];
		updatedSwatches[ index ] = newSwatch;
		setAttributes( { swatches: updatedSwatches } );
	};

	const addSwatch = () => {
		const newSwatch = { name: 'New Color', color: '#000000' };
		const updatedSwatches = [ ...swatches, newSwatch ];
		setAttributes( { swatches: updatedSwatches } );
		setSelectedSwatchIndex( updatedSwatches.length - 1 );
	};

	const deleteSwatch = ( index ) => {
		const updatedSwatches = swatches.filter( ( _, i ) => i !== index );
		setAttributes( { swatches: updatedSwatches } );
		setSelectedSwatchIndex( Math.max( 0, index - 1 ) );
	};

	const updateDimensions = ( value, dimension ) => {
		console.log( 'Updating dimensions:', value, dimension );
		if ( dimension === 'both' ) {
			setAttributes( { swatchWidth: value, swatchHeight: value } );
			maxBorderRadius = Math.max( value, attributes.borderRadius ) / 2;
		} else {
			setAttributes( { [ dimension ]: value } );
			maxBorderRadius =
				Math.max( attributes.swatchHeight, attributes.swatchWidth ) / 2;
		}
	};

	let maxBorderRadius =
		Math.max( attributes.swatchHeight, attributes.swatchWidth ) / 2;
	return (
		<>
			<InspectorControls>
				<div style={ { padding: '16px' } }>
					<ToggleControl
						style={ { marginLeft: '10px' } }
						label={ __( 'Show CMYK values', 'color-scheme' ) }
						checked={ showCMYK }
						onChange={ () =>
							setAttributes( { showCMYK: ! showCMYK } )
						}
					/>
				</div>
				<PanelBody
					title={ __( 'Swatch dimensions', 'color-scheme' ) }
					initialOpen={ false }
				>
					{ linked ? (
						<RangeControl
							label={ __( 'Swatch size', 'color-scheme' ) }
							value={ swatchWidth }
							onChange={ ( value ) =>
								updateDimensions( value, 'both' )
							}
							min={ 50 }
							max={ 300 }
						/>
					) : (
						<>
							<RangeControl
								label={ __( 'Swatch width', 'color-scheme' ) }
								value={ swatchWidth }
								onChange={ ( value ) =>
									updateDimensions( value, 'swatchWidth' )
								}
								min={ 50 }
								max={ 300 }
							/>
							<RangeControl
								label={ __( 'Swatch height', 'color-scheme' ) }
								value={ swatchHeight }
								onChange={ ( value ) =>
									updateDimensions( value, 'swatchHeight' )
								}
								min={ 50 }
								max={ 300 }
							/>
						</>
					) }
					<ToggleControl
						label={ __(
							'Link swatch width & height',
							'color-scheme'
						) }
						checked={ linked }
						onChange={ () => setLinked( ! linked ) }
					/>
					<RangeControl
						label={ __( 'Gap between colors', 'color-scheme' ) }
						value={ swatchGap }
						onChange={ ( value ) =>
							setAttributes( { swatchGap: value } )
						}
						min={ 0 }
						max={ 10 }
					/>
				</PanelBody>
				<PanelBody
					title={ __( 'Border settings', 'color-scheme' ) }
					initialOpen={ false }
				>
					<RangeControl
						label={ __( 'Border width', 'color-scheme' ) }
						value={ borderWidth }
						onChange={ ( value ) =>
							setAttributes( { borderWidth: value } )
						}
						min={ 0 }
						max={ 10 }
					/>
					<RangeControl
						label={ __( 'Border radius', 'color-scheme' ) }
						value={ borderRadius }
						onChange={ ( value ) =>
							setAttributes( { borderRadius: value } )
						}
						min={ 0 }
						max={ maxBorderRadius }
					/>
					{ borderWidth > 0 && (
						<ColorPalette
							value={ borderColor }
							colors={ [
								{ name: 'White', color: '#FFFFFF' },
								{ name: 'Light Gray', color: '#F0F0F0' },
								{ name: 'Gray', color: '#888888' },
								{ name: 'Dark Gray', color: '#444444' },
								{ name: 'Black', color: '#000000' },
							] }
							label={ __( 'Border color', 'color-scheme' ) }
							onChange={ ( value ) => {
								console.log( 'New border color:', value );
								setAttributes( { borderColor: value } );
							} }
							disableAlpha
						/>
					) }
				</PanelBody>
				<PanelBody title={ __( 'Edit Swatch', 'color-scheme' ) }>
					{ swatches[ selectedSwatchIndex ] && (
						<>
							<TextControl
								label={ __( 'Color Name', 'color-scheme' ) }
								value={ swatches[ selectedSwatchIndex ].name }
								onChange={ ( name ) =>
									updateSwatch( selectedSwatchIndex, {
										...swatches[ selectedSwatchIndex ],
										name,
									} )
								}
							/>
							<ColorPalette
								value={ swatches[ selectedSwatchIndex ].color }
								onChange={ ( color ) =>
									updateSwatch( selectedSwatchIndex, {
										...swatches[ selectedSwatchIndex ],
										color,
									} )
								}
							/>
							{ showCMYK && (
								<div style={ { marginBottom: '8px' } }>
									<div
										style={ {
											display: 'flex',
											justifyContent: 'space-between',
											alignItems: 'center',
										} }
									>
										<label>
											{ __(
												'CMYK Values',
												'color-scheme'
											) }
										</label>
										<Button
											isLink
											className={ 'calculate' }
											onClick={ () => {
												const cmyk = hexToCmyk(
													swatches[
														selectedSwatchIndex
													].color
												);
												updateSwatch(
													selectedSwatchIndex,
													{
														...swatches[
															selectedSwatchIndex
														],
														cmyk,
													}
												);
											} }
										>
											{ __(
												'Calculate',
												'color-scheme'
											) }
										</Button>
									</div>
									<TextControl
										value={
											swatches[ selectedSwatchIndex ].cmyk
										}
										onChange={ ( cmyk ) =>
											updateSwatch( selectedSwatchIndex, {
												...swatches[
													selectedSwatchIndex
												],
												cmyk,
											} )
										}
									/>
								</div>
							) }
						</>
					) }
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>
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
						const focusClass =
							isSelected && selectedSwatchIndex === index
								? ' focus'
								: '';
						return (
							<div
								key={ index }
								className={
									'swatch' +
									( isColorDark( swatch.color )
										? ' dark'
										: '' ) +
									focusClass
								}
								style={ {
									'--swatchColor': `${ swatch.color }`,
								} }
								onClick={ () =>
									setSelectedSwatchIndex( index )
								}
							>
								<span className={ 'name' }>
									{ swatch.name }
								</span>
								<code>{ swatch.color.toUpperCase() }</code>
								{ showCMYK && (
									<code className={ 'small' }>
										{ 'CMYK: ' + swatch.cmyk }
									</code>
								) }
								{ isSelected && swatches.length > 1 && (
									<button
										onClick={ ( e ) => {
											e.stopPropagation();
											deleteSwatch( index );
										} }
										className={ 'delete' }
										aria-label={ __(
											'Delete Swatch',
											'color-scheme'
										) }
									>
										&times;
									</button>
								) }
							</div>
						);
					} ) }
					<Button
						isSecondary
						onClick={ addSwatch }
						className={ 'add_swatch' }
					>
						+ { __( 'Add Swatch', 'color-scheme' ) }
					</Button>
				</div>
			</div>
		</>
	);
}
