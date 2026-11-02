/**
 * The board page: kanban cards with a detail dialog and comments, the bug tracker, and the board's goals
 * and notes. Ported from the project hubs (index.html); every change is saved to the database through the
 * REST API. The board reloads when the tab regains focus (and every minute), so teammates' edits show up.
 */

import { createApi } from './api.js';
import { setupEditor, notesHtml, notesText, editorHtml, cleanHtml } from './rte.js';

const root = document.querySelector( '[data-board-app]' );
const settings = JSON.parse( root.dataset.settings );
const api = createApi( settings.api, settings.nonce );
const boardPath = `boards/${ settings.boardId }`;
const $ = ( selector ) => root.querySelector( selector );

// Colour dots for the hubs' list IDs. Other lists get the first colour.
const LIST_COLOURS = {
	backlog: '#9a93b3', 'priority-moeez': '#d9480f', 'due-next-call': '#db2777', 'in-progress': '#3d1a78',
	'awaiting-qa': '#b7791f', completed: '#12a389', abandoned: '#6b6680',
};
// The hubs' bug workflow: choosing a status hands the bug to the person who acts next.
const BUG_STATUS_ASSIGNEE = { open: 'Moeez', revalidate: 'Amit', 'wont-fix': 'Amit' };
const TAG_SUGGESTIONS = [ 'Website', 'Design', 'Tech', 'Content', 'SEO', 'App', 'QA', 'Client brief', 'Question' ];
const DEFAULT_TEXT = {
	goals: [ 'Short term goal', 'Long term goal' ],
	notes: [ 'Immediate priority', 'In follow up', 'In near future', 'In execution', 'Commitments' ],
};
const REFRESH_MS = 60000;
const TAB_KEY = `intella-admin-tab-${ settings.boardId }`;
const HIDE_CLOSED_KEY = 'intella-admin-bugs-hide-closed';

let data = null; // { board, lists, cards, bugs, people }
let openId = 0;
let newCardId = 0;
let dragId = 0;

const dialog = $( '[data-card-dialog]' );
const field = ( name ) => dialog.querySelector( `[data-field="${ name }"]` );
const cardById = ( id ) => data.cards.find( ( c ) => c.id === id );
const listById = ( id ) => data.lists.find( ( l ) => l.id === id );

// ----- Small helpers -----

function el( tag, className, text ) {
	const e = document.createElement( tag );
	if ( className ) {
		e.className = className;
	}
	if ( text != null ) {
		e.textContent = text;
	}
	return e;
}

function today() {
	const d = new Date();
	return new Date( d.getTime() - ( d.getTimezoneOffset() * 60000 ) ).toISOString().slice( 0, 10 );
}

function fmtDate( iso, withTime ) {
	if ( ! iso ) {
		return '';
	}
	const d = new Date( iso.length === 10 ? iso + 'T00:00:00' : iso );
	return d.toLocaleDateString( 'en-GB', withTime
		? { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }
		: { day: 'numeric', month: 'short' } );
}

function fillSelect( select, label, values ) {
	const current = select.value;
	select.replaceChildren( new Option( label, '' ), ...values.map( ( v ) => new Option( v, v ) ) );
	select.value = values.includes( current ) ? current : '';
}

function fillDatalist( id, values ) {
	document.getElementById( id ).replaceChildren( ...values.map( ( v ) => new Option( v ) ) );
}

const unique = ( values ) => [ ...new Set( values.map( ( v ) => ( v || '' ).trim() ).filter( Boolean ) ) ].sort();

// People's colours, matched on first name (as the hubs did).
function whoClass( name ) {
	const first = ( name || '' ).trim().split( /\s+/ )[ 0 ].toLowerCase();
	const person = first && data.people.find( ( p ) => p.name.trim().split( /\s+/ )[ 0 ].toLowerCase() === first );
	return person ? `who who--${ person.color }` : 'who';
}

