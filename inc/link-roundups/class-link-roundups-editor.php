<?php

class LinkRoundupsEditor {

	// Initialize the plugin
	public static function init() {
		add_filter( 'mce_external_plugins', array( __CLASS__, 'add_tinymce_plugin' ), 4 );
		add_action( 'admin_init', array( __CLASS__, 'add_editor_styles' ) );
		add_action( 'admin_footer', array( __CLASS__, 'add_modal_template' ) );
		add_action( 'wp_ajax_roundup_block_posts', array( __CLASS__, 'roundup_block_posts' ) );
	}

	/**
	 * Add TinyMCE editor plugin to enable clickable/editable roundup blocks in posts
	 *
	 * @since 0.3
	 */
	public static function add_tinymce_plugin( $plugins ) {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$plugins['link_roundups_editor'] = LROUNDUPS_DIR_URI . '/js/lroundups-editor' . $suffix . '.js';
		return $plugins;
	}

	/**
	 * Add CSS to the post editor for the TinyMCE roundup block editor plugin
	 *
	 * @since 0.3
	 */
	public static function add_editor_styles() {
		add_editor_style( LROUNDUPS_DIR_URI . '/css/lroundups-editor.css' );
	}

	/**
	 * Prints the underscore templates and JSON used in the front-end javascript
	 *
	 * @since 0.2
	 */
	public static function add_modal_template() {
		$screen = get_current_screen();
		if ( $screen->base == 'post' && $screen->post_type == 'roundup' ) {
			LinkRoundupsEditor::modal_underscore_template();
			LinkRoundupsEditor::posts_underscore_template();

	?>
		<script type="text/javascript">
			var LR = <?php echo json_encode( LinkRoundupsEditor::json_obj() ); ?>;
		</script>
	<?php
		}
	}

	/**
	 * Print the underscore template for the LR.Modal view.
	 *
	 * @since 0.2
	 */
	public static function modal_underscore_template() { ?>
		<script type="text/template" id="lroundups-modal-tmpl">
			<div class="lroundups-modal-header">
				<div class="lroundups-modal-close"><span class="close">&#10005;</span></div>
			</div>
			<div class="lroundups-modal-content"><% if (content) { %><%= content %><% } %></div>
			<div class="lroundups-modal-actions">
				<span class="spinner"></span>
				<% _.each(actions, function(v, k) { %>
					<a href="#" class="<%= k %> button button-primary"><%= k %></a>
				<% }); %>
			</div>
		</script>
	<?php }

	public static function posts_underscore_template() { ?>
		<script type="text/template" id="lroundups-post-tmpl">
			<h3><%= title %></h3>
			<h4>Add posts:</h4>
			<div class="selected-roundup-posts">
				<p>No posts selected. Add some posts below.</p>
			</div>
			<h4>Search:</h4>
			<input type="text" class="typeahead" placeholder="Start typing..." />
			<div class="roundup-posts-container">
				<ul class="roundup-posts">
				<% posts.each(function(post, idx) { %>
					<li>
						<%= post.get('post_title') %>
						<p class="actions"><a href="#">Edit</a> | <a href="#">Add</a></p>
					</li>
				<% }); %>
				</ul>
			</div>
		</script>
	<?php }

	/**
	 * Builds a LR object with common attributes used throughout the plugin's javascript files.
	 *
	 * @since 0.2
	 */
	public static function json_obj( $add = array() ) {
		global $post;

		return array_merge( array(
			'post_id' => $post->ID,
			'ajax_nonce' => wp_create_nonce( 'lroundups_ajax_nonce' )
		), $add );
	}


	/*
	 * Load 
	 *
	 * @since 0.3.2
	 */
	public static function roundup_block_posts() {
		// Generic arguments
		$args = apply_filters('link_roundups_roundup_block_post_query', array(
			'post_type' => 'rounduplink',
			'orderby' => 'date',
			'order' => 'desc',
			'posts_per_page' => -1
		));
		$query = new WP_Query($args);
		$posts = $query->get_posts();
		foreach ( $posts as $post ) {
			$post->custom_fields = get_post_custom( $post->ID );
		}
		print json_encode($posts);
		wp_die();
	}
}
