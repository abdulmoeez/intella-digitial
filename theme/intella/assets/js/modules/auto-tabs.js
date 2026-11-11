/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

/** Tabs that cycle on their own ([data-autotabs]) until someone clicks or focuses one. */
export function initAutoTabs() {
	if ( reduceMotion ) return;
	document.querySelectorAll( '[data-autotabs]' ).forEach( ( box ) => {
		const tabs = [ ...box.querySelectorAll( '[role="tab"]' ) ];
		let i = 0;
		let timer = null;
		const stop = () => clearInterval( timer );
		const start = () => { stop(); timer = setInterval( () => { i = ( tabs.findIndex( ( t ) => t.getAttribute( 'aria-selected' ) === 'true' ) + 1 ) % tabs.length; tabs[ i ].click(); }, 3800 ); };
		tabs.forEach( ( t ) => { t.addEventListener( 'pointerdown', () => { stop(); box.dataset.stopped = '1'; } ); t.addEventListener( 'keydown', () => { stop(); box.dataset.stopped = '1'; } ); } );
		new IntersectionObserver( ( e ) => ( e[ 0 ].isIntersecting && ! box.dataset.stopped ? start() : stop() ) ).observe( box );
	} );
}

initAutoTabs();
