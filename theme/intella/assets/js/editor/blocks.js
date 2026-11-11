/**
 * Editor for every Intella section block. One script, no build step.
 *
 * Each block's fields come from its block.json ("intella" → "fields"), passed in by inc/blocks.php as
 * window.intellaBlocks. The page canvas shows the block exactly as the site renders it (server-side preview);
 * the content is edited in the sidebar form built from the fields.
 */
( function ( wp ) {
	const { createElement: el, Fragment, useEffect, useRef } = wp.element;
	const { registerBlockType } = wp.blocks;
	const { InspectorControls, useBlockProps, MediaUpload, MediaUploadCheck } = wp.blockEditor;
	const {
		PanelBody, TextControl, TextareaControl, ToggleControl, SelectControl,
		Button, CheckboxControl, BaseControl, Spinner, Notice,
	} = wp.components;
	const { useSelect } = wp.data;
	const ServerSideRender = wp.serverSideRender;
	const { __ } = wp.i18n;

	/** Put the icon sprite into the editor canvas once, so <use href="#i-…"> icons show in previews. */
	const useSprite = ( ref ) => {
		useEffect( () => {
			const doc = ref.current?.ownerDocument;
			if ( doc && window.intellaSprite && ! doc.getElementById( 'intella-sprite' ) ) {
				const holder = doc.createElement( 'div' );
				holder.id = 'intella-sprite';
				holder.hidden = true;
				holder.innerHTML = window.intellaSprite;
				doc.body.prepend( holder );
			}
		} );
	};

	/** A new, empty item for a repeater. */
	const emptyItem = ( fields ) => Object.fromEntries( fields.map( ( f ) => [ f.key, f.default ?? ( [ 'repeater', 'posts', 'list' ].includes( f.type ) ? [] : '' ) ] ) );

	/** Image picker storing an attachment ID. */
	const ImageField = ( { field, value, onChange } ) => {
		const media = useSelect( ( select ) => ( value ? select( 'core' ).getMedia( value ) : null ), [ value ] );
		return el( BaseControl, { label: field.label, help: field.help, __nextHasNoMarginBottom: true },
			el( MediaUploadCheck, null,
				el( MediaUpload, {
					onSelect: ( m ) => onChange( m.id ),
					allowedTypes: field.allowedTypes || [ 'image' ],
					value,
					render: ( { open } ) => el( 'div', { className: 'intella-image-field' },
						media?.source_url && el( 'img', { src: media.media_details?.sizes?.thumbnail?.source_url || media.source_url, alt: '' } ),
						el( Button, { variant: 'secondary', onClick: open }, value ? __( 'Replace', 'intella' ) : __( 'Choose image', 'intella' ) ),
						value ? el( Button, { variant: 'link', isDestructive: true, onClick: () => onChange( 0 ) }, __( 'Remove', 'intella' ) ) : null
					),
				} )
			)
		);
	};

	/** Pick shared items (testimonials, clients, team). Ticked order = display order; none ticked = show all. */
	const PostsField = ( { field, value, onChange } ) => {
		const ids = Array.isArray( value ) ? value : [];
		const posts = useSelect( ( select ) => select( 'core' ).getEntityRecords( 'postType', field.postType, { per_page: 100, status: 'publish', orderby: 'menu_order', order: 'asc' } ), [ field.postType ] );
		if ( ! posts ) return el( Spinner );
		const title = ( p ) => p.title?.rendered?.replace( /<[^>]+>/g, '' ) || `#${ p.id }`;
		return el( BaseControl, { label: field.label, help: field.help || __( 'Tick the ones to show, in order. Leave all unticked to show every item.', 'intella' ), __nextHasNoMarginBottom: true },
			posts.length === 0 && el( 'p', null, __( 'Nothing added yet.', 'intella' ) ),
			posts.map( ( p ) => el( CheckboxControl, {
				key: p.id,
				label: ( ids.includes( p.id ) ? `${ ids.indexOf( p.id ) + 1 }. ` : '' ) + title( p ),
				checked: ids.includes( p.id ),
				onChange: ( on ) => onChange( on ? [ ...ids, p.id ] : ids.filter( ( id ) => id !== p.id ) ),
				__nextHasNoMarginBottom: true,
			} ) )
		);
	};

	/** A list of items, each with its own fields; add, remove and reorder. */
	const RepeaterField = ( { field, value, onChange } ) => {
		const items = Array.isArray( value ) ? value : [];
		const set = ( next ) => onChange( next );
		const move = ( i, by ) => {
			const next = [ ...items ];
			const [ item ] = next.splice( i, 1 );
			next.splice( i + by, 0, item );
			set( next );
		};
		const label = ( item, i ) => {
			const text = String( item?.[ field.itemLabel || field.fields[ 0 ]?.key ] ?? '' ).replace( /<[^>]+>/g, '' );
			return `${ i + 1 }. ${ text || field.itemName || __( 'Item', 'intella' ) }`;
		};
		return el( BaseControl, { label: field.label, help: field.help, className: 'intella-repeater', __nextHasNoMarginBottom: true },
			items.map( ( item, i ) => el( PanelBody, { key: i, title: label( item, i ), initialOpen: false, className: 'intella-repeater__item' },
				field.fields.map( ( sub ) => el( Field, {
					key: sub.key,
					field: sub,
					value: item?.[ sub.key ],
					onChange: ( v ) => set( items.map( ( it, j ) => ( j === i ? { ...it, [ sub.key ]: v } : it ) ) ),
				} ) ),
				el( 'div', { className: 'intella-repeater__actions' },
					el( Button, { size: 'small', variant: 'tertiary', disabled: i === 0, onClick: () => move( i, -1 ) }, __( 'Move up', 'intella' ) ),
					el( Button, { size: 'small', variant: 'tertiary', disabled: i === items.length - 1, onClick: () => move( i, 1 ) }, __( 'Move down', 'intella' ) ),
					el( Button, { size: 'small', variant: 'tertiary', onClick: () => set( [ ...items.slice( 0, i + 1 ), JSON.parse( JSON.stringify( item ) ), ...items.slice( i + 1 ) ] ) }, __( 'Duplicate', 'intella' ) ),
					el( Button, { size: 'small', variant: 'tertiary', isDestructive: true, onClick: () => set( items.filter( ( _, j ) => j !== i ) ) }, __( 'Remove', 'intella' ) )
				)
			) ),
			( ! field.max || items.length < field.max ) && el( Button, { variant: 'secondary', onClick: () => set( [ ...items, emptyItem( field.fields ) ] ) },
				`+ ${ __( 'Add', 'intella' ) } ${ field.itemName || __( 'item', 'intella' ) }` )
		);
	};

	/** One field of any type. */
	const Field = ( { field, value, onChange } ) => {
		const common = { label: field.label, help: field.help, __nextHasNoMarginBottom: true, __next40pxDefaultSize: true };
		switch ( field.type ) {
			case 'textarea':
				return el( TextareaControl, { ...common, value: value ?? '', rows: field.rows || 3, onChange } );
			case 'inline':
				return el( TextareaControl, { ...common, value: value ?? '', rows: field.rows || 2, help: field.help || __( 'Wrap highlighted words in <em>…</em>.', 'intella' ), onChange } );
			case 'code':
				return el( TextareaControl, { ...common, value: value ?? '', rows: 10, className: 'intella-code', help: field.help || __( 'HTML/SVG illustration. Replace with a real screenshot when there is one.', 'intella' ), onChange } );
			case 'list':
				return el( TextareaControl, { ...common, value: ( Array.isArray( value ) ? value : [] ).join( '\n' ), rows: field.rows || 4, help: field.help || __( 'One per line.', 'intella' ), onChange: ( v ) => onChange( v.split( '\n' ) ) } );
			case 'toggle':
				return el( ToggleControl, { ...common, checked: !! value, onChange } );
			case 'select':
				return el( SelectControl, { ...common, value: value ?? '', options: field.options.map( ( o ) => ( typeof o === 'string' ? { label: o, value: o } : o ) ), onChange } );
			case 'number':
				return el( TextControl, { ...common, type: 'number', value: value ?? '', onChange: ( v ) => onChange( v === '' ? undefined : Number( v ) ) } );
			case 'image':
				return el( ImageField, { field, value, onChange } );
			case 'posts':
				return el( PostsField, { field, value, onChange } );
			case 'repeater':
				return el( RepeaterField, { field, value, onChange } );
			case 'heading':
				return el( 'h3', { className: 'intella-field-heading' }, field.label );
			default:
				return el( TextControl, { ...common, type: field.type === 'url' ? 'text' : 'text', value: value ?? '', onChange } );
		}
	};

	/** Fields grouped into sidebar panels by their "panel" name (first panel open). */
	const Panels = ( { fields, attributes, setAttributes } ) => {
		const groups = [];
		fields.forEach( ( f ) => {
			const name = f.panel || __( 'Content', 'intella' );
			let group = groups.find( ( g ) => g.name === name );
			if ( ! group ) groups.push( ( group = { name, fields: [] } ) );
			group.fields.push( f );
		} );
		return groups.map( ( g, i ) => el( PanelBody, { key: g.name, title: g.name, initialOpen: i === 0 },
			g.fields.map( ( f ) => el( Field, { key: f.key || f.label, field: f, value: attributes[ f.key ], onChange: ( v ) => setAttributes( { [ f.key ]: v } ) } ) )
		) );
	};

	Object.entries( window.intellaBlocks || {} ).forEach( ( [ name, meta ] ) => {
		registerBlockType( name, {
			apiVersion: 3,
			title: meta.title,
			description: meta.description,
			icon: meta.icon || 'layout',
			category: meta.category || 'intella',
			attributes: meta.attributes,
			supports: { html: false, ...( meta.supports || {} ) },
			edit: ( { attributes, setAttributes } ) => {
				const ref = useRef();
				useSprite( ref );
				return el( Fragment, null,
					el( InspectorControls, null,
						meta.help && el( 'div', { className: 'intella-block-help' }, el( Notice, { status: 'info', isDismissible: false }, meta.help ) ),
						el( Panels, { fields: meta.fields, attributes, setAttributes } )
					),
					el( 'div', useBlockProps( { ref, className: 'intella-block-preview' } ),
						el( ServerSideRender, { block: name, attributes, httpMethod: 'POST', skipBlockSupportAttributes: true } )
					)
				);
			},
			save: () => null,
		} );
	} );
} )( window.wp );
