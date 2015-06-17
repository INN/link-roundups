<?php
/**
 * Class: Argo_This_Button
 * Contains all relevant functionality to implement "Argo Link This!" button.
 * 
 * @since 0.3
 * 
 * Structure based on:
 * @see https://wordpress.org/plugins/press-this-reloaded/
 */

/**
 * This file used to be loaded directly from the Argo Link! bookmarklet.
 * 
 * If WordPress is not loaded, then direct them to the right edit screen
 * and pass along the previous query vars.
 * 
 * @since 0.3
 */
if ( !defined('ABSPATH') ) {

	// Load WordPress
	define('WP_USE_THEMES', false);
	require_once('../../../wp-admin/admin.php');

	// Generate URL redirect
	$URL = parse_url( $_SERVER["REQUEST_URI"] );
	$newURL = admin_url( 'post-new.php?' . $URL['query'] );

	// Header redirect
	header( 'Location: ' . $newURL );
}

class Argo_This_Button {

	private static $title;
	private static $description;
	private static $url;
	private static $source;
	private static $imgUrl;
	const plugin_domain = 'press-this-reloaded';

	/**
	 * Initialize the class.
	 * 
	 * @since 0.3
	 */
	function init() {

		add_filter( 'redirect_post_location', array( __CLASS__, 'redirect' ) );

		if ( isset( $_GET[ 'u' ] ) ) {
			
			add_action( 'load-post-new.php', array( __CLASS__, 'load' ) );
			add_action( 'load-post.php', array( __CLASS__, 'load' ) );

		}
		elseif ( isset($_REQUEST[ 'ajax' ]) ) { 
			// this is for video only
			// from the original plugin, currently not really used.
			add_action( 'load-post-new.php', array( __CLASS__, 'manageAjaxRequest' ) );
		}

	}

	/**
	 * Currently not used. Left as an example from original 
	 * plugin file of how to handle an ajax request from the page.
	 * 
	 * This could be used to ajax-ify the fetching of URL information.
	 * 
	 * @since 0.3
	 */
	public static function manageAjaxRequest() {

		$selection = '';
		if ( !empty($_GET['s']) ) {
			$selection = str_replace('&apos;', "'", stripslashes($_GET['s']));
			$selection = trim( htmlspecialchars( html_entity_decode($selection, ENT_QUOTES) ) );
		}

		if ( ! empty($selection) ) {
			$selection = preg_replace('/(\r?\n|\r)/', '</p><p>', $selection);
			$selection = '<p>' . str_replace('<p></p>', '', $selection) . '</p>';
		}

		$url = isset( $_GET[ 'u' ] ) ? esc_url( $_GET[ 'u' ] ) : '';
		$image = isset( $_GET[ 'i' ] ) ? $_GET[ 'i' ] : '';


		if ( !empty( $_REQUEST[ 'ajax' ] ) ) {
			switch ( $_REQUEST[ 'ajax' ] ) {
				case 'urlInfo':	
					// return urlInfo
					break;
				default:
					// default case.
			}
			die();
		}
	}

	/**
	 * Returns the link for the bookmarklet button.
	 * 
	 * @since 0.3
	 * 
	 * @return String. Javascript bookmarklet code.
	 */ 
	static function shortcut_link() {

		// This is the default 'Press This!' button link.
		$shortcut_link = get_shortcut_link();

		$post_type = 'rounduplink';

		// We alter it for our post type.
		$shortcut_link = str_replace('press-this.php', 'post-new.php', $shortcut_link);
		$shortcut_link = str_replace('width=720', 'width=840', $shortcut_link);
		$shortcut_link = str_replace('post-new.php', "post-new.php?post_type=$post_type", $shortcut_link);
		$shortcut_link = str_replace('?u=', '&u=', $shortcut_link);

		return $shortcut_link;

	}

	/**
	 * Passes ?u= query var through login process
	 * 
	 * @since 0.3
	 * 
	 * @return url with query vars still attached.
	 */
	function redirect( $location ) {

		$referrer = wp_get_referer();

		if ( false !== strpos( $referrer, '?u=' ) || false !== strpos( $referrer, '&u=' ) )
			$location = add_query_arg( 'u', 1, $location );

		return $location;
	
	}

	/**
	 * Sets up default values for new argolink.
	 * 
	 * @since 0.3
	 */
	function load() {

		// Default source
		self::$url = isset( $_GET[ 'u' ] ) ? esc_url( $_GET[ 'u' ] ) : '';
		self::$url = wp_kses( urldecode( self::$url ), null );

		// Get meta data from url.
		$meta = argo_get_page_info(self::$url);
		$meta = $meta['meta'];

		// Default title.
		self::$title = '';
		if( !empty($meta['ogp']['title']) ) {
			self::$title = $meta['ogp']['title'];
		} else {
			self::$title = isset( $_GET[ 't' ] ) ? trim( strip_tags( html_entity_decode( stripslashes( $_GET[ 't' ] ), ENT_QUOTES ) ) ) : '';
		}

		$selection = '';
		if ( !empty( $_GET[ 's' ] ) ) {
			$selection = str_replace( '&apos;', "'", stripslashes( $_GET[ 's' ] ) );
			$selection = trim( htmlspecialchars( html_entity_decode( $selection, ENT_QUOTES ) ) );
		}

		// Default description.
		self::$description = '';
		if ( !empty( $selection ) ) {
			self::$description = $selection;
		} else if( !empty($meta['ogp']['description']) ) {
			self::$description = $meta['ogp']['description'];
		}  

		// Default source.
		self::$source = '';
		if( !empty($meta['ogp']['site_name']) ) {
			self::$source = $meta['ogp']['site_name'];
		} else if( self::$url ) {
			$url = parse_url(self::$url);
			self::$source = $url['host'];
		}
		
		self::$imgUrl = '';
		if( !empty($meta['ogp']['image']) ) {
			self::$imgUrl = $meta['ogp']['image'];
		}

		add_filter( 'default_title', array( __CLASS__, 'default_title' ) );
		add_filter( 'default_argo_link_url', array( __CLASS__, 'default_link' ));
		add_filter( 'default_argo_link_description', array( __CLASS__, 'default_description' ) );
		add_filter( 'default_argo_link_source', array( __CLASS__, 'default_source' ) );

		add_filter( 'show_admin_bar', '__return_false' );

		self::manageAjaxRequest();

	}

	/**
	 * Returns the default title value for this argo link. 
	 * 
	 * @since 0.3
	 * 
	 * @return String. Default title. 
	 */
	public static function default_title( $title = null ) {
		return self::$title;
	}

	/**
	 * Returns the default description value for this argo link. 
	 * 
	 * @since 0.3
	 * 
	 * @return String. Default description. 
	 */
	public static function default_description( $description = null ) {
		return self::$description;
	}

	/**
	 * Returns the default link value for this argo link. 
	 * 
	 * @since 0.3
	 * 
	 * @return String. Default link. 
	 */
	public static function default_link( $link = null ) {
		return self::$url;
	}

	/**
	 * Returns the default source value for this argo link. 
	 * 
	 * @since 0.3
	 * 
	 * @return String. Default source. 
	 */
	public static function default_source( $source = null ) {
		return self::$source;
	}

	/**
	 * Returns the default image src value for this argo link. 
	 * 
	 * @since 0.3
	 * 
	 * @return String. Default image src. 
	 */
	public static function default_imgUrl( $imgUrl = null ) {
		return self::$imgUrl;
	}

}

Argo_This_Button::init();



