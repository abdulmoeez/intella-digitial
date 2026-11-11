/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

export function initCaseStack() {
	const stack = document.querySelector( '[data-cs-stack]' );
	if ( ! stack ) return;
	const wrap = stack.parentElement;
	const cards = [ ...stack.querySelectorAll( '[data-cs-card]' ) ];
	const pips = [ ...wrap.querySelectorAll( '[data-cs-pip]' ) ];
	let order = [ ...cards ];
	let busy = false;

	const place = ( skip ) => order.forEach( ( card, i ) => {
		if ( card !== skip ) card.dataset.pos = String( i );
	} );
	const mark = () => {
		const front = cards.indexOf( order[ 0 ] );
		cards.forEach( ( card, i ) => {
			card.tabIndex = i === front ? 0 : -1;
			card.setAttribute( 'aria-hidden', String( i !== front ) );
		} );
		pips.forEach( ( pip, i ) => {
			pip.classList.remove( 'is-active' );
			pip.setAttribute( 'aria-current', String( i === front ) );
		} );
		const pip = pips[ front ];
		if ( pip ) {
			void pip.offsetWidth; // restart the progress bar
			pip.classList.add( 'is-active' );
		}
	};
	const bringToFront = ( card ) => {
		const k = order.indexOf( card );
		if ( k <= 0 || busy ) return;
		const old = order[ 0 ];
		order = order.slice( k ).concat( order.slice( 0, k ) );
		if ( reduceMotion ) {
			place();
			mark();
			return;
		}
		busy = true;
		old.dataset.pos = 'out';
		place( old );
		mark();
		setTimeout( () => {
			old.dataset.pos = String( order.indexOf( old ) );
			busy = false;
		}, 480 );
	};

	place();
	mark();

	cards.forEach( ( card ) => card.addEventListener( 'click', ( event ) => {
		if ( card !== order[ 0 ] ) {
			event.preventDefault();
			bringToFront( card );
		}
	} ) );
	pips.forEach( ( pip, i ) => {
		pip.addEventListener( 'click', () => bringToFront( cards[ i ] ) );
		pip.addEventListener( 'animationend', () => {
			if ( pip.classList.contains( 'is-active' ) ) bringToFront( order[ 1 ] );
		} );
	} );

	const pause = ( on ) => wrap.classList.toggle( 'is-paused', on );
	let hover = false;
	let focus = false;
	let offscreen = false;
	const sync = () => pause( hover || focus || offscreen || document.hidden );
	wrap.addEventListener( 'mouseenter', () => { hover = true; sync(); } );
	wrap.addEventListener( 'mouseleave', () => { hover = false; sync(); } );
	wrap.addEventListener( 'focusin', () => { focus = true; sync(); } );
	wrap.addEventListener( 'focusout', () => { focus = false; sync(); } );
	document.addEventListener( 'visibilitychange', sync );
	if ( 'IntersectionObserver' in window ) {
		new IntersectionObserver( ( entries ) => {
			offscreen = ! entries[ 0 ].isIntersecting;
			sync();
		}, { threshold: 0.3 } ).observe( stack );
	}
}

initCaseStack();
