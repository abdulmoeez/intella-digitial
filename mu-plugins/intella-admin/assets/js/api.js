/**
 * Small client for the panel's REST API. PATCH and DELETE are sent as POST with an override header,
 * because some servers block those methods; WordPress reads the header and routes them correctly.
 */

/** Create an API client for a base URL (ending in /) and a wp_rest nonce. */
export function createApi( base, nonce ) {
	async function request( method, path, body ) {
		const headers = { 'X-WP-Nonce': nonce };
		if ( method !== 'GET' && method !== 'POST' ) {
			headers[ 'X-HTTP-Method-Override' ] = method;
		}
		const options = { method: method === 'GET' ? 'GET' : 'POST', headers, credentials: 'same-origin' };
		if ( body instanceof FormData ) {
			options.body = body;
		} else if ( body !== undefined ) {
			headers[ 'Content-Type' ] = 'application/json';
			options.body = JSON.stringify( body );
		}
		const response = await fetch( base + path, options );
		const json = await response.json().catch( () => null );
		if ( ! response.ok ) {
			const error = new Error( json?.message || `${ response.status } ${ response.statusText }` );
			error.status = response.status;
			throw error;
		}
		return json;
	}
	return {
		get: ( path ) => request( 'GET', path ),
		post: ( path, body ) => request( 'POST', path, body ),
		patch: ( path, body ) => request( 'PATCH', path, body ),
		del: ( path ) => request( 'DELETE', path ),
	};
}
