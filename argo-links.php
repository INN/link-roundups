<?php
/**
  * @package Argo_Links
  * @version 0.01
  */
/*
Plugin Name: Argo Links
Plugin URI: https://github.com/argoproject/argo-links
Description: The Argo Links Plugin
Author: Project Argo, Mission Data
Version: 0.01
Author URI:
License: GPLv2
*/

/* The Argo Links Plugin class - so we don't have function naming conflicts */
class ArgoLinks {

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
    
    /*Register our custom taxonomy of "argo-link-categories" so we can have our own tags/categories for our Argo Links post type*/
    /* moved into a function per wordpress 3.0 issues with calling it directly*/
    add_action('init', array(__CLASS__, 'register_argo_links_taxonomy'));
    
    /*Add the Argo This! sub menu*/
    add_action("admin_menu", array(__CLASS__, "add_argo_this_sub_menu"));

    /*Add our custom post fields for our custom post type*/
    add_action("admin_init", array(__CLASS__, "add_custom_post_fields"));

    /*Save our custom post fields! Very important!*/
    add_action('save_post', array(__CLASS__, 'save_custom_fields'));

    /*Add our new custom post fields to the display columns on the main Argo Links admin page*/
    add_filter("manage_edit-argolinks_columns", array(__CLASS__, "display_custom_columns"));

    /*Populate those new columns with the custom data*/
    add_action("manage_posts_custom_column", array(__CLASS__, "data_for_custom_columns"));
  }

  /*Register the Argo Links post type */
  function register_post_type() {
        register_post_type('argolinks', array(
                                              'labels' => array(
                                                                'name' => 'Argo Links',
                                                                'singular_name' => 'Argo Link',
                                                                'add_new' => 'Add New',
                                                                'add_new_item' => 'Add New Argo Link',
                                                                'edit' => 'Edit',
                                                                'edit_item' => 'Edit Argo Link',
                                                                'view' => 'View',
                                                                'view_item' => 'View Argo Link',
                                                                'search_items' => 'Search Argo Links',
                                                                'not_found' => 'No Argo Links found',
                                                                'not_found_in_trash' => 'No Argo Links found in Trash',
                                                                ),
                                              'description' => 'Argo Links',
                                              'supports' => array( 'title' ),
                                              'public' => true,
                                              'publicly_queryable' => false,
                                              'menu_position' => 6,
                                              'taxonomies' => array(),
                                              //'rewrite' => array( 'slug' => 'al' ),
                                              )
                          );
  }

  /*Tell Wordpress where to put our custom fields for our custom post type*/
  function add_custom_post_fields() {
    add_meta_box("argo_links_meta", "Link Information", array(__CLASS__,"display_custom_fields"), "argolinks", "normal", "low");

  }
  /*Register our custom taxonomy*/
  function register_argo_links_taxonomy() {
    register_taxonomy("argo-link-tags", array("argolinks"), array("hierarchical" => false, "label" => "Link Tags", "singular_label" => "Link Tag", "rewrite" => true));

  }
  /*Show our custom post fields in the add/edit Argo Links admin pages*/
  function display_custom_fields() {
    global $post;
    $custom = get_post_custom($post->ID);
    if (isset($custom["argo_link_url"][0])) {
      $argo_link_url = $custom["argo_link_url"][0];
    } else {
      $argo_link_url = "";
    }
    if (isset($custom["argo_link_description"][0])) {
      $argo_link_description = $custom["argo_link_description"][0];
    } else {
      $argo_link_description = "";
    }
?>
    <p><label>URL:</label><br />
    <input type='text' name='argo_link_url' value='<?php echo $argo_link_url; ?>' style='width:98%;'/></p>
    <p><label>Description:</label><br />
    <textarea cols="100" rows="5" name="argo_link_description" style='width:98%;'><?php echo $argo_link_description; ?></textarea></p>
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

  /*Create the new columns to display our custom post fields*/
  function display_custom_columns($columns){
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

  /*Fill in our custom data for the new columns*/
  function data_for_custom_columns($column){
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
        $base_url = "edit.php?post_type=argolinks";
        $terms = get_the_terms($post->ID, 'argo-link-tags');
        if (count($terms) > 1) {
          foreach ($terms as $term) {
            echo "<a href='".$base_url."&argo-link-tags=".$term->slug."'>".$term->name."</a>,";
          }
        } else {
          echo "&nbsp;";
        }
        break;
    }
  }

  /*Add the Argo Link This! sub menu*/
  function add_argo_this_sub_menu() {
    add_submenu_page( "edit.php?post_type=argolinks", "Argo Link This!", "Argo Link This!", "edit_posts", "argo-this", array(__CLASS__, 'build_argo_this_page' ) );
  }

  /*Custom page for people to pull the Argo Link This! code from (similar to Press This!)*/
  function build_argo_this_page() {
?>

<div id="icon-tools" class="icon32"><br></div><h2>Tools</h2>

<div class="tool-box">
	<h3 class="title">Press This</h3>
	<p>Argo Link This! is a bookmarklet: a little app that runs in your browser and lets you grab bits of the web.</p>

	<p>Use Argo Link This! to clip links to any web page. Then edit and add more straight from Argo Link This! before you save or publish it in a post on your site.</p>
	<p class="description">Drag-and-drop the following link to your bookmarks bar or right click it and add it to your favorites for a posting shortcut.</p>
	<p class="pressthis"><a onclick="return false;" oncontextmenu="if(window.navigator.userAgent.indexOf('WebKit')!=-1||window.navigator.userAgent.indexOf('MSIE')!=-1)jQuery('.pressthis-code').show().find('textarea').focus().select();return false;" href="javascript:var%20d=document,w=window,e=w.getSelection,k=d.getSelection,x=d.selection,s=(e?e():(k)?k():(x?x.createRange().text:0)),f='<?php echo plugins_url( 'argo-this.php', __FILE__ );?>',l=d.location,e=encodeURIComponent,u=f+'?post_type=argolinks&u='+e(l.href)+'&t='+e(d.title)+'&s='+e(s)+'&v=4';a=function(){if(!w.open(u,'t','toolbar=0,resizable=1,scrollbars=1,status=1,width=720,height=570'))l.href=u;};if%20(/Firefox/.test(navigator.userAgent))%20setTimeout(a,%200);%20else%20a();void(0)"><span>Argo Link This!</span></a></p>
	<div class="pressthis-code" style="display:none;">
	<p class="description">If your bookmarks toolbar is hidden: copy the code below, open your Bookmarks manager, create new bookmark, type Press This into the name field and paste the code into the URL field.</p>
	<p><textarea rows="5" cols="120" readonly="readonly">javascript:var%20d=document,w=window,e=w.getSelection,k=d.getSelection,x=d.selection,s=(e?e():(k)?k():(x?x.createRange().text:0)),f='<?php echo plugins_url( 'argo-this.php', __FILE__ );?>',l=d.location,e=encodeURIComponent,u=f+'?post_type=argolinks&u='+e(l.href)+'&t='+e(d.title)+'&s='+e(s)+'&v=4';a=function(){if(!w.open(u,'t','toolbar=0,resizable=1,scrollbars=1,status=1,width=720,height=570'))l.href=u;};if%20(/Firefox/.test(navigator.userAgent))%20setTimeout(a,%200);%20else%20a();void(0)</textarea></p>
	</div>
</div>


    
    
<?php
  }
}
/* Initialize the plugin using it's init() function */
ArgoLinks::init();
?>
