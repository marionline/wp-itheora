<?php
/* 
Plugin Name: itheora in wordpress
Plugin URI: http://github.com/marionline/wp-itheora
Description: With this plugin you can use itheora script (included in this plugin) to add theora video on your blog.
Author: Mario Santagiuliana
Version: v0.1.2
Author URI: http://www.marionline.it/
License: GPL version 3

	Copyright 2010  Mario Santagiuliana  (email : mario at marionline.it)

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/*****************************************************
 *
 *
 *  Start wp-itheora section for administration menu
 *
 *
 *****************************************************/
class WPItheora {

	private $_wsh_raw_parts=array();
	private $_domain = 'wpitheora';
	private $_dir;

	protected $_itheora_config;

	/**
	 * __CONSTRUCT 
	 * 
	 * @access protected
	 * @return void
	 */
	function __CONSTRUCT() {
		$this->_dir = dirname(plugin_basename(__FILE__));
		load_plugin_textdomain( $this->_domain, 'wp-content/plugins/'.$this->_dir.'/lang/');

		// Retrive itheora_config
		$this->_itheora_config = get_option( 'wp_itheora_options' );

	}

	/**
	 * file_size 
	 * 
	 * @param mixed $size 
	 * @access private
	 * @return void
	 */
	private function file_size( $size ) {
		$filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
		return $size ? round($size/pow(1024, ($i = floor(log((double)$size, 1024)))), 2) . $filesizename[$i] : '0 Bytes';
	}

	/**
	 * currentPage 
	 * 
	 * @access private
	 * @return void
	 */
	private function currentPage() {
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on")
			$pageURL .= "s";
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80")
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		else
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		return $pageURL;
	}

	/**
	 * getAmazonS3 
	 * 
	 * @access protected
	 * @return AmazonS3 object
	 */
	protected function getAmazonS3() {
		require_once( dirname( __FILE__ ) . '/itheora/lib/aws-sdk/sdk.class.php' );

		// Create AmazonS3 object
		$s3 = new AmazonS3( $this->_itheora_config['aws_key'], $this->_itheora_config['aws_secret_key'] );
		$s3->set_region( $this->_itheora_config['s3_region'] );
		$s3->set_vhost( $this->_itheora_config['s3_vhost'] );

		return $s3;
	}

	/**
	 * getItheora 
	 * 
	 * @access protected
	 * @return itheora object
	 */
	protected function getItheora() {
		require_once( dirname( __FILE__ ) . '/itheora/lib/itheora.class.php' );

		$itheora = new itheora();
		$itheora->setVideoDir( $this->_itheora_config['video_dir'] );

		return $itheora;
	}

	function itheora_admin() {
		add_action( 'admin_menu' , array(&$this, 'wp_itheora_menu') );
	}

	/**
	 * wp_itheora_activation 
	 * 
	 * When the plugin is activated this function run
	 */
	function wp_itheora_activation() {
		/**
		 * Add MP4 source to VideoJS?
		 */
		$itheora_config['MP4_source'] = true;
		/**
		 * Add WEBM source to VideoJS?
		 */
		$itheora_config['WEBM_source'] = true;
		/**
		 * Use VideoJS flash fallback?
		 */
		$itheora_config['flash_fallback'] = true;

		/**
		 * AmazonS3 config options  
		 */
		$itheora_config['bucket_name']    = 'media.marionline.it';
		$itheora_config['s3_region']      = 'AmazonS3::REGION_EU_W1';
		$itheora_config['s3_vhost']       = 'media.marionline.it';
		$itheora_config['aws_key']        = 'Amazon web service key';
		$itheora_config['aws_secret_key'] = 'Amazon web service secret key';

		$itheora_config['video_dir']      = realpath(dirname(__FILE__) . '/../../../wp-content') . '/wp-itheora-data';
		$itheora_config['video_url']      = WP_CONTENT_URL . '/wp-itheora-data';

		if( !is_dir( $itheora_config['video_dir'] ) )
			mkdir($itheora_config['video_dir']);

		if( !get_option( 'wp_itheora_options' ) )
			update_option( 'wp_itheora_options', $itheora_config );
	}

	/**
	 * wp_itheora_deactivation 
	 * 
	 * @access public
	 * @return void
	 */
	function wp_itheora_deactivation() {
	}

	/**
	 * wp_itheora_shortcode 
	 * 
	 * @access public
	 * @return void
	 */
	function wp_itheora_shortcode($atts) {
		$options = shortcode_atts(array(
			'video' => 'example',
			'width' => null,
			'height' => null,
			'remote' => false,
			'skin' => '',
			'alternativeName' => null,
		), $atts);

		// If no width or height are passed I use the image width and height
		if( $options['width'] === null || $options['height'] === null ) {
			if($options['useFilesInCloud']) {
				// Inizialise AmazonS3 and itheora
				$s3 = $this->getAmazonS3();
				$itheora = new itheora(60, null, $s3, $this->_itheora_config);
			} else {
				$itheora = $this->getItheora();
			}
			$itheora->setVideoName($options['video']);
			$posterSize = $itheora->getPosterSize();
		}

		if($options['width'] !== null) {
			$width_style = 'width: ' . $options['width'] . 'px;';
			$width_url = '&amp;w=' . $options['width'];
		} else {
			$width_style = 'width: ' . $posterSize[0] . 'px;';
			$width_url = '&amp;w=' . $posterSize[0];
		}

		if($options['height'] !== null) {
			$height_style = 'height: ' . $options['height'] . 'px;';
			$height_url = '&amp;h=' . $options['height'];
		} else {
			$height_style = 'height: ' . $posterSize[1] . 'px;';
			$height_url = '&amp;h=' . $posterSize[1];
		}

		if($options['remote']) {
			$key = 'r';
		} else {
			$key = 'v';
		}

		if(isset($options['skin']) && $options['skin'] !== null)
			$skin = '&amp;skin=' . $options['skin'];
		else
			$skin = '';

		if(isset($options['alternativeName']) && $options['alternativeName'] !== null){
			$id = $options['alternativeName'];
			$name = $options['alternativeName'];
		} else {
			$id = $options['video'];
			$name = $options['video'];
		}

		return '
			<object id="' . $id . '" name="' . $name . '" class="itheora3-fork" type="application/xhtml+xml" data="' . WP_PLUGIN_URL. '/' . $this->_dir . '/itheora/index.php?' . $key . '=' . $options['video'] . $width_url . $height_url . $skin . '" style="' . $width_style . ' ' . $height_style . '"> 
			</object>
			<!--[if IE]>
			<iframe id="' . $id . '" name="' . $name . '" class="itheora3-fork" type="application/xhtml+xml" data="' . WP_PLUGIN_URL. '/' . $this->_dir . '/itheora/index.php?' . $key . '=' . $options['video'] . $width_url . $height_url . $skin . '" style="' . $width_style . ' ' . $height_style . '" allowtransparency="true" frameborder="0" >
			</iframe>
			<![endif]-->
			';
	}

