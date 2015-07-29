<?php

class SavedLinksTestFunctions extends WP_UnitTestCase {

	function setUp() {
		parent::setUp();

		// Set up global $post object
		$this->savedlinks_post = $this->factory->post->create(array('post_type' => 'roundup'));
		global $post;
		$this->tmp_post = $post;
		$post = get_post($this->savedlinks_post);
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

	function test_lroundups_activation() {
		lroundups_activation();
		$this->assertTrue(get_option('argolinks_flush'));
	}

	function test_lroundups_deactivation() {
		lroundups_activation();
		lroundups_deactivation();
		$this->assertFalse(get_option('argolinks_flush'));
	}

	function test_lroundups_flush_permalinks() {
		global $wp_rewrite;

		lroundups_activation();
		$ret = lroundups_flush_permalinks();

		// Testing when it should run
		$this->assertFalse(get_option('argolinks_flush'), "lroundups_flush_permalinks did not reset the argolinks_flush option");
		$this->assertTrue($ret);
		unset($ret);

		// Testing when it should not run
		$ret = lroundups_flush_permalinks();
		$this->assertFalse($ret);
	}

	function test_lroundups_create_mailchimp_campaign_button() {
		// Test the function output
		$this->expectOutputRegex('/Create MailChimp Campaign/');
		lroundups_create_mailchimp_campaign_button();
	}

	function test_link_roundups_enqueue_assets() {
		link_roundups_enqueue_assets();

		global $wp_styles, $wp_scripts;
		$this->assertTrue(!empty($wp_scripts->registered['link-roundups']));
		$this->assertTrue(!empty($wp_styles->registered['links-common']));
	}

	function test_lroundups_modal_underscore_template() {
		$this->expectOutputRegex('/id="lroundups-modal-tmpl"/');
		lroundups_modal_underscore_template();
	}

	function test_lroundups_json_obj() {
		$serializable_obj = lroundups_json_obj();
		$this->assertEquals($this->savedlinks_post, $serializable_obj['post_id']);
		$this->assertEquals($this->mc_api_endpoint, $serializable_obj['mc_api_endpoint']);
	}

	function test_lroundups_add_modal_template() {
		$this->expectOutputRegex('/var LR =/');
		lroundups_add_modal_template();
	}

	function test_lroundups_get_mc_api_endpoint() {
		$ret = lroundups_get_mc_api_endpoint();
		$this->assertEquals($ret, $this->mc_api_endpoint);
	}
}
