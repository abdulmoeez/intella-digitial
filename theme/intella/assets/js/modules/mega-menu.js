/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

/** Mega menu: categories on the left switch the links panel on hover, focus, click or arrow keys. */
export function initMegaMenu() {
	document.querySelectorAll( '[data-mega]' ).forEach( ( mega ) => {
		const tabs = [ ...mega.querySelectorAll( '[data-mega-tab]' ) ];
		if ( ! tabs.length ) return;
		mega.classList.add( 'is-enhanced' );
		const first = tabs.find( ( t ) => t.classList.contains( 'is-active' ) ) || tabs[ 0 ];
		mega.querySelector( `[data-mega-cta="${ first.id.replace( 'mega-tab-', '' ) }"]` )?.classList.add( 'is-active' );

		const activate = ( tab, focus = false ) => {
			tabs.forEach( ( t ) => {
				const on = t === tab;
				t.classList.toggle( 'is-active', on );
				t.setAttribute( 'aria-selected', String( on ) );
				t.tabIndex = on ? 0 : -1;
				document.getElementById( t.getAttribute( 'aria-controls' ) )?.classList.toggle( 'is-active', on );
				// The free-review strip at the foot of the menu matches the service group in view.
				mega.querySelector( `[data-mega-cta="${ t.id.replace( 'mega-tab-', '' ) }"]` )?.classList.toggle( 'is-active', on );
			} );
			if ( focus ) tab.focus();
		};

		tabs.forEach( ( tab ) => {
			tab.addEventListener( 'mouseenter', () => activate( tab ) );
			tab.addEventListener( 'focus', () => activate( tab ) );
			tab.addEventListener( 'click', () => activate( tab ) );
			tab.addEventListener( 'keydown', ( event ) => {
				const i = tabs.indexOf( tab );
				const moves = { ArrowDown: i + 1, ArrowUp: i - 1, Home: 0, End: tabs.length - 1 };
				if ( ! ( event.key in moves ) ) return;
				event.preventDefault();
				activate( tabs[ ( moves[ event.key ] + tabs.length ) % tabs.length ], true );
			} );
		} );
	} );
}

initMegaMenu();