	/**
	 * wp_itheora_menu 
	 * 
	 * @access public
	 * @return void
	 */
	function wp_itheora_menu() {
		//minimal capability
		$mincap=9;

		$page = array();
		$page[] = add_menu_page('itheora', 'itheora', $mincap, basename(__FILE__), array(&$this, 'wp_itheora_infopage'), WP_PLUGIN_URL.'/'.$this->_dir.'/img/fish_theora_org.png');
		$page[] = add_submenu_page(basename(__FILE__), __('Wordpress itheora administration', $this->_domain), __('itheora info', $this->_domain), $mincap, basename(__FILE__),  array(&$this, 'wp_itheora_infopage'));
		$page[] = add_submenu_page(basename(__FILE__),__('Wordpress itheora administration', $this->_domain), __('Options', $this->_domain), $mincap, 'wp-itheora/options',  array(&$this, 'wp_itheora_config_player'));
		$page[] = add_submenu_page(basename(__FILE__),__('Wordpress itheora administration', $this->_domain), __('Video', $this->_domain), $mincap, 'wp-itheora/video',  array(&$this, 'wp_itheora_video'));

		for($i = 0; $i < count($page); $i++) {
			add_action( "admin_print_scripts-".$page[$i], array(&$this, 'wp_itheora_admin_head') );
		}
		add_action('admin_print_scripts-'.$page[3], array(&$this, 'ajax_local_file'));
		add_action('admin_print_scripts-'.$page[3], array(&$this, 'ajax_object_amazon'));

		add_action( 'admin_init', array( &$this, 'wp_itheora_register_settings' ) );

		// Include this jQuery library
		wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_style('wp-itheora-ui-stylesheet', WP_PLUGIN_URL . '/' . $this->_dir . '/css/smoothness/jquery-ui-1.8.11.custom.css' );
	}

	/**
	 * wp_itheora_admin_head 
	 * my stylesheet for wp-itheora section
	 */
	function wp_itheora_admin_head() {
		echo "<link rel='stylesheet' href='".WP_PLUGIN_URL."/".$this->_dir."/style.css' type='text/css'/>";
	}

	/**
	 * wp_itheora_header 
	 * add image to the top of wp-itheora pages
	 */
	protected function wp_itheora_header() {
		echo "\n<div class=\"itheora-admin\">\n";
		echo "<img src=\"".WP_PLUGIN_URL."/".$this->_dir."/img/titre.jpg\" alt=\"\" />\n";
		echo "<img src=\"".WP_PLUGIN_URL."/".$this->_dir."/img/logo.png\" alt=\"\" />\n";
		echo "</div>\n";
	}

	/**
	 * wp_itheora_register_settings 
	 * 
	 * @access public
	 * @return void
	 */
	function wp_itheora_register_settings() {
		register_setting( 'wp_itheora-group', 'wp_itheora_options', array( &$this, 'wp_itheora_settings_validate' ) );
	}

	/**
	 * wp_itheora_settings_validate 
	 * validation input
	 * 
	 * @param array $input 
	 * @return array
	 */
	function wp_itheora_settings_validate( $input ) {
		$input['MP4_source']     = ( $input['MP4_source'] == 1 ? true : false );
		$input['WEBM_source']    = ( $input['WEBM_source'] == 1 ? true : false );
		$input['flash_fallback'] = ( $input['flash_fallback'] == 1 ? true : false );
		$input['bucket_name']    = $input['bucket_name'];
		$input['s3_region']      = ( $input['s3_region'] ? $input['s3_region'] : 'AmazonS3::REGION_EU_W1' );
		$input['s3_vhost']       = $input['s3_vhost'];
		$input['aws_key']        = ( $input['aws_key'] ? wp_filter_nohtml_kses( $input['aws_key'] ) : 'Amazon web service key' );
		$input['aws_secret_key'] = ( $input['aws_secret_key'] ? wp_filter_nohtml_kses($input['aws_secret_key'] ) : 'Amazon web service secret key' );
		$input['video_dir']      = ( ( substr($input['video_dir'], -1 ) == '/')  ? substr( $input['video_dir'], 0, -1 ) : $input['video_dir'] );
		$input['video_url']      = ( ( substr($input['video_url'], -1 ) == '/' ) ? substr( $input['video_url'], 0, -1 ) : $input['video_url'] );

		return $input;
	}

	/**
	 * wp_itheora_config_player 
	 * call when the user whant to config the basic settings of itheora player
	 */
	function wp_itheora_config_player() {
		$this->wp_itheora_header();
		echo '<h2>' . __( 'WP-itheora configuration page' ) . '</h2>';
		?>
		<form method="post" action="options.php">
			<?php settings_fields( 'wp_itheora-group' ); ?>
			<table>
				<tr> 
					<td><?php _e( 'Include Mp4 source:' ); ?></td>
					<td><input type="radio" name="wp_itheora_options[MP4_source]" value="1" <?php checked( true, $this->_itheora_config['MP4_source'] ); ?> /> <?php _e( 'Yes' ); ?></td>
					<td><input type="radio" name="wp_itheora_options[MP4_source]" value="0" <?php checked( false, $this->_itheora_config['MP4_source'] ); ?> /> <?php  _e( 'No' ); ?></td>
				</tr>
				<tr>
					<td><?php _e( 'Include WebM source:' ); ?></td>
					<td><input type="radio" name="wp_itheora_options[WEBM_source]" value="1" <?php checked( true, $this->_itheora_config['WEBM_source'] ); ?> /> <?php _e( 'Yes' ); ?></td>
					<td><input type="radio" name="wp_itheora_options[WEBM_source]" value="0" <?php checked( false, $this->_itheora_config['WEBM_source'] ); ?> /> <?php  _e( 'No' ); ?></td>
				</tr>
				<tr>
					<td><?php _e( 'Use flash fallback:' ); ?></td>
					<td><input type="radio" name="wp_itheora_options[flash_fallback]" value="1" <?php checked( true, $this->_itheora_config['flash_fallback'] ); ?> /> <?php _e( 'Yes' ); ?></td>
					<td><input type="radio" name="wp_itheora_options[flash_fallback]" value="0" <?php checked( false, $this->_itheora_config['flash_fallback'] ); ?> /> <?php  _e( 'No' ); ?></td>
				</tr>

			</table>
				<p>
					<?php _e( 'Bucket name:' ); ?>
					<input type="text" name="wp_itheora_options[bucket_name]" value="<?php echo $this->_itheora_config['bucket_name']; ?>" />
				</p>
				<p>
					<?php _e( 'Bucket region:' ); ?>
					<select name="wp_itheora_options[s3_region]">
						<option value="AmazonS3::REGION_US_E1">US Standard</option>
						<option value="AmazonS3::REGION_US_W1">US West (Northern California)</option>
						<option value="AmazonS3::REGION_EU_W1">EU (Ireland)</option>
						<option value="AmazonS3::REGION_APAC_NE1">Asia Pacific (Tokyo)</option>
						<option value="AmazonS3::REGION_APAC_SE1">Asia Pacific (Singapore)</option>
					</select>
				</p>
				<p>
					<?php _e( 'Set bucket virtual host:' ); ?>
					<input type="text" name="wp_itheora_options[s3_vhost]" value="<?php echo $this->_itheora_config['s3_vhost']; ?>" />
				</p>
				<p>
					<?php _e( 'Amazon Web Service Key:' ); ?>
					<input type="text" name="wp_itheora_options[aws_key]" value="<?php echo $this->_itheora_config['aws_key']; ?>" />
				</p>
				<p>
					<?php _e( 'Amazon Web Service Secret Key:' ); ?>
					<input type="text" name="wp_itheora_options[aws_secret_key]" value="<?php echo $this->_itheora_config['aws_secret_key']; ?>" />
				</p>
				<p>
					<?php _e( 'Set local video directory:' ); ?>
					<input type="text" name="wp_itheora_options[video_dir]" value="<?php echo $this->_itheora_config['video_dir']; ?>" />
				</p>
				<p>
					<?php _e( 'Change video Url:' ); ?>
					<input type="text" name="wp_itheora_options[video_url]" value="<?php echo $this->_itheora_config['video_url']; ?>" />
				</p>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Save' ); ?>" />
				</p>
		</form>
		<?php
	}

