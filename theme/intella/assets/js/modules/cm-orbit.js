/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

export function initCmOrbit() {
	document.querySelectorAll( '[data-orbit]' ).forEach( ( root ) => {
		const nodes = [ ...root.querySelectorAll( '[data-orbit-node]' ) ];
		const slides = [ ...root.querySelectorAll( '[data-orbit-slide]' ) ];
		const n = nodes.length;
		if ( n < 2 ) return;
		let step = 0; // keeps counting up, so the ring always turns the same way
		let paused = false;
		let seen = false;
		const show = () => {
			const i = ( ( step % n ) + n ) % n;
			root.style.setProperty( '--turn', `${ -step * 360 / n }deg` );
			nodes.forEach( ( b, k ) => b.toggleAttribute( 'aria-current', k === i ) );
			slides.forEach( ( s, k ) => s.classList.toggle( 'is-on', k === i ) );
		};
		nodes.forEach( ( b, k ) => b.addEventListener( 'click', () => {
			step += ( ( k - step ) % n + n ) % n;
			paused = true;
			show();
		} ) );
		root.addEventListener( 'mouseenter', () => { paused = true; } );
		root.addEventListener( 'mouseleave', () => { paused = false; } );
		root.classList.add( 'is-ready' );
		show();
		if ( reduceMotion ) return;
		if ( 'IntersectionObserver' in window ) new IntersectionObserver( ( e ) => { seen = e[ 0 ].isIntersecting; } ).observe( root );
		else seen = true;
		setInterval( () => {
			if ( paused || ! seen || document.hidden ) return;
			step += 1;
			show();
		}, 3400 );
	} );
}

initCmOrbit();
