<?php
/**
 * Redirect wp-admin URLs containing 'post_type=argolinks' to the
 * same url replaced with 'post_type=rounduplink'
 *
 * If a user had an old Argo Link bookmark tool installed on their browser bar,
 * then it would continue to work because of this...
 *
 * @since 0.3
 */
function redirect_argolinks_requests() {
	// @see http://webcheatsheet.com/php/get_current_page_url.php
	$pageURL = 'http';

	if ( isset( $_SERVER['HTTPS'] ) ) {
		$pageURL .= 's';
	}

	$pageURL .= '://';

	if ( isset( $_SERVER['SERVER_PORT'] ) && $_SERVER['SERVER_PORT'] != '80' ) {
		$pageURL .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
	} else {
		$pageURL .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	}

	if ( strpos( $pageURL, 'post_type=argolinks' ) ) {
		$newURL = str_replace( 'post_type=argolinks', 'post_type=rounduplink', $pageURL );

		// Header redirect
		header( 'Location: ' . $newURL );
		die();
	}
}
add_action( 'admin_init', 'redirect_argolinks_requests' );
