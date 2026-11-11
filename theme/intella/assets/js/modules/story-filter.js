/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

export function initStoryFilter() {
	const bar = document.querySelector( '[data-story-filter]' );
	const results = document.querySelector( '[data-story-results]' );
	if ( ! bar || ! results ) return;
	const grid = results.querySelector( '.stories__grid' );
	const FIRST = Number( grid.dataset.first ) || 6;
	const MORE = Number( grid.dataset.more ) || 6;
	const PER_PAGE = Number( grid.dataset.perPage ) || 12;
	const cards = [ ...grid.querySelectorAll( '[data-story]' ) ];
	const status = results.querySelector( '[data-story-status]' );
	const more = results.querySelector( '[data-story-more]' );
	const pager = results.querySelector( '[data-pager]' );
	const empty = results.querySelector( '[data-story-empty]' );
	const input = bar.querySelector( '[data-search-input]' );
	const searchBar = bar.querySelector( '[data-search-bar]' );
	const dropdowns = [ ...bar.querySelectorAll( '[data-dd]' ) ];
	const pageFromUrl = () => Math.max( 1, parseInt( new URLSearchParams( location.search ).get( 'page' ), 10 ) || 1 );
	let page = pageFromUrl();
	let limit = FIRST;

	const pageLink = ( n, label, cls = '', current = false ) =>
		`<a class="pager__link ${ cls }" href="?page=${ n }" data-page="${ n }"${ current ? ' aria-current="page"' : '' }>${ label }</a>`;

	const renderPager = ( pages ) => {
		const arrowL = '<svg viewBox="0 0 24 24" aria-hidden="true"><use href="#i-arrow-left"/></svg>';
		const arrowR = '<svg viewBox="0 0 24 24" aria-hidden="true"><use href="#i-arrow-right"/></svg>';
		let html = page > 1 ? pageLink( page - 1, `${ arrowL } Previous`, 'pager__prev' ) : '';
		html += '<ol class="pager__pages" role="list">';
		for ( let n = 1; n <= pages; n++ ) {
			html += `<li>${ pageLink( n, `<span class="visually-hidden">Page </span>${ n }`, '', n === page ) }</li>`;
		}
		html += '</ol>';
		html += page < pages ? pageLink( page + 1, `Next page ${ arrowR }`, 'pager__next button button--primary' ) : '';
		pager.innerHTML = html;
	};

	const apply = () => {
		const picked = {};
		bar.querySelectorAll( '[data-filter-option]:checked' ).forEach( ( box ) => {
			( picked[ box.name ] ||= [] ).push( box.value );
		} );
		const words = input.value.trim().toLowerCase().split( /\s+/ ).filter( Boolean );
		const matches = cards.filter( ( card ) =>
			Object.entries( picked ).every( ( [ group, values ] ) => {
				const own = ( card.getAttribute( `data-${ group }` ) || '' ).split( ' ' );
				return values.some( ( v ) => own.includes( v ) );
			} ) && words.every( ( w ) => card.getAttribute( 'data-search-item' ).includes( w ) )
		);
		const pages = Math.max( 1, Math.ceil( matches.length / PER_PAGE ) );
		page = Math.min( page, pages );
		const onPage = matches.slice( ( page - 1 ) * PER_PAGE, page * PER_PAGE );
		const visible = onPage.slice( 0, limit );
		cards.forEach( ( card ) => {
			card.hidden = ! visible.includes( card );
		} );
		const from = ( page - 1 ) * PER_PAGE + 1;
		const to = from + visible.length - 1;
		status.textContent = `Showing ${ from }–${ to } of ${ matches.length } case ${ matches.length === 1 ? 'study' : 'studies' }`;
		status.hidden = matches.length === 0;
		const pageDone = visible.length >= onPage.length;
		more.hidden = pageDone;
		pager.hidden = ! pageDone || pages < 2;
		if ( ! pager.hidden ) renderPager( pages );
		empty.hidden = matches.length > 0;
		dropdowns.forEach( ( dd ) => {
			const n = dd.querySelectorAll( '[data-filter-option]:checked' ).length;
			dd.querySelector( '[data-dd-count]' ).textContent = n ? String( n ) : '';
		} );
	};

	const goTo = ( n, push = true ) => {
		page = n;
		limit = FIRST;
		if ( push ) {
			const url = new URL( location.href );
			if ( n > 1 ) url.searchParams.set( 'page', String( n ) );
			else url.searchParams.delete( 'page' );
			history.pushState( { page: n }, '', url );
		}
		apply();
	};

	// Any filter or search change starts again from page 1.
	const refilter = () => {
		if ( page !== 1 ) goTo( 1 );
		else {
			limit = FIRST;
			apply();
		}
	};

	pager.addEventListener( 'click', ( event ) => {
		const link = event.target.closest( '[data-page]' );
		if ( ! link ) return;
		event.preventDefault();
		goTo( Number( link.dataset.page ) );
		bar.scrollIntoView( { behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' } );
		grid.querySelector( '[data-story]:not([hidden]) a' )?.focus( { preventScroll: true } );
	} );
	window.addEventListener( 'popstate', () => goTo( pageFromUrl(), false ) );

	// Dropdowns: one open at a time; close on outside click or Escape.
	const closeAll = ( except ) => {
		dropdowns.forEach( ( dd ) => {
			if ( dd === except ) return;
			dd.querySelector( '[data-dd-toggle]' ).setAttribute( 'aria-expanded', 'false' );
			dd.querySelector( '[data-dd-panel]' ).hidden = true;
		} );
	};
	dropdowns.forEach( ( dd ) => {
		const toggle = dd.querySelector( '[data-dd-toggle]' );
		const panel = dd.querySelector( '[data-dd-panel]' );
		toggle.addEventListener( 'click', () => {
			const open = toggle.getAttribute( 'aria-expanded' ) !== 'true';
			closeAll( dd );
			toggle.setAttribute( 'aria-expanded', String( open ) );
			panel.hidden = ! open;
		} );
		dd.addEventListener( 'keydown', ( event ) => {
			if ( event.key !== 'Escape' ) return;
			closeAll();
			toggle.focus();
		} );
	} );
	document.addEventListener( 'click', ( event ) => {
		if ( ! event.target.closest( '[data-dd]' ) ) closeAll();
	} );
	bar.addEventListener( 'change', ( event ) => {
		if ( event.target.matches( '[data-filter-option]' ) ) refilter();
	} );

	// Search box: the clear button shows once there is text; Escape also clears it.
	const clearBtn = bar.querySelector( '[data-search-close]' );
	const clearSearch = () => {
		input.value = '';
		clearBtn.hidden = true;
		refilter();
	};
	input.addEventListener( 'input', () => {
		clearBtn.hidden = ! input.value;
		refilter();
	} );
	input.addEventListener( 'keydown', ( event ) => {
		if ( event.key === 'Escape' && input.value ) clearSearch();
	} );
	clearBtn.addEventListener( 'click', () => {
		clearSearch();
		input.focus();
	} );
	searchBar.addEventListener( 'submit', ( event ) => event.preventDefault() );

	document.querySelectorAll( '[data-filter-clear]' ).forEach( ( button ) => {
		button.addEventListener( 'click', () => {
			bar.querySelectorAll( '[data-filter-option]' ).forEach( ( box ) => {
				box.checked = false;
			} );
			input.value = '';
			clearBtn.hidden = true;
			refilter();
		} );
	} );

	// Links can open the page already filtered, e.g. case-studies.html?industry=healthcare&outcome=more-leads
	const params = new URLSearchParams( location.search );
	bar.querySelectorAll( '[data-filter-option]' ).forEach( ( box ) => {
		if ( params.getAll( box.name ).some( ( v ) => v.split( ',' ).includes( box.value ) ) ) box.checked = true;
	} );
	if ( params.get( 'q' ) ) {
		input.value = params.get( 'q' );
		bar.querySelector( '[data-search-close]' ).hidden = false;
	}

	more.addEventListener( 'click', () => {
		const firstNew = limit;
		limit += MORE;
		apply();
		// Move focus to the first newly shown card's link so keyboard users keep their place.
		cards.filter( ( c ) => ! c.hidden )[ firstNew ]?.querySelector( 'a' )?.focus( { preventScroll: true } );
	} );

	apply();
}

initStoryFilter();
