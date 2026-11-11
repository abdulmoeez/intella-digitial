/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

/** Before/after slider ([data-compare], WordPress SEO hero): the range input sets how much of "before" shows. */
export function initCompare() {
	document.querySelectorAll( '[data-compare]' ).forEach( ( box ) => {
		const range = box.querySelector( '[data-compare-range]' );
		const set = () => box.style.setProperty( '--pos', `${ range.value }%` );
		range.addEventListener( 'input', () => { box.dataset.touched = '1'; set(); } );
		set();
		// One gentle sweep the first time it scrolls into view, so both sides are seen.
		if ( reduceMotion || ! ( 'IntersectionObserver' in window ) ) return;
		const observer = new IntersectionObserver( ( entries ) => {
			if ( ! entries[ 0 ].isIntersecting ) return;
			observer.disconnect();
			const keys = [ [ 0, 50 ], [ 900, 88 ], [ 2100, 12 ], [ 2900, 50 ] ];
			const start = performance.now();
			const step = ( now ) => {
				if ( box.dataset.touched ) return;
				const t = now - start;
				const i = keys.findIndex( ( k ) => k[ 0 ] > t );
				if ( i === -1 ) { range.value = 50; set(); return; }
				const [ t0, v0 ] = keys[ i - 1 ];
				const [ t1, v1 ] = keys[ i ];
				const e = ( t - t0 ) / ( t1 - t0 );
				range.value = v0 + ( v1 - v0 ) * ( e < 0.5 ? 2 * e * e : 1 - Math.pow( -2 * e + 2, 2 ) / 2 );
				set();
				requestAnimationFrame( step );
			};
			setTimeout( () => requestAnimationFrame( step ), 500 );
		}, { threshold: 0.5 } );
		observer.observe( box );
	} );
}

initCompare();