// Plain text with URLs turned into links. Built from text nodes, so the text is never parsed as HTML.
const URL_IN_TEXT = /(https?:\/\/|www\.)[^\s<>"']*[^\s<>"'.,;:!?)\]]/gi;
function linkifiedText( className, text ) {
	const box = el( 'div', className );
	let last = 0;
	for ( const m of ( text || '' ).matchAll( URL_IN_TEXT ) ) {
		if ( m.index > last ) {
			box.append( text.slice( last, m.index ) );
		}
		const a = el( 'a', '', m[ 0 ] );
		a.href = /^www\./i.test( m[ 0 ] ) ? 'https://' + m[ 0 ] : m[ 0 ];
		a.target = '_blank';
		a.rel = 'noopener';
		box.append( a );
		last = m.index + m[ 0 ].length;
	}
	if ( last < ( text || '' ).length ) {
		box.append( text.slice( last ) );
	}
	return box;
}

// ----- Saving -----
// Typing is saved after a short pause (debounced, one timer per field); other changes are saved at once.

const queued = new Map(); // key => { timer, run }
let pending = 0;
let saveError = '';

function setSaveState() {
	const box = $( '[data-save-state]' );
	if ( saveError ) {
		box.textContent = `Couldn't save: ${ saveError }. Reload the page to see the latest version.`;
		box.className = 'save-state save-state--error';
	} else {
		box.textContent = pending || queued.size ? 'Saving…' : 'All changes saved';
		box.className = 'save-state';
	}
}

function track( promise ) {
	pending++;
	setSaveState();
	return promise
		.then( ( result ) => {
			saveError = '';
			return result;
		} )
		.catch( ( error ) => {
			saveError = error.message;
			throw error;
		} )
		.finally( () => {
			pending--;
			setSaveState();
		} );
}

function later( key, run, ms = 600 ) {
	clearTimeout( queued.get( key )?.timer );
	queued.set( key, { run, timer: setTimeout( () => {
		queued.delete( key );
		run();
	}, ms ) } );
	setSaveState();
}

function flushQueued() {
	for ( const [ key, entry ] of queued ) {
		clearTimeout( entry.timer );
		queued.delete( key );
		entry.run();
	}
}

const busy = () => pending > 0 || queued.size > 0;
const quiet = ( promise ) => promise.catch( () => {} ); // Errors are shown in the save state.

function saveCard( card, fields ) {
	return quiet( track( api.patch( `cards/${ card.id }`, fields ) ) );
}

function saveBug( bug, fields ) {
	return quiet( track( api.patch( `bugs/${ bug.id }`, fields ) ) );
}

window.addEventListener( 'beforeunload', ( e ) => {
	if ( busy() ) {
		flushQueued();
		e.preventDefault();
	}
} );

// ----- Loading -----

async function load() {
	try {
		data = await api.get( boardPath );
		render();
		renderBoardText();
		setSaveState();
	} catch ( error ) {
		saveError = error.message;
		setSaveState();
	}
}

// Pick up teammates' changes, unless this person is in the middle of something.
function refresh() {
	const typing = [ ...root.querySelectorAll( '[data-bug-rows], [data-board-text]' ) ]
		.some( ( e ) => e.contains( document.activeElement ) );
	if ( data && ! busy() && ! dialog.open && ! dragId && ! typing && ! saveError ) {
		load();
	}
}

// ----- Kanban -----

function matches( card ) {
	const q = $( '[data-filter="text"]' ).value.trim().toLowerCase();
	const who = $( '[data-filter="who"]' ).value;
	const tag = $( '[data-filter="tag"]' ).value;
	if ( ( who && card.assignee !== who ) || ( tag && card.tag !== tag ) ) {
		return false;
	}
	return ! q || [ card.title, notesText( card.notes ), card.assignee, card.tag, ...card.comments.map( ( c ) => c.text ) ]
		.join( ' ' ).toLowerCase().includes( q );
}

function cardEl( card ) {
	const k = el( 'div', 'kcard' );
	k.draggable = true;
	k.dataset.id = card.id;
	k.tabIndex = 0;
	k.setAttribute( 'role', 'button' );
	k.append( el( 'div', 'kcard__title', card.title || 'Untitled' ) );
	const meta = el( 'div', 'kcard__meta' );
	if ( card.tag ) {
		meta.append( el( 'span', 'kcard__tag', card.tag ) );
	}
	if ( card.due ) {
		const overdue = card.due < today() && ! listById( card.list_id )?.done;
		meta.append( el( 'span', overdue ? 'kcard__due is-overdue' : 'kcard__due', 'Due ' + fmtDate( card.due ) ) );
	}
	if ( notesText( card.notes ).trim() ) {
		meta.append( el( 'span', '', '≡' ) );
	}
	if ( card.comments.length ) {
		meta.append( el( 'span', '', '💬 ' + card.comments.length ) );
	}
	if ( card.assignee ) {
		meta.append( el( 'span', 'kcard__who ' + whoClass( card.assignee ), card.assignee ) );
	}
	if ( meta.children.length ) {
		k.append( meta );
	}
	return k;
}

// The card the dragged card should land in front of, from the pointer's height.
function cardBelow( box, y ) {
	return [ ...box.querySelectorAll( '.kcard:not(.is-dragging)' ) ].find( ( k ) => {
		const r = k.getBoundingClientRect();
		return y < r.top + ( r.height / 2 );
	} );
}

function clearDropMarks() {
	root.querySelectorAll( '.is-drop' ).forEach( ( e ) => e.classList.remove( 'is-drop' ) );
	root.querySelectorAll( '.is-drop-before' ).forEach( ( e ) => e.classList.remove( 'is-drop-before' ) );
}

function renderKanban() {
	const board = $( '[data-kanban]' );
	board.replaceChildren( ...data.lists.map( ( list ) => {
		const col = el( 'div', 'kcol' );
		col.dataset.listId = list.id;
		const inList = data.cards.filter( ( c ) => c.list_id === list.id );
		const shown = inList.filter( matches );
		const head = el( 'div', 'kcol__head' );
		const dot = el( 'span', 'kcol__dot' );
		dot.style.background = LIST_COLOURS[ list.slug ] || LIST_COLOURS.backlog;
		head.append( dot, el( 'h2', 'kcol__name', list.name ),
			el( 'span', 'count', shown.length === inList.length ? String( inList.length ) : `${ shown.length }/${ inList.length }` ) );
		const box = el( 'div', 'kcol__cards' );
		box.append( ...shown.map( cardEl ) );
		const add = el( 'button', 'kcol__add', '+ Add card' );
		add.type = 'button';
		add.dataset.addCard = list.id;
		col.append( head, box, add );
		return col;
	} ) );

	const people = unique( [ ...data.cards.map( ( c ) => c.assignee ), ...data.people.map( ( p ) => p.name ) ] );
	const tags = unique( data.cards.map( ( c ) => c.tag ) );
	fillSelect( $( '[data-filter="who"]' ), 'Everyone', people );
	fillSelect( $( '[data-filter="tag"]' ), 'All tags', tags );
	fillDatalist( 'dl-people', people );
	fillDatalist( 'dl-tags', unique( [ ...tags, ...TAG_SUGGESTIONS ] ) );
}

function render() {
	renderKanban();
	renderBugs();
	if ( openId ) {
		renderComments();
	}
}

const kanban = $( '[data-kanban]' );
kanban.addEventListener( 'click', ( e ) => {
	const add = e.target.closest( '[data-add-card]' );
	if ( add ) {
		addCard( Number( add.dataset.addCard ) );
		return;
	}
	const card = e.target.closest( '.kcard' );
	if ( card ) {
		openCard( Number( card.dataset.id ) );
	}
} );
kanban.addEventListener( 'keydown', ( e ) => {
	const card = e.target.closest( '.kcard' );
	if ( card && ( e.key === 'Enter' || e.key === ' ' ) ) {
		e.preventDefault();
		openCard( Number( card.dataset.id ) );
	}
} );
kanban.addEventListener( 'dragstart', ( e ) => {
	const card = e.target.closest( '.kcard' );
	if ( ! card ) {
		return;
	}
	dragId = Number( card.dataset.id );
	e.dataTransfer.effectAllowed = 'move';
	e.dataTransfer.setData( 'text/plain', String( dragId ) );
	requestAnimationFrame( () => card.classList.add( 'is-dragging' ) );
} );
kanban.addEventListener( 'dragend', ( e ) => {
	e.target.closest( '.kcard' )?.classList.remove( 'is-dragging' );
	dragId = 0;
	clearDropMarks();
} );
kanban.addEventListener( 'dragover', ( e ) => {
	const col = e.target.closest( '.kcol' );
	if ( ! dragId || ! col ) {
		return;
	}
	e.preventDefault();
	clearDropMarks();
	col.classList.add( 'is-drop' );
	cardBelow( col.querySelector( '.kcol__cards' ), e.clientY )?.classList.add( 'is-drop-before' );
} );
kanban.addEventListener( 'drop', ( e ) => {
	const col = e.target.closest( '.kcol' );
	if ( ! dragId || ! col ) {
		return;
	}
	e.preventDefault();
	const below = cardBelow( col.querySelector( '.kcol__cards' ), e.clientY );
	const id = dragId;
	dragId = 0;
	clearDropMarks();
	moveCard( id, Number( col.dataset.listId ), below ? Number( below.dataset.id ) : 0 );
} );
root.querySelectorAll( '[data-filter]' ).forEach( ( f ) => f.addEventListener( 'input', renderKanban ) );

// Put a card in front of another (or at the end of the list) and save the list's new order.
function placeCard( card, listId, beforeId ) {
	data.cards.splice( data.cards.indexOf( card ), 1 );
	card.list_id = listId;
	let to = beforeId ? data.cards.findIndex( ( c ) => c.id === beforeId ) : -1;
	if ( to < 0 ) {
		const lastInList = data.cards.map( ( c ) => c.list_id ).lastIndexOf( listId );
		to = lastInList < 0 ? data.cards.length : lastInList + 1;
	}
	data.cards.splice( to, 0, card );
}

function moveCard( id, listId, beforeId ) {
	const card = cardById( id );
	if ( ! card || id === beforeId ) {
		return;
	}
	placeCard( card, listId, beforeId );
	const order = data.cards.filter( ( c ) => c.list_id === listId ).map( ( c ) => c.id );
	quiet( track( api.post( `cards/${ id }/move`, { list_id: listId, order } ) ) );
	render();
}

async function addCard( listId ) {
	try {
		const card = await track( api.post( `${ boardPath }/cards`, { list_id: listId } ) );
		placeCard( card, listId, 0 );
		newCardId = card.id;
		render();
		openCard( card.id );
	} catch {}
}

function deleteCard( card ) {
	data.cards = data.cards.filter( ( c ) => c !== card );
	data.bugs.forEach( ( b ) => {
		if ( b.card_id === card.id ) {
			b.card_id = 0;
		}
	} );
	quiet( track( api.del( `cards/${ card.id }` ) ) );
	render();
}

// ----- Card dialog -----

function openCard( id ) {
	const card = cardById( id );
	if ( ! card ) {
		return;
	}
	openId = id;
	field( 'title' ).value = card.title;
	field( 'list_id' ).replaceChildren( ...data.lists.map( ( l ) => new Option( l.name, l.id ) ) );
	field( 'list_id' ).value = card.list_id;
	field( 'assignee' ).value = card.assignee;
	field( 'due' ).value = card.due;
	field( 'tag' ).value = card.tag;
	field( 'notes' ).innerHTML = notesHtml( card.notes );
	dialog.querySelector( '[data-card-created]' ).textContent = card.created ? 'Created ' + fmtDate( card.created ) + ' ' + card.created.slice( 0, 4 ) : '';
	dialog.querySelector( '[data-comment-text]' ).value = '';
	renderComments();
	dialog.showModal();
	if ( ! card.title ) {
		field( 'title' ).focus();
	}
}

field( 'title' ).addEventListener( 'input', () => {
	const card = cardById( openId );
	if ( card ) {
		card.title = field( 'title' ).value;
		later( `card-${ card.id }-title`, () => saveCard( card, { title: card.title } ) );
		renderKanban();
	}
} );

setupEditor( field( 'notes' ), () => {
	const card = cardById( openId );
	if ( card ) {
		card.notes = editorHtml( field( 'notes' ) );
		later( `card-${ card.id }-notes`, () => saveCard( card, { notes: card.notes } ), 800 );
	}
} );

for ( const name of [ 'assignee', 'tag', 'due' ] ) {
	field( name ).addEventListener( 'change', () => {
		const card = cardById( openId );
		if ( card ) {
			card[ name ] = field( name ).value.trim();
			saveCard( card, { [ name ]: card[ name ] } );
			render();
		}
	} );
}

field( 'list_id' ).addEventListener( 'change', () => {
	const card = cardById( openId );
	const listId = Number( field( 'list_id' ).value );
	if ( card && listId !== card.list_id ) {
		placeCard( card, listId, 0 ); // The server also puts it at the end of the new list.
		saveCard( card, { list_id: listId } );
		render();
	}
} );

// Numbered items in a comment ("1. …", "2) …"); each runs until the next number.
function numberedItems( text ) {
	return ( text || '' ).split( /\n(?=[ \t]*\d+[.)][ \t]+)/ )
		.map( ( p ) => p.match( /^[ \t]*\d+[.)][ \t]+([\s\S]*)$/ ) )
		.filter( Boolean )
		.map( ( m ) => m[ 1 ].replace( /[ \t]*\n[ \t]*/g, '\n' ).replace( /\n{2,}/g, '\n' ).trim() )
		.filter( Boolean );
}

