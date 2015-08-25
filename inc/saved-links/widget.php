<?php
/*
 * argo Recent Posts
 */
class saved_links_widget extends WP_Widget {

	function saved_links_widget() {
		$widget_ops = array(
			'classname' 	=> 'saved-links',
			'description' 	=> __( 'Show your most recently saved links in a sidebar widget', 'link-roundups' )
		);
		parent::__construct( 'saved-links-widget', __( 'Saved Links Widget', 'link-roundups' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;

		if ( $title )
			echo $before_title . $title . $after_title; ?>

			<?php
			$query_args = array (
				'post__not_in' 	=> get_option( 'sticky_posts' ),
				'showposts' 	=> $instance['num_posts'],
				'post_type' 	=> 'rounduplink',
				'post_status'	=> 'publish'
			);
			$my_query = new WP_Query( $query_args );
          		if ( $my_query->have_posts() ) {
          			while ( $my_query->have_posts() ) : $my_query->the_post();
          				$custom = get_post_custom( $post->ID ); ?>
          				<?php if ( get_post_type( $post ) === 'roundup' ) continue; ?>
						<div class="post-lead clearfix">
							<?php if (has_post_thumbnail($post->ID) && $instance['show_featured_image'] == 'on') {
								echo get_the_post_thumbnail($post->ID);
							} ?>

	                      	<h5><?php echo ( isset( $custom["lr_url"][0] ) ) ? '<a href="' . $custom["lr_url"][0] . '">' . get_the_title() . '</a>' : get_the_title(); ?></h5>

	                      	<?php
	                      	if ( isset( $custom["lr_desc"][0] ) ) {
		                      	echo '<p class="description">';
		                      	echo ( function_exists( 'largo_trim_sentences' ) ) ? largo_trim_sentences($custom["lr_desc"][0], $instance['num_sentences']) : $custom["lr_desc"][0];
		                      	echo '</p>';
	                      	}
	                      	if ( isset($custom["lr_source"][0] ) ) {
		                      	echo '<p class="source">' . __('Source: ', 'link-roundups') . '<span>';
		                      	echo ( isset( $custom["lr_url"][0] ) ) ? '<a href="' . $custom["lr_url"][0] . '">' . $custom["lr_source"][0] . '</a>' : $custom["lr_source"][0];
		                      	echo '</span></p>';
	                      	}
	                      	?>

	                  	</div> <!-- /.post-lead -->
	            <?php
	            	endwhile;
	            } else {
	    			_e( '<p class="error"><strong>You don\'t have any recent links or the argo links plugin is not active.</strong></p>', 'link-roundups' );
	    		} // end recent links

    		if ( $instance['linkurl'] != '' ) { ?>
				<p class="morelink"><a href="<?php echo $instance['linkurl']; ?>"><?php echo $instance['linktext']; ?></a></p>
			<?php }
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['num_posts'] = strip_tags( $new_instance['num_posts'] );
		$instance['num_sentences'] = strip_tags( $new_instance['num_sentences'] );
		$instance['linktext'] = $new_instance['linktext'];
		$instance['linkurl'] = $new_instance['linkurl'];
		$instance['show_featured_image'] = $new_instance['show_featured_image'];
		return $instance;
	}

	function form( $instance ) {
		$defaults = array(
			'title' => __( 'Recent Links', 'link-roundups' ),
			'num_posts' => 5,
			'num_sentences' => 2,
			'linktext' => '',
			'linkurl' => '',
			'show_featured_image' => null
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'link-roundups' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:90%;" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'num_posts' ); ?>"><?php _e( 'Number of posts to show:', 'link-roundups' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'num_posts' ); ?>" name="<?php echo $this->get_field_name( 'num_posts' ); ?>" value="<?php echo $instance['num_posts']; ?>" style="width:90%;" />
		</p>

		<?php if ( function_exists( 'largo_trim_sentences' ) ) : ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'num_sentences' ); ?>"><?php _e( 'Excerpt Length (# of Sentences):', 'link-roundups' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'num_sentences' ); ?>" name="<?php echo $this->get_field_name( 'num_sentences' ); ?>" value="<?php echo $instance['num_sentences']; ?>" style="width:90%;" />
		</p>
		<?php endif; ?>

		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id('show_featured_image'); ?>" name="<?php echo $this->get_field_name('show_featured_image'); ?>" <?php checked($instance['show_featured_image'], 'on'); ?> />
			<label for="<?php echo $this->get_field_id('show_featured_image'); ?>"><?php _e('Show featured images?', 'link-roundups'); ?></label>
		</p>

		<p><strong>More Link</strong><br /><small><?php _e( 'If you would like to add a more link at the bottom of the widget, add the link text and url here.', 'link-roundups' ); ?></small></p>
		<p>
			<label for="<?php echo $this->get_field_id( 'linktext' ); ?>"><?php _e( 'Link text:', 'link-roundups' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'linktext' ); ?>" name="<?php echo $this->get_field_name( 'linktext' ); ?>" type="text" value="<?php echo $instance['linktext']; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'linkurl' ); ?>"><?php _e( 'URL:', 'link-roundups' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'linkurl' ); ?>" name="<?php echo $this->get_field_name( 'linkurl' ); ?>" type="text" value="<?php echo $instance['linkurl']; ?>" />
		</p>

	<?php
	}
}
