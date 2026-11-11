/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

export function initTypeDoc() {
	document.querySelectorAll( '[data-typedoc]' ).forEach( ( box ) => {
		const para = box.querySelector( '[data-typedoc-text]' );
		const score = box.querySelector( '[data-typedoc-score]' );
		const scoreBox = score?.parentElement;
		const marks = [ ...box.querySelectorAll( '[data-term-at]' ) ];
		const texts = [];
		const walk = ( node ) => node.childNodes.forEach( ( c ) => ( c.nodeType === 3 ? texts.push( [ c, c.textContent ] ) : walk( c ) ) );
		walk( para );
		const total = texts.reduce( ( n, [ , t ] ) => n + t.length, 0 );
		const show = ( shown ) => {
			let left = shown;
			texts.forEach( ( [ node, full ] ) => { node.textContent = full.slice( 0, Math.max( 0, left ) ); left -= full.length; } );
			const p = shown / total;
			const s = Math.round( 42 + 45 * p );
			if ( score ) score.textContent = s;
			scoreBox?.style.setProperty( '--p', s );
			marks.forEach( ( m ) => m.classList.toggle( 'is-on', p >= parseFloat( m.dataset.termAt ) ) );
		};
		if ( reduceMotion || ! ( 'IntersectionObserver' in window ) ) { show( total ); return; }
		let n = 0;
		let timer = null;
		const tick = () => {
			n = Math.min( total, n + 2 );
			show( n );
			if ( n >= total ) {
				clearInterval( timer );
				para.classList.remove( 'is-typing' );
				timer = setTimeout( () => { n = 0; start(); }, 4500 );
			}
		};
		const start = () => { clearInterval( timer ); para.classList.add( 'is-typing' ); timer = setInterval( tick, 35 ); };
		const stop = () => { clearInterval( timer ); clearTimeout( timer ); };
		show( 0 );
		new IntersectionObserver( ( entries ) => ( entries[ 0 ].isIntersecting ? start() : stop() ) ).observe( box );
	} );
}

initTypeDoc();
