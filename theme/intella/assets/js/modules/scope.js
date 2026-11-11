/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

/** Scope picker ([data-scope]): shows the items and timeline for the chosen site size and platform. */
export function initScope() {
	const times = { s: 'About 7 working days', m: 'About 10 working days', l: '3 to 4 weeks, agreed with you' };
	document.querySelectorAll( '[data-scope]' ).forEach( ( box ) => {
		const time = box.querySelector( '[data-scope-time]' );
		const update = () => {
			const size = box.querySelector( 'input[name="size"]:checked' )?.value || 'm';
			const platform = box.querySelector( 'input[name="platform"]:checked' )?.value || 'wp';
			box.querySelectorAll( '[data-scope-size]' ).forEach( ( li ) => { li.hidden = li.dataset.scopeSize !== size; } );
			box.querySelectorAll( '[data-scope-platform]' ).forEach( ( li ) => { li.hidden = li.dataset.scopePlatform !== platform; } );
			if ( time ) time.textContent = times[ size ];
		};
		box.addEventListener( 'change', update );
		update();
	} );
}

initScope();
