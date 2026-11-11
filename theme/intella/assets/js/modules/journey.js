/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

export function initJourney() {
	const root = document.querySelector( '[data-journey]' );
	if ( ! root ) return;
	const rail = root.querySelector( '[role="tablist"]' );
	const tabs = [ ...root.querySelectorAll( '[role="tab"]' ) ];
	const EVERY = 7000;
	let current = 0;
	let timer = null;
	let visible = false;
	let paused = false;

	const show = ( index, focus = false ) => {
		current = ( index + tabs.length ) % tabs.length;
		tabs.forEach( ( tab, i ) => {
			const on = i === current;
			tab.setAttribute( 'aria-selected', String( on ) );
			tab.tabIndex = on ? 0 : -1;
			tab.classList.toggle( 'is-reached', i <= current );
			const card = document.getElementById( tab.getAttribute( 'aria-controls' ) );
			card.classList.toggle( 'is-current', on );
			card.inert = ! on;
			card.setAttribute( 'aria-hidden', String( ! on ) );
		} );
		rail.style.setProperty( '--p', String( current / ( tabs.length - 1 ) ) );
		if ( focus ) tabs[ current ].focus();
	};

	const schedule = () => {
		clearTimeout( timer );
		if ( reduceMotion || ! visible || paused ) return;
		timer = setTimeout( () => {
			show( current + 1 );
			schedule();
		}, EVERY );
	};

	tabs.forEach( ( tab, i ) => {
		tab.addEventListener( 'click', () => {
			show( i );
			schedule();
		} );
		tab.addEventListener( 'keydown', ( event ) => {
			const moves = { ArrowRight: i + 1, ArrowLeft: i - 1, Home: 0, End: tabs.length - 1 };
			if ( ! ( event.key in moves ) ) return;
			event.preventDefault();
			show( moves[ event.key ], true );
		} );
	} );

	root.addEventListener( 'mouseenter', () => { paused = true; schedule(); } );
	root.addEventListener( 'mouseleave', () => { paused = false; schedule(); } );
	root.addEventListener( 'focusin', () => { paused = true; schedule(); } );
	root.addEventListener( 'focusout', () => { paused = false; schedule(); } );

	if ( 'IntersectionObserver' in window ) {
		new IntersectionObserver( ( [ entry ] ) => {
			visible = entry.isIntersecting;
			schedule();
		}, { threshold: 0.4 } ).observe( root );
	}
	show( 0 );
}

initJourney();
