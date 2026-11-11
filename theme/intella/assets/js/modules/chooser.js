/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

export function initChooser() {
	document.querySelectorAll( '[data-chooser]' ).forEach( ( form ) => {
		const panel = form.querySelector( '[data-chooser-panel]' );
		const order = [ ...form.querySelectorAll( '[data-result]' ) ].map( ( el ) => el.dataset.result ).filter( ( k ) => k !== 'none' );
		const total = new Set( [ ...form.querySelectorAll( '[data-choose]' ) ].map( ( i ) => i.dataset.choose ) ).size;
		const update = ( animate ) => {
			const picked = [ ...form.querySelectorAll( '[data-choose]:checked' ) ];
			const score = {};
			picked.forEach( ( i ) => i.dataset.score.split( ',' ).forEach( ( p ) => {
				const [ k, v ] = p.split( ':' );
				score[ k ] = ( score[ k ] || 0 ) + Number( v );
			} ) );
			let res = 'none';
			if ( picked.length ) res = order.reduce( ( best, k ) => ( ( score[ k ] || 0 ) > ( score[ best ] || 0 ) ? k : best ), order[ 0 ] );
			form.querySelectorAll( '[data-result]' ).forEach( ( el ) => { el.hidden = el.dataset.result !== res; } );
			form.querySelectorAll( '[data-result-link]' ).forEach( ( el ) => { el.hidden = el.dataset.resultLink !== res; } );
			const why = form.querySelector( '[data-why-list]' );
			why.innerHTML = '';
			picked.forEach( ( i ) => { const li = document.createElement( 'li' ); li.textContent = i.dataset.why; why.append( li ); } );
			form.querySelector( '[data-why-box]' ).hidden = ! picked.length;
			form.querySelector( '[data-progress]' ).textContent = picked.length === total ? 'All questions answered' : `${ picked.length } of ${ total } answered`;
			form.querySelector( '[data-progress-bar]' ).style.width = `${ ( picked.length / total ) * 100 }%`;
			if ( animate ) {
				panel.classList.remove( 'is-changed' );
				void panel.offsetWidth;
				panel.classList.add( 'is-changed' );
			}
		};
		form.addEventListener( 'change', () => update( true ) );
		update( false );
	} );
}

initChooser();
