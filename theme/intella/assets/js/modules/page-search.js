/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

export function initPageSearch() {
	document.querySelectorAll( '[data-page-search]' ).forEach( ( root ) => {
		const input = root.querySelector( '[data-page-search-input]' );
		const list = root.querySelector( '[data-page-search-results]' );
		const status = root.querySelector( '[data-page-search-status]' );
		const data = root.querySelector( '[data-page-index]' );
		if ( ! input || ! list || ! data ) return;
		let pages = [];
		try {
			pages = JSON.parse( data.textContent );
		} catch ( e ) {
			return;
		}
		const esc = ( t ) => t.replace( /[&<>"]/g, ( c ) => ( { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[ c ] ) );
		let first = null;

		const run = () => {
			const words = input.value.trim().toLowerCase().split( /\s+/ ).filter( Boolean );
			first = null;
			if ( ! words.length ) {
				list.hidden = true;
				list.innerHTML = '';
				status.textContent = '';
				return;
			}
			const hits = pages
				.map( ( p ) => {
					const title = p.t.toLowerCase();
					const all = ( p.t + ' ' + p.d ).toLowerCase();
					if ( ! words.every( ( w ) => all.includes( w ) ) ) return null;
					return { p, score: words.filter( ( w ) => title.includes( w ) ).length * 2 + ( title.startsWith( words[ 0 ] ) ? 1 : 0 ) };
				} )
				.filter( Boolean )
				.sort( ( a, b ) => b.score - a.score )
				.slice( 0, 8 );
			list.hidden = false;
			if ( ! hits.length ) {
				list.innerHTML = '<li class="nf__no-result">No pages match that search. Try another word, or browse the sitemap.</li>';
				status.textContent = 'No pages found';
				return;
			}
			first = hits[ 0 ].p.u;
			list.innerHTML = hits.map( ( { p } ) =>
				`<li><a href="${ esc( p.u ) }"><b>${ esc( p.t ) }</b><span>${ esc( p.d ) }</span></a></li>` ).join( '' );
			status.textContent = `${ hits.length } ${ hits.length === 1 ? 'page' : 'pages' } found`;
		};

		input.addEventListener( 'input', run );
		root.querySelector( '[data-page-search-form]' )?.addEventListener( 'submit', ( event ) => {
			event.preventDefault();
			if ( first ) window.location.href = first;
		} );
	} );
}

initPageSearch();
