<?php
/**
 * Saved Links Widget
 *
 * Displays a list of your recently saved links
 */
class saved_links_widget extends WP_Widget {

	/** Constructor */
	public function __construct() {
		$widget_ops = array(
			'classname'   => 'saved-links',
			'description' => __( 'Show your most recently saved links in a sidebar widget', 'link-roundups' ),
		);
		parent::__construct( 'saved-links-widget', __( 'Saved Links Widget', 'link-roundups' ), $widget_ops );
	}

	/**
	 * Output the widget
	 *
	 * @param Array $args     The sidebar arguments for this widget's widget area.
	 * @param Array $instance The arguments on this instance of this widget.
	 */
	public function widget( $args, $instance ) {

		// make it possible for the widget title to be a link.
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Recent Links', 'link-roundups' ) : $instance['title'], $instance, $this->id_base );

		echo wp_kses_post( $args['before_widget'] );

		if ( ! empty( $title ) ) {
			echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
		}

		$query_args = array(
			'post__not_in' => get_option( 'sticky_posts' ),
			'showposts'    => $instance['num_posts'],
			'post_type'    => 'rounduplink',
			'post_status'  => 'publish',
		);
		$my_query   = new WP_Query( $query_args );

		if ( $my_query->have_posts() ) {
			while ( $my_query->have_posts() ) {
				global $post;
				$my_query->the_post();
				$custom = get_post_custom( $post->ID );

				// skip roundups.
				if ( 'roundup' === get_post_type( $post ) ) {
					continue;
				}

				?>

				<div class="post-lead clearfix">
					<?php
					if ( has_post_thumbnail( $post->ID ) && 'on' === $instance['show_featured_image'] ) {
						echo get_the_post_thumbnail( $post->ID );
					}
					?>

					<h5>
						<?php
							if ( ! empty( $custom['lr_url'][0] ) ) {
								$output = sprintf(
									'<a href="%1$s" rel="noopener noreferrer"',
									esc_attr( $custom['lr_url'][0] )
								);
								if ( 'on' === $instance['new_window'] ) {
									$output .= 'target="_blank"';
								}
								$output .= '>' . get_the_title() . '</a>';
							} else {
								$output = get_the_title();
							}
							echo $output;
						?>
					</h5>

					<?php
					if ( isset( $custom['lr_desc'][0] ) && ! empty( $custom['lr_desc'][0] ) ) {
						echo '<p class="description">';

						if ( function_exists( 'largo_trim_sentences' ) ) {
							echo wp_kses_post( largo_trim_sentences( $custom['lr_desc'][0], $instance['num_sentences'] ) );
						} else {
							echo wp_kses_post( $custom['lr_desc'][0] );
						}

						echo '</p>';
					}

					if ( isset( $custom['lr_source'][0] ) && ! empty( $custom['lr_source'][0] ) ) {
						$lr_source = '<p class="source"><span class="source-label">' . __( 'Source: ', 'link-roundups' ) . '</span><span>';

						if ( ! empty( $custom['lr_url'][0] ) ) {
							$lr_source .= sprintf(
								'<a href="%1$s" rel="noopener noreferrer"',
								esc_attr( $custom['lr_url'][0] )
							);
							if ( 'on' === $instance['new_window'] ) {
								$lr_source .= 'target="_blank" ';
							}
							$lr_source .= '>' . $custom['lr_source'][0] . '</a>';
						} else {
							$lr_source .= wp_kses_post( $custom['lr_source'][0] );
						}

						$lr_source .= '</span></p>';

						echo $lr_source;
					}
					?>
				</div> <!-- /.post-lead -->
				<?php
			}
			wp_reset_postdata();
		} else {
			printf(
				'<p class="error"><strong>%1$s</strong></p>',
				esc_html__( 'You don\'t have any recent Saved Links saved.', 'link-roundups' )
			);
		} // end recent links

		if ( ! empty( $instance['linkurl'] ) ) {
			?>
				<p class="morelink">
					<a href="<?php echo esc_attr( $instance['linkurl'] ); ?>">
						<?php echo wp_kses_post( $instance['linktext'] ); ?>
					</a>
				</p>
			<?php
		}
		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Update the widget settings upon save
	 *
	 * @param  Array $new_instance The arguments passed when saved.
	 * @param  Array $old_instance The previously-saved arguments.
	 * @return Array $instance     The new arguments.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                        = $old_instance;
		$instance['title']               = wp_strip_all_tags( $new_instance['title'] );
		$instance['num_posts']           = intval( $new_instance['num_posts'] );
		$instance['num_sentences']       = intval( $new_instance['num_sentences'] );
		$instance['linktext']            = $new_instance['linktext'];
		$instance['linkurl']             = $new_instance['linkurl'];
		$instance['show_featured_image'] = $new_instance['show_featured_image'];
		$instance['new_window']          = $new_instance['new_window'];
		return $instance;
	}

	/**
	 * Output the form for the widget settings
	 *
	 * @param Array $instance The widget's instance arguments.
	 */
	public function form( $instance ) {
		$defaults = array(
			'title'               => __( 'Recent Links', 'link-roundups' ),
			'new_window'          => 1,
			'num_posts'           => 5,
			'num_sentences'       => 2,
			'linktext'            => '',
			'linkurl'             => '',
			'show_featured_image' => null,
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'link-roundups' ); ?></label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" style="width:100%;" type="text"/>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'num_posts' ) ); ?>"><?php esc_html_e( 'Number of posts to show:', 'link-roundups' ); ?></label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'num_posts' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'num_posts' ) ); ?>" value="<?php echo esc_attr( $instance['num_posts'] ); ?>" style="width:100%;" type="number" min="1"/>
		</p>

		<p>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'new_window' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'new_window' ) ); ?>" <?php checked( $instance['new_window'], 'on' ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'new_window' ) ); ?>"><?php esc_html_e( 'Open links in new window', 'link-roundups' ); ?></label>
		</p>

		<?php if ( function_exists( 'largo_trim_sentences' ) ) : ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'num_sentences' ) ); ?>"><?php esc_html_e( 'Excerpt Length (# of Sentences):', 'link-roundups' ); ?></label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'num_sentences' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'num_sentences' ) ); ?>" value="<?php echo esc_attr( $instance['num_sentences'] ); ?>" style="width:100%;" type="number" min="0"/>
		</p>
		<?php endif; ?>

		<p>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_featured_image' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_featured_image' ) ); ?>" <?php checked( $instance['show_featured_image'], 'on' ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_featured_image' ) ); ?>"><?php esc_html_e( 'Show featured images?', 'link-roundups' ); ?></label>
		</p>

		<p><strong>More Link</strong><br /><small><?php esc_html_e( 'If you would like to add a more link at the bottom of the widget, add the link text and url here.', 'link-roundups' ); ?></small></p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'linktext' ) ); ?>"><?php esc_html_e( 'Link text:', 'link-roundups' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'linktext' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'linktext' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['linktext'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'linkurl' ) ); ?>"><?php esc_html_e( 'URL:', 'link-roundups' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'linkurl' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'linkurl' ) ); ?>" type="url" value="<?php echo esc_attr( $instance['linkurl'] ); ?>" />
		</p>

		<?php
	}
}
