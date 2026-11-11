/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

/** Structure chooser ([data-structure-chooser]): three yes/no answers highlight country domains, subfolders or subdomains. */
export function initStructureChooser() {
	document.querySelectorAll( '[data-structure-chooser]' ).forEach( ( box ) => {
		const update = () => {
			const v = ( n ) => box.querySelector( `input[name="${ n }"]:checked` )?.value === 'yes';
			const rec = v( 'q1' ) && v( 'q2' ) ? 'cctld' : v( 'q3' ) ? 'sub' : 'folder';
			box.querySelectorAll( '[data-structure]' ).forEach( ( o ) => o.classList.toggle( 'is-rec', o.dataset.structure === rec ) );
			box.classList.add( 'is-ready' );
		};
		box.addEventListener( 'change', update );
		update();
	} );
}

initStructureChooser();