const bugKey = ( t ) => ( t || '' ).replace( /\s+/g, ' ' ).trim().toLowerCase();
const bugExists = ( cardId, text ) => data.bugs.some( ( b ) => b.card_id === cardId && bugKey( b.description ) === bugKey( text ) );

// Each numbered item in a comment becomes an open bug linked to the card. Items already logged are skipped.
async function addBugsFromComment( card, text ) {
	const items = numberedItems( text ).filter( ( t ) => ! bugExists( card.id, t ) ).map( ( description ) => ( {
		description, related: card.title, card_id: card.id, status: 'open', assignee: BUG_STATUS_ASSIGNEE.open,
	} ) );
	if ( ! items.length ) {
		return 0;
	}
	const bugs = await track( api.post( `${ boardPath }/bugs`, { items } ) );
	data.bugs.push( ...bugs );
	renderBugs();
	return bugs.length;
}

function renderComments() {
	const card = cardById( openId );
	if ( ! card ) {
		return;
	}
	dialog.querySelector( '[data-comments-head]' ).textContent = card.comments.length ? `Comments (${ card.comments.length })` : 'Comments';
	dialog.querySelector( '[data-comment-list]' ).replaceChildren( ...card.comments.map( ( c ) => {
		const box = el( 'div', 'comment' );
		const head = el( 'div', 'comment__head' );
		const del = el( 'button', 'comment__delete', '✕' );
		del.type = 'button';
		del.title = 'Delete comment';
		del.addEventListener( 'click', () => {
			if ( window.confirm( 'Delete this comment?' ) ) {
				card.comments = card.comments.filter( ( x ) => x !== c );
				quiet( track( api.del( `comments/${ c.id }` ) ) );
				render();
			}
		} );
		head.append( el( 'strong', '', c.author || 'Someone' ), el( 'span', '', fmtDate( c.at, true ) ), del );
		box.append( head, linkifiedText( 'comment__text', c.text ) );
		const items = numberedItems( c.text );
		if ( items.length ) {
			const fresh = items.filter( ( t ) => ! bugExists( card.id, t ) );
			const btn = el( 'button', 'btn btn--small', fresh.length
				? `Add ${ fresh.length } bug${ fresh.length === 1 ? '' : 's' } to Bug tracker`
				: 'All items are in the Bug tracker' );
			btn.type = 'button';
			btn.disabled = ! fresh.length;
			btn.addEventListener( 'click', async () => {
				btn.disabled = true;
				try {
					const added = await addBugsFromComment( card, c.text );
					btn.textContent = `Added ${ added } bug${ added === 1 ? '' : 's' } ✓`;
				} catch {
					btn.disabled = false;
				}
			} );
			box.append( btn );
		}
		return box;
	} ) );
}

