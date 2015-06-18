<?php

/**
 * Update post terms in database.
 *
 *  · argolinkroundups → roundups
 *  · argolinks → rounduplink
 *
 * @since 0.3
 */
function lroundups_update_post_terms($to,$from) {

	_lroundups_convert_posts('argolinkroundups','roundup');
	_lroundups_convert_posts('argolinks','rounduplink');

}
add_action('lroundups_update_0.3','lroundups_update_post_terms',10,2);

/**
 * Converts post types of one kind to another.
 *
 * @since 0.3
 */
function _lroundups_convert_posts($old_post_type,$new_post_type) {

	global $wpdb;
	$wpdb->query( $wpdb->prepare(
		"UPDATE  $wpdb->posts
    		SET  `post_type` =  %s
    		WHERE  `post_type` = %s;"
		, $new_post_type, $old_post_type )
	);

}



?>
