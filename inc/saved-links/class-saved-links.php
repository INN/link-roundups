<?php

/**
 * The Argo Links class
 * So we don't have function naming conflicts.
 *
 * @since 0.1
 */
class SavedLinks {

	protected static $notices = array();

	/**
	 * Initialize the class.
	 *
	 * @since 0.1
	 */
	public static function init() {
		/*Register the custom post type of for saved links: rounduplinks */
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );

		/*Register our custom taxonomy of "argo-link-categories" so we can have our own tags/categories for our Argo Links post type*/
		/* moved into a function per wordpress 3.0 issues with calling it directly*/
		add_action( 'init', array( __CLASS__, 'register_rounduplinks_taxonomy' ) );

		/* Add the Add Browser Bookmark submenu */
		add_action( 'admin_menu', array( __CLASS__, 'add_save_to_site_sub_menu' ) );

		/*Add our custom post fields for our custom post type*/
		add_action( 'admin_init', array( __CLASS__, 'add_custom_post_fields' ) );

		/*Save our custom post fields! Very important!*/
		add_action( 'save_post', array( __CLASS__, 'save_custom_fields' ) );

		/*Add our new custom post fields to the display columns on the main Argo Links admin page*/
		add_filter( 'manage_edit-rounduplink_columns', array( __CLASS__, 'display_custom_columns') );

		/*Populate those new columns with the custom data*/
		add_action( 'manage_posts_custom_column', array( __CLASS__, 'data_for_custom_columns' ) );

		add_action( 'widgets_init', array( __CLASS__, 'add_saved_links_widget' ) );
		add_action( 'widgets_init', array( __CLASS__, 'add_link_roundups_widget' ) );

		/* Argo links have no content, so we have to generate it on request */
		add_filter( 'the_content', array( __CLASS__, 'the_content' ) );
		add_filter( 'the_excerpt', array( __CLASS__, 'the_excerpt' ) );
		add_filter( 'post_type_link', array( __CLASS__, 'the_permalink' ), 0, 2);
		add_filter( 'author_link ', array( __CLASS__, 'the_permalink' ) );
		add_filter( 'the_author', array( __CLASS__, 'the_author' ) );
		add_filter( 'the_author_posts_link', array( __CLASS__, 'the_author_posts_link' ) );

		/* If we have any admin_notices, print them */
		add_action('admin_notices', array(__CLASS__, 'admin_notices'));

		/* Register a shortcode to display links */
		add_shortcode( 'rounduplink', array( __CLASS__, 'rounduplink_shortcode' ) );

		/* Register the ajax call that renders the Saved_Links_List_table class*/
		add_action( 'wp_ajax_lroundups_saved_links_list_table_render', array( __CLASS__, 'lroundups_saved_links_list_table_render' ) );

