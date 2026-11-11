/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

/** Mobile menu toggle, sub-menus and the header shadow on scroll. */
export function initNavigation() {
	const header = document.querySelector( '[data-site-header]' );
	const nav = document.querySelector( '[data-nav]' );
	const toggle = document.querySelector( '[data-nav-toggle]' );
	if ( ! header || ! nav ) return;

	const closeSubmenus = ( except ) => {
		nav.querySelectorAll( '[data-submenu-toggle]' ).forEach( ( button ) => {
			if ( button === except ) return;
			button.setAttribute( 'aria-expanded', 'false' );
			document.getElementById( button.getAttribute( 'aria-controls' ) )?.classList.remove( 'is-open' );
		} );
	};

	const setMenu = ( open ) => {
		toggle?.setAttribute( 'aria-expanded', String( open ) );
		nav.classList.toggle( 'is-open', open );
		if ( ! open ) closeSubmenus();
	};

	toggle?.addEventListener( 'click', () => setMenu( toggle.getAttribute( 'aria-expanded' ) !== 'true' ) );

	const setSubmenu = ( button, open ) => {
		if ( open ) closeSubmenus( button );
		button.setAttribute( 'aria-expanded', String( open ) );
		document.getElementById( button.getAttribute( 'aria-controls' ) )?.classList.toggle( 'is-open', open );
	};

	// Desktop with a mouse: open sub-menus on hover, close shortly after the pointer leaves.
	const hoverQuery = window.matchMedia( '(min-width: 64rem) and (hover: hover)' );
	let hoverOpenedAt = 0;
	nav.querySelectorAll( '[data-submenu-toggle]' ).forEach( ( button ) => {
		const item = button.closest( 'li' );
		let closeTimer = null;
		item.addEventListener( 'mouseenter', () => {
			if ( ! hoverQuery.matches ) return;
			clearTimeout( closeTimer );
			if ( button.getAttribute( 'aria-expanded' ) !== 'true' ) {
				setSubmenu( button, true );
				hoverOpenedAt = Date.now();
			}
		} );
		item.addEventListener( 'mouseleave', () => {
			if ( ! hoverQuery.matches ) return;
			closeTimer = setTimeout( () => setSubmenu( button, false ), 220 );
		} );
	} );

	nav.addEventListener( 'click', ( event ) => {
		const button = event.target.closest( '[data-submenu-toggle]' );
		if ( button ) {
			const open = button.getAttribute( 'aria-expanded' ) !== 'true';
			// A click right after hover-opening should keep the menu open, not close it.
			if ( ! open && hoverQuery.matches && Date.now() - hoverOpenedAt < 800 ) return;
			setSubmenu( button, open );
			return;
		}
		if ( event.target.closest( '[data-mega-tab]' ) ) return;
		// Close the phone menu after following an in-page link.
		if ( event.target.closest( 'a' ) ) setMenu( false );
	} );

	document.addEventListener( 'click', ( event ) => {
		if ( ! header.contains( event.target ) ) closeSubmenus();
	} );

	document.addEventListener( 'keydown', ( event ) => {
		if ( event.key !== 'Escape' ) return;
		closeSubmenus();
		if ( nav.classList.contains( 'is-open' ) ) {
			setMenu( false );
			toggle?.focus();
		}
	} );

	const onScroll = () => header.classList.toggle( 'is-scrolled', window.scrollY > 8 );
	window.addEventListener( 'scroll', onScroll, { passive: true } );
	onScroll();
}

initNavigation();
