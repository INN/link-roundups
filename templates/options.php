<?php
$default_html = <<<EOT
<p class='link-roundup'><a href='#!URL!#'>#!TITLE!#</a> &ndash; <span class='description'>#!DESCRIPTION!#</span> <em>#!SOURCE!#</em></p>
EOT;
?>
<div class="wrap">
	<h2>Argo Link Roundups</h2>

	<?php settings_errors(); ?>

	<form method="post" action="options.php">
		<?php settings_fields( 'argolinkroundups-settings-group' ); ?>
		<?php do_settings_fields( 'argolinkroundups-settings-group', null ); ?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Custom Url Slug</th>
				<td><input type="text" name="argo_link_roundups_custom_url" value="<?php echo get_option('argo_link_roundups_custom_url'); ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row">Custom HTML</th>
				<td><textarea name="argo_link_roundups_custom_html" cols='100' rows='5' ><?php echo (get_option('argo_link_roundups_custom_html') != "" ? get_option('argo_link_roundups_custom_html')	: $default_html); ?></textarea></td>
			</tr>
			<tr>
				<td></td>
				<td>
				<em>(You will need to use single quotes in your html above; all double quotes will be automatically converted to single quotes before use)</em><br />
				You can use the above field to customize the html that is output for each link.	The following tags will be replaced with the url, title, description, and source automatically when the link is pushed into the editor.<br />
				#!URL!#, #!TITLE!#, #!DESCRIPTION!#, #!SOURCE!#<br />
				The current default html for reference is:<br />
				<?php echo htmlspecialchars($default_html); ?><br />
				<em>(Please note that you will have to update your style.css file for your theme to style your new html)</em><br />
				</td>
			</tr>
			<tr>
				<th scope="row">MailChimp Integration</th>
				<td>
					<p>
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
		</table>

		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>
</div>
