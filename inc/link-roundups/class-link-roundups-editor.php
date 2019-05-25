<?php

class LinkRoundupsEditor {

	// Initialize the plugin
	public static function init() {
		add_filter( 'mce_external_plugins', array( __CLASS__, 'add_tinymce_plugin' ), 4 );
		add_action( 'admin_init', array( __CLASS__, 'add_editor_styles' ) );
		add_action( 'admin_footer', array( __CLASS__, 'add_modal_template' ) );
		add_action( 'wp_ajax_roundup_block_posts', array( __CLASS__, 'roundup_block_posts' ) );
		add_action( 'wp_ajax_roundup_update_post', array( __CLASS__, 'roundup_update_post' ) );
		add_shortcode( 'roundup_block', array( __CLASS__, 'roundup_block_shortcode' ) );
		add_filter( 'mailchimp_tools_campaign_content', array( __CLASS__, 'mailchimp_tools_campaign_content' ), 10, 3 );
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

		$content .= apply_filters(
			'link_roundup_block_before_links',
			'',
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
				call_user_func( $formatting_func, $id, 'roundup_block_link', $attrs ),
				$attrs,
				$id
			);
		}

		$content .= apply_filters(
			'link_roundup_block_after_links',
			'',
			$attrs,
			$ids
		);