// Posts what's in the comment box. Also runs when the dialog closes, so a typed comment isn't lost.
async function postComment() {
	const card = cardById( openId );
	const input = dialog.querySelector( '[data-comment-text]' );
	const text = input.value.trim();
	if ( ! card || ! text ) {
		return;
	}
	input.value = '';
	try {
		card.comments.push( await track( api.post( `cards/${ card.id }/comments`, { text } ) ) );
		render();
	} catch {
		input.value = text;
	}
}

dialog.querySelector( '[data-comment-add]' ).addEventListener( 'click', postComment );
dialog.querySelector( '[data-comment-text]' ).addEventListener( 'keydown', ( e ) => {
	if ( e.key === 'Enter' && ( e.metaKey || e.ctrlKey ) ) {
		e.preventDefault();
		postComment();
	}
} );
dialog.querySelector( '[data-card-delete]' ).addEventListener( 'click', () => {
	const card = cardById( openId );
	if ( card && window.confirm( `Delete "${ card.title || 'Untitled' }"?` ) ) {
		openId = 0;
		dialog.close();
		deleteCard( card );
	}
} );
dialog.querySelector( '[data-card-close]' ).addEventListener( 'click', () => dialog.close() );
dialog.addEventListener( 'click', ( e ) => {
	if ( e.target === dialog ) {
		dialog.close();
	}
} );
dialog.addEventListener( 'close', () => {
	const card = cardById( openId );
	if ( card && dialog.querySelector( '[data-comment-text]' ).value.trim() ) {
		postComment();
	}
	flushQueued();
	if ( card && ! card.title.trim() ) {
		if ( card.id === newCardId && ! card.notes && ! card.comments.length ) {
			deleteCard( card );
		} else {
			card.title = 'Untitled';
			saveCard( card, { title: card.title } );
		}
	}
	openId = 0;
	newCardId = 0;
	render();
} );

