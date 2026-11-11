/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

/** Client logo wall ([data-client-wall]): a tile opens its story in a panel in the middle of the wall. */
export function initClientWall() {
	document.querySelectorAll( '[data-client-wall]' ).forEach( ( wall ) => {
		const tiles = [ ...wall.querySelectorAll( '[data-story]' ) ];
		const detail = wall.querySelector( '.bxwall__detail' );
		const stories = [ ...wall.querySelectorAll( '[data-story-panel]' ) ];
		if ( ! tiles.length || ! detail ) return;
		wall.classList.add( 'is-enhanced' );
		let current = 0;
		// The panel always sits in the middle of the wall, after the first half of the tiles,
		// with its arrow pointing up or down at the selected tile.
		const middle = tiles[ Math.ceil( tiles.length / 2 ) - 1 ].parentElement;
		const place = () => {
			middle.after( detail );
			const below = tiles.indexOf( tiles[ current ] ) >= Math.ceil( tiles.length / 2 );
			detail.classList.toggle( 'is-below', below );
			const t = tiles[ current ].getBoundingClientRect(), d = detail.getBoundingClientRect();
			detail.style.setProperty( '--x', `${ t.left - d.left + t.width / 2 }px` );
		};
		const open = ( i, focus = false ) => {
			current = ( i + tiles.length ) % tiles.length;
			tiles.forEach( ( t, k ) => t.setAttribute( 'aria-expanded', String( k === current ) ) );
			stories.forEach( ( s, k ) => { s.hidden = k !== current; } );
			place();
			if ( focus ) tiles[ current ].focus( { preventScroll: true } );
		};
		tiles.forEach( ( t, i ) => t.addEventListener( 'click', () => open( i ) ) );
		detail.addEventListener( 'click', ( e ) => {
			const b = e.target.closest( '[data-story-step]' );
			if ( b ) open( current + Number( b.dataset.storyStep ) );
		} );
		let rt;
		window.addEventListener( 'resize', () => { clearTimeout( rt ); rt = setTimeout( place, 150 ); } );
		open( 0 );
	} );
}

initClientWall();
