<?php

class LinkRoundupsEditor {

	// Initialize the plugin
	public static function init() {
		add_filter( 'mce_external_plugins', array( __CLASS__, 'add_tinymce_plugin' ), 4 );
		add_action( 'admin_init', array( __CLASS__, 'add_editor_styles' ) );
		add_action( 'admin_footer', array( __CLASS__, 'add_modal_template' ) );
		add_action( 'wp_ajax_roundup_block_posts', array( __CLASS__, 'roundup_block_posts' ) );
		add_shortcode( 'roundup_block', array( __CLASS__, 'roundup_block_shortcode' ) );
	}

	/**
	 * Render a roundup block shortcode
	 *
	 * @since 0.3.2
	 */
	public static function roundup_block_shortcode( $attrs ) {
		$content = '';

		/**
		 * Allow for filtering the heading of a roundup block
		 */
		$content .= apply_filters(
			'link_roundup_block_shortcode_heading',
			'<h3>' . $attrs['name'] . '</h3>',
			$attrs,
			$ids
		);

		// The link html for each item included in the block
		$ids = ( isset( $attrs['ids'] ) ) ? explode( ',', $attrs['ids'] ) : array();
		foreach ( $ids as $id ) {
			/**
			 * Allow for filtering/replacing the function used to format individual links
			 * in a roundup block
			 */
			$formatting_func = apply_filters(
				'link_roundup_block_shortcode_link_format_func',
				'SavedLinks::get_html',
				$attrs,
				$id
			);
			/**
			 * Allow for filtering the generated content for an individual link in a roundup
			 * block
			 */
			$content .= apply_filters(
				'link_roundup_block_shortcode_link_content',
				call_user_func( $formatting_func, $id ),
				$attrs,
				$id
			);
		}

		/**
		 * Allow for filtering/replacing the entire contents of a roundup block
		 */
		print apply_filters( 'link_roundup_block_content', $content, $attrs, $ids );
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
		$style_file = LROUNDUPS_DIR_URI . '/css/lroundups-editor.css';
		wp_register_style( 'lroundups-editor', LROUNDUPS_DIR_URI . '/css/lroundups-editor.css' );
		add_editor_style( $style_file );
		add_action( 'admin_enqueue_scripts', function() {
			wp_enqueue_style( 'lroundups-editor' );
		});
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

		<script type="text/template" id="lroundups-posts-tmpl">
			<h3><%= name %></h3>
			<div class="flex-container">
				<div class="added-section section">
					<h4>Added items:</h4>
					<div class="roundup-posts-container">
						<ul class="sortable connected added-posts">
							<li class="<% if (hasPosts) { %>loading<% } else { %>no-posts<% } %>">
								<% if (hasPosts) { %>Loading...<% } else { %>No items have been added.<% } %>
							</li>
						</ul>
					</div>
				</div>
				<div class="available-section section">
					<h4>Available items:</h4>
					<input type="text" disabled class="typeahead" placeholder="Search for posts..." />
					<div class="roundup-posts-container">
						<ul class="available-posts sortable connected">
							<li class="loading">Loading...</li>
						</ul>
					</div>
				</div>
			</div>
		</script>

		<script type="text/template" id="lroundups-post-tmpl">
			<% posts.each(function(post, idx) { %>
				<li data-id="<%= post.get('ID') %>">
					<%= post.get('post_title') %>
					<p class="actions"><a class="edit" data-id="<%= post.get('ID') %>" href="#">Edit</a> | <a class="remove" data-id="<%= post.get('ID') %>" href="#">Remove</a></p>
				</li>
			<% }); %>
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
			'ajax_nonce' => wp_create_nonce( 'lroundups_ajax_nonce' ),
			'plugin_url' => LROUNDUPS_DIR_URI
		), $add );
	}

	/*
	 * Load posts for the roundupp block editor
	 *
	 * @since 0.3.2
	 */
	public static function roundup_block_posts() {
		check_ajax_referer('lroundups_ajax_nonce', 'security');

		$exisitingIds = array();
		if ( isset( $_POST['existingIds'] ) ) {
			$exisitingIds = $_POST['existingIds'];
		}

		// Default arguments
		$args = apply_filters('link_roundups_roundup_block_post_query', array(
			'post_type' => 'rounduplink',
			'orderby' => 'date',
			'order' => 'desc',
			'posts_per_page' => 250
		));
		$query = new WP_Query($args);
		$posts = $query->get_posts();
		$ids = array_map(function($x) { return $x->ID; }, $posts);

		// If any of the post ids in the shortcode attribute don't show up
		// in the queried posts, try finding them separately
		foreach ($exisitingIds as $exisitingId) {
			if ( ! in_array( $exisitingId, $ids ) ) {
				$p = get_post( $exisitingId );
				if ( ! empty( $p ) && ! is_wp_error( $p ) ) {
					array_push( $posts, $p );
				}
			}
		}

		usort( $posts, function($a, $b) { return strcmp( $b->post_date, $a->post_date ); } );

		foreach ( $posts as $idx => $post ) {
			$post->order = $idx;
			$post->custom_fields = get_post_custom( $post->ID );
		}

		print json_encode($posts);
		wp_die();
	}
}
