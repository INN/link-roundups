<?php
/*
 * argo Recent Posts
 */
class argo_links_widget extends WP_Widget {

	function argo_links_widget() {
		$widget_ops = array(
			'classname' => 'argo-links',
			'description' => 'Show your most recently saved links in a sidebar widget', 'argo-links'
		);
		$this->WP_Widget( 'argo-links-widget', __('Argo Links Widget', 'argo-links'), $widget_ops);
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
				'post_type' 	=> 'argolinks',
				'post_status'	=> 'publish'
			);
			$my_query = new WP_Query( $query_args );
          		if ( $my_query->have_posts() ) {
          			while ( $my_query->have_posts() ) : $my_query->the_post();
          				$custom = get_post_custom($post->ID); ?>
          				<?php if (get_post_type($post) === 'argolinkroundups') continue; ?>
	                  	<div class="post-lead clearfix">
	                      	<h5><?php echo ( isset( $custom["argo_link_url"][0] ) ) ? '<a href="' . $custom["argo_link_url"][0] . '">' . get_the_title() . '</a>' : get_the_title(); ?></h5>

	                      	<?php
	                      	if ( isset( $custom["argo_link_description"][0] ) ) {
		                      	echo '<p class="description">';
		                      	echo ( function_exists( 'largo_trim_sentences' ) ) ? largo_trim_sentences($custom["argo_link_description"][0], $instance['num_sentences'])  :  $custom["argo_link_description"][0];
		                      	echo '</p>';
	                      	}
	                      	if ( isset($custom["argo_link_source"][0] ) ) {
		                      	echo '<p class="source">' . __('Source: ', 'argo-links') . '<span>';
		                      	echo ( isset( $custom["argo_link_url"][0] ) ) ? '<a href="' . $custom["argo_link_url"][0] . '">' . $custom["argo_link_source"][0] . '</a>' : $custom["argo_link_source"][0];
		                      	echo '</span></p>';
	                      	}
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
		$instance['num_sentences'] = strip_tags( $new_instance['num_sentences'] );
		$instance['linktext'] = $new_instance['linktext'];
		$instance['linkurl'] = $new_instance['linkurl'];
		return $instance;
	}

	function form( $instance ) {
		$defaults = array(
			'title' 			=> 'Recent Links',
			'num_posts' 		=> 5,
			'num_sentences' 	=> 2,
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

		<?php if ( function_exists( 'largo_trim_sentences' ) ) : ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'num_sentences' ); ?>"><?php _e('Excerpt Length (# of Sentences):', 'argo-links'); ?></label>
			<input id="<?php echo $this->get_field_id( 'num_sentences' ); ?>" name="<?php echo $this->get_field_name( 'num_sentences' ); ?>" value="<?php echo $instance['num_sentences']; ?>" style="width:90%;" />
		</p>
		<?php endif; ?>

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