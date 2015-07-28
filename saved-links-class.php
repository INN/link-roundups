<?php

/**
 * The Argo Links class
 * So we don't have function naming conflicts.
 *
 * @since 0.1
 */
class SavedLinks {

	/**
	 * Initialize the class.
	 * 
	 * @since 0.1
	 */
	public static function init() {

		/*Register the custom post type of for saved links: rounduplinks */
		add_action('init', array(__CLASS__, 'register_post_type' ));

		/*Register our custom taxonomy of "argo-link-categories" so we can have our own tags/categories for our Argo Links post type*/
		/* moved into a function per wordpress 3.0 issues with calling it directly*/
		add_action('init', array(__CLASS__, 'register_rounduplinks_taxonomy'));

		/* Add the Add Browser Bookmark submenu */
		add_action("admin_menu", array(__CLASS__, "add_save_to_site_sub_menu"));

		/*Add our custom post fields for our custom post type*/
		add_action("admin_init", array(__CLASS__, "add_custom_post_fields"));

		/*Save our custom post fields! Very important!*/
		add_action('save_post', array(__CLASS__, 'save_custom_fields'));

		/*Add our new custom post fields to the display columns on the main Argo Links admin page*/
		add_filter("manage_edit-rounduplink_columns", array(__CLASS__, "display_custom_columns"));

		/*Populate those new columns with the custom data*/
		add_action("manage_posts_custom_column", array(__CLASS__, "data_for_custom_columns"));

		add_action('widgets_init', array(__CLASS__, 'add_saved_links_widget'));
		add_action('widgets_init', array(__CLASS__, 'add_link_roundups_widget'));

		/*Add our css stylesheet into the header*/
		add_action('admin_print_styles', array(__CLASS__,'add_styles'));
		add_action('wp_print_styles', array(__CLASS__, 'add_styles'));
		add_filter('mce_css', array(__CLASS__,'plugin_mce_css'));

		/* Argo links have no content, so we have to generate it on request */
		add_filter('the_content', array(__CLASS__,'the_content') );
		add_filter('the_excerpt', array(__CLASS__,'the_excerpt') );
		add_filter('post_type_link', array(__CLASS__,'the_permalink'), 0, 2);
		add_filter('author_link ', array(__CLASS__,'the_permalink') );
		add_filter('the_author', array(__CLASS__,'the_author') );
		add_filter('the_author_posts_link', array(__CLASS__,'the_author_posts_link'));

		/* Register a shortcode to display links */
		add_shortcode('rounduplink', array(__CLASS__,'rounduplink_shortcode'));
	}

	public static function plugin_mce_css($mce_css) {
		if (!empty($mce_css)) {
			$mce_css .= ',';
		} else {
			$mce_css = '';
		}
		$mce_css .= plugins_url("css/lroundups.min.css", __FILE__);
		return $mce_css;
	}

	/*Add our css stylesheet into the header*/
	public static function add_styles() {
		$css = plugins_url('css/lroundups.css', __FILE__);
		wp_enqueue_style('link-roundups', $css, array(), 1);
	}

	/**
	 * Register the Roundup Link post type 
	 *
	 * @since 0.1
	 */
	public static function register_post_type() {
		
		register_post_type('rounduplink', array(
			'labels' => array(
				'name' => 'Saved Links',
				'singular_name' => 'Saved Link',
				'add_new' => 'New Saved Link',
				'add_new_item' => 'New Saved Link',
				'edit' => 'Edit',
				'edit_item' => 'Edit Saved Link',
				'view' => 'View',
				'view_item' => 'View Saved Link',
				'search_items' => 'Search Saved Links',
				'not_found' => 'No Saved Links found',
				'not_found_in_trash' => 'No Saved Links found in Trash',
			),
			'description' => 'Saved Links',
			'supports' => array( 'title', 'thumbnail' ),
			'public' => true,
			'menu_position' => 6,
			'menu_icon'     => 'dashicons-admin-links',
			'taxonomies' => array(),
			'has_archive' => true
		));

	}

	/**
	 * Register our custom taxonomy
	 * 
	 * @since 0.1
	 */
	public static function register_rounduplinks_taxonomy() {
		register_taxonomy(
			"argo-link-tags",
			'rounduplink',
			array(
				"hierarchical" => false,
				"label" => "Saved Link Tags",
				"singular_label" => "Saved Link Tag",
				"rewrite" => true
			)
		);
	}