// ----- Bug tracker -----

const bugRows = $( '[data-bug-rows]' );
const hideClosed = $( '[data-bug-hide-closed]' );
const isClosed = ( bug ) => settings.closed.includes( bug.status );

function growTextarea( t ) {
	t.style.height = 'auto';
	t.style.height = t.scrollHeight + 'px';
}

// Clear focus from the table so the next render isn't skipped (Safari doesn't focus clicked buttons).
function leaveBugTable() {
	if ( bugRows.contains( document.activeElement ) ) {
		document.activeElement.blur();
	}
}

function bugRow( bug, i ) {
	const closed = isClosed( bug );
	const tr = el( 'tr', closed ? 'is-closed' : '' );

	const desc = el( 'textarea' );
	desc.rows = 1;
	desc.value = bug.description || '';
	desc.placeholder = 'Describe the bug';
	desc.setAttribute( 'aria-label', 'Bug description' );
	desc.addEventListener( 'input', () => {
		growTextarea( desc );
		bug.description = desc.value;
		later( `bug-${ bug.id }-description`, () => saveBug( bug, { description: bug.description } ) );
	} );

	// Related to: a card on this board (a link that opens it) or free text.
	const linked = bug.card_id ? cardById( bug.card_id ) : null;
	let rel;
	if ( linked ) {
		rel = el( 'div', 'bugs__card' );
		const a = el( 'a', '', linked.title || 'Untitled' );
		a.href = '#';
		a.title = 'Open this card';
		a.addEventListener( 'click', ( e ) => {
			e.preventDefault();
			showTab( 'cards' );
			openCard( linked.id );
		} );
		const unlink = el( 'button', 'bugs__unlink', '✕' );
		unlink.type = 'button';
		unlink.title = 'Remove the card link';
		unlink.addEventListener( 'click', () => {
			leaveBugTable();
			bug.card_id = 0;
			bug.related = '';
			saveBug( bug, { card_id: 0, related: '' } );
			renderBugs();
		} );
		rel.append( a, unlink );
	} else {
		rel = el( 'input' );
		rel.value = bug.related || '';
		rel.setAttribute( 'list', 'dl-related' );
		rel.placeholder = 'Card name or area';
		rel.setAttribute( 'aria-label', 'Related to' );
		rel.addEventListener( 'change', () => {
			const v = rel.value.trim();
			const match = v && data.cards.find( ( c ) => ( c.title || '' ).trim().toLowerCase() === v.toLowerCase() );
			bug.related = match ? match.title : v;
			bug.card_id = match ? match.id : 0;
			saveBug( bug, { related: bug.related, card_id: bug.card_id } );
		} );
	}

	const status = el( 'select', 'bugs__status bugs__status--' + ( bug.status || 'open' ) );
	Object.entries( settings.statuses ).forEach( ( [ v, label ] ) => status.add( new Option( label, v ) ) );
	status.value = bug.status || 'open';
	status.setAttribute( 'aria-label', 'Bug status' );
	status.addEventListener( 'change', () => {
		bug.status = status.value;
		const fields = { status: bug.status };
		if ( BUG_STATUS_ASSIGNEE[ bug.status ] ) {
			bug.assignee = fields.assignee = BUG_STATUS_ASSIGNEE[ bug.status ];
		}
		saveBug( bug, fields );
		status.blur();
		renderBugs();
	} );

	const due = el( 'input' );
	due.type = 'date';
	due.value = bug.due || '';
	due.setAttribute( 'aria-label', 'Deadline' );
	if ( bug.due && bug.due < today() && ! closed ) {
		due.classList.add( 'is-overdue' );
	}
	due.addEventListener( 'change', () => {
		bug.due = due.value;
		saveBug( bug, { due: bug.due } );
		due.blur();
		renderBugs();
	} );

	const who = el( 'input', whoClass( bug.assignee ) );
	who.value = bug.assignee || '';
	who.setAttribute( 'list', 'dl-people' );
	who.placeholder = 'Unassigned';
	who.setAttribute( 'aria-label', 'Assigned to' );
	who.addEventListener( 'change', () => {
		bug.assignee = who.value.trim();
		saveBug( bug, { assignee: bug.assignee } );
	} );

	const del = el( 'button', 'bugs__delete', '✕' );
	del.type = 'button';
	del.title = 'Delete bug';
	del.addEventListener( 'click', () => {
		if ( ! window.confirm( `Delete bug ${ i + 1 }?` ) ) {
			return;
		}
		leaveBugTable();
		data.bugs = data.bugs.filter( ( b ) => b !== bug );
		quiet( track( api.del( `bugs/${ bug.id }` ) ) );
		renderBugs();
	} );

	for ( const [ className, content ] of [ [ 'bugs__sl', String( i + 1 ) ], [ 'bugs__desc', desc ], [ 'bugs__rel', rel ],
		[ '', status ], [ '', due ], [ 'bugs__who', who ], [ '', del ] ] ) {
		const td = el( 'td', className );
		td.append( content );
		tr.append( td );
	}
	return tr;
}

