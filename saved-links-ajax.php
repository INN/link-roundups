<?php

function argo_links_create_mailchimp_campaign() {
	check_ajax_referer('argo_links_ajax_nonce', 'security');

	if (isset($_POST['post_id']))
		$post = get_post($_POST['post_id']);
	else {
		print json_encode(array(
			"success" => false,
			"message" => 'No post_id specified.'
		));
		wp_die();
	}

	$mc_api_key = get_option('argo_link_roundups_mailchimp_api_key');
	if (!empty($mc_api_key)) {
		$opts = array('debug' => (defined('WP_DEBUG') && WP_DEBUG)? WP_DEBUG:false);
		$mcapi = new Mailchimp($mc_api_key, $opts);

		$template_id = get_option('argo_link_mailchimp_template');
		if (!empty($template_id))
			$template_info = $mcapi->templates->info($template_id);
		else {
			print json_encode(array(
				"success" => false,
				"message" => 'No MailChimp template ID found.'
			));
			wp_die();
		}

		if (!_argo_links_ensure_template_tags($template_info['source'])) {
			print json_encode(array(
				"success" => false,
				"message" => 'Your MailChimp template is missing the required *|ROUNDUPLINKS|* template tag.'
			));
			wp_die();
		}

		/**
		 * Arguments for MailChimp
		 */
		$list_results = $mcapi->lists->getList(array(
			'list_id' => get_option('argo_link_mailchimp_list')
		));
		$list = $list_results['data'][0];

		$campaign_options = array(
			'list_id' => get_option('argo_link_mailchimp_list'), // the list to sent this campaign to, get lists using lists/list()
			'subject' => $post->post_title, // post title
			'from_email' => $list['default_from_email'], // the From: email address for your campaign message
			'from_name' => $list['default_from_name'], // the From: name for your campaign message (not an email address)
			'title' => $post->post_title, // post title,
			'generate_text' => true // automatically generate text content from HTML
		);

		/**
		 * Replace the template tags in the MailChimp post
		 */
		$html = _argo_links_render_mailchimp_template($template_info['source'], $post);

		$campaign_content = array(
			'html' => $html, // the content!
			'text' => '', // Leave blank for the auto-generated text content
		);

		$response = $mcapi->campaigns->create(
			'regular', // string type of the campaign
			$campaign_options,
			$campaign_content,
			null, null// segment options we have no need to set
			// type options we have no need to set
		);

		$mc_web_id = $response['web_id'];
		update_post_meta($post->ID, 'mc_web_id', $mc_web_id);

		print json_encode(array(
			"success" => true,
			"data" => $response
		));
		wp_die();
	} else {
		print json_encode(array(
			"success" => false,
			"message" => 'No MailChimp API Key found.'
		));
		wp_die();
	}
}
add_action('wp_ajax_argo_links_create_mailchimp_campaign', 'argo_links_create_mailchimp_campaign');

function _argo_links_ensure_template_tags($template_body) {
	return (bool) strstr($template_body, '*|ROUNDUPLINKS|*');
}

function _argo_links_render_mailchimp_template($source, $post) {
	$author = get_user_by('id', $post->post_author);

	$tags = array(
		'*|ROUNDUPLINKS|*' => apply_filters('the_content', $post->post_content),
		'*|ROUNDUPTITLE|*' => $post->post_title,
		'*|ROUNDUPAUTHOR|*' => $author->display_name,
		'*|ROUNDUPDATE|*' => get_the_date('',$post->ID),
		'*|ROUNDUPPERMALINK|*' => get_post_permalink($post->ID)
	);

	foreach ($tags as $tag => $value)
		$source = str_replace($tag, $value, $source);

	return $source;
}
