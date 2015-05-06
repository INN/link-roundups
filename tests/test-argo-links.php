<?php
class ArgoLinksTestFunctions extends WP_UnitTestCase {

	function setUp() {
		parent::setUp();
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
}
