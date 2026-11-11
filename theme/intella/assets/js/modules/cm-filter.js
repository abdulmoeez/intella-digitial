/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

export function initCmFilter() {
	document.querySelectorAll( '[data-cm-filter]' ).forEach( ( box ) => {
		const chips = [ ...box.querySelectorAll( '.cmlib__chip' ) ];
		const cards = [ ...box.querySelectorAll( '.cmlib__card' ) ];
		chips.forEach( ( chip ) => chip.addEventListener( 'click', () => {
			const st = chip.dataset.stage;
			chips.forEach( ( c ) => c.setAttribute( 'aria-pressed', String( c === chip ) ) );
			cards.forEach( ( card ) => { card.hidden = st !== 'all' && card.dataset.stage !== st; } );
		} ) );
	} );
}

initCmFilter();
