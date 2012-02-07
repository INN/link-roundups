<?php
/**
  * @package Argo_Links
  * @version 0.01
  */
/*
Plugin Name: Argo Links - Link Roundups
Plugin URI: https://github.com/argoproject/argo-links
Description: The Argo Links Plugin
Author: Project Argo, Mission Data
Version: 0.01
Author URI:
License: GPLv2
*/

/* The Argo Links Plugin class - so we don't have function naming conflicts */
class ArgoLinkRoundups {

  /* Install function, runs when the plugin is installed - not implemented */
  function install() {

  }

  /* Deactivate function, runs when the plugin is deactivated - not implemented */
  function deactivate() {

  }

  /* Initialize the plugin */
  function init() {
    /*Register the custom post type of argolinks */
    add_action('init', array(__CLASS__, 'register_post_type' ));
    add_action('init', array(__CLASS__, 'wp_head' ));

    /*Add our custom post fields for our custom post type*/
    add_action("admin_init", array(__CLASS__, "add_custom_post_fields"));

    /*Save our custom post fields! Very important!*/
    add_action('save_post', array(__CLASS__, 'save_custom_fields'));

  }

  function wp_head() {
        wp_enqueue_script('argo-link', '/wp-content/plugins/argo-links/js/argo-links.js', array('jquery') ); 
  }
  /*Register the Argo Links post type */
  function register_post_type() {
        register_post_type('argolinkroundups', array(
                                              'labels' => array(
                                                                'name' => 'Link Roundups',
                                                                'singular_name' => 'Argo Link Roundup',
                                                                'add_new' => 'Add New',
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
                                              'taxonomies' => array('category','post_tag'),
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
      jQuery('#argo-links-display-area').load('/wp-content/plugins/argo-links/display-argo-links.php');
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
