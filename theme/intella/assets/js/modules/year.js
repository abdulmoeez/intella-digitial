/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

/** Keeps the footer year current. */
export function initYear() {
	document.querySelectorAll( '[data-year]' ).forEach( ( el ) => {
		el.textContent = String( new Date().getFullYear() );
	} );
}

initYear();
