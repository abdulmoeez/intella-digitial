/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

/** Articles page: topic buttons, a search box and pages (data-per-page cards per page). */
export function initArticleList() {
	document.querySelectorAll( '[data-article-list]' ).forEach( ( root ) => {
		const PER = Number( root.dataset.perPage ) || 6;
		const items = [ ...root.querySelectorAll( '[data-article]' ) ];
		const buttons = [ ...root.querySelectorAll( '[data-topic]' ) ];
		const input = root.querySelector( '[data-article-search]' );
		const status = root.querySelector( '[data-article-status]' );
		const empty = root.querySelector( '[data-article-empty]' );
		const pager = root.querySelector( '[data-article-pager]' );
		let topic = '';
		let page = 1;

		const render = () => {
			const words = ( input?.value || '' ).trim().toLowerCase().split( /\s+/ ).filter( Boolean );
			const matches = items.filter( ( el ) =>
				( ! topic || el.dataset.topics.split( '|' ).includes( topic ) ) &&
				words.every( ( w ) => el.dataset.searchItem.includes( w ) ) );
			const pages = Math.max( 1, Math.ceil( matches.length / PER ) );
			page = Math.min( page, pages );
			const shown = matches.slice( ( page - 1 ) * PER, page * PER );
			items.forEach( ( el ) => { el.hidden = ! shown.includes( el ); } );
			empty.hidden = matches.length > 0;
			status.textContent = matches.length
				? `Showing ${ ( page - 1 ) * PER + 1 }–${ ( page - 1 ) * PER + shown.length } of ${ matches.length } article${ matches.length === 1 ? '' : 's' }`
				: '';
			pager.hidden = pages < 2;
			if ( pages < 2 ) return;
			const arrowL = '<svg viewBox="0 0 24 24" aria-hidden="true"><use href="#i-arrow-left"/></svg>';
			const arrowR = '<svg viewBox="0 0 24 24" aria-hidden="true"><use href="#i-arrow-right"/></svg>';
			let html = page > 1 ? `<button type="button" class="pager__link pager__prev" data-go="${ page - 1 }">${ arrowL } Previous</button>` : '';
			html += '<ol class="pager__pages" role="list">';
			for ( let n = 1; n <= pages; n++ ) {
				html += `<li><button type="button" class="pager__link" data-go="${ n }"${ n === page ? ' aria-current="page"' : '' }><span class="visually-hidden">Page </span>${ n }</button></li>`;
			}
			html += '</ol>';
			html += page < pages ? `<button type="button" class="pager__link pager__next button button--primary" data-go="${ page + 1 }">Next page ${ arrowR }</button>` : '';
			pager.innerHTML = html;
		};

		buttons.forEach( ( b ) => b.addEventListener( 'click', () => {
			topic = b.dataset.topic;
			buttons.forEach( ( x ) => x.setAttribute( 'aria-pressed', String( x === b ) ) );
			page = 1;
			render();
		} ) );
		input?.addEventListener( 'input', () => { page = 1; render(); } );
		root.querySelector( '[data-article-search-form]' )?.addEventListener( 'submit', ( e ) => e.preventDefault() );
		pager.addEventListener( 'click', ( e ) => {
			const b = e.target.closest( '[data-go]' );
			if ( ! b ) return;
			page = Number( b.dataset.go );
			render();
			root.scrollIntoView( { behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' } );
		} );
		render();
	} );
}

initArticleList();
