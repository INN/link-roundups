<?php
/**
 * Functions adding compatibility to INN's Largo theme
 *
 * @link https://github.com/INN/Largo
 * @since 0.2
 * @package 
 */

/**
 * Use partials/content-rounduplink.php for the rounduplink archive LMP
 *
 * @param String   $partial    The largo partial to use for the rounduplink post type.
 * @param WP_Query $post_query The WP_Query being used for Load More Posts.
 * @return String
 */
function lr_lmp_choose_partial( $partial, $post_query ) {
	if ( is_object( $post_query ) && property_exists( $post_query, 'query_vars' ) ) {
		$post_query = $post_query->query_vars;
	}

	if ( isset( $post_query['post_type'] ) && 'rounduplink' === $post_query['post_type'] ) {
		$partial = 'rounduplink';
	}
	return $partial;
}
add_filter( 'largo_lmp_template_partial', 'lr_lmp_choose_partial', 10, 2 );

/**
 * Use partials/content-rounduplink.php for the search LMP.
 *
 * @param  String $partial   The template partial to use for link roundups.
 * @param  String $post_type The post type.
 * @param  Mixed  $context   The context of this partial's use.
 * @return String $partial   The template partial to use.
 */
function lr_largo_partial_by_post_type( $partial, $post_type, $context ) {
	if ( 'rounduplink' === $post_type ) {
		$partial = 'rounduplink';
	}
	return $partial;
}
add_filter( 'largo_partial_by_post_type', 'lr_largo_partial_by_post_type', 10, 3 );
