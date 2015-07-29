<?php

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

	<div id="link-roundups-publish-actions">
	<?php if (empty($mc_web_id)) { ?>
		<input type="submit"
			name="link_roundups_create_mailchimp_campaign"
			id="link-roundups-create-mailchimp-campaign"
			class="button button-primary button-large" value="Create MailChimp Campaign">
	<?php } else { ?>
		<p>A MailChimp roundup campaign exists for this post.</p>
		<a class="button" target="blank"
			href="https://<?php echo lroundups_get_mc_api_endpoint(); ?>.admin.mailchimp.com/campaigns/wizard/confirm?id=<?php echo $mc_web_id; ?>">Edit in MailChimp.</a>
	<?php } ?>
	</div>
<?php
}
add_action('post_submitbox_start', 'lroundups_create_mailchimp_campaign_button');

/**
 * Print the underscore template for the LR.Modal view.
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
 * Builds a LR object with common attributes used throughout the plugin's javascript files.
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
