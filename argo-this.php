<?php
/**
 * Class: Argo_This_Button
 * Contains all relevant functionality to implement "Argo Link This!" button.
 * 
 * @since 0.3
 * 
 * Structure based on:
 * @see https://wordpress.org/plugins/press-this-reloaded/
 */

class Argo_This_Button {

	private static $title;
	private static $description;
	private static $url;
	private static $source;
	private static $imgUrl;
	const plugin_domain = 'press-this-reloaded';

	function init() {

		add_filter( 'redirect_post_location', array( __CLASS__, 'redirect' ) );

		add_action( 'admin_print_scripts-post-new.php', array( __CLASS__, 'add_scripts' ) );
		add_action( 'admin_print_scripts-post.php', array( __CLASS__, 'add_scripts' ) );

		if ( isset( $_GET[ 'u' ] ) ) {
			
			add_action( 'load-post-new.php', array( __CLASS__, 'load' ) );
			add_action( 'load-post.php', array( __CLASS__, 'load' ) );

			// remove_action( 'media_buttons', 'media_buttons',1 );
			// add_action( 'media_buttons', array( __CLASS__, 'press_this_media_buttons' ), 11 );

		}
		elseif ( isset($_REQUEST[ 'ajax' ]) ) { 
			// this is for video only
			// from the original plugin, currently not really used.
			add_action( 'load-post-new.php', array( __CLASS__, 'manageAjaxRequest' ) );
		}

	}

