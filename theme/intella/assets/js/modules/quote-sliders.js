/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

/** Small testimonial slider (dots, auto-rotate, pauses on hover or focus). */
export function initQuoteSliders() {
	document.querySelectorAll( '[data-quote-slider]' ).forEach( ( slider ) => {
		const slides = [ ...slider.querySelectorAll( '[data-quote-slide]' ) ];
		const dots = [ ...slider.querySelectorAll( '[data-quote-dot]' ) ];
		if ( slides.length < 2 ) return;
		let current = 0;
		let timer = null;

		const show = ( index ) => {
			current = ( index + slides.length ) % slides.length;
			slides.forEach( ( s, i ) => s.classList.toggle( 'is-active', i === current ) );
			dots.forEach( ( d, i ) => {
				d.classList.toggle( 'is-active', i === current );
				d.setAttribute( 'aria-pressed', String( i === current ) );
			} );
		};
		const stop = () => clearInterval( timer );
		const start = () => {
			if ( reduceMotion ) return;
			stop();
			timer = setInterval( () => show( current + 1 ), 6000 );
		};

		dots.forEach( ( dot ) => dot.addEventListener( 'click', () => {
			show( Number( dot.dataset.quoteDot ) );
			start();
		} ) );
		slider.addEventListener( 'mouseenter', stop );
		slider.addEventListener( 'mouseleave', start );
		slider.addEventListener( 'focusin', stop );
		slider.addEventListener( 'focusout', start );
		start();
	} );
}

initQuoteSliders();
