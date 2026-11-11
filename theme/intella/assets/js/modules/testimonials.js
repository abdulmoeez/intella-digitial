/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

/** Testimonial slider: progress bars, arrows, autoplay that pauses on hover or focus. */
export function initTestimonials() {
	const root = document.querySelector( '[data-testimonials]' );
	if ( ! root ) return;

	const slides = [ ...root.querySelectorAll( '[data-slide]' ) ];
	const tracks = [ ...root.querySelectorAll( '[data-slide-to]' ) ];
	const counter = root.querySelector( '[data-slide-current]' );
	// The small quote cards below change in sets of three with each slide.
	const quotes = [ ...root.querySelectorAll( '[data-quote]' ) ];
	const looks = [ 'quote-card--dark', '', 'quote-card--lavender' ];
	const sets = Math.max( 1, Math.ceil( quotes.length / 3 ) );
	const showQuotes = ( index ) => {
		const first = ( index % sets ) * 3;
		quotes.forEach( ( card, i ) => {
			const slot = i - first;
			const visible = slot >= 0 && slot < 3;
			card.hidden = ! visible;
			card.classList.remove( 'quote-card--dark', 'quote-card--lavender', 'is-entering' );
			if ( ! visible ) return;
			if ( looks[ slot ] ) card.classList.add( looks[ slot ] );
			card.style.setProperty( '--slot', slot );
			void card.offsetWidth;
			card.classList.add( 'is-entering' );
		} );
	};
	const duration = 7000;
	let current = 0;
	let timer = null;

	root.style.setProperty( '--slide-duration', `${ duration }ms` );

	const show = ( index ) => {
		const before = current;
		current = ( index + slides.length ) % slides.length;
		const changed = before !== current;
		slides.forEach( ( slide, i ) => slide.classList.toggle( 'is-active', i === current ) );
		tracks.forEach( ( track, i ) => {
			track.classList.remove( 'is-active' );
			track.classList.toggle( 'is-done', i < current );
			track.setAttribute( 'aria-current', i === current ? 'true' : 'false' );
		} );
		// Restart the fill animation on the active bar.
		void tracks[ current ]?.offsetWidth;
		tracks[ current ]?.classList.add( 'is-active' );
		if ( counter ) counter.textContent = String( current + 1 ).padStart( 2, '0' );
		if ( quotes.length > 3 && changed ) showQuotes( current );
	};

	const stop = () => {
		clearInterval( timer );
		timer = null;
	};

	const start = () => {
		if ( reduceMotion ) return;
		stop();
		timer = setInterval( () => show( current + 1 ), duration );
	};

	root.addEventListener( 'click', ( event ) => {
		const target = event.target.closest( '[data-slide-to], [data-slide-prev], [data-slide-next]' );
		if ( ! target ) return;
		if ( target.hasAttribute( 'data-slide-prev' ) ) show( current - 1 );
		else if ( target.hasAttribute( 'data-slide-next' ) ) show( current + 1 );
		else show( Number( target.dataset.slideTo ) );
		start();
	} );

	const pause = () => {
		root.classList.add( 'is-paused' );
		stop();
	};
	const resume = () => {
		root.classList.remove( 'is-paused' );
		show( current );
		start();
	};
	root.addEventListener( 'mouseenter', pause );
	root.addEventListener( 'mouseleave', resume );
	root.addEventListener( 'focusin', pause );
	root.addEventListener( 'focusout', ( event ) => {
		if ( ! root.contains( event.relatedTarget ) ) resume();
	} );

	if ( reduceMotion ) root.classList.add( 'is-static' );

	// Only run the timer while the section is on screen.
	new IntersectionObserver( ( [ entry ] ) => {
		if ( entry.isIntersecting && ! root.classList.contains( 'is-paused' ) ) resume();
		else stop();
	} ).observe( root );

	show( 0 );
}

initTestimonials();