	public static function manageAjaxRequest() {

		$selection = '';
		if ( !empty($_GET['s']) ) {
			$selection = str_replace('&apos;', "'", stripslashes($_GET['s']));
			$selection = trim( htmlspecialchars( html_entity_decode($selection, ENT_QUOTES) ) );
		}

		if ( ! empty($selection) ) {
			$selection = preg_replace('/(\r?\n|\r)/', '</p><p>', $selection);
			$selection = '<p>' . str_replace('<p></p>', '', $selection) . '</p>';
		}

		$url = isset( $_GET[ 'u' ] ) ? esc_url( $_GET[ 'u' ] ) : '';
		$image = isset( $_GET[ 'i' ] ) ? $_GET[ 'i' ] : '';


		if ( !empty( $_REQUEST[ 'ajax' ] ) ) {
			switch ( $_REQUEST[ 'ajax' ] ) {
				case 'video':
					?>
					<script type="text/javascript">
						/* <![CDATA[ */
						jQuery('.select').click(function() {
							append_editor(jQuery('#embed-code').val());
							jQuery('#extra-fields').hide();
							jQuery('#extra-fields').html('');
							hideToolbar(false);
						});
						jQuery('.close').click(function() {
							jQuery('#extra-fields').hide();
							jQuery('#extra-fields').html('');
							hideToolbar(false);
						});
						/* ]]> */
					</script>
					<div class="postbox">
						<h2><label for="embed-code"><?php _e( 'Embed Code', self::plugin_domain) ?></label></h2>
						<div class="inside">
							<textarea name="embed-code" id="embed-code" rows="8" cols="40"><?php echo esc_textarea( $selection ); ?></textarea>
							<p id="options"><a href="#" class="select button"><?php _e( 'Insert Video', self::plugin_domain ); ?></a> <a href="#" class="close button"><?php _e( 'Cancel', self::plugin_domain ); ?></a></p>
						</div>
					</div>
					<?php
					break;

				case 'photo_thickbox':
					?>
					<script type="text/javascript">
						/* <![CDATA[ */
						jQuery('.cancel').click(function() {
							tb_remove();
						});
						jQuery('.select').click(function() {
							image_selector(this);
						});
						/* ]]> */
					</script>
					<h3 class="tb"><label for="tb_this_photo_description"><?php _e( 'Description', self::plugin_domain ) ?></label></h3>
					<div class="titlediv">
						<div class="titlewrap">
							<input id="tb_this_photo_description" name="photo_description" class="tb_this_photo_description tbtitle text" onkeypress="if (event.keyCode == 13)
								image_selector(this);" value="<?php echo esc_attr( self::$title ); ?>"/>
						</div>
					</div>

					<p class="centered">
						<input type="hidden" name="this_photo" value="<?php echo esc_attr( $image ); ?>" id="tb_this_photo" class="tb_this_photo" />
						<a href="#" class="select">
							<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( __( 'Click to insert.', self::plugin_domain ) ); ?>" title="<?php echo esc_attr( __( 'Click to insert.', self::plugin_domain ) ); ?>" />
						</a>
					</p>

					<p id="options"><a href="#" class="select button"><?php _e( 'Insert Image', self::plugin_domain ); ?></a> <a href="#" class="cancel button"><?php _e( 'Cancel', self::plugin_domain ); ?></a></p>
					<?php
					break;
				case 'photo_images':

					/**
					 * Retrieve all image URLs from given URI.
					 *
					 * @package WordPress
					 * @subpackage Press_This
					 * @since 2.6.0
					 *
					 * @param string $uri
					 * @return string
					 */
					function get_images_from_uri( $uri ) {
						$uri = preg_replace( '/\/#.+?$/', '', $uri );
						if ( preg_match( '/\.(jpe?g|jpe|gif|png)\b/i', $uri ) && !strpos( $uri, 'blogger.com' ) )
							return "'" . esc_attr( html_entity_decode( $uri ) ) . "'";
						$content = wp_remote_fopen( $uri );
						if ( false === $content )
							return '';
						$host = parse_url( $uri );
						$pattern = '/<img ([^>]*)src=(\"|\')([^<>\'\"]+)(\2)([^>]*)\/*>/i';
						$content = str_replace( array( "\n", "\t", "\r" ), '', $content );
						preg_match_all( $pattern, $content, $matches );
						if ( empty( $matches[ 0 ] ) )
							return '';
						$sources = array( );
						foreach ( $matches[ 3 ] as $src ) {
							// if no http in url
							if ( strpos( $src, 'http' ) === false )
							// if it doesn't have a relative uri
								if ( strpos( $src, '../' ) === false && strpos( $src, './' ) === false && strpos( $src, '/' ) === 0 )
									$src = 'http://' . str_replace( '//', '/', $host[ 'host' ] . '/' . $src );
								else
									$src = 'http://' . str_replace( '//', '/', $host[ 'host' ] . '/' . dirname( $host[ 'path' ] ) . '/' . $src );
							$sources[ ] = esc_url( $src );
						}
						return "'" . implode( "','", $sources ) . "'";
					}

					$url = wp_kses( urldecode( $url ), null );
					echo 'new Array(' . get_images_from_uri( $url ) . ')';
					break;

				case 'photo_js':
					?>
					// gather images and load some default JS
					var last = null
					var img, img_tag, aspect, w, h, skip, i, strtoappend = "",hasImages = true;
					if(photostorage == false) {
					var my_src = eval(
					jQuery.ajax({
					type: "GET",
					url: "<?php echo esc_url( $_SERVER[ 'PHP_SELF' ] ); ?>",
					cache : false,
					async : false,
					data: "ajax=photo_images&u=<?php echo urlencode( $url ); ?>",
					dataType : "script"
					}).responseText
					);
					if(my_src.length == 0) {
					var my_src = eval(
					jQuery.ajax({
					type: "GET",
					url: "<?php echo esc_url( $_SERVER[ 'PHP_SELF' ] ); ?>",
					cache : false,
					async : false,
					data: "ajax=photo_images&u=<?php echo urlencode( $url ); ?>",
					dataType : "script"
					}).responseText
					);
					if(my_src.length == 0) {
					hasImages = false;
					strtoappend = '<?php _e( 'Unable to retrieve images or no images on page.', self::plugin_domain ); ?>';
					}
					}
					}
					for (i = 0; i < my_src.length; i++) {
					img = new Image();
					img.src = my_src[i];
					img_attr = 'id="img' + i + '"';
					skip = false;

					maybeappend = '<a href="?ajax=photo_thickbox&amp;i=' + encodeURIComponent(img.src) + '&amp;u=<?php echo urlencode( $url ); ?>&amp;height=400&amp;width=640" title="" class="thickbox"><img src="' + img.src + '" ' + img_attr + '/></a>';

					if (img.width && img.height) {
					if (img.width >= 30 && img.height >= 30) {
					aspect = img.width / img.height;
					scale = (aspect > 1) ? (71 / img.width) : (71 / img.height);

					w = img.width;
					h = img.height;

					if (scale < 1) {
					w = parseInt(img.width * scale);
					h = parseInt(img.height * scale);
					}
					img_attr += ' style="width: ' + w + 'px; height: ' + h + 'px;"';
					strtoappend += maybeappend;
					}
					} else {
					strtoappend += maybeappend;
					}
					}

					function pick(img, desc) {
					if (img) {
					if('object' == typeof jQuery('.photolist input') && jQuery('.photolist input').length != 0) length = jQuery('.photolist input').length;
					if(length == 0) length = 1;
					jQuery('.photolist').append('<input name="photo_src[' + length + ']" value="' + img +'" type="hidden"/>');
					jQuery('.photolist').append('<input name="photo_description[' + length + ']" value="' + desc +'" type="hidden"/>');
					insert_editor( "\n\n" + encodeURI('<p style="text-align: center;"><a href="<?php echo $url; ?>"><img src="' + img +'" alt="' + desc + '" /></a></p>'));
					}
					return false;
					}

					function image_selector(el) {
					var desc, src


					desc = jQuery('#tb_this_photo_description').val() || '';
					src = jQuery('#tb_this_photo').val() || ''

					tb_remove();
					pick(src, desc);
					jQuery('#extra-fields').hide();
					jQuery('#extra-fields').html('');
					hideToolbar(false);
					return false;
					}




					jQuery('#extra-fields').html('<div class="postbox"><h2><?php _e( 'Add Photos', self::plugin_domain ); ?> <small id="photo_directions">(<?php _e( "click images to select", self::plugin_domain ) ?>)</small></h2><div class="inside"><div class="titlewrap"><div id="img_container"></div></div><p id="options"><a href="#" class="close button"><?php _e( 'Cancel', self::plugin_domain ); ?></a><a href="#" class="refresh button"><?php _e( 'Refresh', self::plugin_domain ); ?></a></p></div>');


					//var display = hasImages?'':' style="display:none;"';

						jQuery('#img_container').html(strtoappend);
						<?php
						break;
				}
				die();
			}
		}

