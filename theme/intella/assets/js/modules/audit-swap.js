/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

export function initAuditSwap() {
	document.querySelectorAll( '[data-audit]' ).forEach( ( panel ) => {
		const sets = [ ...panel.querySelectorAll( '[data-audit-set]' ) ];
		const title = panel.querySelector( '[data-audit-title]' );
		const bar = panel.querySelector( '[data-audit-progress]' );
		if ( sets.length < 2 ) return;
		const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		const DELAY = 6000;
		let index = 0;
		let timer;

		const restartBar = () => {
			if ( ! bar ) return;
			bar.classList.remove( 'is-running' );
			void bar.offsetWidth;
			bar.style.animationDuration = `${ DELAY }ms`;
			bar.classList.add( 'is-running' );
		};

		const show = ( next ) => {
			sets[ index ].classList.remove( 'is-active' );
			sets[ index ].setAttribute( 'aria-hidden', 'true' );
			index = next % sets.length;
			sets[ index ].classList.add( 'is-active' );
			sets[ index ].removeAttribute( 'aria-hidden' );
			if ( title ) {
				title.classList.remove( 'is-swapping' );
				void title.offsetWidth;
				title.textContent = sets[ index ].dataset.auditName;
				title.classList.add( 'is-swapping' );
			}
			restartBar();
		};

		const start = () => {
			clearInterval( timer );
			if ( reduceMotion ) return;
			restartBar();
			timer = setInterval( () => {
				if ( ! document.hidden ) show( index + 1 );
			}, DELAY );
		};
		const stop = () => {
			clearInterval( timer );
			bar?.classList.remove( 'is-running' );
		};

		panel.addEventListener( 'mouseenter', stop );
		panel.addEventListener( 'mouseleave', start );
		start();
	} );
}

initAuditSwap();
