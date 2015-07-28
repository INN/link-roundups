<?php
$default_html = <<<EOT
<p class='lr-saved-link#!CLASS!#'><a href='#!URL!#'>#!TITLE!#</a>&ndash;<span class='description'>#!DESCRIPTION!#</span><em>#!SOURCE!#</em></p>
EOT;
?>
<div class="wrap">
	<h2>Link Roundups Options</h2>

	<?php settings_errors(); ?>
	
	<h4>Documentation</h4>
	<p>Read about these settings and using this plugin in <a href="https://github.com/INN/link-roundups/tree/master/docs">the documentation on GitHub</a>.</p>
	<a href="#rename"><strong>Rename Link Roundups</strong></a> | <a href="#html"><strong>Custom HTML for Displaying Links</strong></a> | <a href="#mailchimp"><strong>Mailchimp Integration</strong></a>
	<form method="post" action="options.php">
		<?php settings_fields( 'argolinkroundups-settings-group' ); ?>
		<?php do_settings_fields( 'argolinkroundups-settings-group', null ); ?>
		<div class="options-wrapper">
			<div id="rename" class="card">
				<h3>Rename Link Roundups</h3>
				<p>You might call it a Daily Digest. Recap. Team Newsletter. Etc.</p> 
				<p>Modify the Post Type Name displayed in the WordPress Dashboard Menus and Pages and the Post Type Slug (<a href="http://codex.wordpress.org/Glossary#Post_Slug">learn more</a>) used in the public URL for each Link Roundup post.</p>
				<h4>Custom Name</h4>
				<h5 style="margin-bottom:0;padding-bottom:0;">Singular (Default: Link Roundup)</h5>
				<input type="text" name="link_roundups_custom_name_singular" value="<?php echo get_option('link_roundups_custom_name_singular'); ?>" />
				<h5 style="margin-bottom:0;padding-bottom:0;">Plural (Default: Link Roundups)</h5>
				<input type="text" name="link_roundups_custom_name_plural" value="<?php echo get_option('link_roundups_custom_name_plural'); ?>" />

				<h4 style="margin-bottom:2px;padding-bottom:2px;">Custom URL Slug</h4>
				<h5 style="margin:0;padding:0;">Current URL Slug for Link Roundups:</h5>
				<code style="display:block;margin:0.33em 0;">
					<?php echo get_site_url(); ?>/
					<strong><?php 
					$custom_slug_setting = get_option('argo_link_roundups_custom_url');
					
					if(!empty($custom_slug_setting)) { 
						$current_slug = $custom_slug_setting; // apply custom slug
					} else {
						$current_slug = 'roundup'; // set plugin default to roundup
					}
					echo $current_slug; ?>
					</strong>/random-roundup/</code>
				<?php $custom_slug = get_option('argo_link_roundups_custom_url'); ?>
				<input type="text" name="argo_link_roundups_custom_url" value="<?php echo $custom_slug; ?>" />
				<p>Must be lowercase with no spaces or special characters -- dashes allowed.</p>
				
				<?php // echo get_option('argo_link_roundups_custom_url'); ?>
			    	<p><strong>IMPORTANT</strong>: Whenever you define a new Custom URL Slug, you <strong>must</strong> also update your 
			    	<a href="<?php echo admin_url( '/options-permalink.php' ); ?>"><strong>Permalink Settings</strong></a>.
			    	</p>
			</div>
			<div id="html" class="card">
				<h3>Custom HTML for Displaying Links</h3>
				<p>Modify the display and style of Saved Links.</p>
				<textarea name="argo_link_roundups_custom_html" cols='70' rows='6' ><?php echo (get_option('argo_link_roundups_custom_html') != "" ? get_option('argo_link_roundups_custom_html')	: $default_html); ?></textarea>
				<em>Single quotes are REQUIRED in Custom HTML. Double quotes will be automatically converted to single quotes before use.</em><br /><br />
				The following tags will be replaced with the URL, Title, Description, and Source automatically when the Saved Link is pushed into the Post Editor.<br />
				<blockquote><ul style="list-style-type:square;">
				<li><code>#!URL!#</code></li>
				<li><code>#!TITLE!#</code></li>
				<li><code>#!DESCRIPTION!#</code></li>
				<li><code>#!SOURCE!#</code></li>
				<li><code>#!CLASS!#</code><em><small>Intended for the paragraph wrapper</small></em></li>
				</ul></blockquote>
				<h4 style="margin-bottom:0;padding-bottom:0;">Default HTML</h4><br />
				<code><?php echo htmlspecialchars($default_html); ?></code><br />
				<h4>Sponsored Link Styling</h4>
				<code>.lr-saved-link.sponsored</code> is the proper selector to use in your CSS.
				<h4>General Styling of Saved Links Output</h4>
				<p>Add custom styles to your theme's CSS based on selectors and structure above.</p>
			</div>
			
			<div id="mailchimp" class="card">
				<h3>MailChimp Integration</h3>
					<p style="margin-bottom:5px;">
						<label for="argo_link_roundups_use_mailchimp_integration">
							Enable MailChimp Integration?
							<input type="checkbox" name="argo_link_roundups_use_mailchimp_integration"
								<?php checked(get_option('argo_link_roundups_use_mailchimp_integration'), 'on', true); ?> />
						</label>
					</p>
					<p>
						<label for="argo_link_roundups_mailchimp_api_key">
							MailChimp API Key
							<input style="width: 300px;" type="text" name="argo_link_roundups_mailchimp_api_key"
								value="<?php echo get_option('argo_link_roundups_mailchimp_api_key'); ?>"
								placeholder="Mailchimp API Key" />
						</label>
					</p>
					<p><a href="http://kb.mailchimp.com/accounts/management/about-api-keys#Find-or-Generate-Your-API-Key">Find your MailChimp API Key</a></p>
			<?php if ((bool) get_option('argo_link_roundups_use_mailchimp_integration') && !empty($templates)) { ?>
				<h4>MailChimp Templates</h4>
					<select name="argo_link_mailchimp_template">
						<option value=""></option>
						<?php foreach ($templates['user'] as $key => $template) { ?>
							<option <?php selected(get_option('argo_link_mailchimp_template'), $template['id'], true); ?> value="<?php echo $template['id']; ?>" /><?php echo $template['name']; ?></option>
						<?php } ?>
					</select>
					<p>Choose a MailChimp template to use as the basis for Link Roundup email campaigns.</p>
			<?php } ?>
			<?php if ((bool) get_option('argo_link_roundups_use_mailchimp_integration') && !empty($lists)) { ?>
				<h4>MailChimp Lists</h4>
					<select name="argo_link_mailchimp_list">
						<option value=""></option>
						<?php foreach ($lists['data'] as $key => $list) { ?>
							<option <?php selected(get_option('argo_link_mailchimp_list'), $list['id'], true); ?> value="<?php echo $list['id']; ?>" /><?php echo $list['name']; ?></option>
						<?php } ?>
					</select>
					<p>Choose a MailChimp list that your Link Roundup email campaigns will be sent to.</p>
			<?php } ?>
				</div>


		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>
</div>
