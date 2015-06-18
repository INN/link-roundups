<?php
/**
 * Contains functions and tools for transitioning between versions
 * 
 * @since 0.3
 */


/** --------------------------------------------------------
 * Start updates and helpers
 * ------------------------------------------------------ */

/**
 * Performs various update functions and sets a new verion number.
 *
 * This acts as a main() for applying database updates when the update ajax is
 * called.
 *
 * @since 0.3
 */
function lroundups_perform_update() {

	if (lroundups_need_updates()) {
		// Run 0.3 updates.
		do_action( 'lroundups_update_0.3', lroundups_version(), get_option('lroundups_version') );

		// Run updates for 0.4
		// do_action( 'lroundups_update_0.4', lroundups_version(), get_option('lroundups_version') );

		// &c...

		// Set version.
		update_option('lroundups_version', lroundups_version());
	}

	return true;
}

/**
 * Returns current version of the plugin as set in metadata.
 * Only works if is_admin() is true.
 * 
 * @since 0.3
 */
function lroundups_version() {
	if( is_admin() ) {
		$plugin = get_plugin_data( plugin_dir_path(__FILE__) . "../argo-links.php" );
		return $plugin['Version'];
	}
	return false;
}

/**
 * Checks if updates need to be run.
 * 
 * @since 0.3
 * 
 * @return boolean if updates need to be run
 */
function lroundups_need_updates() {

	// try to figure out which versions of the options are stored. Implemented in 0.3
	if (get_option('lroundups_version')) {
		$compare = version_compare(lroundups_version(), get_option('lroundups_version'));
		if ($compare == 1)
			return true;
		else
			return false;
	}

	// if 'lroundups_version' isn't present, the settings are old!
	return true;

}








/** --------------------------------------------------------
 * Update.php admin page logic.
 * ------------------------------------------------------ */

/**
 * Add an admin notice if lroundups needs to be updated.
 *
 * @since 0.3
 */
function lroundups_update_admin_notice() {
	if (lroundups_need_updates() && !(isset($_GET['page']) && $_GET['page'] == 'update-lroundups')) {
	?>
	<div class="update-nag" style="display: block;">
		<p>Link Roundups has been updated! Please <a href="<? echo admin_url('index.php?page=update-lroundups'); ?>">visit the update page</a> to apply a required database update.</p>
	</div>
	<?php
	}
}
add_action('admin_notices', 'lroundups_update_admin_notice');

/**
 * Register an admin page for updates.
 *
 * @since 0.3
 */
function lroundups_register_update_page() {
	$parent_slug = null;
	$page_title = "Update Link Roundups";
	$menu_title = "Update Link Roundups";
	$capability = "edit_theme_options";
	$menu_slug = "update-lroundups";
	$function = "lroundups_update_page_view";

	if (lroundups_need_updates()) {
		add_submenu_page(
			$parent_slug, $page_title, $menu_title,
			$capability, $menu_slug, $function
		);
	}
}
add_action('admin_menu', 'lroundups_register_update_page');

/**
 * DOM for admin page for updates.
 * 
 * @since 0.3
 */
function lroundups_update_page_view() { ?>
	<style type="text/css">
		.update-message {
			max-width: 700px;
		}
		.update-message,
		.update-message p {
			font-size: 16px;
		}
		.update-message ul li {
			list-style-type: disc;
			list-style-position: inside;
		}
		.update-message .submit-container {
			max-width: 178px;
		}
		.update-message .spinner {
			background: url(../wp-includes/images/spinner.gif) 0 0/20px 20px no-repeat;
			-webkit-background-size: 20px 20px;
			display: none;
			opacity: .7;
			filter: alpha(opacity=70);
			width: 20px;
			height: 20px;
			margin: 0;
			position: relative;
			top: 4px;
		}
		.update-message .updated,
		.update-message .error {
			padding-top: 16px;
			padding-bottom: 16px;
		}
	</style>
	<div class="wrap">
		<div id="icon-tools" class="icon32"></div>
		<h2>Link Roundups Database Update</h2>
		<div class="update-message">

			<p><?php _e('Link Roundups plugin has been updated to version'); echo " " . lroundups_version(); ?>.
			<p><?php _e('This update will migrate <strong>Argo Links</strong> and <strong>Argo Link Roundups</strong> to the new <strong>Saved Links</strong> and <strong>Link Roundups</strong> formats respectively.'); ?></p>
			<p><?php _e('This process will restore previous Argo Links and Argo Link Roundups posts to your site.'); ?></p>
			<p><?php _e('Please run the following update function.'); ?></p>
			<p class="submit-container">
				<input type="submit" class="button-primary" id="update" name="update" value="<?php _e('Update the database!'); ?>">
				<span class="spinner"></span>
			<p>
		</div>
	</div>
<?php
}

/**
 * Enqueues javascript used on the Link Roundup Update page
 * 
 * @since 0.3
 *
 * @global $_GET
 */
function lroundups_update_page_enqueue_js() {
	if (isset($_GET['page']) && $_GET['page'] == 'update-lroundups') {
		wp_enqueue_script(
			'lroundups_update_page', plugins_url('/js/update.js',dirname(__FILE__)),
			array('jquery'), false, 1);
	}
}
add_action('admin_enqueue_scripts', 'lroundups_update_page_enqueue_js');

/**
 * Ajax handler for when update is applied from the updates page.
 * 
 * @since 0.3
 */
function lroundups_ajax_update_database() {
	if (!current_user_can('activate_plugins')) {
		print json_encode(array(
			"status" => __("An error occurred."),
			"success" => false
		));
		wp_die();
	}

	if (!lroundups_need_updates()) {
		print json_encode(array(
			"status" => __("Finished. No update was required."),
			"success" => false
		));
		wp_die();
	}

	$ret = lroundups_perform_update();
	if (!empty($ret)) {
		$message = __("Thank you -- the update is complete.");
		print json_encode(array(
			"status" => $message,
			"success" => true
		));
		wp_die();
	} else {
		print json_encode(array(
			"status" => __("There was a problem applying the update. Please try again."),
			"success" => false
		));
		wp_die();
	}
}
add_action('wp_ajax_lroundups_ajax_update_database', 'lroundups_ajax_update_database');


/** --------------------------------------------------------
 * Update functions.
 * ------------------------------------------------------ */

include_once('lroundups-update-functions.php');

