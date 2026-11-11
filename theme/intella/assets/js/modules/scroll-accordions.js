/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

export function initScrollAccordions() {
	var lists = document.querySelectorAll( '[data-acc]' );
	if ( ! lists.length ) { return; }
	var groups = [];
	lists.forEach( function ( list ) {
		list.classList.add( 'acc--js' );
		var items = Array.prototype.slice.call( list.querySelectorAll( ':scope > .js-acc' ) );
		items.forEach( function ( li ) {
			li.querySelector( '.js-acc-btn' ).addEventListener( 'click', function () {
				li.dataset.touched = '1';
				setOpen( li, ! li.classList.contains( 'is-open' ) );
			} );
		} );
		groups.push( { items: items, last: 0 } );
	} );
	function setOpen( li, on ) {
		li.classList.toggle( 'is-open', on );
		li.querySelector( '.js-acc-btn' ).setAttribute( 'aria-expanded', on ? 'true' : 'false' );
	}
	var GAP = 650; // ms between two items opening
	var ticking = false;
	function check() {
		ticking = false;
		var now = Date.now(), line = window.innerHeight * 0.62, waiting = false;
		groups.forEach( function ( g ) {
			for ( var i = 1; i < g.items.length; i++ ) {
				var li = g.items[ i ];
				if ( li.classList.contains( 'is-open' ) || li.dataset.touched || li.dataset.auto ) { continue; }
				if ( li.getBoundingClientRect().top > line ) { break; }
				if ( now - g.last < GAP ) { waiting = true; break; }
				li.dataset.auto = '1'; g.last = now; setOpen( li, true ); waiting = true;
				break;
			}
		} );
		if ( waiting ) { setTimeout( check, GAP + 20 ); }
	}
	window.addEventListener( 'scroll', function () { if ( ! ticking ) { ticking = true; requestAnimationFrame( check ); } }, { passive: true } );
	check();
}

initScrollAccordions();
