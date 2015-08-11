<?php

/**
 * Update post terms in database.
 *
 *  · argolinkroundups → roundups
 *  · argolinks → rounduplink
 *
 * @since 0.3
 */
function lroundups_update_post_terms( $to, $from ) {
	_lroundups_convert_posts( 'argolinkroundups','roundup' );
	_lroundups_convert_posts( 'argolinks','rounduplink' );
}
add_action( 'lroundups_update_0.3', 'lroundups_update_post_terms', 10, 2 );


/**
 * Converts post types of one kind to another.
 *
 * @since 0.3
 */
function _lroundups_convert_posts( $old_post_type, $new_post_type ) {
	global $wpdb;
	$wpdb->query( $wpdb->prepare(
		"UPDATE  $wpdb->posts
		SET  `post_type` =  %s
		WHERE  `post_type` = %s;"
		, $new_post_type, $old_post_type )
	);
}

/**
 * Migrates options from argo-links
 *
 * @since 0.3
 */
function lroundups_migrate_options() {
	$old_options = array(
		'argo_link_roundups_custom_url',
		'argo_link_roundups_custom_html',
		'link_roundups_custom_name_singular',
		'link_roundups_custom_name_plural',
		'argo_link_roundups_use_mailchimp_integration',
		'argo_link_roundups_mailchimp_api_key',
		'argo_link_mailchimp_template',
		'argo_link_mailchimp_list',
	);
	
	// check for old options and replace the old prefixes with lroundups_
	foreach ( $old_options as $old_option ) {
		$new_option = str_replace(
			array('argo_link_roundups_', 'link_roundups_'), 'lroundups_', $old_option
		);
		 
		$old_value = get_option($old_option);
		
		$result = update_option($new_option, $old_value);
		if ($result)
			delete_option($old_option);
	}
	
	// check if argo-links flush option is around, we don't need it so just delete it
	// option if true triggered func. that 
	// 1) flush_rewrite_rules() 2) delete_option('argo_flush')
	// since been replaced with transient
	delete_option('argolinks_flush');
}
add_action( 'lroundups_update_0.3', 'lroundups_migrate_options', 10 );