<?php
/*
Plugin Name: Link Roundups
Plugin URI: https://github.com/INN/link-roundups
Description: Use Link Roundups (previously Argo Links) to aggregate links and create roundup posts. Mailchimp API integration sends roundups as Campaigns.
Author: INN, Project Argo, Mission Data
Version: 0.3
Author URI: http://nerds.inn.org/
License: GPLv2
*/

/** Mailchimp API **/
require_once(__DIR__ . '/vendor/mailchimp-api-php/src/Mailchimp.php');

/**
 * On activation, we'll set an option called 'argolinks_flush' to true,
 * so our plugin knows, on initialization, to flush the rewrite rules.
 *
 * @link https://gist.github.com/clioweb/871595
 * @since 0.2
 * @see argolinks_deactivation
 * @see argo_flush_permalinks
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
 * @see argolinks_activation
 * @see argo_flush_permalinks
 */
function lroundups_deactivation() {
    delete_option('argolinks_flush');
}
register_deactivation_hook( __FILE__, 'lroundups_deactivation' );

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
function lroundups_flush_permalinks() {
	if (get_option('argolinks_flush') == true) {
		flush_rewrite_rules();
		delete_option('argolinks_flush');
		return true;
	}
	return false;
}

/**
 * Redirect  wp-admin URLs containing 'post_type=argolinks' to the
 * same url replaced with 'post_type=rounduplink'.
 * 
 * Enables backwards-compatiblity with old argo-links bookmarklets.
 * 
 * @since 0.3
 */
function redirect_argolinks_requests() {

	// @see http://webcheatsheet.com/php/get_current_page_url.php
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}

	if( strpos($pageURL,'post_type=argolinks') ) {

		$newURL = str_replace('post_type=argolinks','post_type=rounduplink',$pageURL);

		// Header redirect
		header( 'Location: ' . $newURL );
		die();

	}

}
add_action('admin_init','redirect_argolinks_requests');

/**
 * Add a "Create MailChimp Campaign" button the post publish actions meta box.
 *
 * @since 0.2
 */
function lroundups_create_mailchimp_campaign_button() {
	global $post;

	if ($post->post_type !== 'roundup')
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

	<div id="link-roundups-publish-actions">
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
add_action('post_submitbox_start', 'lroundups_create_mailchimp_campaign_button');

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
		'link-roundups', $plugin_path . '/js/link-roundups.js',
		array('links-common'), 0.2, true
	);

	wp_register_style('links-common', $plugin_path . '/css/links-common.css');

	$screen = get_current_screen();
	if ($screen->base == 'post' && $screen->post_type == 'roundup') {
		wp_enqueue_script('link-roundups');
		wp_enqueue_style('links-common');
	}
}
add_action('admin_enqueue_scripts', 'link_roundups_enqueue_assets');

/**
 * Print the underscore template for the AL.Modal view.
 *
 * @since 0.2
 */
function lroundups_modal_underscore_template() { ?>
<script type="text/template" id="lroundups-modal-tmpl">
	<div class="lroundups-modal-header">
		<div class="lroundups-modal-close"><span class="close">&#10005;</span></div>
	</div>
	<div class="lroundups-modal-content"><% if (content) { %><%= content %><% } %></div>
	<div class="lroundups-modal-actions">
		<span class="spinner"></span>
		<% _.each(actions, function(v, k) { %>
			<a href="#" class="<%= k %> button button-primary"><%= k %></a>
		<% }); %>
	</div>
</script><?php
}

/**
 * Builds an LR object with common attributes used throughout the plugin's javascript files.
 *
 * @since 0.2
 */
function lroundups_json_obj($add=array()) {
	global $post;

	$mc_api_endpoint = lroundups_get_mc_api_endpoint();

	return array_merge(array(
		'post_id' => $post->ID,
		'ajax_nonce' => wp_create_nonce('lroundups_ajax_nonce'),
		'mc_api_endpoint' => $mc_api_endpoint
	), $add);
}

function lroundups_add_modal_template() {
	$screen = get_current_screen();
	if ($screen->base == 'post' && $screen->post_type == 'roundup') {
		lroundups_modal_underscore_template();

?>
		<script type="text/javascript">
			var LR = <?php echo json_encode(lroundups_json_obj()); ?>;
		</script>
<?php
	}
}
add_action('admin_footer', 'lroundups_add_modal_template');

function lroundups_get_mc_api_endpoint() {
	$mc_api_key = get_option('argo_link_roundups_mailchimp_api_key');
	$mc_api_key_parts = explode('-', $mc_api_key);
	return $mc_api_key_parts[1];
}

/**
 * Set us up the files
 */
require_once('lroundups.php');
require_once('lroundups-widget.php');
require_once('saved-links-widget.php');
require_once('saved-links-class.php');
require_once('links-ajax.php');
require_once('browser-bookmark.php');

/* Initialize the plugin using it's init() function */
LRoundups::init();
SavedLinks::init();
add_action('init', 'lroundups_flush_permalinks', 99);

require_once('inc/lroundups-update.php');

/**
 * Fetches info from a pages <meta> tags and
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