	/**
	 * wp_itheora_infopage 
	 * Display information about itheora
	 */
	function wp_itheora_infopage() {
		$this->wp_itheora_header();
		echo "
		<div id=\"wp-itheora-info\">
			<h1>ITheora</h1>
			<p>".__("ITheora is a PHP script allowing you to broadcast ogg/theora/vorbis only videos (and audios) files. It's simple to install and use. It may suit the usual blogger or the expert webmaster.", $this->_domain)."</p>

			<p>".__("Itheora is different from other software allowing to stream videos, because it offers other features for the user visiting the website:", $this->_domain)."</p>
			<ul>
			<li>".__("choose between watching videos in an embedded player (much like a flash player), and watch the video in your favorite media player (using a plugin)", $this->_domain)."</li>
			<li>".__("download the video file", $this->_domain)."</li>
			<li>".__("share the video by using the HTML source code available", $this->_domain)."</li>
			<li>".__("display in full screen mode", $this->_domain)."</li>
			<li>".__("very quick display of the video.", $this->_domain)."</li>

			</ul>
			<p>".__("Itheora has real improvements for the webmaster :", $this->_domain)."</p>
			<ul>
			<li>".__("displaying a thumbnail when the player is being launched", $this->_domain)."</li>
			<li>".__("almost complete interface customisation (skins, options, and languages)", $this->_domain)."</li>
			<li>".__("very simple XHTML-compliant code, easy to configure", $this->_domain)."</li>
			<li>".__("download possible by peer-to-peer (Bittorrent or Coral)", $this->_domain)."</li>
			<li>".__("streaming in real time and playing external videos (on an other server with http or ftp protocol)", $this->_domain)."</li>
			<li>".__("playlist (free format .xspf) or ogg podcast can be used", $this->_domain)."</li>
			<li>".__("support the html5 tag video", $this->_domain)."</li>

			<li>".__("a code generator make easier the configuration", $this->_domain)."</li>
			<li>".__("fall back on flash is possible", $this->_domain)."</li>
			</ul>
			<h1>".__("You can tube, but I theora", $this->_domain)."</h1>
			<p>".__("This software is like an alternative to the proprietary Flash players (file format and software), and is based on the Cortado java applet (ITheora is not a simple wrapper for Cortado), and helps the spreading of ogg/theora free (as in freedom ;) ) format.", $this->_domain)."</p>
			<p>".__("In the same time, it allows you to be independant from online video services, such as youtube and dailymotion, because you can share the source code of the video from a blogger to another.", $this->_domain)."</p>
			<h1>".__("Theora Sea", $this->_domain)."</h1>
			<p>".__("Theora Sea is a sharing video area. This area is a simple list of links which target to hosted video, you cannot upload videos on this site. However, it make easier to generate podcast.", $this->_domain)."</p>
			<p style=\"text-align: center\"><a href=\"http://theorasea.org\"><img src=\"".WP_PLUGIN_URL."/".$this->_dir."/img/logo.png\" alt=\"\" /></a></p>
			<p>".__("So you can submit videos that you host yourself, yet know that you are the unique liable of what you broadcast. Check that you respect copyright low of your country.", $this->_domain)."</p>
		</div>
		";
	} /** end wp_ithoera_infopage() */

	/**
	 * ajax_local_file 
	 * 
	 * @access public
	 * @return void
	 */
	function ajax_local_file() {
	 	?>
		<script type="text/javascript">

 			function edit_local_file(value, obj, dir) {
 				var data = {
					action   : 'edit_local_file',
					filename : value
				};

				jQuery.post(ajaxurl, data, function(response) {

					jQuery('#'+obj.id).append(response);

					jQuery('#wp-itheora-edit-form').dialog({
						autoOpen : true,
						modal    : true,
						title    : '<?php _e('Edit'); ?> : ' + value,
						buttons  : {
							'OK': function() {
								var newdata = {
									action   : 'edit_local_file',
									filename : value,
									newname  : jQuery('#wp-itheora-local-name').val(),
									dirname  : dir
								};
								jQuery.post(ajaxurl, newdata, function(newresponse) {
									if(newresponse == true)
										location.reload();
									else
										alert(newresponse);
								});
							}
						},
						close    : function() { jQuery('#wp-itheora-edit-form').remove() }
					});
				});

				return false;
			}

			function delete_local_file(item, sub_item) {
				if(sub_item==undefined)
					var data = {
						action   : 'delete_local_file',
						item     : item,
					};
				else
					var data = {
						action   : 'delete_local_file',
						item     : item,
						sub_item : sub_item
					};

				if(showNotice.warn()==true)
					jQuery.post(ajaxurl, data, function(delete_response) {
						if(delete_response == true)
							location.reload();
						else
							alert(delete_response);
					});

				return false;
			}

		</script>

		<?php
	}

	/**
	 * edit_local_file 
	 * 
	 * @access public
	 * @return void
	 */
	function edit_local_file() {
		if( isset($_POST['newname'] ) ) {
			if( strstr( $_POST['newname'], ' ' ) ) {
				// Show error
				echo __( 'The new name containe invalid character or space.' );
			} else {
				// Rename the file, if is a directory rename directory and all files inside
				$directory = $this->_itheora_config['video_dir'] . '/' . $_POST['filename'];
				if( is_dir( $directory ) ) {
					$newdirectory = $this->_itheora_config['video_dir'] . '/' . $_POST['newname'];
					rename( $directory, $newdirectory );
					$objects = scandir( $newdirectory );
					foreach ( $objects as $object ) {
						if( $object != "." && $object != ".." ) {
							rename( $newdirectory . '/' . $object, $newdirectory . '/'. $_POST['newname'] . '.' . pathinfo( $object, PATHINFO_EXTENSION ) );
						}
					}
				} else {
					$directory = $this->_itheora_config['video_dir'] . '/' . $_POST['dirname'];
					$file =  $directory . '/' . $_POST['filename'];
					$newfile = $directory . '/' . $_POST['newname'];
					rename( $file, $newfile );
				}
				echo true;
			}
		} else {
			// response with the form
			?>
			<div id="wp-itheora-edit-form">
				<p><?php _e( 'Renaming the directory will rename all the inside files.' ); ?></p>
				<p><?php _e( 'Renaming a file and will not be the same name of the directory name can\'t be use by itheora3-fork.' ); ?></p>
				<form>
					<fieldset>
						<label for="wp-itheora-local-name"><?php _e('Rename it:'); ?></label>
						<input type="text" name="wp-itheora-local-name" id="wp-itheora-local-name" class="text ui-widget-content ui-corner-all" value="<?php echo $_POST['filename']; ?>" />
					</fieldset>
				</form>
			</div>
			<?php
		}
		die;
	}

	/**
	 * change_reduce_redundacy 
	 * 
	 * @access public
	 * @return void
	 */
	function change_reduce_redundacy() {
		$s3 = $this->getAmazonS3();
		$object = $s3->get_object_metadata( $this->_itheora_config['bucket_name'], $_POST['s3object'] );

		if( $object['StorageClass'] == 'STANDARD' )
			$response = $s3->change_storage_redundancy ( $this->_itheora_config['bucket_name'], $_POST['s3object'], AmazonS3::STORAGE_REDUCED ); 
		else
			$response = $s3->change_storage_redundancy ( $this->_itheora_config['bucket_name'], $_POST['s3object'], AmazonS3::STORAGE_STANDARD ); 

		if( $response->isOK() )
			echo true;
		else
			echo false;

		die;
	}

	/**
	 * array_search_value 
	 * Take from: http://www.php.net/manual/en/function.array-search.php#92991
	 * with a fix
	 * 
	 * @param mixed $needle 
	 * @param array $haystack 
	 * @param boolean $arraykey 
	 * @access private
	 * @return key_id | false on failure
	 */
	private function array_search_value( $needle, $haystack, $arraykey = false ) {
		foreach( $haystack as $current_key => $value ) {

			if( $arraykey ){

				if( $needle == $value[$arraykey] ){
					return $current_key;
				}

				if( is_array( $value[$arraykey] ) ) {
					if( $this->array_search_value( $needle, $value[$arraykey] ) == true ) {
						return $current_key;
					}
				}

			}else{

				if( $needle == $value )
					return $value;

				if( is_array( $value[$arraykey] ) ) {
					if( $this->array_search_value( $needle, $value ) == true ) {
						return $current_key;
					}
				}
			}
		}
		return false;
	}

	/**
	 * ajax_object_amazon 
	 * 
	 * @access public
	 * @return void
	 */
	function ajax_object_amazon() {
	?>
		<script type="text/javascript">
			function object_metadata(value, obj) {
				var data = {
					action   : 'get_object_metadata',
					s3object : value
				};

				jQuery.post(ajaxurl, data, function(response) {

					jQuery('#'+obj.id).append(response);

					jQuery('#wp-itheora-object-metadata').dialog({
						autoOpen : true,
						modal    : true,
						title    : '<?php _e('Edit'); ?> : ' + value,
						width    : '600px',
						buttons  : {
							'<?php _e('Close'); ?>': function() {
								jQuery('#wp-itheora-object-metadata').dialog('close');
								jQuery('#wp-itheora-object-metadata').remove();
							}
						},
						close    : function() { 
							jQuery('#wp-itheora-object-metadata').remove();
							jQuery('#wp-itheora-alert').remove();
						},
						open     : function() {
							jQuery('#wp-itheora-object-metadata').append('<div id="wp-itheora-alert"></div>');
						}
					});

					jQuery('#wp-itheora-alert').dialog({
							autoOpen : false,
							title    : '<?php _e( 'Wait response...' ); ?>',
							open     : function() {
								jQuery('#wp-itheora-alert').append('<img id="wp-itheora-alert-gif" style="margin-left: 45%;" src="<?php echo WP_PLUGIN_URL . '/' . $this->_dir . '/img/progress.gif'; ?>" />');
							},
							close    : function() {
								jQuery('#wp-itheora-alert').text('');
								jQuery('#wp-itheora-alert').dialog('option', 'buttons', { });
							}
					});

					jQuery('.wp-itheora-object-acl').click(function() {
						var acldata = {
							action   : 'set_object_metadata',
							s3object : value,
							acl      : jQuery('input:radio[name=wp-itheora-object-acl]:checked').val()
						};
						jQuery('#wp-itheora-alert').dialog('open');
						jQuery.post(ajaxurl, acldata, function(aclresponse) {

							if(!aclresponse) {
								jQuery('#wp-itheora-alert').text('<?php _e( 'Error on change acl rule, impossible to do that.' ); ?>');
							} else {
								jQuery('#wp-itheora-alert').text('<?php _e( 'ACL property change successfully.' ); ?>');
							}
							jQuery('#wp-itheora-alert').append('<p style="text-align: right" id="wp-itheora-button"><button type="button" class="ui-state-default ui-corner-all"><?php _e( 'Ok' ); ?></button></p>'); 
							jQuery('#wp-itheora-button').click(function(){ jQuery('#wp-itheora-alert').dialog('close'); });
						});
					});

					jQuery('#wp-itheora-object-content-type-button').click(function(event) {
						event.preventDefault();
						var ctdata = {
							action      : 'set_object_metadata',
							s3object    : value,
							ContentType : jQuery('#wp-itheora-object-content-type').val()
						};
						jQuery('#wp-itheora-alert').dialog('open');
						jQuery.post(ajaxurl, ctdata, function(ctresponse) {
							if(!ctresponse) {
								jQuery('#wp-itheora-alert').text('<?php _e( 'Error on change Content Type, impossible to do that.' ); ?>');
							} else {
								jQuery('#wp-itheora-alert').text('<?php _e( 'Content Type change correctly. Please check the access of your file.' ); ?>');
							}
							jQuery('#wp-itheora-alert').append('<p style="text-align: right" id="wp-itheora-button"><button type="button" class="ui-state-default ui-corner-all"><?php _e( 'Close' ); ?></button></p>'); 
							jQuery('#wp-itheora-button').click(function(){ jQuery('#wp-itheora-alert').dialog('close'); });
						});
					});
					jQuery('#wp-itheora-object-change-key-button').click(function(event) {
						event.preventDefault();
						var keydata = {
							action   : 'set_object_metadata',
							s3object : value,
							key      : jQuery('#wp-itheora-object-change-key').val()
						};
						jQuery('#wp-itheora-alert').dialog('open');
						jQuery.post(ajaxurl, keydata, function(ctresponse) {
							if(!ctresponse) {
								jQuery('#wp-itheora-alert').text('<?php _e( 'Error on change the object name, impossible to do that.' ); ?>');
								jQuery('#wp-itheora-alert').append('<p style="text-align: right" id="wp-itheora-button"><button type="button" class="ui-state-default ui-corner-all"><?php _e( 'Ok' ); ?></button></p>'); 
								jQuery('#wp-itheora-button').click(function(){ jQuery('#wp-itheora-alert').dialog('close'); });
							} else {
								jQuery('#wp-itheora-alert').text('<?php _e( 'Action complete, the page will be reload in 5 seconds' ); ?>');
								setTimeout('location.reload()',5000);
							}
						});
					});

				});
				return false;
			}

			function change_redundancy(value,obj) {
				var data = {
					action: 'change_reduce_redundacy',
					s3object: value
				};

				jQuery('#'+obj.id).append('<div id="wp-itheora-storage-info"></div>');

				jQuery('#wp-itheora-storage-info').dialog({
						autoOpen : true,
						title    : '<?php _e( 'Wait response...' ); ?>',
						open     : function() {
							jQuery('#wp-itheora-storage-info').append('<img id="wp-itheora-storage-info-gif" style="margin-left: 45%;" src="<?php echo WP_PLUGIN_URL . '/' . $this->_dir . '/img/progress.gif'; ?>" />');
						},
						close    : function() { jQuery('#wp-itheora-storage-info').remove() }
				});
 
				jQuery.post(ajaxurl, data, function(response) {
					if(!response) {
						jQuery('#wp-itheora-storage-info').text('<?php _e( 'Impossible to change redundacy storage type.' ); ?>');
					} else {
						jQuery('#wp-itheora-storage-info').text('<?php _e( 'Storage class type change successfully.' ); ?>');
					}
					jQuery('#wp-itheora-storage-info').append('<p style="text-align: right" id="wp-itheora-storage-button"><button type="button" class="ui-state-default ui-corner-all"><?php _e( 'Close' ); ?></button></p>'); 
					jQuery('#wp-itheora-storage-button').click(function(){ jQuery('#wp-itheora-storage-info').dialog('close'); });
				});
			}

			function delete_object(value, prefix) {
				if(prefix==undefined )
					var data = {
						action: 'delete_object',
						deleteObject: value
					};
				else
					var data = {
						action: 'delete_object',
						deletePrefix: value
					};

				if(showNotice.warn()==true)
					jQuery.post(ajaxurl, data, function(response) {
						if(response==true)
							location.reload();
						else
							alert(response);
					});

				return false;
			}
		</script>
	<?php
	}

	/**
	 * get_object_metadata 
	 * 
	 * @access public
	 * @return void
	 */
	function get_object_metadata() {
		$s3 = $this->getAmazonS3();
		$filename = trim( $_POST['s3object'] );
		$object = $s3->get_object_metadata( $this->_itheora_config['bucket_name'], $filename );
		$public_acl_id = $this->array_search_value( 'http://acs.amazonaws.com/groups/global/AllUsers', $object['ACL'], 'id' );
		?>
		<div id="wp-itheora-object-metadata">
			<form>
				<fieldset>
					<label for="wp-itheora-object-acl"><?php _e( 'Make this file public readable:' ); ?></label>
					<input type="radio" name="wp-itheora-object-acl" id="wp-itheora-object-acl-1" class="wp-itheora-object-acl radio ui-widget-content ui-corner-all" value="1" <?php checked( 'READ', $object['ACL'][$public_acl_id]['permission'] ); ?> ><?php _e( 'Yes' ); ?></input>
					<input type="radio" name="wp-itheora-object-acl" id="wp-itheora-object-acl-0" class="wp-itheora-object-acl radio ui-widget-content ui-corner-all" value="0" <?php if( $public_acl_id === false ) echo 'checked="checked" '; ?>><?php _e( 'No' ); ?></input>
					<br />
					<label for="wp-itheora-object-content-type"><?php _e( 'Change the content type:' ); ?></label>
					<input type="text" name="wp-itheora-object-content-type" id="wp-itheora-object-content-type" class="wp-itheora-object-content-type text ui-widget-content ui-corner-all" value="<?php echo $object['ContentType']; ?>" />
					<button class="button" id="wp-itheora-object-content-type-button"><?php _e( 'Change' ); ?></button>
					<br />
					<label for="wp-itheora-object-change-key"><?php _e( 'Rename or move the file:' ); ?></label>
					<input type="text" name="wp-itheora-object-change-key" id="wp-itheora-object-change-key" class="wp-itheora-object-change-key text ui-widget-content ui-corner-all" value="<?php echo $object['Key']; ?>" />
					<button class="button" id="wp-itheora-object-change-key-button"><?php _e( 'Rename/Move' ); ?></button>
				</fieldset>
			</form>
			<p><?php _e( 'The owner of this file is:' ); ?> <?php echo $object['Owner']['DisplayName']; ?></p>
		</div>
		<?php
		die;
	}

	/**
	 * set_object_metadata 
	 * 
	 * @access public
	 * @return void
	 */
	function set_object_metadata() {
		$s3 = $this->getAmazonS3();
		$filename = trim( $_POST['s3object'] );

		if( isset( $_POST['acl'] ) ) {

			if( $_POST['acl'] )

				$response = $s3->set_object_acl( $this->_itheora_config['bucket_name'], $filename, AmazonS3::ACL_PUBLIC );

			else

				$response = $s3->set_object_acl( $this->_itheora_config['bucket_name'], $filename, AmazonS3::ACL_PRIVATE );

		} elseif( isset( $_POST['ContentType'] ) ) {

			$response = $s3->change_content_type( $this->_itheora_config['bucket_name'], $filename, trim( $_POST['ContentType'] ) );

		} elseif( isset( $_POST['key'] ) ) {
			$key = trim( $_POST['key'] );

			if( $s3->if_object_exists( $this->_itheora_config['bucket_name'], $key ) ) {
				echo false;
				die;
			} else {
				$response = $s3->copy_object(
					array ( 
						'bucket'   => $this->_itheora_config['bucket_name'],
						'filename' => $filename
					),
					array (
						'bucket'   => $this->_itheora_config['bucket_name'],
						'filename' => $key
					),
					array( 
						'metadataDirective' => 'COPY',
						'storage'           => AmazonS3::STORAGE_REDUCED
					)
				);
				// If ok, delete old object
				if( $response->isOK() )
					$response = $s3->delete_object( $this->_itheora_config['bucket_name'], $filename );
			}

		}

		if( $response->isOK() )
			echo true;
		else
			echo false;

		die;
	}

	/**
	 * delete_object 
	 * 
	 * @access public
	 * @return void
	 */
	function delete_object() {
		// Create AmazonS3 object
		$s3 = $this->getAmazonS3();

		if( isset( $_POST['deleteObject'] ) ) {
			// Delete object
			$response = $s3->delete_object( $this->_itheora_config['bucket_name'], $_POST['deleteObject'] );
			$results = $response->isOK();
		} elseif( isset( $_POST['deletePrefix'] ) ) {
			// Delete all object with provided prefix
			$results = $s3->delete_all_objects(  $this->_itheora_config['bucket_name'], '/' . str_replace( '/', '\/', $_POST['deletePrefix'] ) . '.*/' );
		}

		if($results)
			echo true;
		else
			echo __( 'An error occours on object deletion' );

		die;
	}

	/**
	 * rrmdir 
	 * Remove directory and his content recursive
	 * 
	 * @param mixed $dir 
	 * @access private
	 * @return void
	 */
	private function rrmdir( $dir ) {
		if ( is_dir( $dir ) ) {
			$objects = scandir( $dir );
			foreach ( $objects as $object ) {
				if ( $object != "." && $object != ".." ) {
					if ( filetype( $dir."/".$object ) == "dir" ) 
						$this->rrmdir( $dir."/".$object ); 
					else 
						unlink( $dir."/".$object );
				}
			}
			reset( $objects );
			$return = rmdir( $dir );
		}
		
		return $return;
	}

	/**
	 * delete_local_file
	 * Delete local file
	 * 
	 * @param mixed $itheora 
	 * @access private
	 * @return void
	 */
	function delete_local_file() {
		$local_file = basename( $_POST['item'] );
		if( isset( $_POST['sub_item'] ) && $_POST['sub_item'] != '' ) {
			$parentdir = basename( $local_file );
			$local_file = $_POST['sub_item'];
		} else {
			$parentdir = false;
		}

		if( $parentdir )
			$to_be_remove = $this->_itheora_config['video_dir']  . '/' . $parentdir . '/' . $local_file;
		else
			$to_be_remove = $this->_itheora_config['video_dir'] . '/' . $local_file;

		if( is_dir( $to_be_remove ) )
			$return = $this->rrmdir( $to_be_remove );
		else 
			$return = unlink( $to_be_remove );

		if( $return )
			echo true;
		else
			echo __( 'Some error occurs' );

		die;
	}

	/**
	 * addfile 
	 * Add file to local server from upload form
	 * 
	 * @access private
	 * @return void
	 */
	private function addfile() {
		if( is_uploaded_file( $_FILES['file']['tmp_name'] ) ) {

			$tmp_name = $_FILES['file']['tmp_name'];
			$name = $_FILES['file']['name'];

			if( !is_dir( $this->_itheora_config['video_dir'] . '/' . pathinfo( $name, PATHINFO_FILENAME ) ) ) {
				mkdir( $this->_itheora_config['video_dir'] . '/' . pathinfo( $name, PATHINFO_FILENAME ) );
			}
			move_uploaded_file($tmp_name, $this->_itheora_config['video_dir'] . '/' . pathinfo( $name, PATHINFO_FILENAME ) . '/'. $name);

		} else {
			$message = sprintf( __( "Impossible to upload this file: %s." ), $_FILES['file']['name'] );
		}

		return $message;
	}

	/**
	 * wp_itheora_video 
	 * Video Administration page
	 */
	function wp_itheora_video() {
		// Set WP-itheora head
		$this->wp_itheora_header();

		// Create itheora object
		$itheora = $this->getItheora();

		// Create AmazonS3 object
		$s3 = $this->getAmazonS3();

		// If is send a FILE
		if( isset( $_FILES['file'] ) ) {
			$message = $this->addfile();
		}
		?>

		<?php if( isset( $message )): ?>
		<!-- Message from form -->
		<div id="wp-itheora-message">
			<?php echo $message; ?>
		</div>

		<?php endif; ?>

		<!-- START LOCAL FILE TABLE -->
		<table class="widefat fixed wp-itheora-table" cellspacing="0">
			<thead>
				<tr>
					<th><?php echo __( 'File', $this->_domain ); ?></th>
					<th><?php echo __( 'Size', $this->_domain ); ?></th>
					<th><?php echo __( 'Actions', $this->_domain ); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th><?php echo __( 'File', $this->_domain ); ?></th>
					<th><?php echo __( 'Size', $this->_domain ); ?></th>
					<th><?php echo __( 'Actions', $this->_domain ); ?></th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				$content = scandir( $itheora->getVideoDir() );
				$html = '';
				if( $content ) {
					foreach( $content as $id => $item ) {
						$subdir = $itheora->getVideoDir() . '/' . $item;
						if( $id > 1 ) {
							$html .= '<tr>' . PHP_EOL;
							if( is_dir( $subdir ) ) {
								$html .= '<td class="itheora-video-name"><strong>' . $item . ':</strong></td>' . PHP_EOL;
							} else {
								$html .= '<td class="itheora-video-name"><strong>' . $item . '</strong></td>' . PHP_EOL;
							}
							if( is_dir( $subdir ) ) {
								$html .= '<td class="itheora-video-size"> - </td>' . PHP_EOL;
								$html .= '<td class="wp-itheora-row-actions"><a id="wp-itheora-ldir-' . str_replace( ' ', '-', $item ) . '" onclick="return edit_local_file(\'' . $item . '\', this)" href="">' . __( 'Edit' ) . '</a> - <a class="submitdelete" onclick="return delete_local_file(\'' . $item . '\');" href="">' . __( 'Delete' ) . '</a></td>' . PHP_EOL;
							} else {
								$html .= '<td class="itheora-video-size">' . $this->file_size( filesize( $itheora->getVideoDir() . '/' . $item ) ) .'</td>' . PHP_EOL;
								$html .= '<td class="wp-itheora-row-actions"><a id="wp-itheora-lfile' . str_replace( ' ', '-', $item) . '" onclick="return edit_local_file(\'' . $item . '\', this)" href="">' . __( 'Edit' ) . '</a> - <a onclick="return delete_local_file(\'' . $item . '\');" href="">' . __( 'Delete' ) . '</a></td>' . PHP_EOL;
							}
							$html .= '</tr>' . PHP_EOL;
							if( is_dir( $subdir ) ) {
								$subcontent = scandir( $itheora->getVideoDir() . '/' . $item );
								if( $subcontent ) {
									foreach( $subcontent as $sub_id => $sub_item ) {
										if( $sub_id > 1 ) {
											$html .= '<tr class="itheora-local-files">' . PHP_EOL;
											$html .= '<td>' . $sub_item . '</td>' . PHP_EOL;
											$html .= '<td class="itheora-video-size">' . $this->file_size( filesize( $itheora->getVideoDir() . '/' . $item . '/' . $sub_item ) ) .'</td>' . PHP_EOL;
											$html .= '<td class="wp-itheora-row-actions"><a id="wp-itheora-sublfile' . str_replace( array( '.', ' ' ), '-', $sub_item ) . '" onclick="return edit_local_file(\'' . $sub_item . '\', this, \'' . $item . '\')" href="">' . __( 'Edit' ) . '</a> - <a onclick="return delete_local_file(\'' . $item . '\', \'' . $sub_item . '\');" href="">' . __( 'Delete' ) . '</a></td>' . PHP_EOL;
											$html .= '</tr>' . PHP_EOL;
										}
									}
								}
							}
						}
					}
				}
				$html .= '</tbody>' . PHP_EOL;
				echo $html;
				?>
		</table>
		<!-- END LOCAL FILE TABLE -->

		<hr />

		<h3><?php _e( 'Upload File Locally' ); ?></h3>
		<?php if( isset( $error_message ) ) echo $error_message; ?>
		<form action="" method="post" enctype="multipart/form-data">

			<p><?php _e( 'Upload file' ); ?>: <input type="file" name="file" /><input type="submit" name="submit" value="<?php _e( 'Upload' ); ?>" class="button" /></p>
			<p>
				<?php
				$max_upload = (int)(ini_get('upload_max_filesize'));
				$max_post = (int)(ini_get('post_max_size'));
				$memory_limit = (int)(ini_get('memory_limit'));
				$upload_mb = min($max_upload, $max_post, $memory_limit);

				printf( __( 'Max file size that can be upload: %s Mb.' ), $upload_mb );
				?>
			</p>
			<input name="MAX_FILE_SIZE" type="hidden" value="<?php echo $upload_mb * 1048576; ?>" />
		</form>

		<hr />
		<h2><?php _e( 'List of remote files:' ); ?></h2>
		<?php
			$object_list = $s3->get_object_list( $this->_itheora_config['bucket_name'] );
			$objects = $s3->list_objects( $this->_itheora_config['bucket_name'], array( 'delimiter' => '/' ) );
		?>
		<!-- START AMAZON S3 TABLE -->
		<table class="widefat fixed wp-itheora-table" cellspacing="0">
			<thead>
				<tr>
					<th><?php echo __( 'File', $this->_domain ); ?></th>
					<th><?php echo __( 'Size', $this->_domain ); ?></th>
					<th><?php echo __( 'Last modify', $this->_domain ); ?></th>
					<th><?php echo __( 'Actions', $this->_domain ); ?></th>
					<th><?php echo __( 'Reduce redundacy storage', $this->_domain ); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th><?php echo __( 'File', $this->_domain ); ?></th>
					<th><?php echo __( 'Size', $this->_domain ); ?></th>
					<th><?php echo __( 'Last modify', $this->_domain ); ?></th>
					<th><?php echo __( 'Actions', $this->_domain ); ?></th>
					<th><?php echo __( 'Reduce redundacy storage', $this->_domain ); ?></th>
				</tr>
			</tfoot>
			<?php foreach( $objects->body->Contents as $object ) : ?>
				<tr>
					<td><a href="http://<?php if( $this->_itheora_config['vhost'] != '' ) { echo $this->_itheora_config['vhost']; } else { echo $this->_itheora_config['bucket_name']; } echo '/' . $object->Key; ?>"><?php echo $object->Key; ?></a></td>
					<td><?php echo $this->file_size( $object->Size ); ?></td>
					<td><?php echo date_i18n( 'r', strtotime( $object->LastModified ), true ); ?></td>
					<td class="wp-itheora-row-actions"><a id="<?php echo str_replace( array( ' ', '.', '/', ), '-' , $object->Key ) ; ?>" onclick="return object_metadata(' <?php echo $object->Key ;?>', this)" href=""><?php _e( 'Edit' ); ?></a> - <a onclick="return delete_object('<?php echo $object->Key; ?>')" href=""><?php _e( 'Delete' ); ?></a></td>
					<td class="wp-itheora-row-storagetype"><input id="wp-itheora-row-storagetype<?php echo str_replace(array('.', ' ', '/'), '-', $object->Key); ?>" onclick="change_redundancy('<?php echo $object->Key; ?>', this);" type="checkbox" value="<?php echo $object->Key; ?>" <?php checked( 'REDUCED_REDUNDANCY', $object->StorageClass); ?> /></td>
				</tr>
			<?php endforeach; ?>

			<?php foreach( $objects->body->CommonPrefixes as $object ) : ?>
				<tr>
					<td><strong><?php echo $object->Prefix; ?></strong></td>
					<td> - </td>
					<td> - </td>
					<td class="wp-itheora-row-actions"><a id="<?php echo str_replace( array( ' ', '.', '/', ), '-' , $object->Prefix ) ; ?>" onclick="return object_metadata(' <?php echo $object->Prefix ;?>', this)" href=""><?php _e( 'Edit' ); ?></a> - <a onclick="return delete_object('<?php echo $object->Prefix; ?>', true)" href=""><?php _e( 'Delete' ); ?></a></td>
					<td class="wp-itheora-row-storagetype"> - </td>
				</tr>
				<?php $sub_objects = $s3->list_objects( $this->_itheora_config['bucket_name'], array( 'prefix' => $object->Prefix ) ); ?>
				   <?php foreach( $sub_objects->body->Contents as $sub_object ) : ?>
						<?php if( strcmp( $sub_object->Key, $object->Prefix ) != 0 ) : ?>
						<tr>
							<td><a href="http://<?php if( $this->_itheora_config['vhost'] != '' ) { echo $this->_itheora_config['vhost']; } else { echo $this->_itheora_config['bucket_name']; } echo '/' . $sub_object->Key; ?>"><?php echo str_replace( $object->Prefix, '', $sub_object->Key ); ?></a></td>
							<td><?php echo $this->file_size( $sub_object->Size ); ?></td>
							<td><?php echo date_i18n( 'r', strtotime( $object->LastModified ), true ); ?></td>
							<td class="wp-itheora-row-actions"><a id="<?php echo str_replace( array( ' ', '.', '/', ), '-' , $sub_object->Key ) ; ?>" onclick="return object_metadata(' <?php echo $sub_object->Key ;?>', this)" href=""><?php _e( 'Edit' ); ?></a> - <a onclick="return delete_object('<?php echo $sub_object->Key; ?>');" href=""><?php _e( 'Delete' ); ?></a></td>
							<td class="wp-itheora-row-storagetype"><input id="wp-itora-row-storagetype<?php echo str_replace(array('.', ' ', '/'), '-', $sub_object->Key); ?>" onclick="change_redundancy('<?php echo $sub_object->Key; ?>',this);" type="checkbox" value="<?php echo $sub_object->Key; ?>" <?php checked( 'REDUCED_REDUNDANCY', $sub_object->StorageClass ); ?> /></td>
						</tr>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php endforeach; ?>

		</table>
		<!-- END AMAZON S3 TABLE -->

		<?php
			$policy = new CFPolicy( $s3, array(
				'expiration' => $s3->util->convert_date_to_iso8601( '+1 hour' ),
				'conditions' => array(
					array( 'acl' => 'public-read' ),
					array( 'bucket' => $this->_itheora_config['bucket_name'] ),
					array( 'starts-with', '$key', '' ),
					array( 'starts-with', '$success_action_redirect', '' ),
				)
			));
		?>

		<hr />
		<form action="http://<?php if( $this->_itheora_config['s3_vhost'] ) echo $this->_itheora_config['s3_vhost']; else echo $this->_itheora_config['bucket_name'] . '.s3.amazonaws.com' ; ?>" method="post" enctype="multipart/form-data">


			<p>
				<?php _e( "Rename the file or don't change it:" ); ?> <input type="text" name="key" value="${filename}" /><br />
				<label><?php _e( 'Es. to upload your file in <strong>"example"</strong> video folder write <strong>"example/example.extension"</strong>, or <strong>"example/${filename}"</strong> to use original filename.' ); ?></label>
				<input type="hidden" name="acl" value="public-read" />
				<input type="hidden" name="success_action_redirect" value="<?php echo $this->currentPage(); ?>" />
				<input type="hidden" name="AWSAccessKeyId" value="<?php echo $policy->get_key(); ?>" />
				<input type="hidden" name="Policy" value="<?php echo $policy->get_policy(); ?>" />
				<input type="hidden" name="Signature" value="<?php echo base64_encode( hash_hmac( 'sha1', $policy->get_policy(), $s3->secret_key, true ) )?>" />
			</p>
			<p><?php _e( 'Upload to Amazon S3' ); ?>: <input type="file" name="file" /><input type="submit" name="submit" value="Upload to Amazon S3" class="button" /></p>
		</form>

		<?php

	} /* --- End wp_itheora_video --- */

	/*****************************************************
	 *
	 *
	 *  End wp-itheora section for administration menu
	 *
	 *
	 *****************************************************/
	
	/*****************************************************
	 *
	 *
	 *  These functions are copied from Raw HTML capability
	 *  Raw HTML Plugin was written by Janis Elsts
	 *  Plugin URI: http://w-shadow.com/blog/2007/12/13/raw-html-in-wordpress/
	 *  Version: 1.2.5
	 *  Author URI: http://w-shadow.com/blog/
	 *
	 *
	 *****************************************************/
	/**********************************************
	    Filter inline blocks of itheora
	***********************************************/

	/**
	 * wp_itheora_exclusions 
	 * 
	 * @param string $text 
	 * @access public
	 * @return string
	 */
	function wp_itheora_exclusions( $text ) {
		$tags = array( array( '<!--start_itheora-->', '<!--end_itheora-->' ), array( '[itheora]', '[/itheora]' ) );

		foreach ( $tags as $tag_pair ) {
			list( $start_tag, $end_tag ) = $tag_pair;
			
			//Find the start tag
			$start = stripos( $text, $start_tag, 0 );

			while( $start !== false ) {
				$content_start = $start + strlen( $start_tag );
				
				//find the end tag
				$fin = stripos( $text, $end_tag, $content_start );
				
				//break if there's no end tag
				if ( $fin == false ) break;
				
				//extract the content between the tags
				$content = substr( $text, $content_start,$fin-$content_start );
				
				//Store the content and replace it with a marker
				$this->_wsh_raw_parts[]=$content;
				$replacement = "!ITHEORABLOCK".( count( $this->_wsh_raw_parts )-1 )."!";
				$text = substr_replace( $text, $replacement, $start, $fin+strlen( $end_tag )-$start );
				
				//Have we reached the end of the string yet?
				if ( $start + strlen( $replacement ) > strlen( $text ) ) break;
				
				//Find the next start tag
				$start = stripos( $text, $start_tag, $start + strlen( $replacement ) );
			}
		}
		return $text;
	}

	/**
	 * wp_itheora_insertion_callback 
	 * 
	 * @param mixed $matches 
	 * @access protected
	 * @return void
	 */
	protected function wp_itheora_insertion_callback( $matches ) {
		return $this->_wsh_raw_parts[intval( $matches[1] )];
	}

	/**
	 * wp_itheora_insert_exclusions 
	 * 
	 * @param string $text 
	 * @access public
	 * @return string
	 */
	function wp_itheora_insert_exclusions( $text ) {
		if( !isset( $this->_wsh_raw_parts ) ) 
			return $text;
	    return preg_replace_callback( "/!ITHEORABLOCK(\d+?)!/", array( &$this, "wp_itheora_insertion_callback" ), $text );
	}

	/*****************************************************
	 *
	 *
	 *  End Raw HTML capability section
	 *
	 *
	 *****************************************************/
}


