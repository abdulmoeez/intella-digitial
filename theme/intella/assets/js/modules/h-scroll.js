/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

/** Horizontal deck ([data-hscroll]): arrow buttons scroll one card, the bar shows how far along you are. */
export function initHScroll() {
	document.querySelectorAll( '[data-hscroll]' ).forEach( ( deck ) => {
		const track = deck.querySelector( '[data-hscroll-track]' );
		const scope = deck.closest( 'section' ) || deck;
		const prev = scope.querySelector( '[data-hscroll-prev]' );
		const next = scope.querySelector( '[data-hscroll-next]' );
		const bar = deck.querySelector( '[data-hscroll-bar]' );
		const step = () => {
			const card = track.querySelector( '[data-hscroll-item]' );
			return card ? card.getBoundingClientRect().width + parseFloat( getComputedStyle( track ).columnGap || 0 ) : track.clientWidth;
		};
		const update = () => {
			const max = track.scrollWidth - track.clientWidth;
			if ( bar ) bar.style.setProperty( '--p', `${ ( ( track.scrollLeft + track.clientWidth ) / track.scrollWidth * 100 ).toFixed( 1 ) }%` );
			if ( prev ) prev.disabled = track.scrollLeft <= 2;
			if ( next ) next.disabled = track.scrollLeft >= max - 2;
		};
		const go = ( dir ) => track.scrollBy( { left: dir * step(), behavior: reduceMotion ? 'auto' : 'smooth' } );
		prev?.addEventListener( 'click', () => go( -1 ) );
		next?.addEventListener( 'click', () => go( 1 ) );
		track.addEventListener( 'keydown', ( event ) => {
			if ( event.key === 'ArrowRight' ) { event.preventDefault(); go( 1 ); }
			if ( event.key === 'ArrowLeft' ) { event.preventDefault(); go( -1 ); }
		} );
		track.addEventListener( 'scroll', update, { passive: true } );
		window.addEventListener( 'resize', update );
		update();
	} );
}

initHScroll();
