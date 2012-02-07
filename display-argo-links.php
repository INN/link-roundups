<?php
/** WordPress Administration Bootstrap */
require_once('../../../wp-admin/admin.php');

global $post;
// The Query
query_posts(
            array(
                  'post_type' => 'argolinks',
                  'posts_per_page' => 10,
                  'orderby' => 'date',
                  'order' => 'DESC'

                  )
            );

?>
<table class="wp-list-table widefat fixed posts" cellspacing="0">
  <tr>
    <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" id='check-all-boxes'></th>
    <th scope="col" id="title" class="manage-column column-title sortable desc" style=""><a href="http://audioplayer.devlocal/wp-admin/edit.php?post_type=argolinks&amp;orderby=title&amp;order=asc"><span>Title</span><span class="sorting-indicator"></span></a></th>
    <th scope="col" id="author" class="manage-column column-author sortable desc" style=""><a href="http://audioplayer.devlocal/wp-admin/edit.php?post_type=argolinks&amp;orderby=author&amp;order=asc"><span>Author</span><span class="sorting-indicator"></span></a></th>
    <th scope="col" id="link-tags" class="manage-column column-link-tags" style="">Tags</th>
    <th scope="col" id="date" class="manage-column column-date sortable asc" style=""><a href="http://audioplayer.devlocal/wp-admin/edit.php?post_type=argolinks&amp;orderby=date&amp;order=desc"><span>Date</span><span class="sorting-indicator"></span></a></th>
  </tr>
  
  <?php $i=1; ?>
  <?php while ( have_posts() ) : the_post(); ?>
    <tr id='<?php echo get_the_ID(); ?>' class='<?php echo ($i%2 ? 'alternate' : '')?>'>
      <th scope="row" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" class='argo-link' value='<?php echo get_the_ID(); ?>'/></th>
      <td scope="row" id="title" class="manage-column column-title sortable desc" style="">
        <span id="title-<?php echo get_the_ID();?>"><?php echo the_title(); ?></span><br />
        <?php
        $custom = get_post_custom($post->ID);
        ?>
        <span id='url-<?php echo get_the_ID();?>'style='font-size:10px;'><em><?php echo $custom["argo_link_url"][0]; ?></em></span>
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
  <?php endwhile; ?>
</table>
<button id='append-argo-links'>Send links to editor window</button>
<?php
// Reset Query
wp_reset_query();

?>
<script type='text/javascript'>
  jQuery(function(){
    jQuery('#append-argo-links').bind('click',function(){
      jQuery('.argo-link').each(function(){
        if (jQuery(this).is(":checked")) {
          var html = "\n<p><a href='"+jQuery('#url-'+jQuery(this).val()).text()+"'>"+jQuery('#title-'+jQuery(this).val()).text()+"</a></p>";
          if (jQuery('#content').is(":visible")) {
            jQuery('#content').val(jQuery('#content').val()+html);
          } else {
            parent.tinyMCE.activeEditor.setContent(parent.tinyMCE.activeEditor.getContent() + html);
          }
          
        }
        });
      return false;
      
      });  
    
  });
  
</script>