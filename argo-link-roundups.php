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
    // add_action('init', array(__CLASS__, 'wp_head' ));

    /*Add our custom post fields for our custom post type*/
    add_action("admin_init", array(__CLASS__, "add_custom_post_fields"));

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
  
  function wp_head() {
        wp_enqueue_script('argo-link', plugin_dir_url(__FILE__).'js/argo-links.js', array('jquery') ); 
  }
  /*Register the Argo Links post type */
  function register_post_type() {
    register_post_type('argolinkroundups', array(
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
        'has_archive' => true
        //'rewrite' => array( 'slug' => 'al' ),
        )
      );
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
  function save_custom_fields() {
    global $post;
    $argo_post_id = "";
    if (isset($_POST["argo_link_url"])){
      update_post_meta((isset($_POST['post_id']) ? $_POST['post_id'] : $post->ID), "argo_link_url", $_POST["argo_link_url"]);
    }
    if (isset($_POST["argo_link_description"])){
      update_post_meta((isset($_POST['post_id']) ? $_POST['post_id'] : $post->ID), "argo_link_description", $_POST["argo_link_description"]);
    }
  }

}
/* Initialize the plugin using it's init() function */
ArgoLinkRoundups::init();
?>