	function press_this_media_buttons() {

		?>
		<?php _e( 'Add media from page:', self::plugin_domain ); ?>

		<?php
		if ( current_user_can( 'upload_files' ) ) {
			?>
			<a id="photo_button" title="<?php esc_attr_e( 'Insert an Image', self::plugin_domain ); ?>" href="#">
				<img alt="<?php esc_attr_e( 'Insert an Image', self::plugin_domain ); ?>" src="<?php echo esc_url( admin_url( 'images/media-button-image.gif?ver=20100531' ) ); ?>"/></a>
			<?php
		}
		?>
		<a id="video_button" title="<?php esc_attr_e( 'Embed a Video', self::plugin_domain ); ?>" href="#"><img alt="<?php esc_attr_e( 'Embed a Video', self::plugin_domain ); ?>" src="<?php echo esc_url( admin_url( 'images/media-button-video.gif?ver=20100531' ) ); ?>"/></a>
		<div id="waiting" style="display: none"><span class="spinner"></span> <span><?php esc_html_e( 'Loading...', self::plugin_domain ); ?></span></div>

		<div id="extra-fields" style='clear:both; display:none'>

		</div>

		<?php
	}

	function add_scripts() {
		
		global $post_type;
		
		if( 'argolinks' != $post_type )
			return;

		wp_enqueue_script( 'argo-this', plugin_dir_url( __FILE__ ) . 'js/argo-this.js', 'jquery' );

		$type = "";

		if ( preg_match( "/youtube\.com\/watch/i", self::$url ) )
			$type = 'video';
		elseif ( preg_match( "/vimeo\.com\/[0-9]+/i", self::$url ) )
			$type = 'video';
		elseif ( preg_match( "/flickr\.com/i", self::$url ) )
			$type = 'photo';

		$data = array(
			'argoThisUrl' => admin_url( 'post-new.php' ),
			'description' => self::$description,
			'url' => self::$url,
			'urlEncoded' => urlencode( self::$url ),
			'type' => $type
		);

		wp_localize_script( 'argo-this', 'argothis', $data );

	}

	static function shortcut_link() {

		// This is the default 'Press This!' button link.
		$shortcut_link = get_shortcut_link();

		$post_type = 'argolinks';

		// We alter it for our post type.
		$shortcut_link = str_replace('press-this.php', 'post-new.php', $shortcut_link);
		$shortcut_link = str_replace('width=720', 'width=840', $shortcut_link);
		$shortcut_link = str_replace('post-new.php', "post-new.php?post_type=$post_type", $shortcut_link);
		$shortcut_link = str_replace('?u=', '&u=', $shortcut_link);

		return $shortcut_link;

	}

