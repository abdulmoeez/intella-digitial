/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

/**
 * Close the top bar and remember it. The storage key carries the message version
 * (Settings → Intella site), so a new message shows again for visitors who closed the old one.
 */
export function initTopbar() {
	const close = document.querySelector( '[data-topbar-close]' );
	close?.addEventListener( 'click', () => {
		document.documentElement.classList.add( 'topbar-closed' );
		try {
			localStorage.setItem( close.dataset.topbarKey || 'intella-topbar', 'closed' );
		} catch ( e ) {}
		document.getElementById( 'main' )?.focus?.( { preventScroll: true } );
	} );
}

initTopbar();
