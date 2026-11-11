/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

/** Contact form: a link like contact.html?goal=... fills in the message so the visitor only adds details. */
export function initGoalPrefill() {
	const field = document.getElementById( 'contact-message' );
	const goal = new URLSearchParams( location.search ).get( 'goal' );
	if ( ! field || ! goal || field.value ) return;
	field.value = goal.slice( 0, 300 ) + ' ';
}

initGoalPrefill();