	/**
	 * Passes ?u= query var through login process
	 * 
	 * @since 0.3
	 */
	function redirect( $location ) {

		$referrer = wp_get_referer();

		if ( false !== strpos( $referrer, '?u=' ) || false !== strpos( $referrer, '&u=' ) )
			$location = add_query_arg( 'u', 1, $location );

		return $location;
	
	}

	/**
	 * Sets up default values for new argolink.
	 * 
	 * @since 0.3
	 */
	function load() {

		// Default source
		self::$url = isset( $_GET[ 'u' ] ) ? esc_url( $_GET[ 'u' ] ) : '';
		self::$url = wp_kses( urldecode( self::$url ), null );

		// Get meta data from url.
		$meta = argo_get_page_info(self::$url);
		$meta = $meta['meta'];

		// Default title.
		self::$title = '';
		if( !empty($meta['ogp']['title']) ) {
			self::$title = $meta['ogp']['title'];
		} else {
			self::$title = isset( $_GET[ 't' ] ) ? trim( strip_tags( html_entity_decode( stripslashes( $_GET[ 't' ] ), ENT_QUOTES ) ) ) : '';
		}

		$selection = '';
		if ( !empty( $_GET[ 's' ] ) ) {
			$selection = str_replace( '&apos;', "'", stripslashes( $_GET[ 's' ] ) );
			$selection = trim( htmlspecialchars( html_entity_decode( $selection, ENT_QUOTES ) ) );
		}

		// Default description.
		self::$description = '';
		if ( !empty( $selection ) ) {
			self::$description = $selection;
		} else if( !empty($meta['ogp']['description']) ) {
			self::$description = $meta['ogp']['description'];
		}  

		// Default source.
		self::$source = '';
		if( !empty($meta['ogp']['site_name']) ) {
			self::$source = $meta['ogp']['site_name'];
		} else if( self::$url ) {
			$url = parse_url(self::$url);
			self::$source = $url['host'];
		}
		
		self::$imgUrl = '';
		if( !empty($meta['ogp']['image']) ) {
			self::$imgUrl = $meta['ogp']['image'];
		}

		add_action( 'admin_print_styles', array( __CLASS__, 'style' ) );

		add_filter( 'default_title', array( __CLASS__, 'default_title' ) );
		add_filter( 'default_argo_link_url', array( __CLASS__, 'default_link' ));
		add_filter( 'default_argo_link_description', array( __CLASS__, 'default_description' ) );
		add_filter( 'default_argo_link_source', array( __CLASS__, 'default_source' ) );

		add_filter( 'show_admin_bar', '__return_false' );

		self::manageAjaxRequest();
	}

	function default_title( $title = null ) {
		return self::$title;
	}

	function default_description( $description = null ) {
		return self::$description;
	}

	function default_link( $link = null ) {
		return self::$url;
	}

	function default_source( $source = null ) {
		return self::$source;
	}

	function default_imgUrl( $imgUrl = null ) {
		return self::$imgUrl;
	}

	function style() {
		?>
		<style type="text/css">
			/* hide the header */
			#wphead, #screen-meta, #icon-edit, h2 {display: none !important}

			/* hide the menu */
			#wpbody {margin-left:7px !important}

			/* hide the footer */
			#footer {display: none !important}
			#wpcontent {padding-bottom: 0 !important}
			#normal-sortables {margin-bottom: -20px !important}
		</style>
		<?php
	}

}

Argo_This_Button::init();



?>

<?php
/**
 * Plugin Name: Featured Image Via URL
 * Plugin URI: http://blog.tsaiid.idv.tw/project/wordpress-plugins/featured-image-via-url/
 * Description: Allows you to set featured image via URL. The image will be fetched back and saved into the Media Library with thumbnails.
 * Version: 0.1
 * Author: Tsai I-Ta
 * Author URI: http://blog.tsaiid.idv.tw/
 * Modified from Auto Post Thumbnail by adityamooley
 */

