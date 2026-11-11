/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

/** Content work filter ([data-cwk]): buttons show the projects for one industry, or all of them. */
export function initCwFilter() {
	document.querySelectorAll( '[data-cwk]' ).forEach( ( box ) => {
		const buttons = [ ...box.querySelectorAll( '[data-cwk-filter]' ) ];
		const cards = [ ...box.querySelectorAll( '[data-cwk-cat]' ) ];
		buttons.forEach( ( b ) => b.addEventListener( 'click', () => {
			const k = b.dataset.cwkFilter;
			buttons.forEach( ( x ) => x.setAttribute( 'aria-pressed', String( x === b ) ) );
			cards.forEach( ( c ) => { c.hidden = k !== 'all' && c.dataset.cwkCat !== k; } );
		} ) );
	} );
}

initCwFilter();
