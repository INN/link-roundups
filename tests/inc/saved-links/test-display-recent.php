<?php

/*
 * TODO: Add tests once display-recent.php is written in some way that it can actually be tested.
 */

class DisplayRecent_TestCase extends WP_UnitTestCase {
	function setUp() {
		parent::setUp();
		require_once(dirname(dirname(dirname(__DIR__))) . "/inc/saved-links/display-recent.php");
	}

	function test_clone_WP_List_Table_exists() {
		$this->assertTrue(class_exists("clone_WP_List_Table"), "WP_List_Table was not correctly cloned or was not loaded. Please see inc/saved-links/README.md to remedy this problem.");
	}
}
