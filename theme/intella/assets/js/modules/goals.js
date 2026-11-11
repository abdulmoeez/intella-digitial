/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

export function initGoals() {
	const grid = document.querySelector( '[data-goals]' );
	if ( ! grid ) return;
	const buttons = [ ...grid.querySelectorAll( '.goal__open' ) ];
	let open = null;

	const close = () => {
		if ( ! open ) return;
		open.setAttribute( 'aria-expanded', 'false' );
		open.closest( '[data-goal]' ).classList.remove( 'is-selected' );
		document.getElementById( open.getAttribute( 'aria-controls' ) ).hidden = true;
		grid.classList.remove( 'has-selection' );
		open = null;
	};

	buttons.forEach( ( button ) => button.addEventListener( 'click', () => {
		const same = button === open;
		const last = open;
		close();
		if ( same ) {
			last.focus();
			return;
		}
		const plan = document.getElementById( button.getAttribute( 'aria-controls' ) );
		// The plan always opens in the middle of the grid (between the rows), whichever card was clicked.
		// With one column (phones) it opens straight under the card instead.
		const cards = [ ...grid.querySelectorAll( '[data-goal]' ) ];
		const cols = getComputedStyle( grid ).gridTemplateColumns.split( ' ' ).length;
		const index = cards.indexOf( button.closest( '[data-goal]' ) );
		const rows = Math.ceil( cards.length / cols );
		const after = cols > 1 ? Math.min( Math.ceil( rows / 2 ) * cols, cards.length ) - 1 : index;
		cards[ after ].after( plan );
		plan.hidden = false;
		button.setAttribute( 'aria-expanded', 'true' );
		button.closest( '[data-goal]' ).classList.add( 'is-selected' );
		grid.classList.add( 'has-selection' );
		open = button;
		// Bring the plan to the middle of the screen.
		plan.scrollIntoView( { behavior: reduceMotion ? 'auto' : 'smooth', block: 'center' } );
	} ) );

	// The whole card opens its plan (except its service links).
	grid.querySelectorAll( '[data-goal]' ).forEach( ( card ) => card.addEventListener( 'click', ( event ) => {
		if ( event.target.closest( 'a, button' ) ) return;
		card.querySelector( '.goal__open' ).click();
	} ) );

	document.querySelectorAll( '[data-goal-close]' ).forEach( ( btn ) => btn.addEventListener( 'click', () => {
		const last = open;
		close();
		last?.focus();
	} ) );

	document.addEventListener( 'keydown', ( event ) => {
		if ( event.key === 'Escape' && open ) {
			const last = open;
			close();
			last.focus();
		}
	} );
}

initGoals();
