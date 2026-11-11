/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

/** Content calculator ([data-cwcalc]): pieces a month → plan name, mix and included items. */
export function initCwCalc() {
	document.querySelectorAll( '[data-cwcalc]' ).forEach( ( box ) => {
		const range = box.querySelector( '[data-cwcalc-range]' );
		const set = ( sel, text ) => { const el = box.querySelector( sel ); if ( el ) el.textContent = text; };
		const update = () => {
			const n = parseInt( range.value, 10 );
			const plan = n <= 4 ? [ 'Starter', 'For businesses starting to publish regularly.' ]
				: n <= 10 ? [ 'Growth', 'For businesses building traffic in a competitive market.' ]
				: [ 'Scale', 'For larger sites publishing across several topics or markets.' ];
			const pages = Math.max( 1, Math.round( n / 5 ) );
			set( '[data-cwcalc-n]', n );
			set( '[data-cwcalc-plan]', plan[ 0 ] );
			set( '[data-cwcalc-for]', plan[ 1 ] );
			set( '[data-cwcalc-mix]', `About ${ n - pages } article${ n - pages === 1 ? '' : 's' } and ${ pages } service or landing page${ pages === 1 ? '' : 's' }` );
			box.querySelectorAll( '[data-cwcalc-min]' ).forEach( ( li ) => { li.hidden = n < parseInt( li.dataset.cwcalcMin, 10 ); } );
		};
		range.addEventListener( 'input', update );
		update();
	} );
}

initCwCalc();
