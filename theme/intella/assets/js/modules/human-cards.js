/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

/** Contact cards about how we work: rotate every 8 seconds, pause on hover or focus. */
export function initHumanCards() {
	const root = document.querySelector( '[data-human]' );
	if ( ! root ) return;
	const cards = [ ...root.querySelectorAll( '[data-human-card]' ) ];
	const pips = [ ...root.querySelectorAll( '[data-human-dot]' ) ];
	let current = 0;
	let timer = null;

	const show = ( index ) => {
		current = ( index + cards.length ) % cards.length;
		cards.forEach( ( card, i ) => {
			card.classList.toggle( 'is-on', i === current );
			card.setAttribute( 'aria-hidden', String( i !== current ) );
		} );
		pips.forEach( ( pip, i ) => {
			pip.classList.toggle( 'is-on', i === current );
			pip.setAttribute( 'aria-current', String( i === current ) );
		} );
	};
	const start = () => {
		clearInterval( timer );
		if ( ! reduceMotion ) timer = setInterval( () => show( current + 1 ), 8000 );
	};

	pips.forEach( ( pip ) => pip.addEventListener( 'click', () => {
		show( Number( pip.dataset.humanDot ) );
		start();
	} ) );
	root.addEventListener( 'mouseenter', () => clearInterval( timer ) );
	root.addEventListener( 'mouseleave', start );
	root.addEventListener( 'focusin', () => clearInterval( timer ) );
	root.addEventListener( 'focusout', start );
	start();
}

initHumanCards();