	/**
	 * Tell Wordpress where to put our custom fields for our custom post type
	 * 
	 * @since 0.1
	 */
	public static function add_custom_post_fields() {
		add_meta_box("saved_links_meta", "Link Information", array(__CLASS__,"display_custom_fields"), "rounduplink", "normal", "low");
	}

	/**
	 * Show our custom post fields in the add/edit Argo Links admin pages
	 * 
	 * @since 0.1
	 */
	public static function display_custom_fields() {

		global $post;
		$custom = get_post_custom($post->ID);

		if (isset($custom["argo_link_url"][0])) {
			$link_url = $custom["argo_link_url"][0];
		} else {
			$link_url = apply_filters('default_link_url',"");
		}

		if (isset($custom["argo_link_description"][0])) {
			$link_description = $custom["argo_link_description"][0];
		} else {
			$link_description = apply_filters('default_link_description',"");
		}

		if (isset($custom["argo_link_source"][0])) {
			$link_source = $custom["argo_link_source"][0];
		} else {
			$link_source = apply_filters('default_link_source',"");
		}

		$link_img_src = Save_To_Site_Button::default_imgUrl();

	?>
	<p><label>URL:</label><br />
	<input type='text' name='argo_link_url' value='<?php echo $link_url; ?>' style='width:98%;'/></p>
	<p><label>Description:</label><br />
	<textarea cols="100" rows="5" name="argo_link_description" style='width:98%;'><?php echo $link_description; ?></textarea></p>
	<p><label>Source:</label><br />
	<input type='text' name='argo_link_source' value='<?php echo $link_source; ?>' style='width:98%;'/></p>

	<?php if( $link_img_src ) { ?>
		<p><label>Import featured image:</label><br />
		<img src="<?php echo $link_img_src ?>" width="300" />
		<input type='hidden' name='argo_link_img_url' value='<?php echo $link_img_src; ?>'/><br>
		<input type="checkbox" value="1" name="argo_link_img_url_import" id="argo_link_img_url_import"><label for="argo_link_img_url_import">Import as feature image</label>
		</p>
	<?php }
	}

