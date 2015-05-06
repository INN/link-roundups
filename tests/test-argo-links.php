<?php
class ArgoLinksTestFunctions extends WP_UnitTestCase {

	function setUp() {
		parent::setUp();
	}

	function test_argolinks_activation() {
		argolinks_activation();
		$this->assertTrue((bool) get_option('argolinks_flush'));
	}

	function test_argolinks_deactivation() {
		add_option('argolinks_flush', 'true');
		argolinks_deactivation();
		$this->assertFalse(get_option('argolinks_flush'));
	}

	function test_ArgoLinks_init_permalinks() {
		global $wp_rewrite;

		$wp_rewrite->set_permalink_structure('/%year%/%monthnum%/%postname%/');
		argolinks_activation();
		ArgoLinkRoundups::register_post_type();
		ArgoLinks::register_post_type();

		$after = $wp_rewrite->rewrite_rules();
		$after = implode(' ', $after);
		$this->assertRegExp('/argolinks/', $after, "The generated rewrite rules do not account for the argolinks post type");
		$this->assertRegExp('/argolinkroundups/', $after, "The generated rewrite rules do not account for the argolinkroundups post type");
	}
}
