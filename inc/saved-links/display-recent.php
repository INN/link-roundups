<?php
/**
  * @package Link_Roundups
  * @since 0.1
  */

/*
* Recent Saved Links Custom Meta Panel
* for Link Roundups Post Type
*/

/** WordPress Admin Bootstrap */
require_once( '../../../../../wp-admin/admin.php' );

global $post;

// The Query

/*Build our query for what links to show!*/
$posts_per_page = ( isset( $_REQUEST['posts_per_page'] ) ? $_REQUEST['posts_per_page'] : 15 );
$page = ( isset( $_REQUEST['lroundups_page'] ) ? $_REQUEST['lroundups_page'] : 1);
$default_date = array( 'year' => date( 'Y' ), 'monthnum' => date( 'm' ), 'day' => date( 'd' ) );
/*Sort out passed filter dates*/
if ( isset($_REQUEST['link_date'] ) ) {
  if ( $_REQUEST['link_date'] == 'today' ) {
    $default_date = array( 'year' => date( 'Y' ), 'monthnum' => date( 'm' ), 'day' => date( 'd' ) );
  } elseif ( $_REQUEST['link_date'] == 'this_week ') {
    $default_date = array( 'year' => date( 'Y' ), 'w' => date( 'W' ));
  } elseif ( $_REQUEST['link_date'] == 'this_month' ) {
    $default_date = array( 'year' => date( 'Y' ), 'monthnum' => date( 'm' ) );
  } elseif ( $_REQUEST['link_date'] == 'this_year') {
    $default_date = array( 'year' => date( 'Y' ) );
  } elseif( $_REQUEST['link_date'] == 'show_all' ) {
    $default_date = array();
  }
}
$args = array(
	'post_type' 	=> 'rounduplink',
	'orderby' 		=> ( isset($_REQUEST['orderby'] ) ? $_REQUEST['orderby'] : 'date' ),
	'order' 		=> ( isset($_REQUEST['order'] ) ? $_REQUEST['order'] : 'desc' ),
	'posts_per_page' => -1
);
$args = array_merge( $args, $default_date );
$the_posts_count_query = new WP_Query( $args );
$total_post_count = $the_posts_count_query->post_count;
$the_posts_count_query = '';
$the_query = new WP_Query( array_merge( $args, array( 'posts_per_page' => $posts_per_page, 'paged' => $page ) ) );
$from_result = 1;
$to_result = $posts_per_page;
if ($page != 1) {
  $from_result = $posts_per_page * ( $page - 1 );
  $to_result = $from_result + $posts_per_page - 1;
}
if ($to_result > $total_post_count)
  $to_result = $total_post_count;

/*Build pagination links*/
$pagination_first = 1;
$pagination_previous = $page - 1;
$pagination_next = $page + 1;
$pagination_last = ceil( $total_post_count / $posts_per_page );