function renderBugs() {
	const open = data.bugs.filter( ( b ) => ! isClosed( b ) ).length;
	$( '[data-bug-count]' ).textContent = open ? String( open ) : '';
	// Don't rebuild while someone is typing in the table.
	if ( bugRows.contains( document.activeElement ) ) {
		return;
	}
	const rows = [];
	data.bugs.forEach( ( bug, i ) => {
		if ( ! ( hideClosed.checked && isClosed( bug ) ) ) {
			rows.push( bugRow( bug, i ) );
		}
	} );
	if ( ! rows.length ) {
		const tr = el( 'tr' );
		const td = el( 'td', 'bugs__empty', data.bugs.length ? 'No open bugs.' : 'No bugs yet. Click “+ Add bug” to log one.' );
		td.colSpan = 7;
		tr.append( td );
		rows.push( tr );
	}
	bugRows.replaceChildren( ...rows );
	bugRows.querySelectorAll( 'textarea' ).forEach( growTextarea );
	const titles = unique( data.cards.map( ( c ) => c.title ) );
	fillDatalist( 'dl-related', unique( [ ...titles, ...data.bugs.filter( ( b ) => ! b.card_id ).map( ( b ) => b.related ) ] ) );
}

$( '[data-bug-add]' ).addEventListener( 'click', async () => {
	try {
		const [ bug ] = await track( api.post( `${ boardPath }/bugs`, {} ) );
		data.bugs.push( bug );
		leaveBugTable();
		renderBugs();
		bugRows.querySelector( 'tr:last-child textarea' )?.focus();
	} catch {}
} );
hideClosed.addEventListener( 'change', () => {
	try {
		localStorage.setItem( HIDE_CLOSED_KEY, hideClosed.checked ? '1' : '' );
	} catch {}
	renderBugs();
} );
// Re-render once the person leaves the table, so Sl No, colours and overdue marks catch up.
bugRows.addEventListener( 'focusout', ( e ) => {
	if ( ! bugRows.contains( e.relatedTarget ) ) {
		setTimeout( renderBugs, 0 );
	}
} );