	/**
	 * Save the custom post field data. Very important!
	 * 
	 * @since 0.1
	 */
	public static function save_custom_fields($post_id) {

		if (isset($_POST["argo_link_url"])){
			update_post_meta((isset($_POST['post_ID']) ? $_POST['post_ID'] : $post_id), "argo_link_url", $_POST["argo_link_url"]);
		}

		if (isset($_POST["argo_link_description"])){
			update_post_meta((isset($_POST['post_ID']) ? $_POST['post_ID'] : $post_id), "argo_link_description", $_POST["argo_link_description"]);
		}

		if (isset($_POST["argo_link_source"])){
			update_post_meta((isset($_POST['post_ID']) ? $_POST['post_ID'] : $post_id), "argo_link_source", $_POST["argo_link_source"]);
		}

		if (isset($_POST["argo_link_img_url_import"]) && $_POST["argo_link_img_url_import"]) {
			$attachment_id = self::lroundups_media_sideload_image($_POST["argo_link_img_url"],$post_id);
			if(!empty($attachment_id) && !is_wp_error($attachment_id)) {
				update_post_meta((isset($_POST['post_ID']) ? $_POST['post_ID'] : $post_id), "_thumbnail_id", $attachment_id);
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
	public static function lroundups_media_sideload_image($file, $post_id, $desc=null) {

		if (!empty($file)) {
			// Set variables for storage, fix file filename for query strings.
			preg_match('/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches);
			$file_array = array();
			$file_array['name'] = basename($matches[0]);
			// Download file to temp location.
			$file_array['tmp_name'] = download_url($file);
			// If error storing temporarily, return the error.
			if (is_wp_error($file_array['tmp_name'])) {
				return $file_array['tmp_name'];
			}
			// Do the validation and storage stuff.
			$id = media_handle_sideload($file_array, $post_id, $desc);
			// If error storing permanently, unlink.
			if (is_wp_error($id)) {
				@unlink($file_array['tmp_name']);
			}
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
			"cb" => "<input type=\"checkbox\" />",
			"title" => "Link Title",
			"author" => "Author",
			"url" => "URL",
			"description" => "Description",
			"link-tags" => "Tags",
			"date" => "Date"
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

			case "description":
				echo $custom["argo_link_description"][0];
				break;

			case "url":
				echo $custom["argo_link_url"][0];
				break;

			case "link-tags":
				$base_url = "edit.php?post_type=rounduplink";
				$terms = get_the_terms($post->ID, 'argo-link-tags');
				if (is_array($terms)) {
					$term_links = array();
					foreach ($terms as $term) {
						$term_links[] = "<a href='".$base_url."&argo-link-tags=".$term->slug."'>".$term->name."</a>";
					}
					echo implode(", ",$term_links);
				} else {
					echo "&nbsp;";
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
			"edit.php?post_type=rounduplink",
			"Add Browser Bookmark",
			"Add Browser Bookmark",
			"edit_posts",
			"install-browser-bookmark",
			array(__CLASS__, 'build_lroundups_page'
		));
	}

	/**
	 * Add Browser Bookmark Tool based on WP Core Press This! tool
	 *
	 * @see  
	 * @since 0.1
	 */
	public static function build_lroundups_page() {
	/** WordPress Administration Bootstrap */
	include_once( ABSPATH  . '/admin.php' );
	?>
	
	<div id="icon-tools" class="icon32"><br></div><h2><?php _e('Add Save to Site Bookmark to Your Web Browser'); ?></h2>

	<div class="tool-box">

	 <div class="card pressthis">
	<h3><?php _e('Save to Site') ?></h3>
	<p><?php _e( 'Save to Site is a tool that lets you send Saved Links to your WordPress Dashboard while browsing the web.' );?></p>
	<p><?php _e( 'Click the Save to Site bookmark and a new WordPress window will popup, attempting to prefill Title, URL and Source information.' ); ?></p>


	<form>
		<h3><?php _e( 'Install Save to Site' ); ?></h3>
		<h4><?php _e( 'Browser Bookmarklet' ); ?></h4>
		<p><?php _e( 'Drag the Save to Site bookmarklet below to your web browser\'s Bookmarks Toolbar.<br /><em>If you can\'t drag, click the Clipboard.</em>' ); ?></p>

		<p class="pressthis-bookmarklet-wrapper">
			<a class="pressthis-bookmarklet" onclick="return false;" href="<?php echo Save_To_Site_Button::shortcut_link(); ?>"><span><?php _e( 'Save to Site' ); ?></span></a>
			<button type="button" class="button button-secondary pressthis-js-toggle js-show-pressthis-code-wrap" aria-expanded="false" aria-controls="pressthis-code-wrap">
				<span class="dashicons dashicons-clipboard"></span>
				<span class="screen-reader-text"><?php _e( 'Copy Save to Site bookmarklet code' ) ?></span>
			</button>
		</p>

		<div class="hidden js-pressthis-code-wrap clear" id="pressthis-code-wrap">
			<p id="pressthis-code-desc">
				<?php _e( 'If you can&#8217;t drag the bookmarklet to your bookmarks, copy the following code and create a new bookmark. Paste the code into the new bookmark&#8217;s URL field.' ) ?>
			</p>
			<p>
				<textarea class="js-pressthis-code" rows="5" cols="120" readonly="readonly" aria-labelledby="pressthis-code-desc"><?php echo htmlspecialchars( get_shortcut_link() ); ?></textarea>
			</p>
		</div>

		<h4><?php _e( 'Direct link (best for mobile and tablets)' ); ?></h4>
		<p><?php _e( 'Follow the link to open Save to Site. Then add it to your device&#8217;s bookmarks or home screen.' ); ?></p>

		<p>
			<a class="button button-secondary" href="<?php echo Save_To_Site_Button::shortcut_link(); ?>"><?php _e( 'Open Save to Site' ) ?></a>
		</p>
		<script>
			jQuery( document ).ready( function( $ ) {
				var $showPressThisWrap = $( '.js-show-pressthis-code-wrap' );
				var $pressthisCode = $( '.js-pressthis-code' );

				$showPressThisWrap.on( 'click', function( event ) {
					var $this = $( this );

					$this.parent().next( '.js-pressthis-code-wrap' ).slideToggle( 200 );
					$this.attr( 'aria-expanded', $this.attr( 'aria-expanded' ) === 'false' ? 'true' : 'false' );
				});

				// Select Press This code when focusing (tabbing) or clicking the textarea.
				$pressthisCode.on( 'click focus', function() {
					var self = this;
					setTimeout( function() { self.select(); }, 50 );
				});

			});
		</script>
	</form>
</div>
	</div>
<?php
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
		$post = get_post($post);

		// Only run for argo_links
		$meta = get_post_meta($post->ID);
		$remoteUrl = !empty($meta["argo_link_url"]) ? $meta["argo_link_url"][0] : '';

		if ( empty($url) || !( 'rounduplink' == $post->post_type ) ) {
			return $url;
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
		$source = !empty($meta["argo_link_source"]) ? $meta["argo_link_source"][0] : $default;

		if ( empty($source) || !( 'rounduplink' == $post->post_type ) ) {
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

		$meta = get_post_meta($post->ID);

		$url = !empty($meta["argo_link_url"]) ? $meta["argo_link_url"][0] : '';
		$title = get_the_title($post->ID);
		$description = array_key_exists("argo_link_description",$meta) ? $meta["argo_link_description"][0] : '';;
		$source = !empty($meta["argo_link_source"]) ? $meta["argo_link_source"][0] : '';

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

		// Only run for argo_links
		global $post;
		if ( ! ( 'rounduplink' == $post->post_type ) ) {
			return $content;
		}

		return self::get_html($post);
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

		// Only run for argo_links
		global $post;
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
	public static function get_html( $post = null, $link_style = null) {

		$post = get_post($post); // getting a saved link...

		if(!$post)
			return;

		$meta = get_post_meta($post->ID); // getting meta fields from saved link...

		$url = !empty($meta["argo_link_url"]) ? $meta["argo_link_url"][0] : '';
		$title = get_the_title($post->ID);
		$description = array_key_exists("argo_link_description",$meta) ? $meta["argo_link_description"][0] : '';
		$source = !empty($meta["argo_link_source"]) ? $meta["argo_link_source"][0] : '';
		$style = $link_style; // $link_style is defined below in rounduplink_shortcode()
		
		ob_start(); 
		
		/* begin shortcode output
		 *
		 * NOTE: 
		 * This default output is overwritten 98% of the time by $lroundups_html
		 * regardless of whether you've modified the code in setting
		 * Needs improvement in future version
		*/
		?>
		
	  <p class='lr-saved-link' style='#!STYLE!#'>
		<a href='#!URL!#'>#!TITLE!#</a>
		&ndash;
		<span class='description'>#!DESCRIPTION!#</span>
		<em>#!SOURCE!#</em>
	  </p>
	
	  <?php
		$default_html = ob_get_clean();

		if (get_option("argo_link_roundups_custom_html") != "") {
			$lroundups_html = get_option("argo_link_roundups_custom_html");
			$lroundups_html = preg_replace("/\"/","'",$lroundups_html);
		} else {
			$lroundups_html = $default_html;
		}
		$lroundups_html = str_replace("#!URL!#",$url,$lroundups_html);
		$lroundups_html = str_replace("#!TITLE!#",$title,$lroundups_html);
		$lroundups_html = str_replace("#!DESCRIPTION!#",$description,$lroundups_html);
		$lroundups_html = str_replace("#!SOURCE!#",$source,$lroundups_html);
		$lroundups_html = str_replace("#!STYLE!#",$style,$lroundups_html);
		return $lroundups_html;
	}

	/**
	 * Displays rounduplink html.
	 * 
	 * @since 0.3
	 */
	public static function rounduplink_shortcode( $atts ) {

		$a = shortcode_atts( 
			array( 'id' => '', 
				   'title' => '', 
				   'style' => '' ), 
			$atts
		);
		
		// check if a link has style like 'sponsored'
		if(!empty($a['style'])) { 
		
			$custom_css = get_option('argo_link_roundups_sponsored_style');
			
			// check for custom css
			if(!empty($custom_css)) {
				$output_style = $custom_css;
			}
			
			// set a default style for the plugin
			else {
				$output_style = 'font-style:italics;background:#aaa;';
			}
			
			$link_style = $output_style; // we pass this variable below
			
		}
		
		else { // if no style="" in shortcode, display nothing
			$link_style='';
		}
		
		if( $a['id'] != null )
			return self::get_html( $a['id'], $link_style ); // id and style
		else
			return '';
		
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
	public static function get_excerpt($post) {

		$post = get_post($post);
		$custom = get_post_meta($post->ID);

		ob_start();
		if ( isset( $custom["argo_link_description"][0] ) )
			echo '<p class="description">' . $custom["argo_link_description"][0] . '</p>';
		if ( isset($custom["argo_link_source"][0] ) && ( $custom["argo_link_source"][0] != '' ) ) {
			echo '<p class="source">' . __('Source: ', 'link-roundups') . '<span>';
			echo ( isset( $custom["argo_link_url"][0] ) ) ? '<a href="' . $custom["argo_link_url"][0] . '">' . $custom["argo_link_source"][0] . '</a>' : $custom["argo_link_source"][0];
			echo '</span></p>';
		}
		$html = ob_get_clean();

		return $html;

	}

}
