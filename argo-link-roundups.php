<?php
/**
 * @package Argo_Links
 * @version 0.01
 */

/*
*Argo Links - Link Roundups Code
*/

/* The Argo Link Roundups class - so we don't have function naming conflicts */
class ArgoLinkRoundups {

	/* Initialize the plugin */
	public static function init() {

		/* Register the custom post type of roundup */
		add_action('init', array(__CLASS__, 'register_post_type' ));

		/* Add our custom post fields for our custom post type */
		add_action("admin_init", array(__CLASS__, "add_custom_post_fields"));

		/* Add the Argo Link Roundups Options sub menu */
		add_action("admin_menu", array(__CLASS__, "add_argo_link_roundup_options_page"));

		/* Save our custom post fields! Very important! */
		add_action('save_post', array(__CLASS__, 'save_custom_fields'));

		/* Make sure our custom post type gets pulled into the river */
		add_filter( 'pre_get_posts', array(__CLASS__,'my_get_posts') );

	}

	/*Pull the argolinkroundups into the rivers for is_home, is_tag, is_category, is_archive*/
	/*Merge the post_type query var if there is already a custom post type being pulled in, otherwise do post & argolinkroundups*/
	public static function my_get_posts( &$query ) {
		// bail out early if suppress filters is set to true
		if ($query->get('suppress_filters')) return;

		/*
		 * Add roundup to the post type in the query if it is not already in it.
		 */
		if ( $query->is_home() || $query->is_tag() || $query->is_category() ) {
			if (isset($query->query_vars['post_type']) && is_array($query->query_vars['post_type'])) {
				if ( ! in_array( 'roundup', $query->query_vars['post_type'] ) ) {
					// There is an array of post types and roundup is not in it
					$query->set( 'post_type', array_merge(array('roundup' ), $query->query_vars['post_type']) );
				}
			} elseif (isset($query->query_vars['post_type']) && !is_array($query->query_vars['post_type'])) {
				if ( $query->query_vars['post_type'] !== 'roundup' ) {
					// There is a single post type, so we shall add it to an array
					$query->set( 'post_type', array('roundup', $query->query_vars['post_type']) );
				}
			} else {
				// Post type is not set, so it shall be post and roundup
				$query->set( 'post_type', array('post','roundup') );
			}
		}
	}

	/**
	 * Register the Argo Links post type
	 * 
	 * @since 0.1
	 */
	public static function register_post_type() {
		$roundup_options = array(
			'labels' => array(
				'name' => 'Link Roundups',
				'singular_name' => 'Link Roundup',
				'add_new' => 'Add New Roundup',
				'add_new_item' => 'Add New Link Roundup',
				'edit' => 'Edit',
				'edit_item' => 'Edit Link Roundup',
				'view' => 'View',
				'view_item' => 'View Link Roundup',
				'search_items' => 'Search Link Roundups',
				'not_found' => 'No Links Roundups found',
				'not_found_in_trash' => 'No Link Roundups found in Trash',
			),
			'description' => 'Link Roundups',
			'supports' => array(
				'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields',
				'comments', 'revisions', 'page-attributes', 'post-formats'
			),
			'public' => true,
			'menu_position' => 7,
			'menu_icon' 	=> 'dashicons-list-view',
			'taxonomies' => apply_filters('roundup_taxonomies', array('category','post_tag')),
			'has_archive' => true,
		);

		if (get_option('argo_link_roundups_custom_url') != "")
			$roundup_options['rewrite'] = array('slug' => get_option('argo_link_roundups_custom_url'));

		register_post_type('roundup', $roundup_options);


	}

	/**
	 * Register meta box for custom fields on roundup edit pages.
	 * 
	 * @since 0.1
	 * @see display_custom_fields()
	 */
	public static function add_custom_post_fields() {
		add_meta_box(
			"argo_link_roundups_roundup", "Recent Roundup Links",
			array(__CLASS__, "display_custom_fields"), "roundup", "normal", "high"
		);
	}

