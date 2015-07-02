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
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Custom URL Slug</th>
				<td><input type="text" name="argo_link_roundups_custom_url" value="<?php echo get_option('argo_link_roundups_custom_url'); ?>" /></td>
			</tr>
			<tr>
				<td></td>
				<td><p>Overwrites <code>roundup</code> as the slug in Link Roundup URLs.</p><br />
			    <strong>IMPORTANT</strong>: After saving a custom slug, you <strong>must</strong> update your Permalink Settings (Settings --> Permalinks).
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Custom HTML</th>
				<td><textarea name="argo_link_roundups_custom_html" cols='100' rows='5' ><?php echo (get_option('argo_link_roundups_custom_html') != "" ? get_option('argo_link_roundups_custom_html')	: $default_html); ?></textarea></td>
			</tr>
			<tr>
				<td></td>
				<td>
				<h4 style="margin-top:0;padding-top:0;">Modify the output of Saved Links sent to the editor.</h4>
				<em>Single quotes are REQUIRED in Custom HTML. Double quotes will be automatically converted to single quotes before use.</em><br /><br />
				The following tags will be replaced with the URL, Title, Description, and Source automatically when the Saved Link is pushed into the Post Editor.<br />
				<ul style="list-style-type:square;">
				<li><code>#!URL!#</code></li>
				<li><code>#!TITLE!#</code></li>
				<li><code>#!DESCRIPTION!#</code></li>
				<li><code>#!SOURCE!#</code></li>
				</ul>
				<br /><br />
				<h4 style="margin-bottom:0;padding-bottom:0;">Default HTML</h4><br />
				<code><?php echo htmlspecialchars($default_html); ?></code><br />
				<h5>Style Links</h5>
				Add custom styles to your theme's CSS based on selectors and structure above.
				</td>
			</tr>
			<tr>
				<th scope="row">MailChimp Integration</th>
				<td>
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
				</td>
			</tr>
			<?php if ((bool) get_option('argo_link_roundups_use_mailchimp_integration') && !empty($templates)) { ?>
			<tr>
				<th scope="row">MailChimp Templates</th>
				<td>
					<select name="argo_link_mailchimp_template">
						<option value=""></option>
						<?php foreach ($templates['user'] as $key => $template) { ?>
							<option <?php selected(get_option('argo_link_mailchimp_template'), $template['id'], true); ?> value="<?php echo $template['id']; ?>" /><?php echo $template['name']; ?></option>
						<?php } ?>
					</select>
					<p>Choose a MailChimp template to use as the basis for Argo Link Roundup email campaigns.</p>
				</td>
			</tr>
			<?php } ?>
			<?php if ((bool) get_option('argo_link_roundups_use_mailchimp_integration') && !empty($lists)) { ?>
			<tr>
				<th scope="row">MailChimp Lists</th>
				<td>
					<select name="argo_link_mailchimp_list">
						<option value=""></option>
						<?php foreach ($lists['data'] as $key => $list) { ?>
							<option <?php selected(get_option('argo_link_mailchimp_list'), $list['id'], true); ?> value="<?php echo $list['id']; ?>" /><?php echo $list['name']; ?></option>
						<?php } ?>
					</select>
					<p>Choose a MailChimp list that your Argo Link Roundup email campaigns will be sent to.</p>
				</td>
			</tr>
			<?php } ?>
		</table>

		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>
</div>
