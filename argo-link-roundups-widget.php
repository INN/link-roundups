<?php
/*
 * argo Recent Posts
 */
class argo_link_roundups_widget extends WP_Widget {

	function argo_link_roundups_widget() {
		$widget_ops = array(
			'classname' => 'argo-link-roundups',
			'description' => 'Show your most recent link roundups in the sidebar', 'argo-links'
		);
		$this->WP_Widget( 'argo-link-roundups-widget', __('Argo Link Roundups Widget', 'argo-links'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters('widget_title', $instance['title'] );

		echo $before_widget;

		if ( $title )
			echo $before_title . $title . $after_title;?>

			<?php
			$query_args = array (
				'post__not_in' 	=> get_option( 'sticky_posts' ),
				'showposts' 	=> $instance['num_posts'],
				'exceprt'	=> $instance['show_excerpt'],
				'post_type' 	=> 'post',
				'post_status'	=> 'publish'
			);
			$my_query = new WP_Query( $query_args );
          		if ( $my_query->have_posts() ) {
          			while ( $my_query->have_posts() ) : $my_query->the_post();
          				$custom = get_post_custom($post->ID); ?>
	                  	<div class="post-lead clearfix">
	                      	<?php
	                      	
	                      	// the date
							$output .='<span>' . get_the_date("F d Y") . '</span>';
								                      	
	                      	// the headline
							$output .= '<h4><a href="' . get_permalink() . '">' . get_the_title() . '</a></h4>';
							
							// the excerpt
							$output .= '<p>' . get_the_excerpt() . '</p>';
							
							echo $output;
	                      	?>

	                  	</div> <!-- /.post-lead -->
	            <?php
	            	endwhile;
	            } else {
	    			_e('<p class="error"><strong>You don\'t have any recent links or the argo links plugin is not active.</strong></p>', 'argo-links');
	    		} // end recent links

    		if ( $instance['linkurl'] !='' ) { ?>
				<p class="morelink"><a href="<?php echo $instance['linkurl']; ?>"><?php echo $instance['linktext']; ?></a></p>
			<?php }
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['num_posts'] = strip_tags( $new_instance['num_posts'] );
		$instance['linktext'] = $new_instance['linktext'];
		$instance['linkurl'] = $new_instance['linkurl'];
		return $instance;
	}

	function form( $instance ) {
		$defaults = array(
			'title' 			=> 'Recent Link Roundups',
			'num_posts' 		=> 1,
			'linktext' 			=> '',
			'linkurl' 			=> ''
		);

		$instance = wp_parse_args( (array) $instance, $defaults );
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'argo-links'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:90%;" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'num_posts' ); ?>"><?php _e('Number of posts to show:', 'argo-links'); ?></label>
			<input id="<?php echo $this->get_field_id( 'num_posts' ); ?>" name="<?php echo $this->get_field_name( 'num_posts' ); ?>" value="<?php echo $instance['num_posts']; ?>" style="width:90%;" />
		</p>

		
		<p><strong>More Link</strong><br /><small><?php _e('If you would like to add a more link at the bottom of the widget, add the link text and url here.', 'argo-links'); ?></small></p>
		<p>
			<label for="<?php echo $this->get_field_id('linktext'); ?>"><?php _e('Link text:', 'argo-links'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('linktext'); ?>" name="<?php echo $this->get_field_name('linktext'); ?>" type="text" value="<?php echo $instance['linktext']; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('linkurl'); ?>"><?php _e('URL:', 'argo-links'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('linkurl'); ?>" name="<?php echo $this->get_field_name('linkurl'); ?>" type="text" value="<?php echo $instance['linkurl']; ?>" />
		</p>

	<?php
	}
}