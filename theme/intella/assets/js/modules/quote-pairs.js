/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

export function initQuotePairs() {
	document.querySelectorAll( '[data-quote-pairs]' ).forEach( ( strip ) => {
		const cards = [ ...strip.querySelectorAll( '[data-quote-pair-card]' ) ];
		const dots = [ ...strip.querySelectorAll( '[data-quote-pair-dot]' ) ];
		const pages = Math.ceil( cards.length / 2 );
		const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		let page = 0;
		let timer;

		const show = ( next ) => {
			page = ( next + pages ) % pages;
			cards.forEach( ( card, i ) => {
				const on = Math.floor( i / 2 ) === page;
				card.hidden = ! on;
				card.classList.remove( 'is-entering' );
				if ( on ) {
					card.style.setProperty( '--slot', i % 2 );
					void card.offsetWidth;
					card.classList.add( 'is-entering' );
				}
			} );
			dots.forEach( ( dot, i ) => dot.setAttribute( 'aria-current', i === page ? 'true' : 'false' ) );
		};

		const stop = () => clearInterval( timer );
		const start = () => {
			stop();
			if ( ! reduceMotion ) timer = setInterval( () => show( page + 1 ), 7000 );
		};

		dots.forEach( ( dot, i ) => dot.addEventListener( 'click', () => { show( i ); start(); } ) );
		strip.addEventListener( 'mouseenter', stop );
		strip.addEventListener( 'mouseleave', start );
		strip.addEventListener( 'focusin', stop );
		strip.addEventListener( 'focusout', start );
		start();
	} );
}

initQuotePairs();
