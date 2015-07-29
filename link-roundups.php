<?php
/*
Plugin Name: Link Roundups
Plugin URI: https://github.com/INN/link-roundups
Description: Use Link Roundups to aggregate links and create roundup posts. Mailchimp API integration and browser bookmark tool. Formerly argo-links from NPR's Project Argo.
Author: INN, Project Argo, Mission Data
Version: 0.3
Author URI: http://nerds.inn.org/
License: GPLv2

Seeking Link Roundups Post Type functions? They use lroundups instead of link-roundups.

*/

/** Mailchimp API **/

/** Saved Links Post Type **/
require_once('saved-links-class.php');
/** Saved Links Widget **/
require_once('saved-links-widget.php');

/** Link Roundup Post Type Functions **/
require_once('lroundups.php');
/** Link Roundups Widget **/
require_once('lroundups-widget.php');

/** Mailchimp API and Modal Functions **/
require_once(__DIR__ . '/vendor/mailchimp-api-php/src/Mailchimp.php'); // API files
require_once(__DIR__ . '/inc/mailchimp-admin.php'); // Integration Code

/** Save to Site Browser Bookmark Tool **/
require_once('browser-bookmark.php');

/** AJAX Helper Functions **/
require_once('links-ajax.php');

/** Add Backwards Compatability with argo-links **/
require_once(__DIR__ . '/inc/argo-links-compatability.php');


/* Initialize the plugin using its init() function */
LRoundups::init();
SavedLinks::init();
add_action('init', 'lroundups_flush_permalinks', 99);

require_once('inc/lroundups-update.php');

/**
 * On activation, we'll set an option called 'argolinks_flush' to true,
 * so our plugin knows, on initialization, to flush the rewrite rules.
 *
 * @link https://gist.github.com/clioweb/871595
 * @since 0.2
 * @see lroundups_deactivation
 * @see lroundups_flush_permalinks
 */
function lroundups_activation() {
	add_option('argolinks_flush', true);
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
    delete_option('argolinks_flush');
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
	if (get_option('argolinks_flush') == true) {
		flush_rewrite_rules();
		delete_option('argolinks_flush');
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
	$plugin_path = plugins_url(basename(__DIR__), __DIR__);

	wp_register_script(
		'links-common', $plugin_path . '/js/links-common.js',
		array('jquery', 'underscore', 'backbone'), 0.2, true
	);

	wp_register_script(
		'link-roundups', $plugin_path . '/js/lroundups.js',
		array('links-common'), 0.2, true
	);

	wp_register_style('links-common', $plugin_path . '/css/links-common.min.css');

	$screen = get_current_screen();
	if ($screen->base == 'post' && $screen->post_type == 'roundup') {
		wp_enqueue_script('link-roundups');
		wp_enqueue_style('links-common');
	}
}
add_action('admin_enqueue_scripts', 'link_roundups_enqueue_assets');


/**
 * Fetches info from a page's <meta> tags and
 * returns an array of that information.
 *
 * @see http://code.ramonkayo.com/simple-scraper/
 * @see browser-bookmark.php
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

	}

	return $response;
}