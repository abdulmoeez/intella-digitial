/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

export function initPopIn() {
	if ( reduceMotion || ! ( 'IntersectionObserver' in window ) ) return;
	document.querySelectorAll( '[data-pop]' ).forEach( ( group ) => {
		const items = [ ...group.querySelectorAll( '[data-pop-item]' ) ].filter( ( el ) => el.closest( '[data-pop]' ) === group );
		if ( ! items.length ) return;
		const order = items.map( ( _, i ) => i ).sort( () => Math.random() - 0.5 );
		const gap = items.length > 8 ? 0.12 : 0.28;
		order.forEach( ( k, step ) => items[ k ].style.setProperty( '--pop', ( 0.15 + step * gap ).toFixed( 2 ) + 's' ) );
		group.classList.add( 'is-waiting' );
		const observer = new IntersectionObserver( ( entries ) => {
			if ( ! entries[ 0 ].isIntersecting ) return;
			group.classList.add( 'is-in' );
			observer.disconnect();
		}, { threshold: 0.2 } );
		observer.observe( group );
	} );
}

initPopIn();
