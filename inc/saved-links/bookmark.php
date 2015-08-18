<?php
	//** WordPress Administration Bootstrap */
	include_once( ABSPATH  . 'wp-admin/admin.php' );
	?>
	<div id="icon-tools" class="icon32"><br></div><h2><?php _e( 'Add Save to Site Bookmark to Your Web Browser', 'link-roundups' ); ?></h2>

	<div class="tool-box">

	 <div class="card pressthis">
	<h3><?php _e( 'Save to Site', 'link-roundups' ) ?></h3>
	<p><?php _e( 'Save to Site is a tool that lets you send Saved Links to your WordPress Dashboard while browsing the web.', 'link-roundups' );?></p>
	<p><?php _e( 'Click the Save to Site bookmark and a new WordPress window will popup, attempting to prefill Title, URL and Source information.', 'link-roundups' ); ?></p>


	<form>
		<h3><?php _e( 'Install Save to Site', 'link-roundups' ); ?></h3>
		<input id="bookmarkName" class="bookmarkName" type="textbox" value="Save to Site" />
		<h4><?php _e( 'Browser Bookmarklet', 'link-roundups' ); ?></h4>
		<p><?php _e( 'Drag the Save to Site bookmarklet below to your web browser\'s Bookmarks Toolbar.<br /><em>If you can\'t drag, click the Clipboard.</em>', 'link-roundups' ); ?></p>

		<p class="pressthis-bookmarklet-wrapper">
			<a class="pressthis-bookmarklet" onclick="return false;" href="<?php echo Save_To_Site_Button::shortcut_link(); ?>"><span><?php _e( '<span class="replace-text">Save to Site</span>', 'link-roundups' ); ?></span></a>
			<button type="button" class="button button-secondary pressthis-js-toggle js-show-pressthis-code-wrap" aria-expanded="false" aria-controls="pressthis-code-wrap">
				<span class="dashicons dashicons-clipboard"></span>
				<span class="screen-reader-text"><?php _e( 'Copy <span class="replace-text">Save to Site</span> bookmarklet code', 'link-roundups' ) ?></span>
			</button>
		</p>

		<div class="hidden js-pressthis-code-wrap clear" id="pressthis-code-wrap">
			<p id="pressthis-code-desc">
				<?php _e( 'If you can&#8217;t drag the bookmarklet to your bookmarks, copy the following code and create a new bookmark. Paste the code into the new bookmark&#8217;s URL field.', 'link-roundups' ) ?>
			</p>
			<p>
				<textarea class="js-pressthis-code" rows="5" cols="120" readonly="readonly" aria-labelledby="pressthis-code-desc"><?php echo htmlspecialchars( get_shortcut_link() ); ?></textarea>
			</p>
		</div>

		<h4><?php _e( 'Direct link (best for mobile and tablets)', 'link-roundups' ); ?></h4>
		<p><?php _e( 'Follow the link to open Save to Site. Then add it to your device&#8217;s bookmarks or home screen.', 'link-roundups' ); ?></p>
		<p>
			<a class="button button-secondary" href="<?php echo Save_To_Site_Button::shortcut_link(); ?>"><?php _e( 'Open <span class="replace-text">Save to Site</span>', 'link-roundups' ) ?></a>
		</p>
		<script>
			jQuery( document ).ready( function( $ ) {
				var $showPressThisWrap = $( '.js-show-pressthis-code-wrap' );
				var $pressthisCode = $( '.js-pressthis-code' );

				$showPressThisWrap.on( 'click', function( event ) {
					var $this = $( this );

					$this.parent().next( '.js-pressthis-code-wrap' ).slideToggle( 200 );
					$this.attr( 'aria-expanded', $this.attr( 'aria-expanded' ) === 'false' ? 'true' : 'false' );
				});

				// Select Press This code when focusing (tabbing) or clicking the textarea.
				$pressthisCode.on( 'click focus', function() {
					var self = this;
					setTimeout( function() { self.select(); }, 50 );
				});
				
				$( '#bookmarkName' ).on( 'click change input keyup', function() {
					var self = this;
					var customName = self.document.getElementById('#bookmarkName').value;
					self.alert(customName);
					
					$('span.replace-text').replaceWith('<span class="replaced">David Ryan</span>');
					
				});

			});
			
		</script>
	</form>
</div>
	</div>
<?php
