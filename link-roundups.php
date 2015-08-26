<?php

/*
Plugin Name: Link Roundups
Plugin URI: https://github.com/INN/link-roundups
Description: Use Link Roundups to aggregate links and create roundup posts. Mailchimp API integration and browser bookmark tool. Formerly argo-links from NPR's Project Argo.
Author: INN, Project Argo, Mission Data
Version: 0.3.2
Author URI: http://nerds.inn.org/
License: GPLv2

Seeking Link Roundups Post Type functions? They use lroundups instead of link-roundups.
*/

// Plugin directory
define( 'LROUNDUPS_PLUGIN_FILE', __FILE__ );

/**
 * Saved Links
 */
// Post Type Functions
require_once(__DIR__ . '/inc/saved-links/class-saved-links.php');
// Widget
require_once(__DIR__ . '/inc/saved-links/widget.php');

/**
 * Link Roundups
 */
// Post Type Functions
require_once(__DIR__ . '/inc/lroundups/class-lroundups.php');
// Widget
require_once(__DIR__ . '/inc/lroundups/widget.php');

/**
 * Mailchimp API and Modal Functions
 */
require_once(__DIR__ . '/vendor/mailchimp-api-php/src/Mailchimp.php'); // API files
require_once(__DIR__ . '/inc/lroundups/mailchimp-admin.php'); // Integration Code

/**
 * Save to Site Browser Bookmark Tool
 */
require_once(__DIR__ . '/inc/lroundups/browser-bookmark.php');

/**
 * Add Backwards Compatability with argo-links
 */
require_once(__DIR__ . '/inc/compatibility.php');


/**
 * Initialize the plugin using its init() function
 */
LRoundups::init();
SavedLinks::init();
add_action( 'init', 'lroundups_flush_permalinks', 99 );

require_once( 'inc/updates/index.php' );


/**
 * On activation, we'll set an transient (temporary option) called 'lroundups_flush' to true,
 * so our plugin knows, on initialization, to flush the rewrite rules.
 *
 * @link https://gist.github.com/clioweb/871595
 * @since 0.2
 * @see lroundups_deactivation
 * @see lroundups_flush_permalinks
 */
function lroundups_activation() {
	set_transient( 'lroundups_flush', true, 30 );
}
register_activation_hook( __FILE__, 'lroundups_activation' );


/**
 * On deactivation, we'll remove our 'argolinks_flush' option if it is
 * still around. It shouldn't be after we register our post type.
 *
 * @link https://gist.github.com/clioweb/871595
 * @since 0.2
 * @see lroundups_activation
 * @see lroundups_flush_permalinks
 */
function lroundups_deactivation() {
	if ( get_transient('lroundups_flush' ) !== false ) {
		delete_transient( 'lroundups_flush');
	}
}
register_deactivation_hook( __FILE__, 'lroundups_deactivation' );


/**
 * Utility function to reset the permalinks.
 *
 * Called in ArgoLinks::register_permalinks() to reset the WordPress permalinks after the
 * Saved Links post type is registered in SavedLinks::register_permalinks(), which is run after
 * the Link Roundups post type is registered in LRoundups::register_permalinks()
 *
 * @return bool If get_option('argolinks_flush') is true or false
 * @link https://gist.github.com/clioweb/871595
 * @since 0.2
 * @see lroundups_activation
 * @see lroundups_deactivation
 */
function lroundups_flush_permalinks() {
	if (get_transient('lroundups_flush') === true) {
		flush_rewrite_rules();
		delete_transient( 'lroundups_flush' );
		return true;
	}
	return false;
}

/**
 * Include CSS and Javascript files used on the post edit screen.
 *
 * @since 0.2
 */
function link_roundups_enqueue_assets() {
	$plugin_path = plugins_url( basename( __DIR__ ), __DIR__ );
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_register_script(
		'links-common', $plugin_path . '/js/links-common' . $suffix . '.js',
		array( 'jquery', 'underscore', 'backbone' ), 0.3, true
	);

	wp_register_script(
		'link-roundups', $plugin_path . '/js/lroundups' . $suffix . '.js',
		array( 'links-common' ), 0.3, true
	);

	wp_register_style( 'lroundups-admin', $plugin_path . '/css/lroundups-admin' . $suffix . '.css' );

	$screen = get_current_screen();
	if ( $screen->base == 'post' && ( $screen->post_type == 'roundup' || $screen->post_type == 'rounduplink' ) ) {
		wp_enqueue_script( 'link-roundups' );
		wp_enqueue_style( 'lroundups-admin' );
	}

	if ($screen->base == 'roundup_page_link-roundups-options')
		wp_enqueue_script('link-roundups');
}
add_action( 'admin_enqueue_scripts', 'link_roundups_enqueue_assets' );

/**
 * Load plugin textdomain.
 *
 * @since 0.3
 */
function link_roundups_load_textdomain() {
  load_plugin_textdomain( 'link-roundups', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' ); 
}
add_action( 'plugins_loaded', 'link_roundups_load_textdomain' );

/**
 * Fetches info from a page's <meta> tags and
 * returns an array of that information.
 *
 * @see http://code.ramonkayo.com/simple-scraper/
 * @see link-roundups-browser-bookmark.php
 * @since 0.3
 *
 * @param string $url the url of the page to scrape
 */
function lroundups_scrape_url($url) {

	require_once __DIR__. '/inc/WPSimpleScraper.php'; // license in directory

	$response = array();
	try {
		$scraper = new WPSimpleScraper($url);
		$data = $scraper->getAllData();

		$response['success'] = true;
		$response['meta'] = $data;
	} catch (Exception $e) {
		$response['success'] = false;
		$response['message'] = 'Something went wrong.';
		$response['meta'] = false;
	}

	return $response;
}
