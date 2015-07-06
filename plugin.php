<?php
/*
Plugin Name: Link Roundups
Plugin URI: https://github.com/INN/link-roundups
Description: Link Roundups (previously Argo Links) allows you to save links from the web to use in roundup posts for your WordPress site.
Author: INN, Project Argo, Mission Data
Version: 0.3
Author URI: http://nerds.inn.org/
License: GPLv2
*/

/**
 * Register Plugin filesName
 *
 * @todo: http://wppb.me/
 * @since 0.3
 */

/** Setup Saved Links **/
require_once('saved-links.php');
require_once('saved-links-class.php');

/** Setup Saved Links Peripherals **/
require_once('saved-links-widget.php');
require_once('saved-links-ajax.php');

require_once('link-roundups-save-this.php');

/** Setup Link Roundups **/
require_once('link-roundups.php');
require_once('link-roundups-widget.php');
require_once('inc/link-roundups-update.php');


/** Include the MailChimp PHP API **/
require_once(__DIR__ . '/vendor/mailchimp-api-php/src/Mailchimp.php');

/** Initialize Plugin using its' class 
  *
  * @see saved-links-class.php
  *
  **/
  
SavedLinks::init();
LinkRoundups::init();
add_action('init', 'argo_flush_permalinks', 99);


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