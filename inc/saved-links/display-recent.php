<?php
/**
 * Recent Saved Links Custom Meta Panel
 * for Link Roundups Post Type
 *
 * @package Link_Roundups
 * @since 0.1
 */

// WordPress Admin Bootstrap
require_once( '../../../../../wp-admin/admin.php' );
require_once( './class-wp-list-table-clone.php' );

/**
 * Class to generate the table of saved links in the link roundups editor
 *
 * @link http://www.smashingmagazine.com/2011/11/native-admin-tables-wordpress/
 * @see clone_WP_List_Table
 * @see ./README.md
 * @since 0.3.2
 */
class Saved_Links_List_Table extends clone_WP_List_Table {
	/**
	 * Run the WP_List_Table constructor and set the class names
	 *
	 * @since 0.3.2
	 */
	function __construct() {
		parent::__construct( array(
			'singular' => 'lroundups-link',
			'plural' => 'lroundups-links',
		));
	}

	/**
	 * Additional decorations for the table: "Send to editor" button and "Date range" filter
	 *
	 * @param string $which is either "top" or "bottom", and tells you which nav you're outputting.
	 * @since 0.3.2
	 */
	function bulk_actions( $which ) {
		// this will display at top and bottom
		?>
		<button class='button append-saved-links' style='float:left;'><?php _e( 'Send to Editor', 'link-roundups' ); ?></button>
		<?php
		if ( $which == 'top' ) {
			// Date range:
			?>
			<div style='float:left;'>
				<form action='' method='get' id='filter_links'>
					<label for='link_date'><b><?php _e( 'Date Range:', 'link-roundups' ); ?></b></label>
					<select name='link_date'>
						<option value='today' <?php echo ( ( isset( $_REQUEST['link_date'] ) && $_REQUEST['link_date'] == 'today' ) ? 'selected' : '' );?>><?php _e( 'Today',' link-roundups' ); ?></option>
						<option value='this_week' <?php echo ( ( isset( $_REQUEST['link_date'] ) && $_REQUEST['link_date'] == 'this_week' ) ? 'selected' : '' );?>><?php _e( 'This Week',' link-roundups' ); ?></option>
						<option value='this_month' <?php echo ( ( isset( $_REQUEST['link_date']) && $_REQUEST['link_date'] == 'this_month' ) ? 'selected' : '' );?>><?php _e( 'This Month',' link-roundups' ); ?></option>
						<option value='this_year' <?php echo ( ( isset( $_REQUEST['link_date'] ) && $_REQUEST['link_date'] == 'this_year' ) ? 'selected' : '' );?>><?php _e( 'This Year',' link-roundups' ); ?></option>
						<option value='show_all' <?php echo ( ( isset( $_REQUEST['link_date'] ) && $_REQUEST['link_date'] == 'show_all' ) ? 'selected' : '' );?>><?php _e( 'Show All',' link-roundups' ); ?></option>
					</select>
					<?php if( isset( $_REQUEST['orderby'] ) ) : ?>
						<input type='hidden' name='orderby' value='<?php echo $_REQUEST['orderby']; ?>'/>
					<?php endif;?>
					<?php if( isset($_REQUEST['order'] ) ) : ?>
						<input type='hidden' name='order' value='<?php echo $_REQUEST['order']; ?>'/>
					<?php endif;?>
					<input class='button' type='submit' value='Filter'/><span class="spinner"></span>
				</form>
			</div>
		<?php
		}
		if ( $which == 'bottom' ) {
			// Nothing to see here.
		}
	}

	/**
	 * Set the column IDs and titles for the table
	 *
	 * @since 0.3.2
	 */
	function get_columns() {
		return $columns = array(
			// name => text,
			'cb' => 'cb', // single_row_columns will turn this into a checkbox.
			'title' => 'Title',
			'post_author' => 'Author',
			'tags' => 'Tags',
			'date' => 'Date'
		);
	}

	/**
	 * Set which columns are sortable
	 *
	 * @since 0.3.2
	 */
	function get_sortable_columns() {
		return $columns = array(
			'title' => 'post_title',
			'post_author' => 'post_author',
			'tags' => 'tags_input',
			'date' => 'post_date'
		);
	}

