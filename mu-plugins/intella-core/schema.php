<?php
/**
 * Structured data collected from blocks as the page renders (FAQ questions for now).
 *
 * With Rank Math active, FAQPage is added to Rank Math's schema graph, so the page has one JSON-LD block.
 * Without it, a small JSON-LD script is printed in the footer.
 *
 * @package Intella_Core
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'rank_math/json_ld', 'intella_schema_rank_math', 99 );
add_action( 'wp_footer', 'intella_schema_print' );

/**
 * Questions collected from FAQ blocks on this page.
 *
 * @param array|null $add Items to add (question, answer). Null to read.
 */
function intella_schema_faq( ?array $add = null ): array {
	static $items = array();
	if ( null !== $add ) {
		foreach ( $add as $item ) {
			if ( ! empty( $item['question'] ) && ! empty( $item['answer'] ) ) {
				$items[ $item['question'] ] = wp_strip_all_tags( $item['answer'] );
			}
		}
	}
	return $items;
}

/**
 * The FAQPage node, or null when the page has no questions.
 */
function intella_schema_faq_node(): ?array {
	// Blocks render inside the_content(); make sure that has happened before we read the questions.
	$items = intella_schema_faq();
	if ( ! $items ) {
		return null;
	}
	$questions = array();
	foreach ( $items as $question => $answer ) {
		$questions[] = array(
			'@type'          => 'Question',
			'name'           => $question,
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $answer,
			),
		);
	}
	return array(
		'@type'      => 'FAQPage',
		'@id'        => get_permalink() . '#faq',
		'mainEntity' => $questions,
	);
}

/**
 * Add FAQPage to Rank Math's graph.
 *
 * @param array $data Rank Math JSON-LD entities.
 */
function intella_schema_rank_math( array $data ): array {
	if ( is_singular() ) {
		// Rank Math builds the graph in <head>, before the content renders, so read the FAQ blocks directly.
		$post = get_post();
		$walk = static function ( array $blocks ) use ( &$walk ): void {
			foreach ( $blocks as $block ) {
				if ( 'intella/faq' === $block['blockName'] && ( $block['attrs']['schema'] ?? true ) ) {
					intella_schema_faq( (array) ( $block['attrs']['items'] ?? array() ) );
				}
				$walk( $block['innerBlocks'] );
			}
		};
		$walk( parse_blocks( $post ? $post->post_content : '' ) );
		$node = intella_schema_faq_node();
		if ( $node ) {
			$data['intellaFaq'] = $node;
		}
	}
	return $data;
}

/**
 * Print FAQPage JSON-LD in the footer when Rank Math isn't handling schema.
 */
function intella_schema_print(): void {
	if ( defined( 'RANK_MATH_VERSION' ) || ! is_singular() ) {
		return;
	}
	$node = intella_schema_faq_node();
	if ( $node ) {
		wp_print_inline_script_tag( wp_json_encode( array( '@context' => 'https://schema.org' ) + $node, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ), array( 'type' => 'application/ld+json' ) );
	}
}
