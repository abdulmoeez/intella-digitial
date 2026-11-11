/* Ported from the prototype (Code/frontend/assets/js/main.js). Runs on import; does nothing if its markup is absent. */

export function initStepForm() {
	const urlPattern = /^(https?:\/\/)?([a-z0-9-]+\.)+[a-z]{2,}(\/\S*)?$/i;
	document.querySelectorAll( '[data-step-form]' ).forEach( ( form ) => {
		const one = form.querySelector( '[data-step="1"]' );
		const two = form.querySelector( '[data-step="2"]' );
		const label = form.querySelector( '[data-step-label]' );
		const site = one.querySelector( 'input' );
		const show = ( step ) => {
			form.dataset.stepNow = step;
			one.hidden = step === 2;
			two.hidden = step !== 2;
			two.querySelectorAll( '[data-step-required]' ).forEach( ( el ) => { el.required = step === 2; } );
			if ( label ) label.textContent = `Step ${ step } of 2`;
			( step === 2 ? two.querySelector( '.field__input' ) : site ).focus();
		};
		form.querySelector( '[data-step-back]' )?.addEventListener( 'click', () => show( 1 ) );
		document.addEventListener( 'submit', ( event ) => {
			if ( event.target !== form || form.dataset.stepNow === '2' ) return;
			if ( ! urlPattern.test( site.value.trim() ) ) return; // let the validator show the error
			event.preventDefault();
			event.stopImmediatePropagation();
			show( 2 );
		}, true );
	} );
}

initStepForm();
