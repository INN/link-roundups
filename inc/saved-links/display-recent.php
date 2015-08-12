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

global $post;

// The Query

// Now we can finally run the query

// From here down, it's manually building the table.
// We can fix this.
//
// Things to keep:
// 	- "Send to editor" button
// 	- "Data Range" filter


/**
 * Class to generate the table of saved links in the link roundups editor
 *
 * @link http://www.smashingmagazine.com/2011/11/native-admin-tables-wordpress/
 * @see clone_WP_List_Table
 */
class Saved_Links_List_Table extends clone_WP_List_Table {
	function __construct() {
		parent::__construct( array(
			'singular' => 'lroundups-link',
			'plural' => 'lroundups-links',
		));
	}

	function extra_tablenav( $which ) {
		// this will display at top and bottom
		?>
		<button class='button append-saved-links'><?php _e( 'Send to Editor', 'link-roundups' ); ?></button>
		<?php
		if ( $which == 'top' ) {
			// Date range:
			?>
			<div >
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
				<input class='button' type='submit' value='Filter'/>
			</form>
		</div>
		<?php
		}
		if ( $which == 'bottom' ) {
			// Nothing to see here.
		}
	}

	function get_columns() {
		return $columns = array(
			'col_link_checkbox' => 'cb', // single_row_columns will turn this into a checkbox.
			'col_link_title' => 'Title',
			'col_link_author' => 'Author',
			'col_link_tags' => 'Tags',
			'col_link_date' => 'Date'
		);
	}

	function get_sortable_columns() {
		return $columns = array(
			'col_link_title' => 'post_title',
			'col_link_author' => 'post_author',
			'col_link_tags' => 'tags_input',
			'col_link_date' => 'post_date'
		);
	}

	function prepare_items() {

		/*
		 * Build our query for what links to show!
		 */

		// Number of posts per page, from $_REQUEST
		$posts_per_page = ( isset( $_REQUEST['posts_per_page'] ) ? $_REQUEST['posts_per_page'] : 15 );
		// Which page of results to get, from $_REQUEST
		$page = ( isset( $_REQUEST['lroundups_page'] ) ? $_REQUEST['lroundups_page'] : 1);

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
		var_log($links_query->posts);
		// So where smash magazine uses wpdb->get_results, what WP_Query does is:
		//	wp_query->get_posts()
		//		wpdb->get_results
		//		but then it converts those results to WP_Post objects
		// However, we can do whatever we want with these results, because $this->items is then parsed in $this->display_rows, which runs $this->single_row($item) for each item, which wraps $this->single_row_columns($item) in a <tr>, which does most of the dirty work I think.
	}

	/*
	 * Turn a WP_Post object into a column
	 *
	 * @param $item WP_Post object
	 */
	function single_row_columns() {
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();
		foreach ( $columns as $column_name => $column_display_name ) {
			$classes = "$column_name column-$column_name";
			if ( $primary === $column_name ) {
				$classes .= ' has-row-actions column-primary';
			}
			if ( in_array( $column_name, $hidden ) ) {
				$classes .= ' hidden';
			}
			// Comments column uses HTML in the display name with screen reader text.
			// Instead of using esc_attr(), we strip tags to get closer to a user-friendly string.
			$data = 'data-colname="' . wp_strip_all_tags( $column_display_name ) . '"';
			$attributes = "class='$classes' $data";
			if ( 'cb' == $column_name ) {
				echo '<th scope="row" class="check-column">';
				echo $this->column_cb( $item );
				echo '</th>';
			} elseif ( method_exists( $this, '_column_' . $column_name ) ) {
				echo call_user_func(
					array( $this, '_column_' . $column_name ),
					$item,
					$classes,
					$data,
					$primary
				);
			} elseif ( method_exists( $this, 'column_' . $column_name ) ) {
				echo "<td $attributes>";
				echo call_user_func( array( $this, 'column_' . $column_name ), $item );
				echo $this->handle_row_actions( $item, $column_name, $primary );
				echo "</td>";
			} else {
				echo "<td $attributes>";
				echo $this->column_default( $item, $column_name );
				echo $this->handle_row_actions( $item, $column_name, $primary );
				echo "</td>";
			}
		}
	}
}

$links_list_table = new Saved_Links_List_Table();
$links_list_table->prepare_items();
$links_list_table->display();

// Reset Query
wp_reset_query();

/**
 * Get a shortcode string in a jQuery context.
 * Returns a PHP concatenated string of jQuery concatenated selectors. Sorry.
 *
 * @since 0.3
 */
function link_roundups_get_shortcode() {
$javascript_title = <<<JAVASCRIPT_TITLE
'+jQuery('#title-'+jQuery(this).val()).text()+'
JAVASCRIPT_TITLE;
  $shortcode = "[rounduplink ";
  $shortcode .= "id=\"'+jQuery(this).val()+'\" ";
  $shortcode .= "title=\"".$javascript_title."\"]";
  return $shortcode;
}

?>
<script type='text/javascript'>
jQuery(function(){
  // When "Send to Editor" is clicked
  jQuery('.append-saved-links').bind('click',function(){
    // find all the roundups links in the table, and send them to the editor if they're checked
    jQuery('.lroundups-link').each(function(){
      if (jQuery(this).is(":checked"))
        send_to_editor('<?php echo link_roundups_get_shortcode(); ?>');
    });
    return false;
  });

  // If an a inside the "Recent Saved Links" div is clicked, submit its href to this file and display the response.
  jQuery('div.display-saved-links a').bind("click",function(){
    var urlOptions = jQuery(this).attr('href');
    jQuery('#lroundups-display-area').load('<?php echo plugin_dir_url(LROUNDUPS_PLUGIN_FILE); ?>inc/saved-links/display-recent.php?'+urlOptions);
    return false;
  });

  // When "Filter Links" is clicked, fill the table display area with the HTML produced by this file, when supplied with the query args.
  jQuery("#filter_links").bind("submit", function() {
    jQuery('#lroundups-display-area').load('<?php echo plugin_dir_url(LROUNDUPS_PLUGIN_FILE); ?>inc/saved-links/display-recent.php?'+jQuery(this).serialize());
    return false;
  });

  // Check all the checkboxes if the "Check all boxes" checkbox is checked, and if it's unchecked, uncheck all the checkboxes.
  jQuery('#check-all-boxes').change(function(){
    if (jQuery(this).is(':checked')) {
      jQuery('.lroundups-link').each(function(){
        jQuery(this).prop("checked", true);
      });
    } else {
      jQuery('.lroundups-link').each(function(){
        jQuery(this).prop("checked", false);
      });
    }
  });
});
</script>
