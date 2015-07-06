<?php

/**
 * Utility function to reset the permalinks.
 *
 * Called in SavedLinks::register_permalinks() to reset the WordPress permalinks after the
 * savedlinks post type is registered in ArgoLinks::register_permalinks(), which is run after
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
 * Redirect all admin URLs containing 'post_type=argolinks' to the
 * same url replaced with 'post_type=rounduplink'.
 * 
 * Used to be backwards compatible with old bookmarklets.
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
function argo_links_create_mailchimp_campaign_button() {
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

	<div id="argo-links-publish-actions">
	<?php if (empty($mc_web_id)) { ?>
		<input type="submit"
			name="argo_links_create_mailchimp_campaign"
			id="argo-links-create-mailchimp-campaign"
			class="button button-primary button-large" value="Create MailChimp Campaign">
	<?php } else { ?>
		<p>A MailChimp Campaign exists for thist post.</p>
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
		'saved-links', $plugin_path . '/js/saved-links.js',
		array('jquery', 'underscore', 'backbone'), 0.3, true
	);

	wp_register_script(
		'link-roundups', $plugin_path . '/js/link-roundups.js',
		array('link-roundups'), 0.3, true
	);

	wp_register_style('saved-links', $plugin_path . '/css/saved-links-common.min.css');

	$screen = get_current_screen();
	if ($screen->base == 'post' && $screen->post_type == 'roundup') {
		wp_enqueue_script('link-roundups');
		wp_enqueue_style('saved-links');
	}
}
add_action('admin_enqueue_scripts', 'argo_links_enqueue_assets');

/**
 * Print the underscore template for the SL.Modal view.
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
 * Builds an SL object with common attributes used throughout the plugin's javascript files.
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
	if ($screen->base == 'post' && $screen->post_type == 'roundup') {
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
 * Fetches info from a pages <meta> tags and
 * returns an array of that information.
 *
 * @see http://code.ramonkayo.com/simple-scraper/
 * @since 0.3
 *
 * @param string $url the url of the page to scrape
 */
function argo_get_page_info($url) {

	require_once __DIR__. '/inc/WPSimpleScraper.php';

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
