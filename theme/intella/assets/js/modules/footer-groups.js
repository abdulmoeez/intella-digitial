/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

/** Footer service groups: folded on phones, open (and fixed open) on wider screens. */
export function initFooterGroups() {
	const groups = [ ...document.querySelectorAll( '[data-footer-group]' ) ];
	if ( ! groups.length ) return;
	const wide = window.matchMedia( '(min-width: 48rem)' );
	const sync = () => groups.forEach( ( group ) => {
		group.open = wide.matches;
	} );
	groups.forEach( ( group ) => group.querySelector( 'summary' ).addEventListener( 'click', ( event ) => {
		if ( wide.matches ) event.preventDefault();
	} ) );
	wide.addEventListener( 'change', sync );
	sync();

	// The whole Services block starts closed; its heading button opens it.
	const toggle = document.querySelector( '[data-footer-toggle]' );
	const nav = toggle && toggle.closest( '.site-footer__services' );
	if ( ! nav ) return;
	const set = ( open ) => {
		toggle.setAttribute( 'aria-expanded', String( open ) );
		nav.classList.toggle( 'is-collapsed', ! open );
	};
	set( false );
	toggle.addEventListener( 'click', () => {
		const open = toggle.getAttribute( 'aria-expanded' ) !== 'true';
		set( open );
		nav.classList.toggle( 'is-opening', open );
	} );
}

initFooterGroups();