		add_filter( 'gettext', array( __CLASS__, 'change_publish_button' ), 10, 2 );
	}

	/**
	 * Register the Roundup Link post type
	 *
	 * @since 0.1
	 */
	public static function register_post_type() {
		register_post_type( 'rounduplink', array(
			'labels' 		=> array(
				'name' 					=> __( 'Saved Links', 'link-roundups' ),
				'singular_name' 		=> __( 'Saved Link', 'link-roundups' ),
				'add_new' 				=> __( 'New Saved Link', 'link-roundups' ),
				'add_new_item' 			=> __( 'New Saved Link', 'link-roundups' ),
				'edit' 					=> __( 'Edit', 'link-roundups' ),
				'edit_item' 			=> __( 'Edit Saved Link', 'link-roundups' ),
				'view' 					=> __( 'View', 'link-roundups' ),
				'view_item' 			=> __( 'View Saved Link', 'link-roundups' ),
				'search_items' 			=> __( 'Search Saved Links', 'link-roundups' ),
				'not_found' 			=> __( 'No Saved Links found', 'link-roundups' ),
				'not_found_in_trash' 	=> __( 'No Saved Links found in Trash', 'link-roundups' ),
			),
			'description' 	=> __( 'Saved Links', 'link-roundups' ),
			'supports' 		=> array( 'title', 'thumbnail' ),
			'public' 		=> true, // https://github.com/INN/link-roundups/issues/120
			'menu_position' => 6,
			'menu_icon'     => 'dashicons-admin-links',
			'taxonomies' 	=> array(),
			'has_archive' 	=> true
		));
	}

	public static function change_publish_button( $translation, $text ) {
		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ( ! empty( $screen ) ) {
				if ( $screen->post_type == 'rounduplink' ) {
					if ( $text == 'Publish' ) {
						return 'Save link';
					}
				}
			}
		}

		return $translation;
	}

	/**
	 * Register our custom taxonomy
	 *
	 * @since 0.1
	 * @updated 0.3.1
	 */
	public static function register_rounduplinks_taxonomy() {
		register_taxonomy(
			"lr-tags",
			'rounduplink',
			array(
				'hierarchical' => false,
				'label' => __( 'Saved Link Tags', 'link-roundups' ),
				'singular_label' => __( 'Saved Link Tag', 'link-roundups' ),
				'rewrite' => true
			)
		);
	}

	/**
	 * Tell Wordpress where to put our custom fields for our custom post type
	 *
	 * @since 0.1
	 */
	public static function add_custom_post_fields() {
		add_meta_box(
			'saved_links_meta',
			__( 'Link Information', 'link-roundups' ),
			array( __CLASS__, 'display_custom_fields' ),
			'rounduplink',
			'normal','low'
		);
	}

	/**
	 * Show our custom post fields in the add/edit Argo Links admin pages
	 *
	 * @since 0.1
	 */
	public static function display_custom_fields() {

		global $post;
		$custom = get_post_custom( $post->ID );

		if ( isset( $custom['lr_url'][0] ) ) {
			$link_url = $custom['lr_url'][0];
		} else {
			$link_url = apply_filters( 'default_link_url', '' );
		}

		if ( isset( $custom['lr_desc'][0] ) ) {
			$link_description = $custom['lr_desc'][0];
		} else {
			$link_description = apply_filters( 'default_link_description', '' );
		}

		if ( isset( $custom['lr_source'][0] ) ) {
			$link_source = $custom['lr_source'][0];
		} else {
			$link_source = apply_filters( 'default_link_source', '' );
		}

		$link_img_src = Save_To_Site_Button::default_imgUrl();

	?>
	<p>
		<label><?php _e( 'URL:', 'link-roundups' ); ?></label><br />
		<input type='text' name='lr_url' value='<?php echo $link_url; ?>' style='width:98%;'/>
	</p>

	<p>
		<label><?php _e ( 'Description:', 'link-roundups' ); ?></label><br />
		<?php
			wp_editor($link_description, 'lr_desc', array(
				'teeny' => true,
				'media_buttons' => false
			));
		?>
	</p>

	<p>
		<label><?php _e( 'Source:', 'link-roundups' ); ?></label><br />
		<input type='text' name='lr_source' value='<?php echo $link_source; ?>' style='width:98%;'/>
	</p>

	<?php if( $link_img_src ) { ?>
		<p><label><?php _e( 'Import featured image:', 'link-roundups' ); ?></label><br />
		<img src="<?php echo $link_img_src ?>" width="300" />
		<input type='hidden' name='argo_link_img_url' value='<?php echo $link_img_src; ?>'/><br>
		<input type="checkbox" value="1" name="lr_img" id="lr_img"><label for="lr_img"><?php _e( 'Import as feature image', 'link-roundups' ); ?></label>
		</p>
	<?php }
	}

	/**
	 * Save the custom post field data. Very important!
	 *
	 * @since 0.1
	 */
	public static function save_custom_fields($post_id) {

		if ( isset( $_POST['lr_url'] ) )
			update_post_meta( ( isset($_POST['post_ID'] ) ? $_POST['post_ID'] : $post_id ), 'lr_url', $_POST['lr_url'] );

		if ( isset( $_POST["lr_desc"] ) )
			update_post_meta( ( isset($_POST['post_ID'] ) ? $_POST['post_ID'] : $post_id ), 'lr_desc', $_POST['lr_desc'] );


		if ( isset( $_POST['lr_source'] ) )
			update_post_meta( ( isset( $_POST['post_ID'] ) ? $_POST['post_ID'] : $post_id ), 'lr_source', $_POST['lr_source'] );

		if ( isset( $_POST['lr_img'] ) && $_POST['lr_img'] ) {
			$attachment_id = self::lroundups_media_sideload_image( $_POST['argo_link_img_url'], $post_id );
			if( !empty( $attachment_id ) && !is_wp_error( $attachment_id )  ) {
				update_post_meta( ( isset( $_POST['post_ID'] ) ? $_POST['post_ID'] : $post_id ), '_thumbnail_id', $attachment_id );
			} else {
				self::add_notice('error', 'Unable to import featured image.');
			}
		}
	}

	/**
	 * Similar to `media_sideload_image` except that it simply returns the attachment's ID on success
	 *
	 * @param (string) $file the url of the image to download and attach to the post
	 * @param (integer) $post_id the post ID to attach the image to
	 * @param (string) $desc an optional description for the image
	 *
	 * @since 0.1
	 */
	public static function lroundups_media_sideload_image( $file, $post_id, $desc = null ) {
		if ( !empty( $file ) ) {
			// Set variables for storage, fix file filename for query strings.
			preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches );
			$file_array = array();
			$file_array['name'] = basename( $matches[0] );

			// Download file to temp location.
			$file_array['tmp_name'] = download_url( $file );

			// If error storing temporarily, return the error.
			if ( is_wp_error( $file_array['tmp_name'] ) )
				return $file_array['tmp_name'];

			// Do the validation and storage stuff.
			$id = media_handle_sideload( $file_array, $post_id, $desc );
			// If error storing permanently, unlink.
			if ( is_wp_error( $id ) )
				@unlink( $file_array['tmp_name'] );

			return $id;
		}
	}

	/**
	 * Create the new columns to display our custom post fields
	 *
	 * @since 0.1
	 */
	public static function display_custom_columns($columns){
		$columns = array(
			'cb' => '<input type=\"checkbox\" />',
			'title' => __( 'Link Title', 'link-roundups' ),
			'author' => __( 'Author', 'link-roundups' ),
			'url' => __( 'URL', 'link-roundups' ),
			'description' => __( 'Description', 'link-roundups' ),
			'link-tags' => __( 'Tags', 'link-roundups' ),
			'date' => __( 'Date', 'link-roundups' )
		);
		return $columns;
	}

	/**
	 * Fill in our custom data for the new columns
	 *
	 * @since 0.1
	 */
	public static function data_for_custom_columns($column){
		global $post;
		$custom = get_post_custom();

		switch ($column) {

			case 'description':
				echo $custom['lr_desc'][0];
				break;

			case 'url':
				echo $custom["lr_url"][0];
				break;

			case 'link-tags':
				$base_url = 'edit.php?post_type=rounduplink';
				$terms = get_the_terms( $post->ID, 'lr-tags' );
				if ( is_array( $terms ) ) {
					$term_links = array();
					foreach ( $terms as $term ) {
						$term_links[] = '<a href="' . $base_url . '&lr-tags=' . $term->slug . '">' . $term->name . '</a>';
					}
					echo implode( ', ', $term_links );
				} else {
					echo '&nbsp;';
				}
				break;
		}

	}

	/**
	 * Add the Argo Link This! sub menu
	 *
	 * @since 0.1
	 */
	public static function add_save_to_site_sub_menu() {
		add_submenu_page(
			'edit.php?post_type=rounduplink',
			__( 'Add Browser Bookmark', 'link-roundups' ),
			__( 'Add Browser Bookmark', 'link-roundups' ),
			apply_filters( 'link_roundups_minimum_capability', 'edit_posts' ),
			'install-browser-bookmark',
			array(
				__CLASS__, 'build_lroundups_page'
			)
		);
	}

	/**
	 * Add Browser Bookmark Tool based on WP Core Press This! tool
	 *
	 * @see
	 * @since 0.1
	 */
	public static function build_lroundups_page() {
		include_once dirname( LROUNDUPS_PLUGIN_FILE ) . '/inc/saved-links/bookmark.php';
	}

	public static function add_saved_links_widget() {
		register_widget( 'saved_links_widget' );
	}

	public static function add_link_roundups_widget() {
		register_widget( 'link_roundups_widget' );
	}

	/**
	 * Filter saved link content & excerpt
	 *
	 * Saved links have no content, so we have to generate it for inclusion on
	 * archive pages.
	 *
	 * @since 0.3
	 *
	 * @param string $content content passed in by the filter (should be empty).
	 */
	public static function the_permalink($url, $post=null) {
		$post = get_post( $post );

		// Only run for argo_links
		$meta = get_post_meta( $post->ID );
		$remoteUrl = !empty( $meta['lr_url'] ) ? $meta['lr_url'][0] : '';

		if ( empty( $remoteUrl ) || ! ( 'rounduplink' == $post->post_type ) ) {
			remove_filter( 'post_type_link', array( __CLASS__, 'the_permalink' ), 0, 2);
			$permalink = get_permalink( $post->ID );
			add_filter( 'post_type_link', array( __CLASS__, 'the_permalink' ), 0, 2);
			return $permalink;
		}

		return $remoteUrl;
	}

	/**
	 * Returns source as string instead of author.
	 *
	 * Excerpt DOM is static:
	 *  <p class="description">#!DESCRIPTION!#</p>
	 *  <p class="source">Source:<span class="source"><a class="source" href="#!URL!#>#!SOURCE!#</a></span></p>
	 *
	 * @since 0.3
	 *
	 * @param string $content content passed in by the filter (should be empty).
	 */
	public static function the_author($author) {
		// Only run for argo_links
		global $post;

		$default = '';

		$meta = get_post_meta($post->ID);
		$source = !empty( $meta['lr_source'] ) ? $meta['lr_source'][0] : $default;

		if ( empty( $source ) || !( 'rounduplink' == $post->post_type ) ) {
			return $author;
		}

		return $source;

	}

	/**
	 * Returns a link to the source article in place of a link to the author's page.
	 *
	 * @since 0.3
	 *
	 * @param string $content content passed in by the filter (should be empty).
	 */
	public static function the_author_posts_link($link) {
		global $post;

		if ( !( 'rounduplink' == $post->post_type ) ) {
			return $link;
		}

		$meta = get_post_meta( $post->ID );
		$url = self::the_permalink( $post ); //! empty( $meta['lr_url'] ) ? $meta['lr_url'][0] : '';
		$title = get_the_title( $post->ID );
		$source = ! empty( $meta['lr_source'] ) ? $meta['lr_source'][0] : '';

		$link = sprintf(
			'<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
			esc_url( $url ),
			esc_attr( $title ),
			$source
		);

		return $link;
	}

	/**
	 * Filter Saved Link content.
	 *
	 * Saved links have no content, so we have to generate it for inclusion on
	 * archive pages.
	 *
	 * @since 0.3
	 *
	 * @param string $content content passed in by the filter (should be empty).
	 */
	public static function the_content($content) {
		// Only run for roundup links
		global $post;

		if ( ! isset( $post ) ) {
			return $content;
		}

		if ( is_post_type_archive( 'rounduplink' ) ) {
			return get_post_meta( $post->ID, 'lr_desc', true );
		}

		if ( ! ( 'rounduplink' == $post->post_type ) ) {
			return $content;
		}

		return self::get_html( $post );
	}

	/**
	 * Filter Saved Link content & excerpt
	 *
	 * Saved Links have no content, so we have to generate it for inclusion on
	 * archive pages.
	 *
	 * @since 0.3
	 *
	 * @param string $content content passed in by the filter (should be empty).
	 */
	public static function the_excerpt($content) {
		global $post;

		// Only run for argo_links
		if ( ! ( 'rounduplink' == $post->post_type ) ) {
			return $content;
		}

		return self::get_excerpt();
	}

	/**
	 * Returns DOM for an argolink post content.
	 *
	 * DOM is generated either from the default HTML string or from a user
	 * specified dom string in rounduplink options.
	 *
	 * @since 0.3
	 *
	 * @param string $content content passed in by the filter (should be empty).
	 */
	public static function get_html($post=null, $link_class=null, $attrs=array()) {
		$post = get_post( $post );

		if ( ! $post ) {
			return;
		}

		$meta = get_post_meta( $post->ID );

		if ( $post->post_type == 'rounduplink' ) {
			$url = !empty($meta['lr_url'])? $meta['lr_url'][0] : '';
			$description = array_key_exists( 'lr_desc', $meta ) ? $meta['lr_desc'][0] : '';
			$source = ! empty( $meta['lr_source'] ) ? $meta['lr_source'][0] : '';
		} else {
			// Fallback when post types other than 'rounduplink' are used in Link Roundups
			if ( ! empty( $meta['lr_url'] ) && ! empty( $meta['lr_url'][0] ) ) {
				$url = $meta['lr_url'][0];
			} else {
				$url = get_permalink( $post );
			}

			if ( ! empty( $meta['lr_desc'] ) && ! empty( $meta['lr_desc'][0] ) ) {
				$description = $meta['lr_desc'][0];
			} else if ( ! empty( $post->post_excerpt ) ) {
				$description = $post->post_excerpt;
			} else {
				$description = $post->post_content;
			}

			if ( ! empty( $meta['lr_source'] ) && ! empty( $meta['lr_source'][0] )  ) {
				$source = $meta['lr_source'][0];
			} else {
				$source = get_bloginfo( 'name' );
			}
		}

		/**
		 * Allow the ability to manipulate the link title based on the post and link class
		 *
		 * @since 0.3.2
		 */
		$title = apply_filters(
			'lroundups_link_title',
			get_the_title( $post->ID ),
			$post,
			$link_class
		);

		$lroundups_html = apply_filters(
			'lroundups_custom_html',
			get_option( 'lroundups_custom_html' ),
			$post,
			$link_class,
			$attrs
		);

		if ( $lroundups_html == '' ) {
			$lroundups_html = self::lroundups_default_link_html();
		}

		$lroundups_html = str_replace('#!URL!#', $url, $lroundups_html);
		$lroundups_html = str_replace('#!TITLE!#', $title, $lroundups_html);
		$lroundups_html = str_replace('#!DESCRIPTION!#', $description, $lroundups_html);
		$lroundups_html = str_replace('#!SOURCE!#', $source, $lroundups_html);
		$lroundups_html = str_replace('#!CLASS!#', $link_class, $lroundups_html);

		if ( has_post_thumbnail( $post->ID ) ) {
			$lroundups_html = str_replace( '#!IMAGE!#', get_the_post_thumbnail( $post->ID ), $lroundups_html );
		 } else {
			 $lroundups_html = str_replace( '#!IMAGE!#', '', $lroundups_html );
		 }

		return $lroundups_html;
	}

	/**
	 * Displays rounduplink html.
	 *
	 * @since 0.3
	 */
	public static function rounduplink_shortcode( $atts ) {
		$a = shortcode_atts(
			array(
				'id' => '',
				'title' => '',
				'class' => ''
			),
			$atts
		);

		$link_class = (!empty($a['class']))? ' ' . $a['class'] : '';

		// send it all over to get_html (see above)
		if ( $a['id'] != null ) {
			return self::get_html( $a['id'], $link_class ); // id and sponsored class
		} else {
			return '';
		}
	}

	/**
	 * Returns DOM for a rounduplink excerpt.
	 *
	 * Excerpt DOM is static:
	 *  <p class="description">#!DESCRIPTION!#</p>
	 *  <p class="source">Source:<span class="source"><a class="source" href="#!URL!#>#!SOURCE!#</a></span></p>
	 *
	 * @since 0.3
	 *
	 * @param string $content content passed in by the filter (should be empty).
	 */
	public static function get_excerpt( $post ) {
		$post = get_post( $post );
		$custom = get_post_meta( $post->ID );

		ob_start();
		if ( isset( $custom['lr_desc'][0] ) )
			echo '<p class="description">' . $custom['lr_desc'][0] . '</p>';
		if ( isset( $custom['lr_source'][0] ) && ( $custom['lr_source'][0] != '' ) ) {
			echo '<p class="source">' . __('Source: ', 'link-roundups') . '<span>';
			echo isset( $custom['lr_url'][0] ) ? '<a href="' . $custom["lr_url"][0] . '">' . $custom["lr_source"][0] . '</a>' : $custom["lr_source"][0];
			echo '</span></p>';
		}
		$html = ob_get_clean();

		return $html;
	}

	/*
	 * Load the Saved_Links_List_Table class
	 *
	 * @since 0.3.2
	 */
	public static function lroundups_saved_links_list_table_render() {
		require_once( __DIR__ . '/class-saved-links-list-table.php' );

		// Set up and generate the table.
		$links_list_table = new Saved_Links_List_Table();
		$links_list_table->prepare_items();
		$links_list_table->display();
		// Reset Query
		wp_reset_query();

		wp_die();
	}

	/**
	 * A utility function for adding admin notices after the save_post action has fired
	 *
	 * @since 0.3.2
	 */
	public static function add_notice($type, $message) {
		self::$notices[] = array($type, urlencode($message));
		add_filter('redirect_post_location', array(__CLASS__, 'add_notice_query_var'), 99);
	}

	/**
	 * When the post save_post redirect happens, make sure we're adding the notices arg if necessary
	 *
	 * @since 0.3.2
	 */
	public static function add_notice_query_var($location) {
		if (!empty(self::$notices)) {
			remove_filter('redirect_post_location', array(__CLASS__, 'add_notice_query_var'), 99);
			return add_query_arg(array('lroundups_notices' => self::$notices), $location);
		}
	}

	/**
	 * If the lroundups_notices $_GET arg is set, print our admin notices
	 *
	 * @since 0.3.2
	 */
	public static function admin_notices() {
		if (!isset($_GET['lroundups_notices']))
			return;

		foreach ($_GET['lroundups_notices'] as $notice) { ?>
			<div class="<?php echo $notice[0]; ?>"><p><?php echo urldecode($notice[1]); ?></p></div>
	<?php }
	}

	public static function lroundups_default_link_html() {
		ob_start(); ?>
		<p class="lr-saved-link #!CLASS!#">
			#!IMAGE!#
			<a href="#!URL!#">#!TITLE!#</a>&ndash;<span class="description">#!DESCRIPTION!#</span> <em>#!SOURCE!#</em>
		</p><?php
		return ob_get_clean();
	}
}
