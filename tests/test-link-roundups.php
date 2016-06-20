<?php

class LinkRoundupsFunctionsTests extends WP_UnitTestCase {

	function setUp() {
		parent::setUp();

		// Set up global $post object
		$this->savedlinks_post = $this->factory->post->create(array('post_type' => 'roundup'));
		global $post;
		$this->tmp_post = $post;
		$post = get_post($this->savedlinks_post);
		setup_postdata($post);

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

	function test_link_roundups_enqueue_assets() {
		link_roundups_enqueue_assets();

		global $wp_styles, $wp_scripts;
		$this->assertTrue(!empty($wp_scripts->registered['link-roundups']));
		$this->assertTrue(!empty($wp_styles->registered['lroundups-admin']));
	}

}
