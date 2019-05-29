<?php
/*
Plugin Name: Link Roundups
Plugin URI: https://github.com/INN/link-roundups
Description: Use Link Roundups to aggregate links and create roundup posts. Mailchimp API integration and browser bookmark tool. Formerly argo-links from NPR's Project Argo.
Author: INN Labs
Author URI: http://labs.inn.org/
Version: 1.0.1
License: GPLv2
Text Domain: link-roundups

Seeking Link Roundups Post Type functions? They use lroundups instead of link-roundups.
 */

/**
 * The code that runs during plugin activation.
 */
function activate_link_roundups() {
	$plugin = get_plugin_data( __FILE__ );
	update_option( 'lroundups_version', $plugin['Version'] );
}
register_activation_hook( __FILE__, 'activate_link_roundups' );

/**
 * Mailchimp API and Modal Functions
 */
if ( ! class_exists( 'MailChimp' ) && file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
} else {
	error_log(
		sprintf(
			// translators: %1$s is a URL.
			__( 'Your installation of the Link Roundups Plugin is missing its vendor dependencies. Please visit %1$s for more information.', 'link-roundups' ),
			'https://github.com/INN/link-roundups/blob/136-update-wordpress-mailchimp-tools/docs/installation.md'
		)
	);
}

/**
 * Link Roundups init
 *
 * @since 0.3
 */
function link_roundups_init() {
	// Plugin constants
	define( 'LROUNDUPS_PLUGIN_FILE', __FILE__ );
	define( 'LROUNDUPS_PLUGIN_DIR', __DIR__ );
	define( 'LROUNDUPS_DIR_URI', plugins_url( basename( LROUNDUPS_PLUGIN_DIR ), LROUNDUPS_PLUGIN_DIR ) );

	/**
	 * Saved Links
	 */
	require_once __DIR__ . '/inc/saved-links/class-saved-links.php';
	require_once __DIR__ . '/inc/saved-links/class-saved-links-widget.php';

	/**
	 * Link Roundups
	 */
	require_once __DIR__ . '/inc/link-roundups/class-link-roundups.php';
	require_once __DIR__ . '/inc/link-roundups/class-link-roundups-editor.php';
	require_once __DIR__ . '/inc/link-roundups/class-link-roundups-widget.php';

	/**
	 * Save to Site Browser Bookmark Tool
	 */
	require_once __DIR__ . '/inc/link-roundups/class-save-to-site-button.php';

	/**
	 * Add Backwards Compatability with argo-links
	 */
	require_once __DIR__ . '/inc/compatibility.php';

	/**
	 * Add compatibility filters for INN/Largo
	 */
	require_once __DIR__ . '/inc/compatibility-largo.php';

	/**
	 * Initialize the plugin using its init() function
	 */
	LinkRoundups::init();
	SavedLinks::init();
	LinkRoundupsEditor::init();

	/**
	 * Include updates framework
	 */
	require_once 'inc/updates/index.php';

	load_plugin_textdomain( 'link-roundups', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
}
add_action( 'plugins_loaded', 'link_roundups_init' );

/**
 * Include CSS and Javascript files used on the post edit screen.
 *
 * @since 0.2
 */
function link_roundups_enqueue_assets() {
	$plugin_path = plugins_url( basename( __DIR__ ), __DIR__ );
	$suffix      = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_register_script(
		'links-common',
		$plugin_path . '/js/links-common' . $suffix . '.js',
		array( 'jquery', 'underscore', 'backbone' ),
		0.3,
		true
	);

	wp_register_script(
		'link-roundups',
		$plugin_path . '/js/lroundups' . $suffix . '.js',
		array( 'links-common' ),
		0.3,
		true
	);

	wp_register_style( 'lroundups-admin', $plugin_path . '/css/lroundups-admin' . $suffix . '.css' );

	$screen = get_current_screen();
	if ( $screen->base == 'post' && ( $screen->post_type == 'roundup' || $screen->post_type == 'rounduplink' ) ) {
		wp_enqueue_script( 'link-roundups' );
		wp_enqueue_style( 'lroundups-admin' );
	}

	if ( $screen->base == 'roundup_page_link-roundups-options' ) {
		wp_enqueue_script( 'link-roundups' );
	}
}
add_action( 'admin_enqueue_scripts', 'link_roundups_enqueue_assets' );