global $WPItheora;
$WPItheora = new WPItheora();

register_activation_hook( __FILE__, array( &$WPItheora, 'wp_itheora_activation' ) );
register_deactivation_hook( __FILE__, array( &$WPItheora, 'wp_itheora_deactivation' ) );

add_action( 'init', array( &$WPItheora, 'itheora_admin' ) );

if( is_admin() ) {
	// Ajax function for local storage
	add_action( 'wp_ajax_edit_local_file', array( &$WPItheora, 'edit_local_file' ) );
	add_action( 'wp_ajax_delete_local_file', array( &$WPItheora, 'delete_local_file' ) );
	// Ajax function for remote storage
	add_action( 'wp_ajax_get_object_metadata', array( &$WPItheora, 'get_object_metadata' ) );
	add_action( 'wp_ajax_set_object_metadata', array( &$WPItheora, 'set_object_metadata' ) );
	add_action( 'wp_ajax_change_reduce_redundacy', array( &$WPItheora, 'change_reduce_redundacy' ) );
	add_action( 'wp_ajax_delete_object', array( &$WPItheora, 'delete_object' ) );
}

add_filter( 'the_content', array( &$WPItheora, 'wp_itheora_exclusions' ), 2 );
add_filter( 'the_content', array( &$WPItheora, 'wp_itheora_insert_exclusions' ), 1001 );

add_shortcode('wp-itheora', array( &$WPItheora, 'wp_itheora_shortcode' ) );