	/**
	 * Build the query of posts that should be displayed, and run the query, and fill $this->items with those posts.
	 *
	 * @since 0.3.2
	 */
	function prepare_items() {

		/*
		 * Pagination
		 */

		// Number of posts per page, from $_REQUEST
		$posts_per_page = ( isset( $_REQUEST['posts_per_page'] ) ? $_REQUEST['posts_per_page'] : 15 );
		// Which page of results to get, from $_REQUEST
		$page = ( isset( $_REQUEST['lroundups_page'] ) ? $_REQUEST['lroundups_page'] : 1);

		/*
		 * Date
		 */

		// Define the default date query
		$default_date = array(
			'year' => date( 'Y' ),
			'monthnum' => date( 'm' ),
			'day' => date( 'd' )
		);
		// Turn the filter date button's response into a meaningful WP_Query date argument
		if ( isset($_REQUEST['link_date'] ) ) {
			switch ($_REQUEST['link_date']) {
				case 'today':
					$default_date = array( 'year' => date( 'Y' ), 'monthnum' => date( 'm' ), 'day' => date( 'd' ) );
				case 'this_week':
					$default_date = array( 'year' => date( 'Y' ), 'w' => date( 'W' ));
				case 'this_month':
					$default_date = array( 'year' => date( 'Y' ), 'monthnum' => date( 'm' ) );
				case 'this_year':
					$default_date = array( 'year' => date( 'Y' ) );
				case 'show_all':
					$default_date = array();
			}
		}

		// Generic arguments
		$args = array(
			'post_type' 	=> 'rounduplink',
			'orderby' 		=> ( isset($_REQUEST['orderby'] ) ? $_REQUEST['orderby'] : 'date' ),
			'order' 		=> ( isset($_REQUEST['order'] ) ? $_REQUEST['order'] : 'desc' ),
			'posts_per_page' => -1
		);

		// Join the date query with the generic args.
		$args = array_merge( $args, $default_date );

		$screen = get_current_screen();
		$_wp_column_headers;

		$the_posts_count_query = new WP_Query( $args );
		$total_post_count = $the_posts_count_query->post_count;
		unset($the_posts_count_query); // to save memory

		// Set the pagination links automagically
		$this->set_pagination_args(array(
			'total_items' => $total_post_count,
			'total_pages' => ceil($total_post_count/$posts_per_page),
			'per_page' => $posts_per_page,
		));

		// Set the columns
		$columns = $this->get_columns();
		$_wp_column_headers[$screen->id] = $columns;

		// Fetch the items
		$links_query = new WP_Query($args);
		$this->items = $links_query->posts;
		/* This is where we begin to deviate from http://www.smashingmagazine.com/2011/11/native-admin-tables-wordpress/
		 * Smash Magazine uses wpdb->get_results, and defines its own display_rows method to parse that.
		 * WP_Query does the following instead::
		 *   wp_query->get_posts()
		 *      wpdb->get_results
		 *      then WP_Query converts those results to WP_Post objects and stores it in the $links_query->posts.
		 * Since we can override any of WP_List_Table's functions, we can use whatever flavor of item we want in $this->items. $this->items is parsed in $this->display_rows, which runs $this->single_row($item) for each item, which wraps $this->single_row_columns($item) in a <tr>. We'll replace the single_row() method.
		 */
	}

	/*
	 * Turn a WP_Post object into a row for the table
	 * Mostly copied from WP_Posts_List_Table's single_row.
	 *
	 * @param $post WP_Post object
	 * @since 0.3.2
	 */
	function single_row($post) {
		$post = get_post($post);
		$classes .= "lroundups-link";

		print "<tr class='$classes' data-post-id='$post->ID'>";
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();
		foreach ( $columns as $column_name => $column_display_name ) {
			$classes = "$column_name column-$column_name";
			if ( $primary === $column_name ) {
				$classes .= ' has-row-actions column-primary';
			}
			if ( in_array( $column_name, $hidden ) ) {
				$classes .= ' hidden';
			}
			// Instead of using esc_attr(), we strip tags to get closer to a user-friendly string.
			$data = 'data-colname="' . wp_strip_all_tags( $column_display_name ) . '"';
			$attributes = "class='$classes' $data";
			switch ($column_name) {
				case 'cb':
					echo '<th scope="row" class="check-column">';
					echo $this->column_cb( $post ); // Creates a checkbox for the $post
					echo '</th>';
					break;
				case 'title':
					echo "<td $attributes>";
					echo $post->post_title;
					echo $this->handle_row_actions( $post, $column_name, $primary );
					echo "</td>";
					break;
				case 'post_author':
					echo "<td $attributes>";
					echo the_author_meta('display_name', $post->post_author);
					echo "</td>";
					break;
				case 'tags':
					echo "<td $attributes>";
					echo get_the_term_list($post->ID, 'argo-link-tags', '', ', ', '');
					echo "</td>";
					break;
				case 'date':
					echo "<td $attributes>";
					echo get_the_date( '', $post->ID);
					echo "</td>";
					break;
			}
		}
		print "</tr>";
	}
	