// ----- Tabs -----

function showTab( name ) {
	root.querySelectorAll( '[data-tab]' ).forEach( ( t ) => t.setAttribute( 'aria-selected', String( t.dataset.tab === name ) ) );
	root.querySelectorAll( '[data-panel]' ).forEach( ( p ) => {
		p.hidden = p.dataset.panel !== name;
	} );
	try {
		localStorage.setItem( TAB_KEY, name );
	} catch {}
}
root.querySelectorAll( '[data-tab]' ).forEach( ( t ) => t.addEventListener( 'click', () => showTab( t.dataset.tab ) ) );

// ----- Goals and notes -----

const boardText = [ ...root.querySelectorAll( '[data-board-text]' ) ];

function defaultText( key ) {
	return DEFAULT_TEXT[ key ].map( ( h ) => `<h3>${ h }</h3><ul><li><br></li><li><br></li><li><br></li></ul>` ).join( '' );
}

function renderBoardText() {
	for ( const editor of boardText ) {
		const key = editor.dataset.boardText;
		if ( document.activeElement !== editor ) {
			const html = cleanHtml( data.board[ key ] || defaultText( key ) );
			if ( editor.innerHTML !== html ) {
				editor.innerHTML = html;
			}
		}
	}
}

for ( const editor of boardText ) {
	const key = editor.dataset.boardText;
	setupEditor( editor, () => {
		data.board[ key ] = editorHtml( editor );
		later( `board-${ key }`, () => quiet( track( api.patch( boardPath, { [ key ]: data.board[ key ] } ) ) ), 800 );
	} );
}

