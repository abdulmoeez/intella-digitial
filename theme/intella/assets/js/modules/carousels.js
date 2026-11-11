/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

export function initCarousels() {
	document.querySelectorAll( '[data-carousel]' ).forEach( ( root ) => {
		const track = root.querySelector( '[data-track], .svc-carousel__track' );
		const slides = [ ...root.querySelectorAll( '[data-slide]' ) ];
		const dots = [ ...root.querySelectorAll( '[data-dot]' ) ];
		if ( slides.length < 2 ) return;
		root.classList.add( 'is-ready' );
		let index = 0;
		let paused = false;

		const go = ( i ) => {
			index = ( i + slides.length ) % slides.length;
			track.style.transform = `translateX(${ -100 * index }%)`;
			slides.forEach( ( s, n ) => {
				s.setAttribute( 'aria-hidden', String( n !== index ) );
				s.inert = n !== index;
			} );
			dots.forEach( ( d, n ) => d.setAttribute( 'aria-current', String( n === index ) ) );
		};
		root.querySelector( '[data-prev]' )?.addEventListener( 'click', () => go( index - 1 ) );
		root.querySelector( '[data-next]' )?.addEventListener( 'click', () => go( index + 1 ) );
		dots.forEach( ( d ) => d.addEventListener( 'click', () => go( Number( d.dataset.dot ) ) ) );
		root.addEventListener( 'mouseenter', () => { paused = true; } );
		root.addEventListener( 'mouseleave', () => { paused = false; } );
		root.addEventListener( 'focusin', () => { paused = true; } );
		root.addEventListener( 'focusout', () => { paused = false; } );
		go( 0 );

		if ( reduceMotion ) return;
		setInterval( () => {
			// Only rotate when this carousel is on screen (its tab is open) and the page is visible.
			if ( paused || document.hidden || ! root.offsetParent ) return;
			const r = root.getBoundingClientRect();
			if ( r.bottom < 0 || r.top > window.innerHeight ) return;
			go( index + 1 );
		}, 5000 );
	} );
}

initCarousels();
