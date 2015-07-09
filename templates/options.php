<?php
$default_html = <<<EOT
<p class='link-roundup'><a href='#!URL!#'>#!TITLE!#</a> &ndash; <span class='description'>#!DESCRIPTION!#</span> <em>#!SOURCE!#</em></p>
EOT;
?>
<div class="wrap">
	<h2>Link Roundups Options</h2>

	<?php settings_errors(); ?>
	
	<h4>Documentation</h4>
	Read about these settings and using this plugin in <a href="https://github.com/INN/link-roundups/tree/master/docs">the documentation on GitHub</a>.

	<form method="post" action="options.php">
		<?php settings_fields( 'argolinkroundups-settings-group' ); ?>
		<?php do_settings_fields( 'argolinkroundups-settings-group', null ); ?>
		<div class="options-wrapper">
			<div class="card">
				<h3>Rename Link Roundups</h3>
				<h4>Custom URL Slug</h4>
				<p>Provide a new post slug (lowercase, no spaces, dashes allowed)</p>
				<input type="text" name="argo_link_roundups_custom_url" value="<?php echo get_option('argo_link_roundups_custom_url'); ?>" />
				<br /><br /><code style="display:block;"><?php echo get_site_url(); ?>/<strong>roundup</strong>/random-saved-link/</code><br />
			    	<strong>IMPORTANT</strong>: Whenever you define a new custom slug, you <strong>must</strong> update your Permalink Settings (Settings --> Permalinks).
				<h4>Custom Name</h4>
				<h5 style="margin-bottom:0;padding-bottom:0;">Singular (Post)</h5>
				<input type="text" name="link_roundups_custom_name_singular" value="<?php echo get_option('link_roundups_custom_name_singular'); ?>" />
				<h5 style="margin-bottom:0;padding-bottom:0;">Plural (Posts)</h5>
				<input type="text" name="link_roundups_custom_name_plural" value="<?php echo get_option('link_roundups_custom_name_plural'); ?>" />

			</div>
			<div class="card">
				<h3>Custom HTML</h3>
				<p>Modify the output of Saved Links sent to the editor.</p>
				<textarea name="argo_link_roundups_custom_html" cols='70' rows='6' ><?php echo (get_option('argo_link_roundups_custom_html') != "" ? get_option('argo_link_roundups_custom_html')	: $default_html); ?></textarea>
				<em>Single quotes are REQUIRED in Custom HTML. Double quotes will be automatically converted to single quotes before use.</em><br /><br />
				The following tags will be replaced with the URL, Title, Description, and Source automatically when the Saved Link is pushed into the Post Editor.<br />
				<blockquote><ul style="list-style-type:square;">
				<li><code>#!URL!#</code></li>
				<li><code>#!TITLE!#</code></li>
				<li><code>#!DESCRIPTION!#</code></li>
				<li><code>#!SOURCE!#</code></li>
				</ul></blockquote>
				<h4 style="margin-bottom:0;padding-bottom:0;">Default HTML</h4><br />
				<code><?php echo htmlspecialchars($default_html); ?></code><br />
				<h4>Style Links</h4>
				Add custom styles to your theme's CSS based on selectors and structure above.
			</div>
			
			<div class="card">
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
					<p><a href="http://kb.mailchimp.com/accounts/management/about-api-keys#Find-or-Generate-Your-API-Key">Finding your key</a></p>
				</div>
			<?php if ((bool) get_option('argo_link_roundups_use_mailchimp_integration') && !empty($templates)) { ?>
			<div class="card">
				<h3>MailChimp Templates</h3>
					<select name="argo_link_mailchimp_template">
						<option value=""></option>
						<?php foreach ($templates['user'] as $key => $template) { ?>
							<option <?php selected(get_option('argo_link_mailchimp_template'), $template['id'], true); ?> value="<?php echo $template['id']; ?>" /><?php echo $template['name']; ?></option>
						<?php } ?>
					</select>
					<p>Choose a MailChimp template to use as the basis for Argo Link Roundup email campaigns.</p>
			</div>
			<?php } ?>
			<?php if ((bool) get_option('argo_link_roundups_use_mailchimp_integration') && !empty($lists)) { ?>
			<div class="card"
				<h3>MailChimp Lists</h3>
					<select name="argo_link_mailchimp_list">
						<option value=""></option>
						<?php foreach ($lists['data'] as $key => $list) { ?>
							<option <?php selected(get_option('argo_link_mailchimp_list'), $list['id'], true); ?> value="<?php echo $list['id']; ?>" /><?php echo $list['name']; ?></option>
						<?php } ?>
					</select>
					<p>Choose a MailChimp list that your Argo Link Roundup email campaigns will be sent to.</p>
			</div>
			<?php } ?>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>
</div>
