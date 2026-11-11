/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

/** B2B SEO ROI calculator ([data-roi-calc]): visitors, demo rate, close rate and deal size give demos, customers and revenue. */
export function initRoiCalc() {
	document.querySelectorAll( '[data-roi-calc]' ).forEach( ( box ) => {
		const val = ( k ) => Number( box.querySelector( `[data-roi="${ k }"]` ).value );
		const out = ( k, v ) => { const el = box.querySelector( `[data-roi-out="${ k }"]` ); if ( el ) el.textContent = v; };
		const money = ( n ) => n >= 1e6 ? `$${ ( n / 1e6 ).toFixed( 1 ) }M` : n >= 1e3 ? `$${ Math.round( n / 1e3 ) }K` : `$${ Math.round( n ) }`;
		const update = () => {
			box.querySelectorAll( '[data-roi]' ).forEach( ( input ) => {
				const show = box.querySelector( `[data-roi-show="${ input.dataset.roi }"]` );
				const n = Number( input.value ), u = input.dataset.unit;
				if ( show ) show.textContent = u === '$' ? `$${ n.toLocaleString( 'en-US' ) }` : u === '%' ? `${ n }%` : n.toLocaleString( 'en-US' );
			} );
			const demos = val( 'visits' ) * val( 'demo' ) / 100;
			const customers = demos * 12 * val( 'close' ) / 100;
			out( 'demos', Math.round( demos ).toLocaleString( 'en-US' ) );
			out( 'customers', Math.round( customers ).toLocaleString( 'en-US' ) );
			out( 'revenue', money( customers * val( 'acv' ) ) );
		};
		box.addEventListener( 'input', update );
		update();
	} );
}

initRoiCalc();
