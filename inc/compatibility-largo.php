<?php
/**
 * Functions adding compatibility to INN's Largo theme
 *
 * @link https://github.com/INN/Largo
 * @since 0.2
 */

/**
 * Use partials/content-rounduplink.php for the rounduplink archive LMP
 *
 */
function lr_lmp_choose_partial($partial, $post_query) {
	if ( is_object($post_query) && property_exists( $post_query, 'query_vars')) {
		$post_query = $post_query->query_vars;
	}

	if ( isset($post_query['post_type']) && $post_query['post_type'] == 'rounduplink' ) {
		$partial = 'rounduplink';
	}
	return $partial;
}
add_filter( 'largo_lmp_template_partial', 'lr_lmp_choose_partial', 10, 2);

/**
 * Use partials/content-rounduplink.php for the search LMP.
 */
function lr_largo_partial_by_post_type($partial, $post_type, $context) {
	if ( $post_type == 'rounduplink' ) {
		$partial = 'rounduplink';
	}
	return $partial;
}
add_filter('largo_partial_by_post_type', 'lr_largo_partial_by_post_type', 10, 3);
