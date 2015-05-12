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
 * Add a "Create MailChimp Campaign" button the post publish actions meta box.
 *
 * @since 0.2
 */
function argo_links_create_mailchimp_campaign_button() {
	global $post;

	if ($post->post_type !== 'argolinkroundups')
		return;

	if ( false == get_option('argo_link_roundups_use_mailchimp_integration') || false == get_option('argo_link_roundups_mailchimp_api_key') )
		return;

	$mc_web_id = get_post_meta($post->ID, 'mc_web_id', true);
?>
	<style type="text/css">
		#argo-links-publish-actions {
		  margin: 10px 0;
		  padding: 0 0 12px 0;
		  border-bottom: 1px solid #dadada;
		  text-align: center;
		}
	</style>

	<div id="argo-links-publish-actions">
	<?php if (empty($mc_web_id)) { ?>
		<input type="submit"
			name="argo_links_create_mailchimp_campaign"
			id="argo-links-create-mailchimp-campaign"
			class="button button-primary button-large" value="Create MailChimp Campaign">
	<?php } else { ?>
		<p>A MailChimp roundup campaign exists for thist post.</p>
		<a class="button" target="blank"
			href="https://<?php echo argo_links_get_mc_api_endpoint(); ?>.admin.mailchimp.com/campaigns/wizard/confirm?id=<?php echo $mc_web_id; ?>">Edit in MailChimp.</a>
	<?php } ?>
	</div>
<?php
}
add_action('post_submitbox_start', 'argo_links_create_mailchimp_campaign_button');

/**
 * Include CSS and Javascript files used on the post edit screen.
 *
 * @since 0.2
 */
function argo_links_enqueue_assets() {
	$plugin_path = plugins_url(basename(__DIR__), __DIR__);

	wp_register_script(
		'argo-links-common', $plugin_path . '/js/argo-links-common.js',
		array('jquery', 'underscore', 'backbone'), 0.2, true
	);

	wp_register_script(
		'argo-link-roundups', $plugin_path . '/js/argo-link-roundups.js',
		array('argo-links-common'), 0.2, true
	);

	wp_register_style('argo-links-common', $plugin_path . '/css/argo-links-common.css');

	$screen = get_current_screen();
	if ($screen->base == 'post' && $screen->post_type == 'argolinkroundups') {
		wp_enqueue_script('argo-link-roundups');
		wp_enqueue_style('argo-links-common');
	}
}
add_action('admin_enqueue_scripts', 'argo_links_enqueue_assets');

/**
 * Print the underscore template for the AL.Modal view.
 *
 * @since 0.2
 */
function argo_links_modal_underscore_template() { ?>
<script type="text/template" id="argo-links-modal-tmpl">
	<div class="argo-links-modal-header">
		<div class="argo-links-modal-close"><span class="close">&#10005;</span></div>
	</div>
	<div class="argo-links-modal-content"><% if (content) { %><%= content %><% } %></div>
	<div class="argo-links-modal-actions">
		<span class="spinner"></span>
		<% _.each(actions, function(v, k) { %>
			<a href="#" class="<%= k %> button button-primary"><%= k %></a>
		<% }); %>
	</div>
</script><?php
}

/**
 * Builds an AL object with common attributes used throughout the plugin's javascript files.
 *
 * @since 0.2
 */
function argo_links_json_obj($add=array()) {
	global $post;

	$mc_api_endpoint = argo_links_get_mc_api_endpoint();

	return array_merge(array(
		'post_id' => $post->ID,
		'ajax_nonce' => wp_create_nonce('argo_links_ajax_nonce'),
		'mc_api_endpoint' => $mc_api_endpoint
	), $add);
}

function argo_links_add_modal_template() {
	$screen = get_current_screen();
	if ($screen->base == 'post' && $screen->post_type == 'argolinkroundups') {
		argo_links_modal_underscore_template();

?>
		<script type="text/javascript">
			var AL = <?php echo json_encode(argo_links_json_obj()); ?>;
		</script>
<?php
	}
}
add_action('admin_footer', 'argo_links_add_modal_template');

function argo_links_get_mc_api_endpoint() {
	$mc_api_key = get_option('argo_link_roundups_mailchimp_api_key');
	$mc_api_key_parts = explode('-', $mc_api_key);
	return $mc_api_key_parts[1];
}

/**
 * Set us up the files
 */
require_once('argo-link-roundups.php');
require_once('argo-links-widget.php');
require_once('argo-links-class.php');
require_once('argo-links-ajax.php');

/* Initialize the plugin using it's init() function */
ArgoLinkRoundups::init();
ArgoLinks::init();
add_action('init', 'argo_flush_permalinks', 99);
