<?php
/**
 * A board: cards (kanban) and bug tracker tabs, then goals and notes. board.js fills it in from the
 * REST API; this file only lays out the frame and passes settings in data-* attributes.
 *
 * @package Intella_Admin
 * @var array $args The board.
 */

defined( 'ABSPATH' ) || exit;

$intella_board    = $args['board'];
$intella_settings = array(
	'api'      => esc_url_raw( rest_url( 'intella-admin/v1/' ) ),
	'nonce'    => wp_create_nonce( 'wp_rest' ),
	'boardId'  => (int) $intella_board->id,
	'me'       => intella_admin_board_name( wp_get_current_user() ),
	'canManage' => intella_admin_can_manage(),
	'statuses' => intella_admin_bug_statuses(),
	'closed'   => intella_admin_closed_bug_statuses(),
);
?>
<div class="board-page" data-board-app data-settings="<?php echo esc_attr( wp_json_encode( $intella_settings ) ); ?>">
	<div class="board-head">
		<h1><?php echo esc_html( $intella_board->name ); ?></h1>
		<p class="save-state" data-save-state role="status" aria-live="polite"><?php esc_html_e( 'Loading…', 'intella' ); ?></p>
		<div class="board-head__tools">
			<a class="btn" data-export href="#"><?php esc_html_e( 'Download backup', 'intella' ); ?></a>
			<?php if ( intella_admin_can_manage() ) : ?>
				<label class="btn">
					<?php esc_html_e( 'Import tracker-data.js', 'intella' ); ?>
					<input class="visually-hidden" type="file" accept=".js,.json" data-import>
				</label>
			<?php endif; ?>
		</div>
	</div>
	<noscript><p class="notice notice--error"><?php esc_html_e( 'The board needs JavaScript.', 'intella' ); ?></p></noscript>

	<div class="tabs" role="tablist">
		<button type="button" role="tab" id="tab-cards" aria-controls="panel-cards" aria-selected="true" data-tab="cards"><?php esc_html_e( 'Cards', 'intella' ); ?></button>
		<button type="button" role="tab" id="tab-bugs" aria-controls="panel-bugs" aria-selected="false" data-tab="bugs"><?php esc_html_e( 'Bug tracker', 'intella' ); ?> <span class="count" data-bug-count></span></button>
	</div>

	<section id="panel-cards" role="tabpanel" aria-labelledby="tab-cards" data-panel="cards">
		<div class="filters">
			<label class="visually-hidden" for="filter-text"><?php esc_html_e( 'Search cards', 'intella' ); ?></label>
			<input type="search" id="filter-text" placeholder="<?php esc_attr_e( 'Search cards…', 'intella' ); ?>" data-filter="text">
			<label class="visually-hidden" for="filter-who"><?php esc_html_e( 'Filter by assignee', 'intella' ); ?></label>
			<select id="filter-who" data-filter="who"></select>
			<label class="visually-hidden" for="filter-tag"><?php esc_html_e( 'Filter by tag', 'intella' ); ?></label>
			<select id="filter-tag" data-filter="tag"></select>
		</div>
		<div class="kanban" data-kanban></div>
	</section>

	<section id="panel-bugs" role="tabpanel" aria-labelledby="tab-bugs" data-panel="bugs" hidden>
		<div class="filters">
			<button type="button" class="btn btn--primary" data-bug-add><?php esc_html_e( '+ Add bug', 'intella' ); ?></button>
			<label class="check"><input type="checkbox" data-bug-hide-closed> <?php esc_html_e( "Hide fixed and won't fix", 'intella' ); ?></label>
		</div>
		<div class="table-wrap">
			<table class="table bugs">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Sl No', 'intella' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Bug description', 'intella' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Related to', 'intella' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Bug status', 'intella' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Deadline', 'intella' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Assigned to', 'intella' ); ?></th>
						<th scope="col"><span class="visually-hidden"><?php esc_html_e( 'Delete', 'intella' ); ?></span></th>
					</tr>
				</thead>
				<tbody data-bug-rows></tbody>
			</table>
		</div>
	</section>

	<div class="hub-text">
		<section class="hub-text__block">
			<h2 id="goals-label"><?php esc_html_e( 'Goals', 'intella' ); ?></h2>
			<div class="rte" contenteditable="true" aria-labelledby="goals-label" data-board-text="goals" data-placeholder="<?php esc_attr_e( 'Write the goals here…', 'intella' ); ?>"></div>
		</section>
		<section class="hub-text__block">
			<h2 id="notes-label"><?php esc_html_e( 'Notes', 'intella' ); ?></h2>
			<div class="rte" contenteditable="true" aria-labelledby="notes-label" data-board-text="notes" data-placeholder="<?php esc_attr_e( 'Write notes here…', 'intella' ); ?>"></div>
		</section>
	</div>

	<dialog class="card-dialog" data-card-dialog aria-labelledby="card-title">
		<input class="card-dialog__title" id="card-title" placeholder="<?php esc_attr_e( 'Card title', 'intella' ); ?>" aria-label="<?php esc_attr_e( 'Card title', 'intella' ); ?>" data-field="title">
		<div class="card-dialog__fields">
			<label><?php esc_html_e( 'List', 'intella' ); ?><select data-field="list_id"></select></label>
			<label><?php esc_html_e( 'Assignee', 'intella' ); ?><input list="dl-people" placeholder="<?php esc_attr_e( 'Unassigned', 'intella' ); ?>" data-field="assignee"></label>
			<label><?php esc_html_e( 'Due date', 'intella' ); ?><input type="date" data-field="due"></label>
			<label><?php esc_html_e( 'Tag / area', 'intella' ); ?><input list="dl-tags" placeholder="<?php esc_attr_e( 'e.g. Design', 'intella' ); ?>" data-field="tag"></label>
		</div>
		<span class="field-label" id="card-notes-label"><?php esc_html_e( 'Description', 'intella' ); ?></span>
		<div class="rte" contenteditable="true" aria-labelledby="card-notes-label" data-field="notes" data-placeholder="<?php esc_attr_e( 'Add more detail…', 'intella' ); ?>"></div>
		<div class="comments">
			<h3 data-comments-head><?php esc_html_e( 'Comments', 'intella' ); ?></h3>
			<div data-comment-list></div>
			<div class="comments__form">
				<label class="visually-hidden" for="comment-text"><?php esc_html_e( 'Comment', 'intella' ); ?></label>
				<textarea id="comment-text" placeholder="<?php esc_attr_e( 'Write a comment… (⌘/Ctrl+Enter to post)', 'intella' ); ?>" data-comment-text></textarea>
				<button type="button" class="btn" data-comment-add><?php esc_html_e( 'Add comment', 'intella' ); ?></button>
			</div>
		</div>
		<div class="card-dialog__foot">
			<button type="button" class="btn btn--danger" data-card-delete><?php esc_html_e( 'Delete card', 'intella' ); ?></button>
			<span class="muted" data-card-created></span>
			<button type="button" class="btn btn--primary" data-card-close><?php esc_html_e( 'Done', 'intella' ); ?></button>
		</div>
	</dialog>

	<datalist id="dl-people"></datalist>
	<datalist id="dl-tags"></datalist>
	<datalist id="dl-related"></datalist>
</div>
