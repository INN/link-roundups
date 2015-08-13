<?php

/**
 * Update post terms in database.
 *
 *  · argolinkroundups → roundups
 *  · argolinks → rounduplink
 *
 * @since 0.3
 */
function lroundups_update_post_types( $to, $from ) {
	_lroundups_convert_posts( 'argolinkroundups','roundup' );
	_lroundups_convert_posts( 'argolinks','rounduplink' );
}
add_action( 'lroundups_update_0.3', 'lroundups_update_post_types', 10, 2 );

/**
 * Update rounduplink post meta in database.
 *
 *  · argo_link_url → lr_url
 *  · argo_link_description → lr_desc
 *  · argo_link_source → lr_source
 *  · argo_link_img_url_import → lr_img
 *
 * Note: _lroundups_convert_posts_meta() indiscriminately targets ALL post types
 * This is okay because argo_link_* is a unique prefix, but note for future use.
 *
 * @since 0.3.1
 */
function lroundups_update_post_terms( $to, $from ) {
	_lroundups_convert_posts_meta( 'argo_link_url', 'lr_url' );
	_lroundups_convert_posts_meta( 'argo_link_description', 'lr_desc' );
	_lroundups_convert_posts_meta( 'argo_link_source', 'lr_source' );
	_lroundups_convert_posts_meta( 'argo_link_img_url_import', 'lr_img' );
}
add_action( 'lroundups_update_0.3.1', 'lroundups_update_post_terms', 15, 2 );

/**
 * Migrate custom post types
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
 * Migrate custom meta fields
 *
 * @since 0.3.1
 */
function _lroundups_convert_posts_meta( $old_meta, $new_meta ) {
	global $wpdb;
	$wpdb->query( $wpdb->prepare(
		"UPDATE  $wpdb->postmeta
		SET  `meta_key` =  `%s`
		WHERE  `meta_key` = `%s`;"
		, $new_meta, $old_meta )
	);
}

/**
 * Migrates options from argo
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
	
	foreach ( $old_options as $old_option ) {
		$new_option = str_replace(
				array('argo_link_roundups_', 'link_roundups_'), 'lroundups_', $old_option
		);
		 
		$old_value = get_option($old_option);
		
		$result = update_option($new_option, $old_value);
		if ($result)
			delete_option($old_option);
	}
}
add_action( 'lroundups_update_0.3', 'lroundups_migrate_options', 10 );