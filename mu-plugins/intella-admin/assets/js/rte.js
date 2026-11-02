/**
 * Rich-text editing for card descriptions, goals and notes (a contenteditable box with a toolbar),
 * ported from the project hubs. Notes are HTML limited to the tags below; the server applies the same list.
 */

const NOTE_TAGS = new Set( [ 'P', 'DIV', 'BR', 'B', 'STRONG', 'I', 'EM', 'U', 'S', 'STRIKE',
	'H3', 'H4', 'UL', 'OL', 'LI', 'A', 'BLOCKQUOTE', 'CODE', 'PRE' ] );
const NOTE_DROP = new Set( [ 'SCRIPT', 'STYLE', 'IFRAME', 'OBJECT', 'EMBED', 'TEMPLATE', 'META', 'LINK',
	'TITLE', 'SVG', 'MATH', 'FORM', 'INPUT', 'BUTTON', 'TEXTAREA', 'SELECT', 'IMG', 'VIDEO', 'AUDIO' ] );
const TOOLBAR = [
	[ 'bold', 'B', 'Bold (⌘/Ctrl+B)' ], [ 'italic', 'I', 'Italic (⌘/Ctrl+I)' ], [ 'underline', 'U', 'Underline (⌘/Ctrl+U)' ],
	[ 'strikeThrough', 'S', 'Strikethrough' ], null,
	[ 'heading', 'H', 'Heading' ], [ 'paragraph', '¶', 'Normal text' ],
	[ 'insertUnorderedList', '• List', 'Bullet list' ], [ 'insertOrderedList', '1. List', 'Numbered list' ],
	[ 'outdent', '⇤', 'Decrease indent (Shift+Tab)' ], [ 'indent', '⇥', 'Increase indent (Tab)' ], null,
	[ 'link', 'Link', 'Add link (⌘/Ctrl+K)' ], [ 'unlink', 'Unlink', 'Remove link' ], [ 'removeFormat', 'Clear', 'Clear formatting' ],
];
const STATE_COMMANDS = [ 'bold', 'italic', 'underline', 'strikeThrough', 'insertUnorderedList', 'insertOrderedList' ];
const editors = []; // [ editor, toolbar ]

const looksLikeHtml = ( s ) => /<\/?[a-z][^>]*>/i.test( s );

/** Keep only the allowed tags (others are unwrapped or dropped) and safe link addresses. */
export function cleanHtml( html ) {
	const doc = new DOMParser().parseFromString( '<body>' + html + '</body>', 'text/html' );
	( function walk( node ) {
		for ( const child of [ ...node.childNodes ] ) {
			if ( child.nodeType === Node.TEXT_NODE ) {
				continue;
			}
			if ( child.nodeType !== Node.ELEMENT_NODE || NOTE_DROP.has( child.tagName ) ) {
				child.remove();
				continue;
			}
			walk( child );
			if ( ! NOTE_TAGS.has( child.tagName ) ) {
				child.replaceWith( ...child.childNodes );
				continue;
			}
			const href = ( child.getAttribute( 'href' ) || '' ).trim();
			[ ...child.attributes ].forEach( ( a ) => child.removeAttribute( a.name ) );
			if ( child.tagName === 'A' ) {
				if ( /^(https?:|mailto:)/i.test( href ) ) {
					child.setAttribute( 'href', href );
					child.setAttribute( 'target', '_blank' );
					child.setAttribute( 'rel', 'noopener' );
				} else {
					child.replaceWith( ...child.childNodes );
				}
			}
		}
	}( doc.body ) );
	return doc.body.innerHTML;
}

