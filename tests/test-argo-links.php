<?php

class ArgoLinksTestFunctions extends WP_UnitTestCase {

	function setUp() {
		parent::setUp();

		// Set up global $post object
		$this->argolinks_post = $this->factory->post->create(array('post_type' => 'roundup'));
		global $post;
		$this->tmp_post = $post;
		$post = get_post($this->argolinks_post);
		setup_postdata($post);

		update_option('argo_link_roundups_use_mailchimp_integration', 'on');

		$this->mc_api_endpoint = 'us10';
		update_option('argo_link_roundups_mailchimp_api_key', 'TKTK-' . $this->mc_api_endpoint);

		// Mimic the post edit page
		set_current_screen('post');
		$screen = get_current_screen();
		$screen->post_type = 'roundup';
	}

	function tearDown() {
		// Reset global $post object
		$post = $this->tmp_post;
		wp_reset_postdata();
	}

	function test_argolinks_activation() {
		argolinks_activation();
		$this->assertTrue(get_option('argolinks_flush'));
	}

	function test_argolinks_deactivation() {
		argolinks_activation();
		argolinks_deactivation();
		$this->assertFalse(get_option('argolinks_flush'));
	}

	function test_argo_flush_permalinks() {
		global $wp_rewrite;

		argolinks_activation();
		$ret = argo_flush_permalinks();

		// Testing when it should run
		$this->assertFalse(get_option('argolinks_flush'), "argo_flush_permalinks did not reset the argolinks_flush option");
		$this->assertTrue($ret);
		unset($ret);

		// Testing when it should not run
		$ret = argo_flush_permalinks();
		$this->assertFalse($ret);
	}

	function test_argo_links_create_mailchimp_campaign_button() {
		// Test the function output
		$this->expectOutputRegex('/Create MailChimp Campaign/');
		argo_links_create_mailchimp_campaign_button();
	}

	function test_argo_links_enqueue_assets() {
		argo_links_enqueue_assets();

		global $wp_styles, $wp_scripts;
		$this->assertTrue(!empty($wp_scripts->registered['argo-link-roundups']));
		$this->assertTrue(!empty($wp_styles->registered['argo-links-common']));
	}

	function test_argo_links_modal_underscore_template() {
		$this->expectOutputRegex('/id="argo-links-modal-tmpl"/');
		argo_links_modal_underscore_template();
	}

	function test_argo_links_json_obj() {
		$serializable_obj = argo_links_json_obj();
		$this->assertEquals($this->argolinks_post, $serializable_obj['post_id']);
		$this->assertEquals($this->mc_api_endpoint, $serializable_obj['mc_api_endpoint']);
	}

	function test_argo_links_add_modal_template() {
		$this->expectOutputRegex('/var AL =/');
		argo_links_add_modal_template();
	}

	function test_argo_links_get_mc_api_endpoint() {
		$ret = argo_links_get_mc_api_endpoint();
		$this->assertEquals($ret, $this->mc_api_endpoint);
	}
}
