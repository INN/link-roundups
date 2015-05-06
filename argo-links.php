<?php

/** Mailchimp API **/
require_once(__DIR__ . '/vendor/mailchimp-api-php/src/Mailchimp.php');

/**
  * @package Argo_Links
  * @version 0.01
  */
/*
Plugin Name: Argo Links
Plugin URI: https://github.com/argoproject/argo-links
Description: The Argo Links Plugin
Author: Project Argo, Mission Data
Version: 1.00
Author URI:
License: GPLv2
*/

/**
 * On activation, we'll set an option called 'argolinks_flush' to true,
 * so our plugin knows, on initialization, to flush the rewrite rules.
 *
 * @link https://gist.github.com/clioweb/871595
 * @since 0.2
 * @see argolinks_deactivation
 * @see argo_flush_permalinks
 */
function argolinks_activation() {
	add_option('argolinks_flush', True);
}
register_activation_hook( __FILE__, 'argolinks_activation' );

/**
 * On deactivation, we'll remove our 'argolinks_flush' option if it is
 * still around. It shouldn't be after we register our post type.
 *
 * @link https://gist.github.com/clioweb/871595
 * @since 0.2
 * @see argolinks_activation
 * @see argo_flush_permalinks
 */
function argolinks_deactivation() {
    delete_option('argolinks_flush');
}
register_deactivation_hook( __FILE__, 'argolinks_deactivation' );

/**
 * Utility function to reset the permalinks.
 *
 * Called in ArgoLinks::register_permalinks() to reset the WordPress permalinks after the
 * argolinks post type is registered in ArgoLinks::register_permalinks(), which is run after
 * the argolinkroundups post type is registered in ArgoLinkRoundups::register_permalinks() 
 *
 * @return bool If get_option('argolinks_flush') is true or false
 * @link https://gist.github.com/clioweb/871595
 * @since 0.2
 * @see argolinks_activation
 * @see argolinks_deactivation
 */
function argo_flush_permalinks() {
	if (get_option('argolinks_flush') == true) {
		flush_rewrite_rules();
		delete_option('argolinks_flush');
		return true;
	}
	return false;
}

/**
 * Set us up the files
 */
require_once('argo-link-roundups.php');
require_once('argo-links-widget.php');
require_once('argo-links-class.php');

/* Initialize the plugin using it's init() function */
ArgoLinkRoundups::init();
ArgoLinks::init();
add_action('init', 'argo_flush_permalinks', 99);
