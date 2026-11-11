/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

/** Live crawl window ([data-txcrawl]): counts the crawled URLs up to the total, then starts again. */
export function initTxCrawl() {
	document.querySelectorAll( '[data-txcrawl]' ).forEach( ( box ) => {
		const count = box.querySelector( '[data-txcrawl-count]' );
		const bar = box.querySelector( '[data-txcrawl-bar]' );
		const state = box.querySelector( '[data-txcrawl-state]' );
		const total = 6240;
		const set = ( n ) => {
			count.textContent = n.toLocaleString( 'en-GB' );
			bar.style.setProperty( '--p', `${ ( n / total * 100 ).toFixed( 1 ) }%` );
		};
		if ( reduceMotion ) { set( total ); state.textContent = 'Crawl complete:'; box.classList.add( 'is-done' ); return; }
		let n = 3120;
		let timer = null;
		const tick = () => {
			n = Math.min( total, n + 37 + Math.round( Math.random() * 40 ) );
			set( n );
			if ( n >= total ) {
				state.textContent = 'Crawl complete:';
				box.classList.add( 'is-done' );
				clearInterval( timer );
				timer = setTimeout( () => { n = 3120; state.textContent = 'Crawling'; box.classList.remove( 'is-done' ); start(); }, 5000 );
			}
		};
		const start = () => { clearInterval( timer ); timer = setInterval( tick, 160 ); };
		const stop = () => { clearInterval( timer ); clearTimeout( timer ); };
		new IntersectionObserver( ( entries ) => ( entries[ 0 ].isIntersecting ? start() : stop() ) ).observe( box );
	} );
}

initTxCrawl();
