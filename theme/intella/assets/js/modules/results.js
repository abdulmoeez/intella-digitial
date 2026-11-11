/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

export function initResults() {
	const list = document.querySelector( '[data-results]' );
	if ( ! list ) return;
	const cards = [ ...list.children ];
	const SIZE = 4;
	if ( cards.length <= SIZE ) return;
	const EVERY = 6000;
	const sets = Math.ceil( cards.length / SIZE );
	let current = 0;
	let timer = null;
	let paused = false;
	let visible = false;
	let busy = false;

	const setOf = ( n ) => cards.slice( n * SIZE, n * SIZE + SIZE );

	// Every card gets the height of the tallest, so the block never jumps.
	const measure = () => {
		list.classList.remove( 'is-dealt' );
		list.style.removeProperty( '--card-h' );
		const tallest = Math.max( ...cards.map( ( c ) => c.offsetHeight ) );
		list.style.setProperty( '--card-h', tallest + 'px' );
		list.classList.add( 'is-dealt' );
	};

	const show = ( n ) => {
		cards.forEach( ( c ) => c.classList.remove( 'is-on', 'is-leaving', 'is-dealing' ) );
		setOf( n ).forEach( ( c, i ) => {
			c.style.setProperty( '--slot', i );
			c.classList.add( 'is-on' );
		} );
	};

	const next = () => {
		if ( busy ) return;
		const nextSet = ( current + 1 ) % sets;
		if ( reduceMotion ) {
			current = nextSet;
			show( current );
			return;
		}
		busy = true;
		const leaving = setOf( current );
		leaving.forEach( ( c ) => c.classList.add( 'is-leaving' ) );
		window.setTimeout( () => {
			current = nextSet;
			show( current );
			setOf( current ).forEach( ( c ) => c.classList.add( 'is-dealing' ) );
			window.setTimeout( () => {
				setOf( current ).forEach( ( c ) => c.classList.remove( 'is-dealing' ) );
				busy = false;
			}, 900 );
		}, 420 + 60 * ( SIZE - 1 ) );
	};

	const sync = () => {
		window.clearInterval( timer );
		timer = null;
		if ( visible && ! paused ) timer = window.setInterval( next, EVERY );
	};

	measure();
	show( 0 );
	let resizeTimer;
	window.addEventListener( 'resize', () => {
		window.clearTimeout( resizeTimer );
		resizeTimer = window.setTimeout( () => {
			measure();
			show( current );
		}, 200 );
	} );
	list.addEventListener( 'mouseenter', () => { paused = true; sync(); } );
	list.addEventListener( 'mouseleave', () => { paused = false; sync(); } );
	list.addEventListener( 'focusin', () => { paused = true; sync(); } );
	list.addEventListener( 'focusout', () => { paused = false; sync(); } );
	if ( 'IntersectionObserver' in window ) {
		new IntersectionObserver( ( entries ) => {
			visible = entries[ 0 ].isIntersecting;
			sync();
		}, { threshold: 0.3 } ).observe( list );
	} else {
		visible = true;
		sync();
	}
}

initResults();
