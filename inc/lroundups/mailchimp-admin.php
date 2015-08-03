<?php
/**
 * Add a "Create MailChimp Campaign" button the post publish actions meta box.
 *
 * @since 0.2
 */
function lroundups_create_mailchimp_campaign_button() {
	global $post;

	if ( $post->post_type !== 'roundup' )
		return;

	if ( false == get_option( 'lroundups_use_mailchimp_integration' ) || false == get_option( 'lroundups_mailchimp_api_key' ) )
		return;

	$mc_cid = get_post_meta( $post->ID, 'mc_cid', true );
	if ( !empty( $mc_cid ) ) {
		$mc_api_key = get_option( 'lroundups_mailchimp_api_key' );
		$opts = array( 'debug' => ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? WP_DEBUG : false);
		$mcapi = new Mailchimp( $mc_api_key, $opts );
		try {
			$content = $mcapi->campaigns->content( $mc_cid );
		} catch ( Mailchimp_Campaign_DoesNotExist $e ) {
			delete_post_meta( $post->ID, 'mc_web_id' );
			delete_post_meta( $post->ID, 'mc_cid' );
		}
	}

	$mc_web_id = get_post_meta( $post->ID, 'mc_web_id', true );
	?>

	<div id="link-roundups-publish-actions">
	<?php if ( empty( $mc_web_id ) ) { ?>
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
add_action( 'post_submitbox_start', 'lroundups_create_mailchimp_campaign_button' );

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
function lroundups_json_obj( $add = array() ) {
	global $post;

	$mc_api_endpoint = lroundups_get_mc_api_endpoint();

	return array_merge( array(
		'post_id' 			=> $post->ID,
		'ajax_nonce' 		=> wp_create_nonce( 'lroundups_ajax_nonce' ),
		'mc_api_endpoint' 	=> $mc_api_endpoint
	), $add );
}

/**
 * Prints the underscore templates and JSON used in the front-end javascript
 *
 * @since 0.2
 */
function lroundups_add_modal_template() {
	$screen = get_current_screen();
	if ( $screen->base == 'post' && $screen->post_type == 'roundup' ) {
		lroundups_modal_underscore_template();

?>
	<script type="text/javascript">
		var LR = <?php echo json_encode( lroundups_json_obj() ); ?>;
	</script>
<?php
	}
}
add_action( 'admin_footer', 'lroundups_add_modal_template' );

/**
 * Return the MailChimp API endpoint based on the MailChimp API key provided
 *
 * @since 0.2
 */
function lroundups_get_mc_api_endpoint() {
	$mc_api_key = get_option( 'lroundups_mailchimp_api_key' );
	$mc_api_key_parts = explode( '-', $mc_api_key );
	return $mc_api_key_parts[1];
}

/**
 * Handle ajax requests to create MailChimp campaign based on a Link Roundups post
 *
 * @since 0.2
 */
function lroundups_create_mailchimp_campaign() {
	check_ajax_referer( 'lroundups_ajax_nonce', 'security' );

	if ( isset( $_POST['post_id'] ) )
		$post = get_post( $_POST['post_id'] );
	else {
		print json_encode( array(
			'success' => false,
			'message' => 'No post_id specified.'
		));
		wp_die();
	}

	$mc_api_key = get_option( 'lroundups_mailchimp_api_key' );
	if ( !empty( $mc_api_key ) ) {
		$opts = array( 'debug' => ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? WP_DEBUG : false );
		$mcapi = new Mailchimp( $mc_api_key, $opts );

		$template_id = get_option( 'lroundups_mailchimp_template' );
		if ( !empty( $template_id ) )
			$template_info = $mcapi->templates->info( $template_id );
		else {
			print json_encode( array(
				'success' => false,
				'message' => 'No MailChimp template ID found.'
			));
			wp_die();
		}

		if ( ! _lroundups_ensure_template_tags( $template_info['source'] ) ) {
			print json_encode( array(
				'success' => false,
				'message' => 'Your MailChimp template is missing the required *|ROUNDUPLINKS|* template tag.'
			));
			wp_die();
		}

		/**
		 * Arguments for MailChimp
		 */
		$list_results = $mcapi->lists->getList( array(
			'list_id' => get_option( 'lroundups_mailchimp_list' )
		));
		$list = $list_results['data'][0];

		$campaign_options = array(
			'list_id' 		=> get_option( 'lroundups_mailchimp_list' ), // the list to sent this campaign to, get lists using lists/list()
			'subject' 		=> $post->post_title, // post title
			'from_email' 	=> $list['default_from_email'], // the From: email address for your campaign message
			'from_name' 	=> $list['default_from_name'], // the From: name for your campaign message (not an email address)
			'title' 		=> $post->post_title, // post title,
			'generate_text' => true // automatically generate text content from HTML
		);

		/**
		 * Replace the template tags in the MailChimp post
		 */
		$html = _lroundups_render_mailchimp_template( $template_info['source'], $post );

		$campaign_content = array(
			'html' => $html, // the content!
			'text' => '' // Leave blank for the auto-generated text content
		);

		$response = $mcapi->campaigns->create(
			'regular', // string type of the campaign
			$campaign_options,
			$campaign_content,
			null, null// segment options we have no need to set
			// type options we have no need to set
		);

		$mc_web_id = $response['web_id'];
		update_post_meta( $post->ID, 'mc_web_id', $mc_web_id );
		$mc_cid = $response['id'];
		update_post_meta( $post->ID, 'mc_cid', $mc_cid );

		print json_encode( array(
			'success' => true,
			'data' => $response
		));
		wp_die();
	} else {
		print json_encode( array(
			'success' => false,
			'message' => 'No MailChimp API Key found.'
		));
		wp_die();
	}
}
add_action( 'wp_ajax_lroundups_create_mailchimp_campaign', 'lroundups_create_mailchimp_campaign' );

function _lroundups_ensure_template_tags( $template_body ) {
	return ( bool ) strstr( $template_body, '*|ROUNDUPLINKS|*' );
}

function _lroundups_render_mailchimp_template( $source, $post ) {
	$author = get_user_by( 'id', $post->post_author );

	$tags = array(
		'*|ROUNDUPLINKS|*' 		=> apply_filters( 'the_content', $post->post_content ),
		'*|ROUNDUPTITLE|*' 		=> $post->post_title,
		'*|ROUNDUPAUTHOR|*' 	=> $author->display_name,
		'*|ROUNDUPDATE|*' 		=> get_the_date( '',$post->ID ),
		'*|ROUNDUPPERMALINK|*' 	=> get_post_permalink( $post->ID )
	);

	foreach ( $tags as $tag => $value )
		$source = str_replace( $tag, $value, $source );

	return $source;
}
