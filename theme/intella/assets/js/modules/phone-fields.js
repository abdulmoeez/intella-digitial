/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

/** Phone fields: country code dropdown with a short visible label, pre-selected from the browser language. */
export function initPhoneFields() {
	// Flag emoji are built from the two-letter country code. Windows shows letters instead of
	// flags, so there we keep the short code ("GB +44").
	const platform = navigator.userAgentData?.platform || navigator.platform || '';
	const showFlags = ! /win/i.test( platform );
	const flag = ( iso ) => String.fromCodePoint( ...[ ...iso ].map( ( c ) => 127397 + c.charCodeAt( 0 ) ) );

	document.querySelectorAll( '[data-dial-select]' ).forEach( ( select ) => {
		const label = select.closest( '.phone-field__country' )?.querySelector( '[data-dial-label]' );
		if ( showFlags ) {
			select.querySelectorAll( 'option' ).forEach( ( option ) => {
				option.textContent = `${ flag( option.value ) }  ${ option.textContent }`;
			} );
		}
		const update = () => {
			const option = select.selectedOptions[ 0 ];
			if ( ! label || ! option ) return;
			label.textContent = showFlags ? `${ flag( option.value ) } ${ option.dataset.dial }` : `${ option.value } ${ option.dataset.dial }`;
		};
		// "en-GB" -> GB, "ar-SA" -> SA. Only used when that country is in the list.
		const region = ( navigator.language || '' ).split( '-' )[ 1 ]?.toUpperCase();
		if ( region && select.querySelector( `option[value="${ region }"]` ) ) select.value = region;
		select.addEventListener( 'change', update );
		update();
	} );
}

initPhoneFields();
