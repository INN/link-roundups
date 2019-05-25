<?php

/**
 * Recent Link Roundups Widget
 */
class link_roundups_widget extends WP_Widget {

	function __construct() {
		$widget_ops = array(
			'classname'   => 'link-roundups',
			'description' => 'Show your most recent link roundups in the sidebar',
			'link-roundups',
		);
		parent::__construct( 'link_roundups_widget', __( 'Link Roundups Widget', 'link-roundups' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		// make it possible for the widget title to be a link
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Recent Link Roundups', 'link-roundups' ) : $instance['title'], $instance, $this->id_base );

		echo $args['before_widget'];

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$query_args = array(
			'post__not_in'   => get_option( 'sticky_posts' ),
			'posts_per_page' => $instance['num_posts'],
			'post_type'      => 'post',
			'post_status'    => 'publish',
		);

		if ( $instance['cat'] != '' ) {
			$query_args['cat'] = $instance['cat'];
		}

		$my_query = new WP_Query( $query_args );
		if ( $my_query->have_posts() ) {
			while ( $my_query->have_posts() ) :
				$my_query->the_post();
				$custom = get_post_custom( $post->ID ); ?>
					<div class="post-lead clearfix">
						<?php
						// the date
						$output = '<span>' . get_the_date( 'F d Y' ) . '</span>';
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
			_e( '<p class="error"><strong>You don\'t have any recent links or the Link Roundups plugin is not active.</strong></p>', 'link-roundups' );
		} // end recent links

		if ( $instance['linkurl'] != '' ) {
			?>
			<p class="morelink"><a href="<?php echo $instance['linkurl']; ?>"><?php echo $instance['linktext']; ?></a></p>
			<?php
		}

		echo $args['after_widget'];
	}

	function update( $new_instance, $old_instance ) {
		$instance              = $old_instance;
		$instance['title']     = strip_tags( $new_instance['title'] );
		$instance['num_posts'] = intval( strip_tags( $new_instance['num_posts'] ) );
		$instance['linktext']  = $new_instance['linktext'];
		$instance['linkurl']   = esc_url_raw( $new_instance['linkurl'] );
		$instance['cat']       = intval( $new_instance['cat'] );
		return $instance;
	}

	function form( $instance ) {
		$defaults = array(
			'title'     => 'Recent Link Roundups',
			'num_posts' => 1,
			'linktext'  => '',
			'linkurl'   => '',
			'cat'       => 0,
		);

		$instance = wp_parse_args( (array) $instance, $defaults );
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'link-roundups' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:90%;" type="text"/>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'num_posts' ); ?>"><?php _e( 'Number of posts to show:', 'link-roundups' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'num_posts' ); ?>" name="<?php echo $this->get_field_name( 'num_posts' ); ?>" value="<?php echo $instance['num_posts']; ?>" style="width:90%;" type="number" min="1"/>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'cat' ); ?>"><?php _e( 'Limit to category: ', 'link-roundups' ); ?>
			<?php
			wp_dropdown_categories(
				array(
					'name'            => $this->get_field_name( 'cat' ),
					'show_option_all' => __( 'None (all categories)', 'link-roundups' ),
					'hide_empty'      => 0,
					'hierarchical'    => 1,
					'selected'        => $instance['cat'],
				)
			);
			?>
			</label>
		</p>

		<p><strong><?php _e( 'More Link', 'link-roundups' ); ?></strong><br /><small><?php _e( 'If you would like to add a more link at the bottom of the widget, add the link text and url here.', 'link-roundups' ); ?></small></p>
		<p>
			<label for="<?php echo $this->get_field_id( 'linktext' ); ?>"><?php _e( 'Link text:', 'link-roundups' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'linktext' ); ?>" name="<?php echo $this->get_field_name( 'linktext' ); ?>" type="text" value="<?php echo $instance['linktext']; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'linkurl' ); ?>"><?php _e( 'URL:', 'link-roundups' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'linkurl' ); ?>" name="<?php echo $this->get_field_name( 'linkurl' ); ?>" type="url" value="<?php echo $instance['linkurl']; ?>" />
		</p>

		<?php
	}
}
