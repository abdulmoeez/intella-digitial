/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

/** Market builder ([data-market-builder]): ticked markets → versions, hreflang links, structure and timeline. */
export function initMarketBuilder() {
	document.querySelectorAll( '[data-market-builder]' ).forEach( ( box ) => {
		const set = ( k, v ) => { const el = box.querySelector( `[data-mb-${ k }]` ); if ( el ) el.textContent = v; };
		const update = () => {
			const picked = [ ...box.querySelectorAll( 'input[name="market"]:checked' ) ];
			const versions = picked.reduce( ( n, i ) => n + i.dataset.langs.split( ',' ).length, 0 );
			set( 'markets', picked.length );
			set( 'versions', versions );
			set( 'links', versions > 1 ? versions + 1 : 0 );
			set( 'structure', picked.length >= 6 ? 'Subfolders, with country domains for your biggest markets' : picked.length > 1 ? 'Subfolders on one domain' : 'One market: no international set-up needed yet' );
			const weeks = 6 + Math.max( 0, picked.length - 1 ) * 4;
			set( 'time', picked.length > 1 ? `First new market live in about ${ Math.min( weeks, 10 ) } weeks, all ${ picked.length } in about ${ Math.ceil( weeks / 4 ) } months` : 'Tick at least two markets' );
		};
		box.addEventListener( 'change', update );
		update();
	} );
}

initMarketBuilder();
