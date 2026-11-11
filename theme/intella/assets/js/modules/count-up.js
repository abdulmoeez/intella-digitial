/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

/** Numbers count up from zero the first time they scroll into view. */
export function initCountUp() {
	const items = [ ...document.querySelectorAll( '[data-count]' ) ];
	if ( ! items.length || reduceMotion || ! ( 'IntersectionObserver' in window ) ) return;
	const run = ( el ) => {
		const end = parseFloat( el.dataset.count );
		const decimals = ( el.dataset.count.split( '.' )[ 1 ] || '' ).length;
		const startTime = performance.now();
		const tick = ( now ) => {
			const t = Math.min( 1, ( now - startTime ) / 1400 );
			const eased = 1 - Math.pow( 1 - t, 3 );
			el.textContent = ( end * eased ).toFixed( decimals );
			if ( t < 1 ) requestAnimationFrame( tick );
		};
		requestAnimationFrame( tick );
	};
	const observer = new IntersectionObserver( ( entries ) => {
		entries.forEach( ( entry ) => {
			if ( ! entry.isIntersecting ) return;
			observer.unobserve( entry.target );
			run( entry.target );
		} );
	}, { threshold: 0.6 } );
	items.forEach( ( el ) => observer.observe( el ) );
}

initCountUp();