/*  Copyright 2013 (ittsai@gmail.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

add_action('submitpost_box', 'fivu_thumbnail_meta_box');
add_action('save_post', 'fivu_publish_post');

// This hook will now handle all sort publishing including posts, custom types, scheduled posts, etc.
//add_action('transition_post_status', 'fivu_check_required_transition');


/*
*
*/
function fivu_thumbnail_meta_box() {
	$screen = get_current_screen();
	$post_type = $screen->post_type;
	if ( current_theme_supports( 'post-thumbnails', $post_type ) && post_type_supports( $post_type, 'thumbnail' ) )
		add_meta_box('postimageviaurldiv', __('Featured Image via URL'), 'fivu_thumbnail_meta_box_html', null, 'side', 'low');
}

function fivu_thumbnail_meta_box_html() {
	echo '<label for="featured_image_url">';
 	_e("URL of featured image", 'fivu_textdomain' );
	echo '</label> ';
	echo '<input type="text" id="featured_image_url" name="featured_image_url" value="" size="30" />';
}

/**
 * Function to check whether scheduled post is being published. If so, apt_publish_post should be called.
 *
 * @param $new_status
 * @param $old_status
 * @param $post
 * @return void
 */
function fivu_check_required_transition($new_status='', $old_status='', $post='') {
    global $post_ID; // Using the post id from global reference since it is not available in $post object. Strange!

    if ('publish' == $new_status) {
        fivu_publish_post($post_ID);
    }
}


function fivu_publish_post($post_id) {
//		error_log('<pre>'.print_r($_POST['featured_image_url']).'</pre>');
//		exit(1);
	$imageUrl = $_POST['featured_image_url'];

    // First check whether Post Thumbnail is already set for this post.
    if (get_post_meta($post_id, '_thumbnail_id', true) || get_post_meta($post_id, 'skip_post_thumb', true)) {
        return;
    }

    // Generate thumbnail

    $thumb_id = fivu_generate_post_thumb($imageUrl, $post_id);

    // If we succeed in generating thumg, let's update post meta
    if ($thumb_id) {
        update_post_meta( $post_id, '_thumbnail_id', $thumb_id );
    }
}

function fivu_generate_post_thumb ($imageUrl, $post_id) {
	// Get image from url & check if is an available image

	// Get the file name
	$filename = substr($imageUrl, (strrpos($imageUrl, '/'))+1);

    if (!(($uploads = wp_upload_dir(current_time('mysql')) ) && false === $uploads['error'])) {
        return null;
    }

    // Generate unique file name
    $filename = wp_unique_filename( $uploads['path'], $filename );

    // Move the file to the uploads dir
    $new_file = $uploads['path'] . "/$filename";

    if (!ini_get('allow_url_fopen')) {
        $file_data = curl_get_file_contents($imageUrl);
    } else {
        $file_data = @file_get_contents($imageUrl);
    }

    if (!$file_data) {
        return null;
    }

    file_put_contents($new_file, $file_data);

    // Set correct file permissions
    $stat = stat( dirname( $new_file ));
    $perms = $stat['mode'] & 0000666;
    @ chmod( $new_file, $perms );

    // Get the file type. Must to use it as a post thumbnail.
    $wp_filetype = wp_check_filetype( $filename, $mimes );

    extract( $wp_filetype );

    // No file type! No point to proceed further
    if ( ( !$type || !$ext ) && !current_user_can( 'unfiltered_upload' ) ) {
        return null;
    }

    // Compute the URL
    $url = $uploads['url'] . "/$filename";

    // Construct the attachment array
    $attachment = array(
        'post_mime_type' => $type,
        'guid' => $url,
        'post_parent' => null,
        'post_title' => $imageTitle,
        'post_content' => '',
    );

    $thumb_id = wp_insert_attachment($attachment, $file, $post_id);
    if ( !is_wp_error($thumb_id) ) {
        require_once(ABSPATH . '/wp-admin/includes/image.php');

        // Added fix by misthero as suggested
        wp_update_attachment_metadata( $thumb_id, wp_generate_attachment_metadata( $thumb_id, $new_file ) );
        update_attached_file( $thumb_id, $new_file );

        return $thumb_id;
    }

    return null;

}

/**
 * Function to fetch the contents of URL using curl in absense of allow_url_fopen.
 *
 * Copied from user comment on php.net (http://in.php.net/manual/en/function.file-get-contents.php#82255)
 */
function curl_get_file_contents($URL) {
    $c = curl_init();
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($c, CURLOPT_URL, $URL);
    $contents = curl_exec($c);
    curl_close($c);

    if ($contents) {
        return $contents;
    }

    return FALSE;
}