	/**
	 * Output a checkbox for a given WP_Post object
	 *
	 * @param $post WP_Post
	 * @since 0.3.2
	 */
	function column_cb( $post ) { ?>
		<label class="screen-reader-text" for="cb-select-<?php echo $post->ID; ?>"><?php
				printf( __( 'Select %s' ), _draft_or_post_title() );
		?></label>
		<input id="cb-select-<?php echo $post->ID; ?>" type="checkbox" class="cb-select" name="post[]" value="<?php the_ID(); ?>" />
		<div class="locked-indicator"></div>
	<?php
	}
}

// Set up and generate the table.
$links_list_table = new Saved_Links_List_Table();
$links_list_table->prepare_items();
$links_list_table->display();
// Reset Query
wp_reset_query();

/*
 * JavaScripts for the table's functionality
 */
?>
<script type='text/javascript'>
jQuery(function(){

  /**
   * From a checkbox element, find the post ID and title, and return a WP shortcode.
   *
   * @since 0.3.2
   */
  var link_roundups_get_shortcode = function(checkbox) {
    var row = jQuery(checkbox).parent().parent(),
      post_id = row.data('post-id'),
      title = row.find('.column-title').text();
    return '[rounduplink id="' + post_id + '" title="' + title + '"]';
  };

  /**
   * When "Send to Editor" is clicked, send checked stories to the editor
   * Also, do not reload the page
   *
   * @since 0.3.2
   * @uses link_roundups_get_shortcode
   */
  jQuery('.append-saved-links').bind('click',function(){
    // find all the roundups links in the table, and send them to the editor if they're checked
    jQuery('.lroundups-link .cb-select').each(function(){
      if (jQuery(this).is(":checked"))
        send_to_editor(link_roundups_get_shortcode(this));
    });
    return false;
  });

  /**
   * If an <a> inside the "Recent Saved Links" div is clicked, submit its href to this file and display the response.
   *
   * @since 0.1
   */
  jQuery('div.display-saved-links a').bind("click",function(){
    var urlOptions = jQuery(this).attr('href');
    jQuery('#lroundups-display-area').load('<?php echo plugin_dir_url(LROUNDUPS_PLUGIN_FILE); ?>inc/saved-links/display-recent.php?'+urlOptions);
    return false;
  });

  /**
   * When "Filter Links" is clicked, fill the table display area with the HTML produced by this file, when supplied with the query args.
   */
  jQuery("#filter_links").bind("submit", function() {
    var self=jQuery(this);
    self.find(".spinner").css('visibility','visible');
    jQuery('#lroundups-display-area').load('<?php echo plugin_dir_url(LROUNDUPS_PLUGIN_FILE); ?>inc/saved-links/display-recent.php?'+jQuery(self).serialize(), function() {
      self.find(".spinner").css('visibility','hidden');
    });
    return false;
  });

  /**
   * Check all the checkboxes if the "Check all boxes" checkbox is checked, and if it's unchecked, uncheck all the checkboxes.
   */
  jQuery('#cb-select-all-1,#cb-select-all-2').change(function(){
    if (jQuery(this).is(':checked')) {
      jQuery('.lroundups-links input[type=checkbox]').each(function(){
        jQuery(this).prop("checked", true);
      });
    } else {
      jQuery('.lroundups-links input[type=checkbox]').each(function(){
        jQuery(this).prop("checked", false);
      });
    }
  });
});
</script>
