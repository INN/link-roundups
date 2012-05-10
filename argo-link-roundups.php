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
  function init() {
    /*Register the custom post type of argolinks */
    add_action('init', array(__CLASS__, 'register_post_type' ));

    /*Add our custom post fields for our custom post type*/
    add_action("admin_init", array(__CLASS__, "add_custom_post_fields"));

    /*Add the Argo Link Roundups Options sub menu*/
    add_action("admin_menu", array(__CLASS__, "add_argo_link_roundup_options_page"));

    /*Save our custom post fields! Very important!*/
    add_action('save_post', array(__CLASS__, 'save_custom_fields'));
    
    /*Make sure our custom post type gets pulled into the river*/
    add_filter( 'pre_get_posts', array(__CLASS__,'my_get_posts') );
  }

  /*Pull the argolinkroundups into the rivers for is_home, is_tag, is_category, is_archive*/
  /*Merge the post_type query var if there is already a custom post type being pulled in, otherwise do post & argolinkroundups*/
  function my_get_posts( $query ) {
    if (is_home() || is_tag() || is_category()) {
      if (isset($query->query_vars['post_type']) && is_array($query->query_vars['post_type'])) {
        $query->set( 'post_type', array_merge(array('argolinkroundups' ), $query->query_vars['post_type']) );
      } elseif (isset($query->query_vars['post_type']) && !is_array($query->query_vars['post_type'])) {
        $query->set( 'post_type', array('argolinkroundups', $query->query_vars['post_type']) );
      } else {
        $query->set( 'post_type', array('post','argolinkroundups') );
      }
    }
  }
  
  /*Register the Argo Links post type */
  function register_post_type() {
    $argolinkroundups_options = array(
        'labels' => array(
            'name' => 'Link Roundups',
            'singular_name' => 'Argo Link Roundup',
            'add_new' => 'Add New Roundup',
            'add_new_item' => 'Add New Argo Link Roundup',
            'edit' => 'Edit',
            'edit_item' => 'Edit Argo Link Roundup',
            'view' => 'View',
            'view_item' => 'View Argo Link Roundup',
            'search_items' => 'Search Argo Link Roundups',
            'not_found' => 'No Argo Links Roundups found',
            'not_found_in_trash' => 'No Argo Link Roundups found in Trash',
        ),
        'description' => 'Argo Link Roundups',
        'supports' => array( 'title', 'editor','author','thumbnail','excerpt','trackbacks','custom-fields','comments','revisions','page-attributes','post-formats'),
        'public' => true,
        'menu_position' => 7,
        'taxonomies' => apply_filters('argolinkroundups_taxonomies', array('category','post_tag')),
        'has_archive' => true,
        );
        if (get_option('argo_link_roundups_custom_url') != "") {
          $argolinkroundups_options['rewrite'] = array('slug' => get_option('argo_link_roundups_custom_url'));
        }
    register_post_type('argolinkroundups', $argolinkroundups_options);
  }
  
  /*Tell Wordpress where to put our custom fields for our custom post type*/
  function add_custom_post_fields() {
    add_meta_box("argo_link_roundups_roundup", "Recent Roundup Links", array(__CLASS__,"display_custom_fields"), "argolinkroundups", "normal", "high");

  }
  /*Show our custom post fields in the add/edit Argo Link Roundups admin pages*/
  function display_custom_fields() {
?>
    <div id='argo-links-display-area'>
    </div>
    <script type='text/javascript'>
    jQuery(function(){
      jQuery('#argo-links-display-area').load('<?php echo plugin_dir_url(__FILE__); ?>display-argo-links.php');
    });
    </script>
<?php
  }

  /*Save the custom post field data.  Very important!*/
  function save_custom_fields($post_id) {
    if (isset($_POST["argo_link_url"])){
      update_post_meta((isset($_POST['post_id']) ? $_POST['post_ID'] : $post_id), "argo_link_url", $_POST["argo_link_url"]);
    }
    if (isset($_POST["argo_link_description"])){
      update_post_meta((isset($_POST['post_id']) ? $_POST['post_ID'] : $post_id), "argo_link_description", $_POST["argo_link_description"]);
    }
  }
  /*Add the Argo Link Roundup options sub menu*/
  function add_argo_link_roundup_options_page() {
    add_submenu_page( "edit.php?post_type=argolinkroundups", "Options", "Options", "edit_posts", "argo-link-roundups-options", array(__CLASS__, 'build_argo_link_roundups_options_page' ) );
    //call register settings function
    add_action( 'admin_init', array(__CLASS__,'register_mysettings') );
  }


  function register_mysettings() {
    //register our settings
    register_setting( 'argolinkroundups-settings-group', 'argo_link_roundups_custom_url' );
    register_setting( 'argolinkroundups-settings-group', 'argo_link_roundups_custom_html' );
  }

  function build_argo_link_roundups_options_page() { ?>
  <?php
$default_html = <<<EOT
<p class='link-roundup'><a href='#!URL!#'>#!TITLE!#</a> &ndash; <span class='description'>#!DESCRIPTION!#</span> <em>#!SOURCE!#</em></p>
EOT;
  ?>
<div class="wrap">
<h2>Argo Link Roundups</h2>

<form method="post" action="options.php">
    <?php settings_fields( 'argolinkroundups-settings-group' ); ?>
    <?php do_settings_fields( 'argolinkroundups-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
          <th scope="row">Custom Url Slug</th>
          <td><input type="text" name="argo_link_roundups_custom_url" value="<?php echo get_option('argo_link_roundups_custom_url'); ?>" /></td>
        </tr>
        <tr valign="top">
          <th scope="row">Custom HTML</th>
          <td><textarea name="argo_link_roundups_custom_html" cols='100' rows='5' ><?php echo (get_option('argo_link_roundups_custom_html') != "" ? get_option('argo_link_roundups_custom_html')  : $default_html); ?></textarea></td>
        </tr>
        <tr>
          <td></td>
          <td>
          <em>(You will need to use single quotes in your html above, all double quotes will be automatically converted to single quotes before use)</em><br />
          You can use the above field to customize the html that is output for each link.  The following tags will be replaced with the url, title, description, and source automatically when the link is pushed into the editor.<br />
          #!URL!#, #!TITLE!#, #!DESCRIPTION!#, #!SOURCE!#<br />
          The current default html for reference is:<br />
          <?php echo htmlspecialchars($default_html); ?><br />
          <em>(Please note that you will have to update your style.css file for your theme to style your new html)</em><br />
          </td>
        </tr>
    </table>
    
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
</div>
<?php } 
}
/* Initialize the plugin using it's init() function */
ArgoLinkRoundups::init();
?>
