const assert = require( 'node:assert/strict' );
const { readFileSync } = require( 'node:fs' );
const { join } = require( 'node:path' );
const { test } = require( 'node:test' );
const { runInNewContext } = require( 'node:vm' );

const source = readFileSync(
	join( __dirname, '../assets/download-links-editor.js' ),
	'utf8'
);

// Exercise the registered control without booting WordPress. Browser checks cover
// WordPress's real format parsing, serialization, and link-editing behavior.
function control( value, blocks = {}, isVisible = true ) {
	let edit;
	let change;
	const wp = {
		element: {
			createElement: ( type, props, ...children ) => ( {
				type,
				props,
				children,
			} ),
		},
		i18n: { __: ( text ) => text },
		blockEditor: { BlockControls: 'controls' },
		components: { ToolbarButton: 'button' },
		data: {
			useSelect: ( callback ) =>
				callback( () => ( {
					getSelectedBlockClientId: () => 'selected',
					getBlockParents: () =>
						Object.keys( blocks ).filter(
							( id ) => id !== 'selected'
						),
					getBlockAttributes: ( id ) => blocks[ id ] || {},
				} ) ),
		},
		richText: {
			registerFormatType: ( name, settings ) => {
				edit = settings.edit;
			},
			getActiveFormat: ( current ) => current.activeFormats?.[ 0 ],
			applyFormat: ( original, format, start, end ) => ( {
				original,
				format,
				start,
				end,
			} ),
		},
	};
	runInNewContext( source, { wp } );
	const result = edit( {
		value,
		isVisible,
		onChange: ( next ) => {
			change = next;
		},
	} );
	return { button: result?.children[ 0 ].props, change: () => change };
}

function fixture( start, end = start, attributes = {} ) {
	const link = {
		type: 'core/link',
		attributes: { url: '/file.pdf', ...attributes },
	};
	const second = { type: 'core/link', attributes: { url: '/other.pdf' } };
	return {
		text: 'first next',
		start,
		end,
		activeFormats: [ link ],
		formats: [
			[ link ],
			[ link ],
			[ link, { type: 'core/bold' } ],
			[ link ],
			[ link ],
			[],
			[ second ],
			[ second ],
			[ second ],
			[ second ],
		],
	};
}

test( 'caret and partial selections change the whole link, never its neighbor', () => {
	for ( const [ start, end ] of [
		[ 0, 0 ],
		[ 2, 2 ],
		[ 1, 3 ],
		[ 5, 5 ],
	] ) {
		const value = fixture( start, end );
		const original = JSON.stringify( value );
		const ui = control( value );
		assert.equal( ui.button.label, 'This link is a download' );
		assert.equal( ui.button.isPressed, false );
		ui.button.onClick();
		assert.equal( ui.change().start, 0 );
		assert.equal( ui.change().end, 5 );
		assert.equal( ui.change().format.attributes.class, 'ba-download' );
		assert.equal(
			JSON.stringify( value ),
			original,
			'Original formatting must not be mutated'
		);
	}
} );

test( 'mixed selections, plain text, and hidden toolbars have no toggle', () => {
	assert.equal( control( fixture( 2, 8 ) ).button, undefined );
	assert.equal( control( fixture( 2, 6 ) ).button, undefined );
	assert.equal(
		control( { ...fixture( 2 ), activeFormats: [] } ).button,
		undefined
	);
	assert.equal( control( fixture( 2 ), {}, false ).button, undefined );
} );

test( 'adjacent distinct links with the same URL remain separate', () => {
	const value = fixture( 2 );
	value.formats[ 5 ] = [
		{ type: 'core/link', attributes: { url: '/file.pdf' } },
	];
	const ui = control( value );
	ui.button.onClick();
	assert.equal( ui.change().end, 5 );
} );

test( 'toggling preserves other link attributes and classes', () => {
	const value = fixture( 2, 2, {
		class: 'custom ba-download another',
		target: '_blank',
		rel: 'nofollow sponsored',
	} );
	value.activeFormats[ 0 ].unregisteredAttributes = { 'data-custom': 'keep' };
	const ui = control( value );
	assert.equal( ui.button.isPressed, true );
	ui.button.onClick();
	const { format } = ui.change();
	assert.equal( format.attributes.class, 'custom another' );
	assert.equal( format.attributes.target, '_blank' );
	assert.equal( format.attributes.rel, 'nofollow sponsored' );
	assert.equal( format.unregisteredAttributes[ 'data-custom' ], 'keep' );
} );

test( 'legacy block downloads can be disabled and re-enabled for one link', () => {
	const blocks = { ancestor: { className: 'other ba-download' } };
	const off = control( fixture( 2 ), blocks );
	assert.equal( off.button.isPressed, true );
	off.button.onClick();
	assert.equal( off.change().format.attributes.class, 'ba-no-download' );
	const on = control( fixture( 2, 2, { class: 'ba-no-download' } ), blocks );
	assert.equal( on.button.isPressed, false );
	on.button.onClick();
	assert.equal( on.change().format.attributes.class, 'ba-download' );
} );

test( 'disabling a native download removes only its download attribute', () => {
	const value = fixture( 2 );
	value.activeFormats[ 0 ].unregisteredAttributes = {
		download: 'brand.pdf',
		title: 'Keep me',
	};
	const ui = control( value );
	assert.equal( ui.button.isPressed, true );
	ui.button.onClick();
	assert.equal(
		ui.change().format.unregisteredAttributes.download,
		undefined
	);
	assert.equal( ui.change().format.unregisteredAttributes.title, 'Keep me' );
} );
