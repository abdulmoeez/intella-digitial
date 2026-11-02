/**
 * Forms with a data-confirm message ask before they submit (e.g. "Remove this admin?").
 */
document.addEventListener( 'submit', ( event ) => {
	const message = event.target.closest( 'form[data-confirm]' )?.dataset.confirm;
	if ( message && ! window.confirm( message ) ) {
		event.preventDefault();
	}
} );
