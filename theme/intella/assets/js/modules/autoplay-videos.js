/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

/** Muted looping videos: play only while on screen, never when the visitor prefers less motion. */
export function initAutoplayVideos() {
	const videos = [ ...document.querySelectorAll( 'video[data-autoplay]' ) ];
	if ( ! videos.length || reduceMotion || ! ( 'IntersectionObserver' in window ) ) return;
	const observer = new IntersectionObserver( ( entries ) => {
		entries.forEach( ( { target, isIntersecting } ) => {
			if ( isIntersecting ) {
				if ( target.preload === 'none' ) target.preload = 'auto';
				target.play().catch( () => {} );
			} else {
				target.pause();
			}
		} );
	}, { rootMargin: '150px 0px' } );
	videos.forEach( ( video ) => observer.observe( video ) );
}

initAutoplayVideos();
