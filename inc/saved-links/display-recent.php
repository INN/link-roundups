<?php
/**
 * Recent Saved Links Custom Meta Panel
 * for Link Roundups Post Type
 *
 * @package Link_Roundups
 * @since 0.1
 */

/**
 * WordPress Admin Bootstrap
 *
 * Check if Wordpress is bootstrapped already before bootstrapping.
 * This allows tests to run. If this check were not here, then tests will
 * error when loading the admin file. INN's current testing rig already
 * bootstraps WordPress, and this file (until 0.3.2) assumed that it was
 * being loaded from outside WordPress via an AJAX call. That is not a safe
 * assumption if we wish to run tests.
 *
 * @since 0.3.2
 */
require_once( dirname(dirname(dirname(dirname(dirname(__DIR__))))) .'/wp-admin/admin.php' );

/*
 * Load the Saved_Links_List_Table class
 *
 * @since 0.3.2
 */
require_once( __DIR__ . '/class-saved-links-list-table.php' );

// Set up and generate the table.
$links_list_table = new Saved_Links_List_Table();
$links_list_table->prepare_items();
$links_list_table->display();
// Reset Query
wp_reset_query();

/*
 * JavaScripts for the table's functionality
 *
 * @uses plugin_dir_url(LROUNDUPS_PLUGIN_FILE)
 * @since 0.1
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
