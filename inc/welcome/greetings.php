<?php
function register_welcome_pages() {
	add_submenu_page('edit.php?post_type=roundup', 'Welcome to Link Roundups','Welcome to Link Roundups', 'read', 'lr-welcome', 'welcome_screen_panel_one');
}
add_action('admin_menu','register_welcome_pages');


function welcome_screen_panel_one() {
?>
<div class="wrap about-wrap">

				<div id="welcome-panel" class="welcome-panel">
					<div class="welcome-panel-content">
						<h2 style="margin:0"><?php _e( 'Welcome to Link Roundups', 'buddypress' ); ?> <small style="float:right;font-weight:700;font-style:italic;">v.<?php echo lroundups_version(); ?></small></h2>
						<div class="welcome-panel-column-container">
							<div class="welcome-panel-column">
								<h4><?php _e( 'Start Here...', 'buddypress' ); ?></h4>
								
								<a class="button button-primary button-hero" style="margin-bottom:20px;margin-top:20px;" href="<?php echo admin_url('edit.php?post_type=roundup&page=link-roundups-options'); ?>"><?php _e( 'Configure Options', 'buddypress' ); ?></a>

								<ul>
									<li><?php printf(
									'<a href="%s"><i class="welcome-icon dashicons-pressthis" style="display:inline-block;"></i> ' . __( 'Add Browser Bookmark Tool', 'buddypress' ) . '</a>', admin_url('edit.php?post_type=rounduplink&page=install-browser-bookmark')); ?></li>
									
									</ul>
							</div>
							<div class="welcome-panel-column">
								<h4><?php _e('... Then Save Links', 'link-roundups'); ?></h4>

								<ul>
									<li><?php printf( '<a href="%s" class="welcome-icon dashicons-admin-links">' . __( 'Save Links to WordPress', 'buddypress' ) . '</a>', admin_url('post-new.php?post_type=rounduplink')); ?></li>
									<li><?php printf( '<a href="%s" class="welcome-icon dashicons-tag">' . __( 'Organize Links with Tags', 'buddypress' ) . '</a>', admin_url( 'edit-tags.php?taxonomy=argo-link-tags&post_type=rounduplink' )); ?></li>
									<li><?php printf(
									'<a href="%s"><i class="welcome-icon dashicons-welcome-widgets-menus" style="display:inline-block;"></i>' . __( 'Display Link Feed in Widget', 'buddypress' ) . '</a>', admin_url( 'widgets.php' )); ?></li>
								</ul>
								
							</div>
							<div class="welcome-panel-column welcome-panel-last">
								<h4><?php _e('... And Publish Roundups','link-roundups'); ?></h4>

								<ul>
									<li><?php printf( '<a href="%s" class="welcome-icon dashicons-list-view">' . __( 'Create Roundups in Custom Order', 'buddypress' ) . '</a>', admin_url( 'post-new.php?post_type=roundup' )); ?></li>
									<li><?php printf( '<a href="%s" class="welcome-icon dashicons-email">' . __( 'Send Roundups as Email Newsletters', 'buddypress' ) . '</a>', admin_url( 'edit.php?post_type=roundup&page=link-roundups-options#mailchimp' )); ?></li>
									<li><?php printf(
									'<a href="%s"><i class="welcome-icon dashicons-welcome-widgets-menus" style="display:inline-block;"></i>' . __( 'Display Recent Roundup(s) in Widget', 'buddypress' ) . '</a>', admin_url( 'widgets.php' )); ?></li>
								
								</ul>
							</div>
						</div>
					</div>
				</div>
				
				<div id="welcome-panel" class="welcome-panel">
					<div class="welcome-panel-content">
						<div class="welcome-panel-column-container" style="text-align:center;display:flex;flex-wrap:wrap;margin-bottom:2.5em;">
							<div style="width:280px;margin:0 auto;">
								<i class="dashicons dashicons-welcome-learn-more" style="font-size:60px;width:60px;height:60px;"></i>
								<br />
								<h4><?php _e( '<a class="button" href=\'<?php esc_url("https://github.com/INN/link-roundups/blob/master/docs/readme.md"); ?>\' style="margin:0.5em 0;">Full Documentation</a>', 'buddypress' ) ?></h4>
							</div>
							<div style="width:280px;margin:0 auto;">
								<i class="dashicons dashicons-editor-code" style="font-size:60px;width:60px;height:60px;"></i>
								<br />
								<h4><?php _e( '<a class="button" href="<?php esc_url_raw(\'https://github.com/INN/link-roundups/compare\'); ?>" style="margin:0.5em 0;">Contribute Code</a>', 'buddypress' ) ?></h4>
								<h4><?php _e( '<a class="button" style="background-color:#cd1713;color:#edf1f4;box-shadow:none;" href="<?php esc_url_raw(\'https://github.com/INN/link-roundups/issues\'); ?>">Report Issues</a>', 'buddypress' ) ?></h4>
							</div>
							<div style="width:280px;margin:0 auto;">
								<i class="dashicons dashicons-heart" style="font-size:60px;width:60px;height:60px;"></i>
								<br />
								<h4><?php _e( '<a class="button" href="<?php esc_url(\'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=M7T6234BREMG2\');?>" style="margin:0.5em 0;">Donate</a>', 'buddypress' ) ?></h4>
							</div>
						</div>
					</div>
				</div>
			
			<div class="feature-list">
				<h2><?php esc_html_e( 'Code Changes', 'buddypress' ); ?></h2>

				<div class="feature-section col two-col">
					<div>
						<h4><?php esc_html_e( 'Update from argo-links', 'buddypress' ); ?></h4>
						<p><?php _e( 'We\'ve renamed every setting, taxonomy, file and custom post field.', 'buddypress' ); ?></p>

						<h4><?php esc_html_e( 'Improved automation and logic', 'buddypress' ); ?></h4>
						<p><?php esc_html_e( 'Permalinks automatically flush when modifying', 'buddypress' ); ?></p>
					</div>
					<div class="last-feature">
						<h4><?php esc_html_e( 'Tests writing for all functionality', 'buddypress' ); ?></h4>
						<p><?php esc_html_e( 'Continued improvements to inline code documentation make it easier for developers to understand how BuddyPress works.', 'buddypress' ); ?></p>

						<h4><?php esc_html_e( 'Improved WordPress Admin design and messaging', 'buddypress' ); ?></h4>
						<p><?php esc_html_e( 'We\'ve cleaned up the design and clarified instructions, headlines and labels.', 'buddypress' ); ?></p>
					</div>
				</div>
			</div>

			<div class="headline-feature">
				<h2>Thanks to Link Roundups Contributors</h2>
				<table class="wp-list-table">
					<tbody>
						<?php foreach ( lroundups_get_contributors() as $contributor ) : ?>
						<tr style="display:inline-flex;flex-wrap:wrap;width:40%;">
							<?php
								$contributions = sprintf( '%d %s', $contributor->contributions, _n( 'contribution', 'contributions', $contributor->contributions ) );
								$url = sprintf( 'http://github.com/%s', $contributor->login );
								$avatar_url = add_query_arg( 's', 150, $contributor->avatar_url );
								$avatar_url = add_query_arg( 'd', esc_url_raw( 'https://secure.gravatar.com/avatar/ad516503a11cd5ca435acc9bb6523536?s=150' ), $avatar_url );
							?>
							<th class="img" style="width:150px;">
								<a href="<?php echo esc_url( $url ); ?>"><img src="<?php echo esc_url( $avatar_url ); ?>" style="max-width:130px;max-height:130px;border:4px solid #23282d;" /></a>
							</th>
							<td class="user">
								<h3 style="margin-top:0;margin-bottom:0;text-align:left;"><a href="<?php echo esc_url( $url ); ?>"><?php echo '@' . $contributor->login; ?></a></h3>
								<h5 style="margin-top:0.25em;"><?php echo $contributions; ?></h5>

							</td>

						</tr>
						<?php endforeach; ?>
					</tbody>
					</table>

				<div class="clear"></div>
			</div>

			<!--<div class="feature-list finer-points">
				<h2><?php esc_html_e( 'The Finer Points', 'buddypress' ); ?></h2>

				<div class="feature-section col two-col">
					<div>
						<span class=" dashicons dashicons-admin-users"></span>
						<h4><?php esc_html_e( 'Member Type Directories', 'buddypress' ); ?></h4>
						<p><?php esc_html_e( 'Create directories of member types in your site using the Member Type API.', 'buddypress' ); ?></p>
					</div>

					<div class="template-pack last-feature">
						<span class=" dashicons dashicons-admin-appearance"></span>
						<h4><?php esc_html_e( 'Companion Stylesheets For Themes', 'buddypress' ); ?></h4>
						<p><?php esc_html_e( 'Improved styling and integration of BuddyPress components with bundled WordPress themes, Twenty Fifteen and Twenty Fourteen.', 'buddypress' ); ?></p>
					</div>

					<div class="group-invites">
						<span class=" dashicons dashicons-admin-post"></span>
						<h4><?php esc_html_e( 'Blog Post Activity', 'buddypress' ); ?></h4>
						<p><?php esc_html_e( 'BuddyPress now generates better excerpts in the activity streams for posts containing images or other embedded media content.', 'buddypress' ); ?></p>
					</div>

					<div class="last-feature">
						<span class=" dashicons dashicons-star-filled"></span>
						<h4><?php esc_html_e( 'Star Private Messages ', 'buddypress' ); ?></h4>
						<p><?php esc_html_e( 'Mark important messages in your inbox from your friends with a star.', 'buddypress' ); ?></p>
					</div>
				</div>
			</div>-->

		</div>
<?php
}

function hide_welcome_screen_menu_link() {
	remove_submenu_page( 'edit.php?post_type=roundup', 'lr-welcome' );
}
add_action('admin_head','hide_welcome_screen_menu_link');

/**
 * Returns an array of contributors from Github.
 * @see https://github.com/Automattic/underscores.me/blob/master/functions.php#L137
 */
function lroundups_get_contributors() {
	$transient_key = 'lroundups_contributors';
	$contributors = get_transient( $transient_key );
	if ( false !== $contributors )
		return $contributors;
	$response = wp_remote_get( 'https://api.github.com/repos/INN/link-roundups/contributors?per_page=100' );
	if ( is_wp_error( $response ) )
		return array();
	$contributors = json_decode( wp_remote_retrieve_body( $response ) );
	if ( ! is_array( $contributors ) )
		return array();
	set_transient( $transient_key, $contributors, HOUR_IN_SECONDS );
	return (array) $contributors;
}