/** Plain text as HTML: line breaks kept, URLs made clickable. */
export function plainToHtml( text ) {
	const div = document.createElement( 'div' );
	div.textContent = text;
	return div.innerHTML
		.replace( /(https?:\/\/[^\s<]+[^\s<.,;:!?)\]'"])/g, '<a href="$1" target="_blank" rel="noopener">$1</a>' )
		.replace( /\n/g, '<br>' );
}

/** Stored notes (HTML or older plain text) as safe HTML for an editor. */
export function notesHtml( notes ) {
	return looksLikeHtml( notes || '' ) ? cleanHtml( notes ) : plainToHtml( notes || '' );
}

/** Stored notes as plain text (for search and the "has notes" mark). */
export function notesText( notes ) {
	if ( ! looksLikeHtml( notes || '' ) ) {
		return notes || '';
	}
	return new DOMParser().parseFromString( notes, 'text/html' ).body.textContent;
}

/** An editor's content as clean HTML, or "" when it's empty. */
export function editorHtml( editor ) {
	const empty = ! editor.textContent.trim() && ! editor.querySelector( 'li' );
	return empty ? '' : cleanHtml( editor.innerHTML );
}

function addLink( editor ) {
	const selection = window.getSelection();
	const range = selection.rangeCount ? selection.getRangeAt( 0 ).cloneRange() : null;
	let url = window.prompt( 'Link address', 'https://' );
	if ( ! url || url.trim() === 'https://' ) {
		return;
	}
	url = url.trim();
	if ( ! /^(https?:|mailto:)/i.test( url ) ) {
		url = ( url.includes( '@' ) && ! url.includes( '/' ) ? 'mailto:' : 'https://' ) + url;
	}
	editor.focus();
	if ( range ) {
		selection.removeAllRanges();
		selection.addRange( range );
	}
	if ( ! range || range.collapsed ) {
		const a = document.createElement( 'a' );
		a.href = url;
		a.textContent = url.replace( /^mailto:/i, '' );
		document.execCommand( 'insertHTML', false, a.outerHTML );
	} else {
		document.execCommand( 'createLink', false, url );
	}
}

function runCommand( editor, cmd ) {
	editor.focus();
	if ( cmd === 'link' ) {
		addLink( editor );
	} else if ( cmd === 'heading' ) {
		document.execCommand( 'formatBlock', false, 'H3' );
	} else if ( cmd === 'paragraph' ) {
		document.execCommand( 'formatBlock', false, 'P' );
	} else {
		document.execCommand( cmd, false, null );
	}
	updateToolbars();
	editor.dispatchEvent( new Event( 'input' ) );
}

function updateToolbars() {
	const node = window.getSelection().anchorNode;
	for ( const [ editor, bar ] of editors ) {
		if ( ! editor.contains( node ) ) {
			continue;
		}
		bar.querySelectorAll( 'button' ).forEach( ( b ) => {
			const cmd = b.dataset.cmd;
			let on = false;
			try {
				if ( cmd === 'heading' ) {
					on = /^h3$/i.test( document.queryCommandValue( 'formatBlock' ) );
				} else if ( STATE_COMMANDS.includes( cmd ) ) {
					on = document.queryCommandState( cmd );
				}
			} catch {}
			b.classList.toggle( 'is-active', on );
		} );
	}
}

function toolbar() {
	const bar = document.createElement( 'div' );
	bar.className = 'rte-bar';
	bar.setAttribute( 'role', 'toolbar' );
	bar.setAttribute( 'aria-label', 'Formatting' );
	for ( const item of TOOLBAR ) {
		if ( ! item ) {
			bar.append( Object.assign( document.createElement( 'span' ), { className: 'rte-bar__sep' } ) );
			continue;
		}
		const b = document.createElement( 'button' );
		b.type = 'button';
		b.dataset.cmd = item[ 0 ];
		b.textContent = item[ 1 ];
		b.title = item[ 2 ];
		bar.append( b );
	}
	return bar;
}

/** Turn a contenteditable element into an editor with a toolbar above it. onInput runs on every change. */
export function setupEditor( editor, onInput ) {
	const bar = toolbar();
	editor.before( bar );
	editors.push( [ editor, bar ] );
	if ( editors.length === 1 ) {
		document.addEventListener( 'selectionchange', updateToolbars );
	}
	bar.addEventListener( 'mousedown', ( e ) => e.preventDefault() ); // Keep the text selection.
	bar.addEventListener( 'click', ( e ) => {
		const b = e.target.closest( 'button' );
		if ( b ) {
			runCommand( editor, b.dataset.cmd );
		}
	} );
	editor.addEventListener( 'input', onInput );
	editor.addEventListener( 'paste', ( e ) => {
		e.preventDefault();
		const html = e.clipboardData.getData( 'text/html' );
		document.execCommand( 'insertHTML', false, html ? cleanHtml( html ) : plainToHtml( e.clipboardData.getData( 'text/plain' ) ) );
	} );
	editor.addEventListener( 'keydown', ( e ) => {
		if ( ( e.metaKey || e.ctrlKey ) && e.key.toLowerCase() === 'k' ) {
			e.preventDefault();
			runCommand( editor, 'link' );
		}
		if ( e.key === 'Tab' && window.getSelection().anchorNode?.parentElement?.closest( 'li' ) ) {
			e.preventDefault();
			runCommand( editor, e.shiftKey ? 'outdent' : 'indent' );
		}
	} );
	// A plain click on a link opens it in a new tab; dragging across a link still selects text.
	editor.addEventListener( 'click', ( e ) => {
		const a = e.target.closest( 'a' );
		if ( a?.href && window.getSelection().isCollapsed ) {
			e.preventDefault();
			window.open( a.href, '_blank', 'noopener' );
		}
	} );
}
