/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

/** Thank-you page: adds the visitor's first name to the greeting when we have it. */
export function initThanksName() {
	const slot = document.querySelector( '[data-thanks-name]' );
	if ( ! slot ) return;
	try {
		const name = sessionStorage.getItem( 'intella:first-name' );
		if ( name ) slot.textContent = `, ${ name }`;
	} catch ( e ) { /* keep the generic greeting */ }
}

initThanksName();
