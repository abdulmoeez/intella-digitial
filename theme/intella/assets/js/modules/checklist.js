/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

/** Checklist ([data-checklist]): counts ticked items, fills the bar and gives a short verdict. */
export function initChecklist() {
	document.querySelectorAll( '[data-checklist]' ).forEach( ( box ) => {
		const items = [ ...box.querySelectorAll( '[data-check-item]' ) ];
		const count = box.querySelector( '[data-check-count]' );
		const bar = box.querySelector( '[data-check-bar]' );
		const verdict = box.querySelector( '[data-check-verdict]' );
		const update = () => {
			const done = items.filter( ( i ) => i.checked ).length;
			const share = done / items.length;
			count.textContent = done;
			// Per-tab counts, e.g. "Settings 2/6"
			box.querySelectorAll( '[data-check-group]' ).forEach( ( group ) => {
				const boxes = [ ...group.querySelectorAll( '[data-check-item]' ) ];
				const label = box.querySelector( `[data-check-tab="${ group.dataset.checkGroup }"]` );
				if ( label ) label.textContent = `${ boxes.filter( ( i ) => i.checked ).length }/${ boxes.length }`;
			} );
			bar.style.setProperty( '--p', `${ ( share * 100 ).toFixed( 1 ) }%` );
			verdict.textContent = done === 0 ? 'Tick what your site already does'
				: share < 0.5 ? 'Plenty to fix: a WordPress SEO review would pay off quickly'
				: share < 0.85 ? 'A good base, with clear gaps to close'
				: 'In great shape. We’d look at content and links next';
		};
		box.addEventListener( 'change', update );
		update();
	} );
}

initChecklist();
