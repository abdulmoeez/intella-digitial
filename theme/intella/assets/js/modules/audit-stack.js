/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

export function initAuditStack() {
	document.querySelectorAll( '[data-astack]' ).forEach( ( stack ) => {
		const cards = [ ...stack.querySelectorAll( '[data-astack-card]' ) ];
		if ( cards.length < 2 ) return;
		stack.classList.add( 'is-on' );
		const view = stack.querySelector( '.astack__view' );
		const step = () => window.innerHeight * 0.7;
		const pinTop = () => parseFloat( getComputedStyle( view ).top ) || 0;
		let current = -1;

		const show = ( n ) => {
			if ( n === current ) return;
			current = n;
			cards.forEach( ( card, i ) => {
				const state = i < n ? 'past' : i === n ? 'active' : 'next';
				card.dataset.state = state;
				const bar = card.querySelector( '[data-astack-bar]' );
				bar.tabIndex = state === 'past' ? 0 : -1;
				if ( state === 'past' ) bar.setAttribute( 'aria-label', 'Open: ' + bar.querySelector( 'h3' ).textContent );
				else bar.removeAttribute( 'aria-label' );
			} );
		};
		const update = () => {
			const progress = ( pinTop() - stack.getBoundingClientRect().top ) / step();
			show( Math.max( 0, Math.min( cards.length - 1, Math.round( progress ) ) ) );
		};
		let ticking = false;
		window.addEventListener( 'scroll', () => {
			if ( ticking ) return;
			ticking = true;
			requestAnimationFrame( () => { update(); ticking = false; } );
		}, { passive: true } );
		window.addEventListener( 'resize', update );
		cards.forEach( ( card, i ) => card.querySelector( '[data-astack-bar]' ).addEventListener( 'click', () => {
			if ( card.dataset.state !== 'past' ) return;
			const y = stack.getBoundingClientRect().top + window.scrollY - pinTop() + i * step();
			window.scrollTo( { top: y, behavior: reduceMotion ? 'auto' : 'smooth' } );
		} ) );
		update();
		requestAnimationFrame( () => requestAnimationFrame( () => stack.classList.add( 'is-ready' ) ) );
	} );
}

initAuditStack();
