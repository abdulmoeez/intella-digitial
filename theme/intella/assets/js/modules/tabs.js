/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

export function initTabs() {
	document.querySelectorAll( '[data-tabs]' ).forEach( ( root ) => {
		// Only this root's own tabs, so tab sets can sit inside other tab panels.
		const own = ( el ) => el.closest( '[data-tabs]' ) === root;
		const tabs = [ ...root.querySelectorAll( '[role="tab"]' ) ].filter( own );
		if ( ! tabs.length ) return;
		const vertical = [ ...root.querySelectorAll( '[role="tablist"]' ) ].find( own )?.getAttribute( 'aria-orientation' ) === 'vertical';
		root.classList.add( 'is-enhanced' );

		const activate = ( tab, focus = false ) => {
			tabs.forEach( ( t ) => {
				const on = t === tab;
				t.setAttribute( 'aria-selected', String( on ) );
				t.tabIndex = on ? 0 : -1;
				const panel = document.getElementById( t.getAttribute( 'aria-controls' ) );
				if ( panel ) panel.hidden = ! on;
			} );
			if ( focus ) tab.focus();
			// Keep the active tab in view when the tab list scrolls sideways (phones), without moving the page.
			const list = tab.parentElement;
			if ( list.scrollWidth > list.clientWidth + 1 ) {
				list.scrollTo( { left: tab.offsetLeft - ( list.clientWidth - tab.offsetWidth ) / 2, behavior: 'smooth' } );
			}
		};

		tabs.forEach( ( tab ) => {
			tab.addEventListener( 'click', () => activate( tab ) );
			tab.addEventListener( 'keydown', ( event ) => {
				const i = tabs.indexOf( tab );
				const next = vertical ? 'ArrowDown' : 'ArrowRight';
				const prev = vertical ? 'ArrowUp' : 'ArrowLeft';
				const moves = { [ next ]: i + 1, [ prev ]: i - 1, Home: 0, End: tabs.length - 1 };
				if ( ! ( event.key in moves ) ) return;
				event.preventDefault();
				activate( tabs[ ( moves[ event.key ] + tabs.length ) % tabs.length ], true );
			} );
		} );

		// A link like services.html#seo opens the tab whose panel has that id.
		const tabForHash = () => location.hash.length > 1 && tabs.find( ( t ) => '#' + t.getAttribute( 'aria-controls' ) === decodeURIComponent( location.hash ) );
		window.addEventListener( 'hashchange', () => {
			const t = tabForHash();
			if ( ! t ) return;
			activate( t );
			root.scrollIntoView( { behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' } );
		} );

		// Start on the tab from the address, or the one marked selected in the HTML (or the first one).
		const hashTab = tabForHash();
		if ( hashTab ) requestAnimationFrame( () => root.scrollIntoView( { block: 'start' } ) );
		const first = hashTab || tabs.find( ( t ) => t.getAttribute( 'aria-selected' ) === 'true' ) || tabs[ 0 ];
		tabs.forEach( ( t ) => {
			const on = t === first;
			t.setAttribute( 'aria-selected', String( on ) );
			t.tabIndex = on ? 0 : -1;
			const panel = document.getElementById( t.getAttribute( 'aria-controls' ) );
			if ( panel ) panel.hidden = ! on;
		} );
		// Bring the tab opened from the address into view in the tab row (phones).
		if ( hashTab ) activate( hashTab );
	} );
}

initTabs();
