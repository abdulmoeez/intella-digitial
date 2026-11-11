/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

export function initAccordionSwap() {
	document.querySelectorAll( '[data-acc-swap]' ).forEach( ( root ) => {
		const items = [ ...root.querySelectorAll( 'details' ) ];
		const panes = [ ...root.querySelectorAll( '[data-acc-pane]' ) ];
		if ( ! panes.length ) return;
		const show = ( i ) => panes.forEach( ( p, n ) => { p.hidden = n !== i; } );
		items.forEach( ( d, i ) => d.addEventListener( 'toggle', () => { if ( d.open ) show( i ); } ) );
		show( Math.max( 0, items.findIndex( ( d ) => d.open ) ) );
	} );
}

initAccordionSwap();
