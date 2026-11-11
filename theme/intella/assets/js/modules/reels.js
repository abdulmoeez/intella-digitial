/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

export function initReels() {
	const reels = [ ...document.querySelectorAll( '[data-reel]' ) ];
	if ( ! reels.length || reduceMotion || ! ( 'IntersectionObserver' in window ) ) return;
	const EVERY = 9000;
	const base = ( reels[ 0 ].querySelector( 'source' )?.getAttribute( 'src' ) || '' ).replace( /[^/]*$/, '' );

	reels.forEach( ( reel, n ) => {
		// data-reel is a JSON list of { mp4, poster } (WordPress), or space-separated names in one folder (prototype).
		const clips = reel.dataset.reel.trim().startsWith( '[' )
			? JSON.parse( reel.dataset.reel )
			: reel.dataset.reel.split( ' ' ).map( ( name ) => ( { mp4: base + name + '.mp4', poster: base + name + '.webp' } ) );
		const first = reel.querySelector( 'video' );
		const spare = document.createElement( 'video' );
		spare.className = 'aud__clip';
		Object.assign( spare, { muted: true, loop: true, playsInline: true, preload: 'none' } );
		spare.setAttribute( 'muted', '' );
		spare.setAttribute( 'playsinline', '' );
		reel.appendChild( spare );

		let index = 0;
		let current = first;
		let visible = false;
		let timer = null;

		const swap = () => {
			if ( clips.length < 2 ) return;
			index = ( index + 1 ) % clips.length;
			const next = current === first ? spare : first;
			next.poster = clips[ index ].poster || '';
			next.src = clips[ index ].mp4;
			next.addEventListener( 'canplay', () => {
				next.play().catch( () => {} );
				next.classList.add( 'is-on' );
				current.classList.remove( 'is-on' );
				const old = current;
				current = next;
				setTimeout( () => old.pause(), 1000 );
			}, { once: true } );
			next.load();
		};

		const start = () => {
			clearTimeout( timer );
			// Stagger the tiles so they never change together.
			timer = setTimeout( function tick() {
				swap();
				timer = setTimeout( tick, EVERY );
			}, EVERY / 2 + ( n * EVERY ) / reels.length );
		};

		new IntersectionObserver( ( [ entry ] ) => {
			visible = entry.isIntersecting;
			if ( visible ) {
				if ( current.preload === 'none' ) current.preload = 'auto';
				current.play().catch( () => {} );
				start();
			} else {
				current.pause();
				clearTimeout( timer );
			}
		}, { rootMargin: '150px 0px' } ).observe( reel );
	} );
}

initReels();
