<?php
/**
 * Link Roundups Post Type and Supporting Functions
 *
 * @package Link_Roundups
 * @version 0.3
 */

/**
 * The LinkRoundups class - so we don't have function naming conflicts with link-roundups
 */
class LinkRoundups {

	// Initialize the plugin
	public static function init() {

		// Register the custom post type of roundup
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );

		// Add the Link Roundups Options sub menu
		add_action( 'admin_menu', array( __CLASS__, 'add_lroundups_options_page' ) );

		// Add our custom post fields for our custom post type
		add_action( 'admin_init', array( __CLASS__, 'add_custom_post_fields' ) );

		// Save our custom post fields! Very important!
		add_action( 'save_post', array( __CLASS__, 'save_custom_fields' ) );

		/*Add our css stylesheet into the header*/
		add_action( 'admin_print_styles', array( __CLASS__, 'add_styles' ) );
		add_action( 'wp_print_styles', array( __CLASS__, 'add_styles' ) );
		add_filter( 'mce_css', array( __CLASS__, 'plugin_mce_css' ) );

		// Make sure our custom post type gets pulled into the river
		add_filter( 'pre_get_posts', array( __CLASS__, 'lr_get_posts' ) );
	}

	// Pull the linkroundups into the queries for is_home, is_tag, is_category, is_archive

	// Merge the post_type query var if there is already a custom post type being pulled in otherwise do post & linkroundups
	public static function lr_get_posts( &$query ) {
		// bail out early if suppress filters is set to true
		if ( $query->get( 'suppress_filters' ) ) {
			return;
		}
		if ( is_admin() ) {
			return;
		}

		// Add roundup to the post type in the query if it is not already in it.
		if ( $query->is_home() || $query->is_tag() || $query->is_category() || $query->is_author() ) {
			if ( isset( $query->query_vars['post_type'] ) && is_array( $query->query_vars['post_type'] ) ) {
				if ( ! in_array( 'roundup', $query->query_vars['post_type'] ) ) {
					// There is an array of post types and roundup is not in it
					$query->set( 'post_type', array_merge( array( 'roundup' ), $query->query_vars['post_type'] ) );
				}
			} elseif ( isset( $query->query_vars['post_type'] ) && ! is_array( $query->query_vars['post_type'] ) ) {
				if ( $query->query_vars['post_type'] !== 'roundup' ) {
					// There is a single post type, so we shall add it to an array
					$query->set( 'post_type', array( 'roundup', $query->query_vars['post_type'] ) );
				}
			} else {
				// Post type is not set, so it shall be post and roundup
				$query->set( 'post_type', array( 'post', 'roundup' ) );
			}
		}
	}

	/**
	 * Register the Link Roundups Custom Post Type
	 * Use Options Page settings to set Names and Slug
	 *
	 * @since 0.1
	 */
	public static function register_post_type() {
		$singular_opt = get_option( 'lroundups_custom_name_singular' );
		$plural_opt   = get_option( 'lroundups_custom_name_plural' );
		$slug_opt     = get_option( 'lroundups_custom_url' );

		if ( ! empty( $singular_opt ) ) {
			$singular = $singular_opt;
		} else {
			$singular = 'Link Roundup';
		}

		if ( ! empty( $plural_opt ) ) {
			$plural = $plural_opt;
		} else {
			$plural = 'Link Roundups';
		}

		$roundup_options = array(
			'labels'        => array(
				'name'               => $plural,
				'singular_name'      => $singular,
				'add_new'            => 'Add ' . $singular,
				'add_new_item'       => 'Add New ' . $singular,
				'edit'               => 'Edit',
				'edit_item'          => 'Edit ' . $singular,
				'view'               => 'View',
				'view_item'          => 'View ' . $singular,
				'search_items'       => 'Search ' . $plural,
				'not_found'          => 'No ' . $plural . ' found',
				'not_found_in_trash' => 'No ' . $plural . ' found in Trash',
			),
			'description'   => $plural,
			'supports'      => array(
				'title',
				'editor',
				'author',
				'thumbnail',
				'excerpt',
				'trackbacks',
				'custom-fields',
				'comments',
				'revisions',
				'page-attributes',
				'post-formats',
			),
			'public'        => true,
			'menu_position' => 7,
			'menu_icon'     => 'dashicons-list-view',
			'taxonomies'    => apply_filters( 'roundup_taxonomies', array( 'category', 'post_tag' ) ),
			'has_archive'   => true,
		);

		if ( $slug_opt != '' ) {
			$roundup_options['rewrite'] = array( 'slug' => $slug_opt );
		}

		register_post_type( 'roundup', $roundup_options );
		if ( function_exists( 'mailchimp_tools_register_for_post_type' ) ) {
			mailchimp_tools_register_for_post_type( 'roundup', array( 'preview' => true ) );
		}
	}

	/*Add our css stylesheet into tinymce*/
	public static function plugin_mce_css( $mce_css ) {
		if ( ! empty( $mce_css ) ) {
			$mce_css .= ',';
		} else {
			$mce_css = '';
		}
		// check if styles have been removed
		$remove_styles = get_option( 'lroundups_dequeue_styles' );
		if ( empty( $remove_styles ) ) {
			$suffix   = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			$mce_css .= plugins_url( 'css/lroundups' . $suffix . '.css', LROUNDUPS_PLUGIN_FILE );
			return $mce_css;
		}
	}

	/*Add our css stylesheet into the header*/
	public static function add_styles() {
		// check if styles should be removed
		$remove_styles = get_option( 'lroundups_dequeue_styles' );
		if ( empty( $remove_styles ) ) {
			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			$css    = plugins_url( 'css/lroundups' . $suffix . '.css', LROUNDUPS_PLUGIN_FILE );
			wp_enqueue_style( 'link-roundups', $css, array(), 1 );
		}
	}

	/**
	 * Register meta box for custom fields on roundup edit pages.
	 *
	 * @since 0.1
	 * @see display_custom_fields()
	 */
	public static function add_custom_post_fields() {
		add_meta_box(
			'link_roundups_roundup',
			'Recent Saved Links',
			array( __CLASS__, 'display_custom_fields' ),
			'roundup',
			'advanced',
			'high'
		);
	}

	/**
	 * Show our custom post fields in the add/edit Argo Link Roundups admin pages
	 *
	 * @since 0.1
	 */
	public static function display_custom_fields() {
		?>
			<div id='lroundups-display-area'></div>
			<script type='text/javascript'>
			jQuery(function(){
				var data = {
					'action': 'lroundups_saved_links_list_table_render'
				};

				jQuery.post(ajaxurl, data).done(function(response) {
					jQuery('#lroundups-display-area').html(response);
				});
			});
			</script>
		<?php
	}

	/**
	 * Save the custom post field data.
	 *
	 * Wait, does this do anything on roundups!? - Will
	 * Ben: Nope. Time to remove it?
	 *
	 * @todo: remove this
	 * @since 0.1
	 */
	public static function save_custom_fields( $post_id ) {
		if ( isset( $_POST ) ) {
			// error_log(var_export( $_POST['mailchimp'], true));
		}
		if ( isset( $_POST['lr_url'] ) ) {
			update_post_meta( ( isset( $_POST['post_id'] ) ? $_POST['post_ID'] : $post_id ), 'lr_url', $_POST['lr_url'] );
		}
		if ( isset( $_POST['lr_desc'] ) ) {
			update_post_meta( ( isset( $_POST['post_id'] ) ? $_POST['post_ID'] : $post_id ), 'lr_desc', $_POST['lr_desc'] );
		}
	}

	/**
	 * Add options sub menu for roundups.
	 *
	 * @since 0.1
	 */
	public static function add_lroundups_options_page() {
		add_submenu_page(
			'edit.php?post_type=roundup',   // $parent_slug
			'Options',                      // $page_title
			'Options',                      // $menu_title
			apply_filters( 'link_roundups_minimum_capability', 'edit_posts' ), // $capability
			'link-roundups-options',        // $menu_slug
			array( __CLASS__, 'build_lroundups_options_page' )  // $function
		);

		// call register settings function
		add_action( 'admin_init', array( __CLASS__, 'register_mysettings' ) );
	}

	public static function register_mysettings() {
		// register our settings
		register_setting( 'lroundups-settings-group', 'lroundups_custom_url' );
		register_setting( 'lroundups-settings-group', 'lroundups_custom_html' );
		register_setting( 'lroundups-settings-group', 'lroundups_dequeue_styles' );
		register_setting( 'lroundups-settings-group', 'lroundups_custom_name_singular' );
		register_setting( 'lroundups-settings-group', 'lroundups_custom_name_plural' );
	}

	public static function build_lroundups_options_page() {
		// get the custom url
		$defined_url = get_option( 'lroundups_custom_url' );

		// check if settings have been updated and if there is a custom slug
		// TODO: don't flush rewrite rules unless they've actually chanaged since
		// the last time they were flushed.
		if ( isset( $_GET['settings-updated'] ) && ! empty( $defined_url ) ) {
			flush_rewrite_rules();
		}

		// get settings fields for options page
		include_once dirname( LROUNDUPS_PLUGIN_FILE ) . '/templates/options.php';
	}
}