		/**
		 * Allow for filtering/replacing the entire contents of a roundup block
		 */
		return apply_filters( 'link_roundup_block_content', $content, $attrs, $ids );
	}

	/**
	 * Add TinyMCE editor plugin to enable clickable/editable roundup blocks in posts
	 *
	 * @since 0.3
	 */
	public static function add_tinymce_plugin( $plugins ) {
		$suffix                          = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
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

		wp_register_script(
			'lroundups-typeahead',
			LROUNDUPS_DIR_URI . '/js/vendor/typeahead.js/dist/typeahead.jquery.min.js'
		);
		wp_register_script(
			'lroundups-jquery-serialize-object',
			LROUNDUPS_DIR_URI . '/js/vendor/jquery-serialize-object/dist/jquery.serialize-object.min.js'
		);

		add_action(
			'admin_enqueue_scripts',
			function() {
				wp_enqueue_script( 'lroundups-typeahead' );
				wp_enqueue_script( 'lroundups-jquery-serialize-object' );
				wp_enqueue_style( 'lroundups-editor' );
			}
		);
	}

	/**
	 * Prints the underscore templates and JSON used in the front-end javascript
	 *
	 * @since 0.2
	 */
	public static function add_modal_template() {
		$screen = get_current_screen();
		if ( $screen->base == 'post' && $screen->post_type == 'roundup' ) {
			self::modal_underscore_template();

			?>
		<script type="text/javascript">
			var LR = <?php echo json_encode( self::json_obj() ); ?>;
		</script>
			<?php
		}
	}

	/**
	 * Print the underscore template for the LR.Modal view.
	 *
	 * @since 0.2
	 */
	public static function modal_underscore_template() {
		?>
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
			<p>Drag items from right to left to add them to the "<%= name %>" block.</p>
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
					<input type="text" disabled class="typeahead" placeholder="Search the last month's items..." />
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
					<div class="status">Status: <em><%= post.getStatus() %></em></div>
					<div class="actions">
						<div class="added">
							<a class="edit" data-id="<%= post.get('ID') %>" href="#">Edit</a> | <a class="remove" data-id="<%= post.get('ID') %>" href="#">Remove</a>
						</div>
						<div class="available">
							<a class="add" data-id="<%= post.get('ID') %>" href="#">Add</a>
						</div>
					</div>
				</li>
			<% }); %>
		</script>

		<script type="text/template" id="lroundups-post-edit-tmpl">
			<small>Currently editing:</small>
			<h4><%= post_title %></h4>
			<p>
				<label>Title</label>
				<input type="text" name="post_title" value="<%= post_title %>"/>
			</p>
			<p>
				<label>Subheadline</label>
				<% if (custom_fields.lr_subhed) { %>
					<input type="text" name="custom_fields[lr_subhed]" value="<%= custom_fields.lr_subhed %>"/>
				<% } else { %>
					<input type="text" name="custom_fields[lr_subhed]" value="<%= custom_fields.lr_subhed %>"/>
				<% } %>
			</p>
			<p>
				<label>URL</label>
				<% if (custom_fields.lr_url) { %>
					<input type="text" name="custom_fields[lr_url]" value="<%= custom_fields.lr_url %>" />
				<% } else if (post_permalink) { %>
					<input type="text" name="custom_fields[lr_url]" value="<%= post_permalink %>" />
				<% } else { %>
					<input type="text" name="custom_fields[lr_url]" />
				<% } %>
			</p>
			<p>
				<label>Description</label>
				<% if (custom_fields.lr_desc) { %>
					<textarea name="custom_fields[lr_desc]"><%= custom_fields.lr_desc %></textarea>
				<% } else if (post_excerpt) { %>
					<textarea name="custom_fields[lr_desc]"><%= post_excerpt %></textarea>
				<% } else { %>
					<textarea name="custom_fields[lr_desc]"></textarea>
				<% } %>
			</p>
			<p>
				<label>Source</label>
				<% if (custom_fields.lr_source) { %>
					<input type="text" name="custom_fields[lr_source]" value="<%= custom_fields.lr_source %>" />
				<% } else if (source) { %>
					<input type="text" name="custom_fields[lr_source]" value="<%= source %>" />
				<% } else { %>
					<input type="text" name="custom_fields[lr_source]" />
				<% } %>
			</p>
		</script>
		<?php
	}

	/**
	 * Builds a LR object with common attributes used throughout the plugin's javascript files.
	 *
	 * @since 0.2
	 */
	public static function json_obj( $add = array() ) {
		global $post;

		$roundup_posts = self::roundup_block_posts_query();

		return array_merge(
			array(
				'post_id'       => $post->ID,
				'ajax_nonce'    => wp_create_nonce( 'lroundups_ajax_nonce' ),
				'plugin_url'    => LROUNDUPS_DIR_URI,
				'roundup_posts' => $roundup_posts,
			),
			$add
		);
	}

	/*
	 * Update a saved link/post
	 *
	 * @since 0.3.2
	 */
	public static function roundup_update_post() {
		check_ajax_referer( 'lroundups_ajax_nonce', 'security' );

		if ( ! current_user_can( apply_filters( 'link_roundups_minimum_capability', 'edit_posts' ) ) ) {
			wp_die();
		}

		if ( isset( $_POST['post'] ) ) {
			$post_data = json_decode( stripslashes( $_POST['post'] ), true );

			$custom_fields = $post_data['custom_fields'];
			unset( $post_data['custom_fields'] );

			$post_id = wp_update_post( $post_data );

			if ( is_wp_error( $post_id ) ) {
				$errors = $post_id->get_error_messages();
				print json_encode(
					array(
						'success' => false,
						'message' => $errors,
					)
				);
				wp_die();
			}

			foreach ( $custom_fields as $meta_key => $meta_value ) {
				update_post_meta( $post_id, $meta_key, $meta_value );
			}
			print json_encode( array( 'success' => true ) );
		}
		wp_die();
	}

	public static function roundup_block_posts_query() {
		// Default arguments
		$args  = apply_filters(
			'link_roundups_roundup_block_post_query',
			array(
				'post_type'      => 'rounduplink',
				'orderby'        => 'date',
				'order'          => 'desc',
				'posts_per_page' => 10,
				'date_query'     => array(
					'year'     => date( 'Y' ),
					'monthnum' => date( 'm' ),
				),
			)
		);
		$query = new WP_Query( $args );
		$posts = $query->get_posts();
		$ids   = array_map(
			function( $x ) {
					return $x->ID;
			},
			$posts
		);

		// If any of the post ids in the shortcode attribute don't show up
		// in the queried posts, try finding them separately
		global $post;

		$exisitingIds = array();
		if ( isset( $post ) ) {
			/**
			 * Check the post for blocks with existing ids
			 */
			preg_match_all( '/\[roundup_block.*ids\=\"([0-9,]+)\".*\]/', $post->post_content, $matches );
			if ( ! empty( $matches ) ) {
				foreach ( $matches[1] as $group ) {
					$foundIds = split( ',', $group );
					foreach ( $foundIds as $id ) {
						array_push( $exisitingIds, $id );
					}
				}
			}
		} else {
			/**
			 * Request from post editor may send existing ids over the wire
			 */
			if ( isset( $_POST['existingIds'] ) ) {
				$exisitingIds = $_POST['existingIds'];
			}
		}

		foreach ( $exisitingIds as $exisitingId ) {
			if ( ! in_array( $exisitingId, $ids ) ) {
				$p = get_post( $exisitingId );
				if ( ! empty( $p ) && ! is_wp_error( $p ) ) {
					array_push( $posts, $p );
				}
			}
		}

		usort(
			$posts,
			function( $a, $b ) {
				return strcmp( $b->post_date, $a->post_date );
			}
		);

		foreach ( $posts as $idx => $post ) {
			$post->order          = $idx;
			$post->custom_fields  = get_post_custom( $post->ID );
			$post->source         = get_bloginfo( 'name' );
			$post->post_permalink = get_permalink( $post->ID );
		}
		return $posts;
	}

	/*
	 * Load posts for the roundup block editor
	 *
	 * @since 0.3.2
	 */
	public static function roundup_block_posts() {
		check_ajax_referer( 'lroundups_ajax_nonce', 'security' );
		$posts = self::roundup_block_posts_query();
		print json_encode( $posts );
		wp_die();
	}

	/**
	 * Apply information from a Link Roundup post to a WordPress MailChimp Tools email via the appropriate filter
	 *
	 * @param Array   $campaign_params An array of request body parameters, as described in the "put" section of https://developer.mailchimp.com/documentation/mailchimp/reference/campaigns/content/#%20
	 * @param WP_Post $post The post that is being turned into a MailChimp Campaign
	 * @param int     $id The campaign ID
	 * @return Array $params
	 *
	 * @link the commit that implemented this filter: https://github.com/INN/wordpress-mailchimp-tools/commit/4e768f7661a2fe8fc2785140e8313280eb230c3f
	 * @link Why: https://github.com/INN/link-roundups/pull/139#issuecomment-488852947
	 */
	public static function mailchimp_tools_campaign_content( $campaign_params, $post, $id ) {
		// shortcut if post not set
		if ( empty( $post ) ) {
			return $campaign_params;
		}

		if ( ! ( $post instanceof WP_Post ) ) {
			$post = get_post( $post );
		}

		if ( ! isset( $campaign_params['template'] ) ) {
			$campaign_params['template'] = array();
		}
		if ( ! isset( $campaign_params['template']['sections'] ) ) {
			$campaign_params['template']['sections'] = array();
		}
		$campaign_params['template']['sections']['rounduplinks']     = apply_filters( 'the_content', $post->post_content );
		$campaign_params['template']['sections']['rounduptitle']     = $post->post_title;
		$campaign_params['template']['sections']['roundupdate']      = get_the_date( '', $post->ID );
		$campaign_params['template']['sections']['rounduppermalink'] = get_post_permalink( $post->ID );

		$author = get_user_by( 'id', $post->post_author );
		$campaign_params['template']['sections']['roundupauthor'] = $author->display_name;

		return $campaign_params;
	}
}