$query_url = '';
$query_url .= ( isset( $_REQUEST['orderby'] ) ? '&orderby='.$_REQUEST['orderby']: '' );
$query_url .= ( isset( $_REQUEST['order'] ) ? '&order='.$_REQUEST['order']: '' );
$query_url .= ( isset( $_REQUEST['link_date'] ) ? '&link_date='.$_REQUEST['link_date']: '' );
?>
<div class='display-saved-links'>
  <div class='pagination'>
    <div style='float:left'>
      <button class='button append-saved-links'><?php _e( 'Send to Editor', 'link-roundups' ); ?></button>
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
    <div class="page-navi" style='float:right'>
      <?php echo $from_result . ' - ' . $to_result . ' ' . __( 'of', 'link-roundups' ) . ' ' . $total_post_count; ?>
      <?php if(!($page <= 6)):?>
        <a class="button" href='lroundups_page=<?php echo $pagination_first;?><?php echo $query_url; ?>' title='First'>&laquo;</a>
      <?php endif; ?>
      <?php if(!($page == 1)):?>
        <a class="button" href='lroundups_page=<?php echo $pagination_previous;?><?php echo $query_url; ?>' title='Previous'>&laquo;</a>
      <?php endif; ?>
      <?php
        $start = 1;
        $count = 0;
        if ( $page == 1 ) {
          if ( $pagination_last >= 11 ) {
            $count = $start + 10;
          } else {
            $count = $pagination_last;
          }
        } else if ( $page == $pagination_last ) {
          if ( $pagination_last <= 11 ) {
            $start = 1;
            $count = $pagination_last;
          } else {
            $start = $pagination_last - 11;
            $count = $start + 11;
          }
        } else {
          if( $pagination_last <= 11 ) {
            $start = 1;
            $count = $pagination_last;
          } else if ( ( $page + 5 ) > $pagination_last ) {
            $start = $pagination_last - 10;
            $count = $start + 10;
          } else if ( ( $page - 5 ) <= 0 ) {
            $start = 1;
            $count = 11;
          } else {
            $start = $page - 5;
            $count = $start + 10;
          }
        }
        while ( $start <= $count ) {
          echo "<a href='lroundups_page=$start$query_url' class='button " . ( $start == $page ? 'current' : '' ) . "'>$start</a> &nbsp;";
          $start++;
        }
      ?>
      <?php if ( !( $page == $pagination_last ) ) : ?>
        <a class="button" href='lroundups_page=<?php echo $pagination_next;?><?php echo $query_url; ?>' title='Next'>&raquo;</a>
      <?php endif; ?>
      <?php if ( !( $page >= ( $pagination_last - 5 ) ) ) : ?>
        <a class="button" href='lroundups_page=<?php echo $pagination_last;?><?php echo $query_url; ?>' title='Last'>&raquo;</a>
      <?php endif;?>
    </div>
  </div>

  <!-- START TABLE -->
  <table class="wp-list-table widefat fixed posts" cellspacing="0">
    <tr>
      <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" id='check-all-boxes'></th>
      <th scope="col" id="title" class="manage-column column-title <?php echo (isset($_REQUEST['orderby']) && $_REQUEST['orderby'] == 'title' ? 'sorted' : 'sortable');?> <?php echo (isset($_REQUEST['orderby']) ? ($_REQUEST['orderby'] == 'title' && $_REQUEST['order'] == 'desc' ? 'desc' : 'asc') : 'desc');?>" style=""><a href="post_type=rounduplink&orderby=title&order=<?php echo (isset($_REQUEST['orderby']) ? ($_REQUEST['orderby'] == 'title' && $_REQUEST['order'] == 'desc' ? 'asc' : 'desc') : 'desc');?><?php echo (isset($_REQUEST['link_date']) ? '&link_date='.$_REQUEST['link_date']: ''); ?>"><span>Title</span><span class="sorting-indicator"></span></a></th>
      <th scope="col" id="author" class="manage-column column-author <?php echo (isset($_REQUEST['orderby']) && $_REQUEST['orderby'] == 'author' ? 'sorted' : 'sortable');?> <?php echo (isset($_REQUEST['orderby']) ? ($_REQUEST['orderby'] == 'author' && $_REQUEST['order'] == 'desc' ? 'desc' : 'asc') : 'desc');?>" style=""><a href="post_type=rounduplink&orderby=author&order=<?php echo (isset($_REQUEST['orderby']) ? ($_REQUEST['orderby'] == 'author' && $_REQUEST['order'] == 'desc' ? 'asc' : 'desc') : 'desc');?><?php echo (isset($_REQUEST['link_date']) ? '&link_date='.$_REQUEST['link_date']: ''); ?>"><span>Author</span><span class="sorting-indicator"></span></a></th>
      <th scope="col" id="link-tags" class="manage-column column-link-tags" style="">Tags</th>
      <th scope="col" id="date" class="manage-column column-date <?php echo (isset($_REQUEST['orderby']) && $_REQUEST['orderby'] == 'date' ? 'sorted' : 'sortable');?> <?php echo (isset($_REQUEST['orderby']) ? ($_REQUEST['orderby'] == 'date' && $_REQUEST['order'] == 'desc' ? 'desc' : 'asc') : 'desc');?>" style=""><a href="post_type=rounduplink&orderby=date&order=<?php echo (isset($_REQUEST['orderby']) ? ($_REQUEST['orderby'] == 'date' && $_REQUEST['order'] == 'desc' ? 'asc' : 'desc') : 'desc');?><?php echo (isset($_REQUEST['link_date']) ? '&link_date='.$_REQUEST['link_date']: ''); ?>"><span>Date</span><span class="sorting-indicator"></span></a></th>
    </tr>

    <?php $i=1; ?>
    <?php if ($the_query->have_posts()) : while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
      <tr id='<?php echo get_the_ID(); ?>' class='<?php echo ($i%2 ? 'alternate' : '')?>'>
        <th scope="row" id="cb" class="manage-column column-cb check-column" style="">
          <input type="checkbox" class='lroundups-link' value='<?php echo get_the_ID(); ?>'/>
        </th>
        <td scope="row" id="title" class="manage-column column-title sortable desc" style="">
          <span id="title-<?php echo get_the_ID();?>"><?php echo the_title(); ?></span><br />
          <?php
          $custom = get_post_custom($post->ID);
          ?>
          <span id='url-<?php echo get_the_ID();?>'style='font-size:10px;'><em><?php echo (isset($custom["argo_link_url"][0]) ? $custom["argo_link_url"][0] : ''); ?></em></span>
          <span id='description-<?php echo get_the_ID();?>'style='display:none;'><em><?php echo (isset($custom["argo_link_description"][0]) ? $custom["argo_link_description"][0] : ''); ?></em></span>
          <span id='source-<?php echo get_the_ID();?>'style='display:none;'><em><?php echo (isset($custom["argo_link_source"][0]) ? $custom["argo_link_source"][0] : ''); ?></em></span>

        </td>
        <td scope="row" id="author" class="manage-column column-author sortable desc" style=""><span><?php the_author();?></span></td>
        <td scope="row" id="link-tags" class="manage-column column-link-tags" style="">
        <?php
        $terms = get_the_terms(get_the_ID(), 'argo-link-tags');
            if (count($terms) > 1) {
              foreach ($terms as $term) {
                echo $term->name.", ";
              }
            } else {
              echo "&nbsp;";
            }
            $terms = "";
        ?>
        </td>
        <td scope="row" id="date" class="manage-column column-date sortable asc" style=""><span><?php echo get_the_date(); ?></span></td>
      </tr>
      <?php $i++;?>
    <?php endwhile; else : // below is the blank template if no links are found ?>
      <tr id="blank" class='alternate'>
        <th scope="row" id="cb" class="manage-column column-cb check-column" style=""></th>
        <td scope="row" id="title" class="manage-column column-title sortable desc" style="">
          <span class="none-found"><?php _e('No links found.', 'link-roundups'); ?></span><small><?php _e('Try selecting a different Date Range above.', 'link-roundups'); ?></small><br />
        <td scope="row" id="author" class="manage-column column-author sortable desc" style=""><span>&nbsp;</span></td>
        <td scope="row" id="link-tags" class="manage-column column-link-tags" style="">
        	 
        </td>
        <td scope="row" id="date" class="manage-column column-date sortable asc" style=""><span>&nbsp;</span></td>
      </tr>
      <?php $hide = true; ?>
    <?php endif; ?>
  </table>

  <div class='pagination'>
    <div style='float:left'>
      <button class='button append-saved-links'>Send to Editor</button>
    </div>
    <div class="page-navi" style='float:right'>
      <br />
      <?php echo $from_result . ' - ' . $to_result . ' ' . __( 'of', 'link-roundups' ) . ' ' . $total_post_count; ?>
      <?php if(!($page <= 6)):?>
        <a class="button" href='lroundups_page=<?php echo $pagination_first;?><?php echo $query_url; ?>' title='First'>&laquo;</a>
      <?php endif; ?>
      <?php if(!($page == 1)):?>
        <a class="button" href='lroundups_page=<?php echo $pagination_previous;?><?php echo $query_url; ?>' title='Previous'>&laquo;</a>
      <?php endif; ?>
      <?php
        $start = 1;
        $count = 0;
        if ($page == 1) {
          if ($pagination_last >= 11) {
            $count = $start + 10;
          } else {
            $count = $pagination_last;
          }
        } else if ($page == $pagination_last) {
          if ($pagination_last <= 11) {
            $start = 1;
            $count = $pagination_last;
          } else {
            $start = $pagination_last - 11;
            $count = $start + 11;
          }
        } else {
          if($pagination_last <= 11) {
            $start = 1;
            $count = $pagination_last;
          } else if (($page + 5) > $pagination_last) {
            $start = $pagination_last - 10;
            $count = $start + 10;
          } else if (($page - 5) <= 0) {
            $start = 1;
            $count = 11;
          } else {
            $start = $page - 5;
            $count = $start + 10;
          }
        }
        while ($start <= $count) {
          echo "<a href='lroundups_page=$start$query_url' class='button ".($start == $page ? 'current' : '')."'>$start</a> &nbsp;";
          $start++;
        }
      ?>
      <?php if(!($page == $pagination_last)):?>
        <a class="button" href='lroundups_page=<?php echo $pagination_next;?><?php echo $query_url; ?>' title='Next'>&raquo;</a>
      <?php endif; ?>
      <?php if(!($page >= ($pagination_last - 5))):?>
        <a class="button" href='lroundups_page=<?php echo $pagination_last;?><?php echo $query_url; ?>' title='Last'>&raquo;</a>
      <?php endif;?>
    </div>
  </div>
</div>
<?php
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
  jQuery('.append-saved-links').bind('click',function(){
    // this is the class for the checkbox in the table, we check how it's doing
    jQuery('.lroundups-link').each(function(){
      if (jQuery(this).is(":checked"))
        send_to_editor('<?php echo link_roundups_get_shortcode(); ?>');
    });
    return false;
    });
  jQuery('div.display-saved-links a').bind("click",function(){
    var urlOptions = jQuery(this).attr('href');
    jQuery('#lroundups-display-area').load('<?php echo plugin_dir_url(LROUNDUPS_PLUGIN_FILE); ?>inc/saved-links/display-recent.php?'+urlOptions);
    return false;
  });
  jQuery("#filter_links").bind("submit", function() {
    jQuery('#lroundups-display-area').load('<?php echo plugin_dir_url(LROUNDUPS_PLUGIN_FILE); ?>inc/saved-links/display-recent.php?'+jQuery(this).serialize());
    return false;
  });
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
