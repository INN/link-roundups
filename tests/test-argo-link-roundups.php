<?php

class ArgoLinkRoundupsTestFunctions extends WP_UnitTestCase {
	function setUp() {
		parent::setUp();

		// Set up global $post object
		$this->argolinks_post = $this->factory->post->create(array('post_type' => 'roundup'));
		global $post;
		$this->tmp_post = $post;
		$post = get_post($this->argolinks_post);
		setup_postdata($post);
	}

	function tearDown() {
		// Reset global $post object
		$post = $this->tmp_post;
		wp_reset_postdata();
	}

	function test_init() {
		// Testing init would be equivalent to test core WordPress functions called within.
		// There's no need for that kind of testing here.
		$this->markTestSkipped(
			'`ArgoLinkRoundups::init` returns null and uses only core WordPress functions.');
	}

	function test_my_get_posts() {
		$test_query = new WP_Query();
		$test_query->is_home = true;

		ArgoLinkRoundups::my_get_posts($test_query);

		$this->assertTrue(
			in_array('roundup', $test_query->query_vars['post_type']));
	}

	function test_register_post_type() {
		global $wp_post_types;

		ArgoLinkRoundups::register_post_type();

		$this->assertTrue(in_array('roundup', array_keys($wp_post_types)));
	}

	function test_add_custom_post_fields() {
		$this->markTestSkipped(
			'`ArgoLinkRoundups::add_custom_post_fields` returns null and uses only core WordPress functions.');
	}

	function test_display_custom_fields() {
		$this->expectOutputRegex('/argo-links-display-area/');
		ArgoLinkRoundups::display_custom_fields();
	}

	function test_save_custom_fields() {
		$test_url = 'http://testurl';
		$test_des = 'TKTK';

		$_POST['argo_link_url'] = $test_url;
		$_POST['argo_link_description'] = $test_des;

		ArgoLinkRoundups::save_custom_fields($this->argolinks_post);

		$post_url = get_post_meta($this->argolinks_post, 'argo_link_url', true);
		$post_des = get_post_meta($this->argolinks_post, 'argo_link_description', true);

		$this->assertEquals($test_url, $post_url);
		$this->assertEquals($test_des, $post_des);
	}

	function test_add_argo_link_roundup_options_page() {
		$this->markTestSkipped(
			'`ArgoLinkRoundups::add_argo_links_roundup_options_page` returns null and uses only core WordPress functions.');
	}

	function test_register_mysettings() {
		$this->markTestSkipped(
			'`ArgoLinkRoundups::register_mysettings` returns null and uses only core WordPress functions.');
	}

	function test_validate_mailchimp_integration() {
		$ret = ArgoLinkRoundups::validate_mailchimp_integration('test');
		$this->assertEquals($ret, '');
	}

	function test_build_argo_link_roundups_options_page() {
		$this->markTestIncomplete('This test has not been implemented yet.');
	}
}
