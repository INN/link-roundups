<?php

function argo_links_create_mailchimp_campaign() {
	print json_encode(array(
		"success" => true
	));

	wp_die();
}
add_action('wp_ajax_argo_links_create_mailchimp_campaign', 'argo_links_create_mailchimp_campaign');
