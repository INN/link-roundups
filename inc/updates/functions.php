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
	_lroundups_convert_posts( 'argolinkroundups', 'roundup' );
	_lroundups_convert_posts( 'argolinks', 'rounduplink' );
}
add_action( 'lroundups_update_0.3', 'lroundups_update_post_types', 10, 2 );

/**
 * Migrates old options from argo-links
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
			array( 'argo_link_roundups_', 'link_roundups_' ),
			'lroundups_',
			$old_option
		);
		$old_value  = get_option( $old_option );

		if ( $old_value ) {
			$result = update_option( $new_option, $old_value );
		}

		if ( $result ) {
			delete_option( $old_option );
		}
	}
}
add_action( 'lroundups_update_0.3', 'lroundups_migrate_options', 10 );

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
 * @since 0.3.2
 */
function lroundups_update_post_terms( $to, $from ) {
	$old_metas = array(
		'argo_link_url'            => 'lr_url',
		'argo_link_description'    => 'lr_desc',
		'argo_link_source'         => 'lr_source',
		'argo_link_img_url_import' => 'lr_img',
	);

	foreach ( $old_metas as $old_meta => $new_meta ) {
		_lroundups_convert_posts_meta( $old_meta, $new_meta );
	}
}
add_action( 'lroundups_update_0.3.2', 'lroundups_update_post_terms', 15, 2 );

/**
 * Update taxonomy name
 *  · argo-link-tags → lr-tags
 *
 * @since 0.3.2
 */
function lroundups_update_taxonomy_name( $to, $from ) {
	_lroundups_convert_taxonomy_name( 'argo-link-tags', 'lr-tags' );
}
add_action( 'lroundups_update_0.3.2', 'lroundups_update_taxonomy_name', 20, 2 );

/**
 * Get rid of an old option
 *
 * @since 0.3.2
 */
function lroundups_delete_argolinks_flush_option() {
	delete_option( 'argolinks_flush' );
}
add_action( 'lroundups_update_0.3.2', 'lroundups_delete_argolinks_flush_option', 10 );

/*
 * Update utility functions below
 */

/**
 * Migrate any custom post type to another
 *
 * @since 0.3
 */
function _lroundups_convert_posts( $old_post_type, $new_post_type ) {
	global $wpdb;
	$wpdb->query(
		$wpdb->prepare(
			"UPDATE $wpdb->posts
		SET post_type = %s
		WHERE post_type = %s;",
			$new_post_type,
			$old_post_type
		)
	);
}

/**
 * Migrate any old custom meta fields to new keys
 *
 * @since 0.3.2
 */
function _lroundups_convert_posts_meta( $old_meta, $new_meta ) {
	global $wpdb;
	$wpdb->query(
		$wpdb->prepare(
			"UPDATE  $wpdb->postmeta
		SET meta_key = %s
		WHERE meta_key = %s;",
			$new_meta,
			$old_meta
		)
	);
}

/**
 * Migrate a custom taxonomy to a new key
 *
 * @since 0.3.2
 */
function _lroundups_convert_taxonomy_name( $old_tax, $new_tax ) {
	global $wpdb;
	$wpdb->query(
		$wpdb->prepare(
			"UPDATE $wpdb->term_taxonomy
		SET taxonomy = %s
		WHERE taxonomy = %s;",
			$new_tax,
			$old_tax
		)
	);
}
