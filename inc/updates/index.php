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

	if ( lroundups_need_updates() ) {
		/** --------------------------------------------------------
		 * 0.3 Updates
		 * ===============================
		 * - Migrate Custom Post Types
		 *  · argolinkroundups → roundups
		 *  · argolinks → rounduplink
		 * - Migrate Plugin Options
		 *		 All argo_link_* and link_roundups_* dropped in favor of lroundups_*
		 *		 --------------------------------------------------------
		 *		'argo_link_roundups_custom_url'
		 * 		'argo_link_roundups_custom_html'
		 * 		'link_roundups_custom_name_singular'
		 * 		'link_roundups_custom_name_plural'
		 * 		'argo_link_roundups_use_mailchimp_integration'
		 * 		'argo_link_roundups_mailchimp_api_key'
		 * 		'argo_link_mailchimp_template'
		 * 		'argo_link_mailchimp_list'
		 * ------------------------------------------------------ */
		do_action( 'lroundups_update_0.3', lroundups_version(), get_option( 'lroundups_version' ) );

		/** --------------------------------------------------------
		 * 0.3.2 Updates
		 * ===============================
		 * - Complete Options Migration
		 * - Migrate Saved Link Custom Post fields
		 *  · lr_url → lr_url
		 *  · lr_desc → lr_desc
		 *  · lr_source → lr_source
		 *  · lr_img → lr_img
		 * ------------------------------------------------------ */
		do_action( 'lroundups_update_0.3.2', lroundups_version(), get_option('lroundups_version') );

		// Set version.
		update_option( 'lroundups_version', lroundups_version() );
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
		$plugin = get_plugin_data( plugin_dir_path( LROUNDUPS_PLUGIN_FILE ) . '/link-roundups.php' );
		return $plugin['Version'];
	}
	return false;
}


/**
 * Checks if updates need to be run.
 * Get's lroundups version from db and compares with value in plugin file 
 * @since 0.3
 *
 * @return boolean if updates need to be run
 */
function lroundups_need_updates() {

	// try to figure out which versions of the options are stored. Implemented in 0.3
	if ( get_option( 'lroundups_version' ) ) {
		$compare = version_compare( lroundups_version(), get_option( 'lroundups_version' ) );
		if ( $compare == 1 )
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
	if ( lroundups_need_updates() && !( isset( $_GET['page'] ) && $_GET['page'] == 'update-lroundups' ) ) {
	?>
	<div class="update-nag" style="display: block;">
		<p><?php
		printf(
			__('Link Roundups has been updated! IMPORTANT: Please <a href="%s">click here</a> to run a required database upgrade.', 'link-roundups'),
			admin_url('index.php?page=update-lroundups')
		); ?></p>
	</div>
	<?php
	}
}
add_action( 'admin_notices', 'lroundups_update_admin_notice' );

/**
 * Register an admin page for updates.
 *
 * @since 0.3
 */
function lroundups_register_update_page() {
	$parent_slug = null;
	$page_title = __( 'Update Link Roundups', 'link-roundups' );
	$menu_title = __( 'Update Link Roundups', 'link-roundups' );
	$capability = 'edit_theme_options';
	$menu_slug = 'update-lroundups';
	$function = 'lroundups_update_page_view';

	if ( lroundups_need_updates() ) {
		add_submenu_page(
			$parent_slug, $page_title, $menu_title,
			$capability, $menu_slug, $function
		);
	}
}
add_action( 'admin_menu', 'lroundups_register_update_page' );

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
		<h2><?php _e ( 'Link Roundups Database Update', 'link-roundups' ); ?></h2>
		<div class="update-message">
			<p><?php _e( 'Link Roundups plugin has been updated to version', 'link-roundups' ); echo " " . lroundups_version(); ?>.

			<p><?php _e( 'This update will migrate <strong>Argo Links</strong> and <strong>Argo Link Roundups</strong> to the new <strong>Saved Links</strong> and <strong>Link Roundups</strong> formats respectively.', 'link-roundups' ); ?></p>
			<p><?php _e( 'This process will restore previous Argo Links and Argo Link Roundups posts to your site.', 'link-roundups' ); ?></p>
			<p><?php _e( '<strong>NOTE:</strong> instances of the "Argo Links Widget" will <strong>NOT</strong> be migrated during the update. Replace instances of "Argo Links Widget" with "Saved Links Widget" after updating.', 'link-roundups' ); ?></p>

			<p><?php _e( 'Please run the following update function.', 'link-roundups' ); ?></p>
			<p class="submit-container">
				<input type="submit" class="button-primary" id="update" name="update" value="<?php _e( 'Update the database!', 'link-roundups' ); ?>">
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
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'update-lroundups') {
		wp_enqueue_script(
			'lroundups_update_page', plugins_url( '/js/update.js', LROUNDUPS_PLUGIN_FILE),
			array( 'jquery' ), false, 1 );
	}
}
add_action( 'admin_enqueue_scripts', 'lroundups_update_page_enqueue_js' );

/**
 * Ajax handler for when update is applied from the updates page.
 *
 * @since 0.3
 */
function lroundups_ajax_update_database() {
	if (!current_user_can('activate_plugins')) {
		print json_encode( array(
			'status' 	=> __( 'An error occurred.', 'link-roundups' ),
			'success' 	=> false
		));
		wp_die();
	}

	if (!lroundups_need_updates()) {
		print json_encode( array(
			'status' 	=> __( 'Finished. No update was required.', 'link-roundups' ),
			'success' 	=> false
		));
		wp_die();
	}

	$ret = lroundups_perform_update();
	if (!empty($ret)) {
		$message = __( 'Thank you -- the update is complete.', 'link-roundups' );
		print json_encode( array(
			'status' 	=> $message,
			'success' 	=> true
		));
		wp_die();
	} else {
		print json_encode( array(
			'status' 	=> __( 'There was a problem applying the update. Please try again.', 'link-roundups' ),
			'success' 	=> false
		));
		wp_die();
	}
}
add_action( 'wp_ajax_lroundups_ajax_update_database', 'lroundups_ajax_update_database' );


/** --------------------------------------------------------
 * Update functions.
 * ------------------------------------------------------ */

include_once( __DIR__ . '/functions.php' );
