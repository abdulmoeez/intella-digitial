/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

const KEYS = [ 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid' ];
const STORE = 'intella:campaign';

/**
 * Fill each form's hidden campaign fields (utm_*, gclid, landing page, referrer), so every lead records where
 * the visitor came from. The first page of the visit is remembered for the session, so the details survive
 * clicking through to another page before enquiring.
 */
export function initCampaignFields() {
	let saved = {};
	try {
		saved = JSON.parse( sessionStorage.getItem( STORE ) || '{}' );
	} catch ( e ) {}

	if ( ! saved.landing_page ) {
		const params = new URLSearchParams( window.location.search );
		saved = { landing_page: window.location.pathname, referrer: document.referrer.slice( 0, 200 ) };
		KEYS.forEach( ( key ) => {
			const value = params.get( key );
			if ( value ) saved[ key ] = value.slice( 0, 200 );
		} );
		try {
			sessionStorage.setItem( STORE, JSON.stringify( saved ) );
		} catch ( e ) {}
	}

	document.querySelectorAll( '[data-campaign-field]' ).forEach( ( field ) => {
		const value = saved[ field.dataset.campaignField ];
		if ( value && ! field.value ) field.value = value;
	} );
}

initCampaignFields();