// ----- Backup and import -----

$( '[data-export]' ).href = `${ settings.api }${ boardPath }/export?_wpnonce=${ encodeURIComponent( settings.nonce ) }`;

$( '[data-import]' )?.addEventListener( 'change', async ( e ) => {
	const input = e.target;
	const file = input.files[ 0 ];
	input.value = '';
	if ( ! file || ! window.confirm( `Replace everything on this board (cards, comments, bugs, goals and notes) with the contents of ${ file.name }?\n\nDownload a backup first if you might need the current version.` ) ) {
		return;
	}
	const body = new FormData();
	body.append( 'file', file );
	try {
		const counts = await track( api.post( `${ boardPath }/import`, body ) );
		window.alert( `Imported ${ counts.cards } cards, ${ counts.comments } comments and ${ counts.bugs } bugs in ${ counts.lists } lists.` );
		load();
	} catch ( error ) {
		window.alert( `Import failed: ${ error.message }` );
	}
} );

// ----- Start -----

try {
	hideClosed.checked = localStorage.getItem( HIDE_CLOSED_KEY ) === '1';
	if ( localStorage.getItem( TAB_KEY ) === 'bugs' ) {
		showTab( 'bugs' );
	}
} catch {}

load();
window.addEventListener( 'focus', refresh );
setInterval( refresh, REFRESH_MS );
