/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

/** Form validation with inline errors. On success the visitor goes to the form's data-success page. */
export function initFormValidation() {
	const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
	const urlPattern = /^(https?:\/\/)?([a-z0-9-]+\.)+[a-z]{2,}(\/\S*)?$/i;

	/** Returns an error message for one field, or an empty string when it is valid. */
	const check = ( input ) => {
		const value = input.value.trim();
		const d = input.dataset;
		if ( input.required && ! value ) return d.errorRequired || 'This field is required.';
		if ( ! value ) return '';
		if ( input.type === 'email' && ! emailPattern.test( value ) ) return d.errorFormat;
		if ( input.minLength > 0 && value.length < input.minLength ) return d.errorShort;
		if ( 'validatePhone' in d ) {
			const digits = value.replace( /\D/g, '' );
			if ( /[^\d\s().-]/.test( value ) || digits.length < 6 || digits.length > 14 ) return d.errorFormat;
		}
		if ( 'validateUrl' in d && ! urlPattern.test( value ) ) return d.errorFormat;
		return '';
	};

	const setError = ( input, message ) => {
		const field = input.closest( '.field' );
		if ( ! field ) return;
		let error = field.querySelector( '.field__error' );
		field.classList.toggle( 'has-error', Boolean( message ) );
		input.setAttribute( 'aria-invalid', message ? 'true' : 'false' );
		if ( message ) {
			if ( ! error ) {
				error = document.createElement( 'p' );
				error.className = 'field__error';
				error.id = `${ input.id }-error`;
				field.append( error );
			}
			error.textContent = message;
			input.setAttribute( 'aria-describedby', error.id );
		} else if ( error ) {
			error.remove();
			input.removeAttribute( 'aria-describedby' );
		}
	};

	document.querySelectorAll( 'form[data-validate]' ).forEach( ( form ) => {
		form.noValidate = true; // our messages replace the browser's
		const inputs = [ ...form.querySelectorAll( '.field__input' ) ];
		const status = form.querySelector( '[data-form-status]' );
		const submit = form.querySelector( '[data-submit]' );

		// Re-check a field as the visitor fixes it, and when they leave a field they've typed in.
		inputs.forEach( ( input ) => {
			input.addEventListener( 'input', () => {
				if ( input.getAttribute( 'aria-invalid' ) === 'true' ) setError( input, check( input ) );
			} );
			input.addEventListener( 'blur', () => {
				if ( input.value.trim() ) setError( input, check( input ) );
			} );
		} );

		form.addEventListener( 'submit', ( event ) => {
			event.preventDefault();
			const invalid = inputs.filter( ( input ) => {
				const message = check( input );
				setError( input, message );
				return Boolean( message );
			} );

			if ( invalid.length ) {
				// Each field shows its own message, so no summary line here.
				invalid[ 0 ].focus();
				return;
			}
			if ( status ) status.hidden = true;

			// Bots fill the hidden honeypot field; people never see it.
			if ( form.querySelector( '[name="company_hp"]' )?.value ) return;

			// Combine country code and number, e.g. "+44 7700 900123".
			const phone = form.querySelector( '[data-validate-phone]' );
			const full = form.querySelector( '[data-phone-full]' );
			const dial = form.querySelector( '[data-dial-select]' )?.selectedOptions[ 0 ]?.dataset.dial;
			if ( phone && full ) full.value = phone.value.trim() ? `${ dial || '' } ${ phone.value.trim() }`.trim() : '';

			// Remember the first name for the thank-you message.
			try {
				const name = form.querySelector( '[name="name"]' )?.value.trim().split( /\s+/ )[ 0 ] || '';
				sessionStorage.setItem( 'intella:first-name', name );
			} catch ( e ) { /* storage blocked: the thank-you page falls back to a generic greeting */ }

			submit?.classList.add( 'is-loading' );
			const label = submit?.querySelector( '[data-submit-label]' );
			if ( label ) {
				label.dataset.text ||= label.textContent;
				label.textContent = 'Sending…';
			}

			// Forms with data-success-message (e.g. comments) stay on the page and show that message instead.
			if ( form.dataset.successMessage && ! form.action.includes( 'admin-post.php' ) ) {
				const message = form.querySelector( '[data-form-status]' );
				form.reset();
				submit?.classList.remove( 'is-loading' );
				if ( label ) label.textContent = label.dataset.text || 'Send';
				if ( message ) {
					message.hidden = false;
					message.classList.add( 'is-success' );
					message.textContent = form.dataset.successMessage;
					message.focus?.();
				}
				return;
			}

			// Send it. The server checks everything again, saves the lead and redirects to the thank-you page.
			HTMLFormElement.prototype.submit.call( form );
		} );
	} );
}

initFormValidation();
