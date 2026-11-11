/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

export function initDeck() {
	const deck = document.querySelector( '[data-deck]' );
	if ( ! deck ) return;
	const cards = [ ...deck.querySelectorAll( '[data-deck-card]' ) ];
	if ( cards.length < 2 ) return;
	const VISIBLE = 5;
	const order = [ ...cards ]; // order[0] is the front card
	const place = () => order.forEach( ( card, i ) => {
		card.dataset.pos = i < VISIBLE ? String( i ) : 'wait';
	} );

	// Place the stack without animating, so nothing moves while the page loads.
	cards.forEach( ( card ) => {
		card.style.transition = 'none';
	} );
	deck.classList.add( 'is-stacked' );
	place();
	void deck.offsetWidth;
	cards.forEach( ( card ) => {
		card.style.transition = '';
	} );

	// Tool logos beside the front card: one set per card, popping out one after another.
	const toolSets = [ ...document.querySelectorAll( '[data-tools-set]' ) ];
	let toolsOn = null;
	const showTools = () => {
		const set = toolSets[ cards.indexOf( order[ 0 ] ) ];
		if ( ! set || set === toolsOn ) return;
		if ( toolsOn ) {
			const leaving = toolsOn;
			leaving.classList.remove( 'is-on' );
			leaving.classList.add( 'is-leaving' );
			setTimeout( () => leaving.classList.remove( 'is-leaving' ), 400 );
		}
		set.classList.add( 'is-on' );
		toolsOn = set;
	};
	showTools();
	if ( reduceMotion ) return;

	// The stack shows straight away; the first card moves shortly after the page opens.
	const START_DELAY = 800;
	const EVERY = 3500;
	const SLIDE = 800; // matches the CSS transition on .deck__card

	let paused = false;
	// Put a card straight into its place in the stack, without animating.
	const snap = ( card ) => {
		card.style.transition = 'none';
		const i = order.indexOf( card );
		card.dataset.pos = i < VISIBLE ? String( i ) : 'wait';
		void card.offsetWidth;
		card.style.transition = '';
	};
	const next = () => {
		if ( paused || document.hidden ) return;
		// If the last card to leave hasn't been moved to the back yet (slow timers), do it now.
		cards.filter( ( c ) => c.dataset.pos === 'out' ).forEach( snap );
		const front = order.shift();
		order.push( front );
		front.dataset.pos = 'out';
		order.slice( 0, -1 ).forEach( ( card, i ) => {
			card.dataset.pos = i < VISIBLE ? String( i ) : 'wait';
		} );
		showTools();
		// Once it has slid away, move it to the back, ready to come round again.
		setTimeout( () => {
			if ( front.dataset.pos === 'out' ) snap( front );
		}, SLIDE + 50 );
	};
	const start = () => setTimeout( () => {
		next();
		setInterval( next, EVERY );
	}, START_DELAY );
	// Start right away: waiting for every image to load made the first move feel late.
	start();

	deck.addEventListener( 'mouseenter', () => { paused = true; } );
	deck.addEventListener( 'mouseleave', () => { paused = false; } );
	deck.addEventListener( 'focusin', () => { paused = true; } );
	deck.addEventListener( 'focusout', () => { paused = false; } );
}

initDeck();
