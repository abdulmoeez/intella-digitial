/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

export function initReportDeck() {
	document.querySelectorAll( '[data-adeck]' ).forEach( ( deck ) => {
		const cards = [ ...deck.querySelectorAll( '[data-adeck-card]' ) ];
		const pips = [ ...deck.querySelectorAll( '[data-adeck-pip]' ) ];
		if ( cards.length < 2 ) return;
		let order = [ ...cards ];
		let busy = false;

		const place = ( skip ) => order.forEach( ( card, i ) => {
			if ( card !== skip ) card.dataset.pos = String( i );
			card.setAttribute( 'aria-hidden', String( i !== 0 ) );
		} );
		const mark = () => {
			const front = cards.indexOf( order[ 0 ] );
			pips.forEach( ( pip, i ) => {
				pip.classList.remove( 'is-active' );
				pip.setAttribute( 'aria-pressed', String( i === front ) );
			} );
			void pips[ front ].offsetWidth; // restart the progress bar
			pips[ front ].classList.add( 'is-active' );
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
				old.style.transition = 'none';
				old.dataset.pos = String( order.indexOf( old ) );
				void old.offsetWidth;
				old.style.removeProperty( 'transition' );
				busy = false;
			}, 600 );
		};

		place();
		mark();
		pips.forEach( ( pip, i ) => {
			pip.addEventListener( 'click', () => bringToFront( cards[ i ] ) );
			pip.addEventListener( 'animationend', () => {
				if ( pip.classList.contains( 'is-active' ) ) bringToFront( order[ 1 ] );
			} );
		} );

		let hover = false;
		let focus = false;
		let offscreen = false;
		const sync = () => deck.classList.toggle( 'is-paused', hover || focus || offscreen || document.hidden );
		deck.addEventListener( 'mouseenter', () => { hover = true; sync(); } );
		deck.addEventListener( 'mouseleave', () => { hover = false; sync(); } );
		deck.addEventListener( 'focusin', () => { focus = true; sync(); } );
		deck.addEventListener( 'focusout', () => { focus = false; sync(); } );
		document.addEventListener( 'visibilitychange', sync );
		if ( 'IntersectionObserver' in window ) {
			new IntersectionObserver( ( entries ) => {
				offscreen = ! entries[ 0 ].isIntersecting;
				sync();
			}, { threshold: 0.3 } ).observe( deck );
		}
	} );
}

initReportDeck();
