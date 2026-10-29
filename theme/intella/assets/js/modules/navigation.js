/**
 * Mobile menu toggle for the primary navigation.
 * Markup: a button with [data-nav-toggle] and aria-controls pointing at the menu's id.
 */

/** Open and close the primary menu, and close it with the Escape key. */
export function initNavigation() {
	const toggle = document.querySelector( '[data-nav-toggle]' );
	const menu = toggle && document.getElementById( toggle.getAttribute( 'aria-controls' ) );

	if ( ! menu ) {
		return;
	}

	const setOpen = ( open ) => {
		toggle.setAttribute( 'aria-expanded', String( open ) );
		menu.classList.toggle( 'is-open', open );
	};

	toggle.addEventListener( 'click', () => {
		setOpen( toggle.getAttribute( 'aria-expanded' ) !== 'true' );
	} );

	document.addEventListener( 'keydown', ( event ) => {
		if ( event.key === 'Escape' && menu.classList.contains( 'is-open' ) ) {
			setOpen( false );
			toggle.focus();
		}
	} );
}