	/** 
	 * Show our custom post fields in the add/edit Argo Link Roundups admin pages
	 * 
	 * @since 0.1
	 */
	public static function display_custom_fields() {
	?>
		<div id='argo-links-display-area'></div>
		<script type='text/javascript'>
		jQuery(function(){
			jQuery('#argo-links-display-area').load('<?php echo plugin_dir_url(__FILE__); ?>display-argo-links.php');
		});
		</script>
	<?php
	}

	/**
	 * Save the custom post field data.	Very important!
	 * 
	 * Wait, does this do anything on roundups!? - Will
	 * 
	 * @since 0.1
	 */
	public static function save_custom_fields($post_id) {
		if (isset($_POST["argo_link_url"])){
			update_post_meta((isset($_POST['post_id']) ? $_POST['post_ID'] : $post_id), "argo_link_url", $_POST["argo_link_url"]);
		}
		if (isset($_POST["argo_link_description"])){
			update_post_meta((isset($_POST['post_id']) ? $_POST['post_ID'] : $post_id), "argo_link_description", $_POST["argo_link_description"]);
		}
	}

	/**
	 * Add options sub menu for roundups.
	 * 
	 * @since 0.1
	 */
	public static function add_argo_link_roundup_options_page() {

		add_submenu_page(
			"edit.php?post_type=roundup", 	// $parent_slug
			"Options", 						// $page_title
			"Options", 						// $menu_title
			"edit_posts", 					// $capability
			"argo-link-roundups-options",  	// $menu_slug
			array(__CLASS__, 'build_argo_link_roundups_options_page') 	// $function
		);

		// call register settings function
		add_action('admin_init', array(__CLASS__, 'register_mysettings'));
	}

	public static function register_mysettings() {
		
		// register our settings
		register_setting('argolinkroundups-settings-group', 'argo_link_roundups_custom_url');
		register_setting('argolinkroundups-settings-group', 'argo_link_roundups_custom_html');
		register_setting(
			'argolinkroundups-settings-group', 'argo_link_roundups_use_mailchimp_integration',
			array(__CLASS__, 'validate_mailchimp_integration')
		);
		register_setting('argolinkroundups-settings-group', 'argo_link_roundups_mailchimp_api_key');
		register_setting('argolinkroundups-settings-group', 'argo_link_mailchimp_template');
		register_setting('argolinkroundups-settings-group', 'argo_link_mailchimp_list');
	}

	public static function validate_mailchimp_integration($input) {
		
		// Can't have an empty MailChimp API Key if the integration functionality is enabled.
		if (empty($_POST['argo_link_roundups_mailchimp_api_key']) && !empty($input)) {
			add_settings_error(
				'argo_link_roundups_use_mailchimp_integration',
				'argo_link_roundups_use_mailchimp_integration_error',
				'Please enter a valid MailChimp API Key.',
				'error'
			);
			return '';
		}

		return $input;
	}

	public static function build_argo_link_roundups_options_page() {
		
		$mc_api_key = get_option('argo_link_roundups_mailchimp_api_key');

		/**
		 * It's not possible to use this functionality if curl is not enabled in php.
		 */
		if ( ! function_exists('curl_init') ) {
			add_settings_error(
				'argo_link_roundups_use_mailchimp_integration',
				'curl_not_enabled',
				__('Curl is not enabled on your server. The MailChimp features will not work without curl. Please contact your server administrator to have curl enabled.', 'argo-links'),
				'error'
			);
			delete_option('argo_link_roundups_use_mailchimp_integration');

		// only query MailChimp if it's possible to do so and if plugins are enabled
		} else if ( get_option('argo_link_roundups_use_mailchimp_integration') && !empty($mc_api_key)) {
			$opts = array('debug' => (defined('WP_DEBUG') && WP_DEBUG)? WP_DEBUG:false);
			$mcapi = new Mailchimp($mc_api_key, $opts);

			$templates = $mcapi->templates->getList(
				array('gallery' => false, 'base' => false),
				array('include_drag_and_drop' => true)
			);

			// The endpoint is lists/list, to list the lists, but there is no lists->list. getList with no args is equivalent.
			$lists = $mcapi->lists->getList();
		}

		include_once __DIR__ . '/templates/options.php';
	}
}
