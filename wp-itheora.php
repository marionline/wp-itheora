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
    private $wsh_raw_parts=array();
    private $domain = 'wpitheora';
    private $dir;

    /**
     * __CONSTRUCT 
     * 
     * @access protected
     * @return void
     */
    function __CONSTRUCT(){
	$this->dir = dirname(plugin_basename(__FILE__));
	load_plugin_textdomain( $this->domain, 'wp-content/plugins/'.$this->dir.'/lang/');
    }

    /**
     * file_size 
     * 
     * @param mixed $size 
     * @access private
     * @return void
     */
    private function file_size($size) {
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

    function itheora_admin(){
	add_action('admin_menu', array(&$this, 'wp_itheora_menu'));
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

	if(!is_dir($itheora_config['video_dir']))
	    mkdir($itheora_config['video_dir']);

	if(!get_option('wp_itheora_options'))
	    update_option('wp_itheora_options', $itheora_config);
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
     * wp_itheora_menu 
     * 
     * @access public
     * @return void
     */
    function wp_itheora_menu() {
	//minimal capability
	$mincap=9;

	$page = array();
	$page[] = add_menu_page('itheora', 'itheora', $mincap, basename(__FILE__), array(&$this, 'wp_itheora_infopage'), WP_PLUGIN_URL.'/'.$this->dir.'/img/fish_theora_org.png');
	$page[] = add_submenu_page(basename(__FILE__), __('Wordpress itheora administration', $this->domain), __('itheora info', $this->domain), $mincap, basename(__FILE__),  array(&$this, 'wp_itheora_infopage'));
	$page[] = add_submenu_page(basename(__FILE__),__('Wordpress itheora administration', $this->domain), __('Options', $this->domain), $mincap, 'wp-itheora/options',  array(&$this, 'wp_itheora_config_player'));
	$page[] = add_submenu_page(basename(__FILE__),__('Wordpress itheora administration', $this->domain), __('Video', $this->domain), $mincap, 'wp-itheora/video',  array(&$this, 'wp_itheora_video'));
	
	for($i = 0; $i < count($page); $i++) {
	    add_action( "admin_print_scripts-".$page[$i], array(&$this, 'wp_itheora_admin_head') );
	}
	add_action('admin_print_scripts-'.$page[3], array(&$this, 'ajax_change_reduce_redundacy'));

	add_action('admin_init', array(&$this, 'wp_itheora_register_settings'));
    }

    /**
     * wp_itheora_admin_head 
     * my stylesheet for wp-itheora section
     */
    function wp_itheora_admin_head() {
	echo "<link rel='stylesheet' href='".WP_PLUGIN_URL."/".$this->dir."/style.css' type='text/css'/>";
    }

    /**
     * wp_itheora_header 
     * add image to the top of wp-itheora pages
     */
    protected function wp_itheora_header() {
	echo "\n<div class=\"itheora-admin\">\n";
	echo "<img src=\"".WP_PLUGIN_URL."/".$this->dir."/img/titre.jpg\" alt=\"\" />\n";
	echo "<img src=\"".WP_PLUGIN_URL."/".$this->dir."/img/logo.png\" alt=\"\" />\n";
	echo "</div>\n";
    }

    /**
     * wp_itheora_register_settings 
     * 
     * @access public
     * @return void
     */
    function wp_itheora_register_settings() {
	register_setting('wp_itheora-group', 'wp_itheora_options', array(&$this, 'wp_itheora_settings_validate'));
    }

    /**
     * wp_itheora_settings_validate 
     * validation input
     * 
     * @param array $input 
     * @return array
     */
    function wp_itheora_settings_validate($input) {
	$input['MP4_source']     = ($input['MP4_source'] == 1 ? true : false);
	$input['WEBM_source']    = ($input['WEBM_source'] == 1 ? true : false);
	$input['flash_fallback'] = ($input['flash_fallback'] == 1 ? true : false);
	$input['bucket_name']    = $input['bucket_name'];
	$input['s3_region']      = ($input['s3_region'] ? $input['s3_region'] : 'AmazonS3::REGION_EU_W1');
	$input['s3_vhost']       = $input['s3_vhost'];
	$input['aws_key']        = ($input['aws_key'] ? wp_filter_nohtml_kses($input['aws_key']) : 'Amazon web service key');
	$input['aws_secret_key'] = ($input['aws_secret_key'] ? wp_filter_nohtml_kses($input['aws_secret_key']) : 'Amazon web service secret key');
	$input['video_dir']      = ((substr($input['video_dir'], -1) == '/') ? substr($input['video_dir'], 0, -1) : $input['video_dir']);
	$input['video_url']      = ((substr($input['video_url'], -1) == '/') ? substr($input['video_url'], 0, -1) : $input['video_url']);
	return $input;
    }

    /**
     * wp_itheora_config_player 
     * call when the user whant to config the basic settings of itheora player
     */
    function wp_itheora_config_player() {
	$this->wp_itheora_header();
	echo '<h2>' . __('WP-itheora configuration page') . '</h2>';
        echo '<form method="post" action="options.php">';
	settings_fields( 'wp_itheora-group' );
	$itheora_config = get_option('wp_itheora_options');
	?>
	<table>
	    <tr> 
		<td><?php _e('Include Mp4 source:'); ?></td>
		<td><input type="radio" name="wp_itheora_options[MP4_source]" value="1" <?php checked(true, $itheora_config['MP4_source']); ?> /> <?php _e('Yes'); ?></td>
		<td><input type="radio" name="wp_itheora_options[MP4_source]" value="0" <?php checked(false, $itheora_config['MP4_source']); ?> /> <?php  _e('No'); ?></td>
	    </tr>
	    <tr>
		<td><?php _e('Include WebM source:'); ?></td>
		<td><input type="radio" name="wp_itheora_options[WEBM_source]" value="1" <?php checked(true, $itheora_config['WEBM_source']); ?> /> <?php _e('Yes'); ?></td>
		<td><input type="radio" name="wp_itheora_options[WEBM_source]" value="0" <?php checked(false, $itheora_config['WEBM_source']); ?> /> <?php  _e('No'); ?></td>
	    </tr>
	    <tr>
		<td><?php _e('Use flash fallback:'); ?></td>
		<td><input type="radio" name="wp_itheora_options[flash_fallback]" value="1" <?php checked(true, $itheora_config['flash_fallback']); ?> /> <?php _e('Yes'); ?></td>
		<td><input type="radio" name="wp_itheora_options[flash_fallback]" value="0" <?php checked(false, $itheora_config['flash_fallback']); ?> /> <?php  _e('No'); ?></td>
	    </tr>
	</table>
	    <p>
		<?php _e('Bucket name:'); ?>
		<input type="text" name="wp_itheora_options[bucket_name]" value="<?php echo $itheora_config['bucket_name']; ?>" />
	    </p>
	    <p>
		<?php _e('Bucket region:'); ?>
		<select name="wp_itheora_options[s3_region]">
		    <option value="AmazonS3::REGION_US_E1">US Standard</option>
		    <option value="AmazonS3::REGION_US_W1">US West (Northern California)</option>
		    <option value="AmazonS3::REGION_EU_W1">EU (Ireland)</option>
		    <option value="AmazonS3::REGION_APAC_NE1">Asia Pacific (Tokyo)</option>
		    <option value="AmazonS3::REGION_APAC_SE1">Asia Pacific (Singapore)</option>
		</select>
	    </p>
	    <p>
		<?php _e('Set bucket virtual host:'); ?>
		<input type="text" name="wp_itheora_options[s3_vhost]" value="<?php echo $itheora_config['s3_vhost']; ?>" />
	    </p>
	    <p>
		<?php _e('Amazon Web Service Key:'); ?>
		<input type="text" name="wp_itheora_options[aws_key]" value="<?php echo $itheora_config['aws_key']; ?>" />
	    </p>
	    <p>
		<?php _e('Amazon Web Service Secret Key:'); ?>
		<input type="text" name="wp_itheora_options[aws_secret_key]" value="<?php echo $itheora_config['aws_secret_key']; ?>" />
	    </p>
	    <p>
		<?php _e('Set local video directory:'); ?>
		<input type="text" name="wp_itheora_options[video_dir]" value="<?php echo $itheora_config['video_dir']; ?>" />
	    </p>
	    <p>
		<?php _e('Change video Url:'); ?>
		<input type="text" name="wp_itheora_options[video_url]" value="<?php echo $itheora_config['video_url']; ?>" />
	    </p>
	    <p class="submit">
		<input type="submit" class="button-primary" value="<?php _e('Save'); ?>" />
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
	    <p>".__("ITheora is a PHP script allowing you to broadcast ogg/theora/vorbis only videos (and audios) files. It's simple to install and use. It may suit the usual blogger or the expert webmaster.", $this->domain)."</p>

	    <p>".__("Itheora is different from other software allowing to stream videos, because it offers other features for the user visiting the website:", $this->domain)."</p>
	    <ul>
	    <li>".__("choose between watching videos in an embedded player (much like a flash player), and watch the video in your favorite media player (using a plugin)", $this->domain)."</li>
	    <li>".__("download the video file", $this->domain)."</li>
	    <li>".__("share the video by using the HTML source code available", $this->domain)."</li>
	    <li>".__("display in full screen mode", $this->domain)."</li>
	    <li>".__("very quick display of the video.", $this->domain)."</li>

	    </ul>
	    <p>".__("Itheora has real improvements for the webmaster :", $this->domain)."</p>
	    <ul>
	    <li>".__("displaying a thumbnail when the player is being launched", $this->domain)."</li>
	    <li>".__("almost complete interface customisation (skins, options, and languages)", $this->domain)."</li>
	    <li>".__("very simple XHTML-compliant code, easy to configure", $this->domain)."</li>
	    <li>".__("download possible by peer-to-peer (Bittorrent or Coral)", $this->domain)."</li>
	    <li>".__("streaming in real time and playing external videos (on an other server with http or ftp protocol)", $this->domain)."</li>
	    <li>".__("playlist (free format .xspf) or ogg podcast can be used", $this->domain)."</li>
	    <li>".__("support the html5 tag video", $this->domain)."</li>

	    <li>".__("a code generator make easier the configuration", $this->domain)."</li>
	    <li>".__("fall back on flash is possible", $this->domain)."</li>
	    </ul>
	    <h1>".__("You can tube, but I theora", $this->domain)."</h1>
	    <p>".__("This software is like an alternative to the proprietary Flash players (file format and software), and is based on the Cortado java applet (ITheora is not a simple wrapper for Cortado), and helps the spreading of ogg/theora free (as in freedom ;) ) format.", $this->domain)."</p>
	    <p>".__("In the same time, it allows you to be independant from online video services, such as youtube and dailymotion, because you can share the source code of the video from a blogger to another.", $this->domain)."</p>
	    <h1>".__("Theora Sea", $this->domain)."</h1>
	    <p>".__("Theora Sea is a sharing video area. This area is a simple list of links which target to hosted video, you cannot upload videos on this site. However, it make easier to generate podcast.", $this->domain)."</p>
	    <p style=\"text-align: center\"><a href=\"http://theorasea.org\"><img src=\"".WP_PLUGIN_URL."/".$this->dir."/img/logo.png\" alt=\"\" /></a></p>
	    <p>".__("So you can submit videos that you host yourself, yet know that you are the unique liable of what you broadcast. Check that you respect copyright low of your country.", $this->domain)."</p>
	</div>
	";
    } /** end wp_ithoera_infopage() */

    function ajax_change_reduce_redundacy() {
    ?>
    <script type="text/javascript">
	function change_redundancy(value) {
	    var data = {
	    action: 'change_reduce_redundacy',
	    s3object: value
	    };

	    jQuery.post(ajaxurl, data, function(response) {
		if(!response) {
		    alert('Impossible to change redundacy storage type.');
		}
	    });
	}

    </script>

    <?php
    }

    function change_reduce_redundacy() {
	require_once(dirname(__FILE__) . '/itheora/lib/aws-sdk/sdk.class.php');
	// Retrive itheora_config
	$itheora_config = get_option('wp_itheora_options');
	// Create AmazonS3 object
	$s3 = new AmazonS3($itheora_config['aws_key'], $itheora_config['aws_secret_key']);
	$s3->set_region($itheora_config['s3_region']);
	$s3->set_vhost($itheora_config['s3_vhost']);
	$object = $s3->get_object_metadata($itheora_config['bucket_name'], $_POST['s3object']);

	if($object['StorageClass'] == 'STANDARD')
	    $response = $s3->change_storage_redundancy ( $itheora_config['bucket_name'], $_POST['s3object'], AmazonS3::STORAGE_REDUCED ); 
	else
	    $response = $s3->change_storage_redundancy ( $itheora_config['bucket_name'], $_POST['s3object'], AmazonS3::STORAGE_STANDARD ); 

	if($response->isOK())
	    echo true;
	else
	    echo false;
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
    private function rrmdir($dir) {
	if (is_dir($dir)) {
	    $objects = scandir($dir);
	    foreach ($objects as $object) {
		if ($object != "." && $object != "..") {
		    if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); else unlink($dir."/".$object);
		}
	    }
	    reset($objects);
	    rmdir($dir);
	}
	return;
    }

    /**
     * deleteLocal 
     * Delete local file
     * 
     * @param mixed $itheora 
     * @access private
     * @return void
     */
    private function deleteLocal(&$itheora) {
	    $local_file = basename($_GET['deleteLocal']);
	    if(isset($_GET['parentdir']))
		$parentdir = basename($_GET['parentdir']);
	    else
		$parentdir = false;

	    if($parentdir)
		$to_be_remove = $itheora->getVideoDir() . '/' . $parentdir . '/' . $local_file;
	    else
		$to_be_remove = $itheora->getVideoDir() . '/' . $local_file;

	    if(is_dir($to_be_remove))
		$this->rrmdir($to_be_remove);
	    else {
		unlink($to_be_remove);
	    }
    }

    /**
     * wp_itheora_video 
     * Video Administration page
     */
    function wp_itheora_video() {
	$this->wp_itheora_header();
	require_once(dirname(__FILE__) . '/itheora/lib/itheora.class.php');
	require_once(dirname(__FILE__) . '/itheora/lib/aws-sdk/sdk.class.php');
	// Retrive itheora_config
	$itheora_config = get_option('wp_itheora_options');
	// Create itheora object
	$itheora = new itheora();
	$itheora->setVideoDir($itheora_config['video_dir']);
	// Create AmazonS3 object
	$s3 = new AmazonS3($itheora_config['aws_key'], $itheora_config['aws_secret_key']);
	$s3->set_region($itheora_config['s3_region']);
	$s3->set_vhost($itheora_config['s3_vhost']);

	// Check if we do some other action from filemanager
	if(isset($_GET['deleteLocal'])){
	    // Delete local file
	    $this->deleteLocal($itheora);
	}
	if(isset($_GET['deleteObject'])) {
	    // Delete object
	    $s3->delete_object($itheora_config['bucket_name'], $_GET['deleteObject']);
	} elseif(isset($_GET['deletePrefix'])) {
	    // Delete all object with provided prefix
	    $results = $s3->delete_all_objects( $itheora_config['bucket_name'], '/' . str_replace('/', '\/', $_GET['deletePrefix']) . '.*/' );
	}
	    ?>
	    <!-- START LOCAL FILE TABLE -->

	    <table class="widefat fixed wp-itheora-table" cellspacing="0">
		<thead>
		    <tr>
			<th><?php echo __('File', $this->domain); ?></th>
			<th><?php echo __('Size', $this->domain); ?></th>
			<th><?php echo __('Actions', $this->domain); ?></th>
		    </tr>
		</thead>
		<tfoot>
		    <tr>
			<th><?php echo __('File', $this->domain); ?></th>
			<th><?php echo __('Size', $this->domain); ?></th>
			<th><?php echo __('Actions', $this->domain); ?></th>
		    </tr>
		</tfoot>
		<tbody>
	    <?php
	    $content = scandir($itheora->getVideoDir());
            $html = '';
	    if($content) {
		foreach($content as $id => $item) {
		    $subdir = $itheora->getVideoDir() . '/' . $item;
		    if( $id > 1 ) {
			$html .= '<tr>' . PHP_EOL;
			if(is_dir($subdir)){
			    $html .= '<td class="itheora-video-name"><strong>' . $item . ':</strong></td>' . PHP_EOL;
			} else {
			    $html .= '<td class="itheora-video-name"><strong>' . $item . '</strong></td>' . PHP_EOL;
			}
			if(is_dir($subdir)){
			    $html .= '<td class="itheora-video-size"> - </td>' . PHP_EOL;
			    $html .= '<td class="wp-itheora-row-actions"><a href="">' . __('Rename') . '</a> - <a class="submitdelete" onclick="return showNotice.warn();" href="' . $this->currentPage() . '&amp;deleteLocal=' . $item . '">' . __('Delete') . '</a></td>' . PHP_EOL;
			} else {
			    $html .= '<td class="itheora-video-size">' . $this->file_size(filesize($itheora->getVideoDir() . '/' . $item )) .'</td>' . PHP_EOL;
			    $html .= '<td class="wp-itheora-row-actions"><a href="">' . __('Rename') . '</a> - <a onclick="return showNotice.warn();" href="' . $this->currentPage() . '&amp;deleteLocal=' . $item . '">' . __('Delete') . '</a></td>' . PHP_EOL;
			}
			$html .= '</tr>' . PHP_EOL;
			if(is_dir($subdir)){
			    $subcontent = scandir($itheora->getVideoDir() . '/' . $item);
			    if($subcontent) {
				foreach($subcontent as $sub_id => $sub_item) {
				    if( $sub_id > 1 ) {
					$html .= '<tr class="itheora-local-files">' . PHP_EOL;
					$html .= '<td>' . $sub_item . '</td>' . PHP_EOL;
					$html .= '<td class="itheora-video-size">' . $this->file_size(filesize($itheora->getVideoDir() . '/' . $item . '/' . $sub_item)) .'</td>' . PHP_EOL;
					$html .= '<td class="wp-itheora-row-actions"><a href="">' . __('Rename') . '</a> - <a href="' . $this->currentPage() . '&amp;parentdir=' . $item . '&amp;deleteLocal=' . $sub_item . '">' . __('Delete') . '</a></td>' . PHP_EOL;
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
	    <h3><?php _e('Upload File Locally'); ?></h3>
	    <?php if(isset($error_message)) echo $error_message; ?>
	    <form action="addfile.php" method="post" enctype="multipart/form-data">
		<p><?php _e('Upload file'); ?>: <input type="file" name="file" /><input type="submit" name="submit" value="<?php _e('Upload'); ?>" class="button" /></p>
	    </form>
	    <hr />
	<h2><?php _e('List of remote files:'); ?></h2>
	<?php
	    $object_list = $s3->get_object_list($itheora_config['bucket_name']);
	    $objects = $s3->list_objects($itheora_config['bucket_name'], array('delimiter' => '/'));
	    ?>
	    <!-- START AMAZON S3 TABLE -->
	    <table class="widefat fixed wp-itheora-table" cellspacing="0">
		<thead>
		    <tr>
			<th><?php echo __('File', $this->domain); ?></th>
			<th><?php echo __('Size', $this->domain); ?></th>
			<th><?php echo __('Actions', $this->domain); ?></th>
			<th><?php echo __('Reduce redundacy storage', $this->domain); ?></th>
			<th><?php echo __('Public', $this->domain); ?></th>
		    </tr>
		</thead>
		<tfoot>
		    <tr>
			<th><?php echo __('File', $this->domain); ?></th>
			<th><?php echo __('Size', $this->domain); ?></th>
			<th><?php echo __('Actions', $this->domain); ?></th>
			<th><?php echo __('Reduce redundacy storage', $this->domain); ?></th>
			<th><?php echo __('Public', $this->domain); ?></th>
		    </tr>
		</tfoot>
	    <?php
	    foreach($objects->body->Contents as $object) {
		?>
		<tr>
		    <td><a href="http://<?php if($itheora_config['vhost'] != '') { echo $itheora_config['vhost']; } else { echo $itheora_config['bucket_name']; } echo '/' . $object->Key; ?>"><?php echo $object->Key; ?></a></td>
		    <td><?php echo $this->file_size($object->Size); ?></td>
		    <td class="wp-itheora-row-actions"><a href=""><?php _e('Rename'); ?></a> - <a onclick="return showNotice.warn();" href="<?php echo $this->currentPage() . '&amp;deleteObject=' . $object->Key; ?>"><?php _e('Delete'); ?></a></td>
		    <td class="wp-itheora-row-storagetype"><input onclick="change_redundancy('<?php echo $object->Key; ?>');" type="checkbox" value="<?php echo $object->Key; ?>" <?php checked('REDUCED_REDUNDANCY', $object->StorageClass); ?> /></td>
		</tr>
		<?php
	    }
	    foreach($objects->body->CommonPrefixes as $object) {
		?>
		<tr>
		    <td><strong><?php echo $object->Prefix; ?></strong></td>
		    <td> - </td>
		    <td class="wp-itheora-row-actions"><a href=""><?php _e('Rename'); ?></a> - <a onclick="return showNotice.warn();" href="<?php echo $this->currentPage() . '&amp;deletePrefix=' . $object->Prefix; ?>"><?php _e('Delete'); ?></a></td>
		    <td class="wp-itheora-row-storagetype"> - </td>
		</tr>
		<?php
		$sub_objects = $s3->list_objects($itheora_config['bucket_name'], array('prefix' => $object->Prefix));
		foreach($sub_objects->body->Contents as $sub_object) {
		    if(strcmp($sub_object->Key, $object->Prefix) != 0 ) {
		    ?>
		    <tr>
			<td><a href="http://<?php if($itheora_config['vhost'] != '') { echo $itheora_config['vhost']; } else { echo $itheora_config['bucket_name']; } echo '/' . $sub_object->Key; ?>"><?php echo str_replace($object->Prefix, '', $sub_object->Key); ?></a></td>
			<td><?php echo $this->file_size($sub_object->Size); ?></td>
			<td class="wp-itheora-row-actions"><a href=""><?php _e('Rename'); ?></a> - <a onclick="return showNotice.warn();" href="<?php echo $this->currentPage() . '&amp;deleteObject=' . $sub_object->Key; ?>"><?php _e('Delete'); ?></a></td>
			<td class="wp-itheora-row-storagetype"><input onclick="change_redundancy('<?php echo $sub_object->Key; ?>');" type="checkbox" value="<?php echo $sub_object->Key; ?>" <?php checked('REDUCED_REDUNDANCY', $sub_object->StorageClass); ?> /></td>
		    </tr>
		    <?php
		    }
		}
	    }
	    echo '</table>';
	    
	    $policy = new CFPolicy($s3, array(
		'expiration' => $s3->util->convert_date_to_iso8601('+1 hour'),
		'conditions' => array(
		    array('acl' => 'public-read'),
		    array('bucket' => $itheora_config['bucket_name']),
		    array('starts-with', '$key', ''),
		    array('starts-with', '$success_action_redirect', ''),
		)
	    ));
	    ?>
	    <!-- END AMAZON S3 TABLE -->
	    <hr />
	    <form action="http://<?php if($itheora_config['s3_vhost']) echo $itheora_config['s3_vhost']; else echo $itheora_config['bucket_name'] . '.s3.amazonaws.com' ; ?>" method="post" enctype="multipart/form-data">


		    <p>
		    <?php _e("Rename the file or don't change it:"); ?> <input type="text" name="key" value="${filename}" />
		    <input type="hidden" name="acl" value="public-read" />
		    <input type="hidden" name="success_action_redirect" value="<?php echo $this->currentPage(); ?>" />
		    <input type="hidden" name="AWSAccessKeyId" value="<?php echo $policy->get_key(); ?>" />
		    <input type="hidden" name="Policy" value="<?php echo $policy->get_policy(); ?>" />
		    <input type="hidden" name="Signature" value="<?php echo base64_encode(hash_hmac('sha1', $policy->get_policy(), $s3->secret_key, true))?>" />
		    </p>
		    <p><?php _e('Upload to Amazon S3'); ?>: <input type="file" name="file" /><input type="submit" name="submit" value="Upload to Amazon S3" class="button" /></p>
	    </form>

	    <?php

    }

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

    function wp_itheora_exclusions($text){
	    $tags = array(array('<!--start_itheora-->', '<!--end_itheora-->'), array('[itheora]', '[/itheora]'));

	    foreach ($tags as $tag_pair){
		    list($start_tag, $end_tag) = $tag_pair;
		    
		    //Find the start tag
		    $start = stripos($text, $start_tag, 0);
		    while($start !== false){
			    $content_start = $start + strlen($start_tag);
			    
			    //find the end tag
			    $fin = stripos($text, $end_tag, $content_start);
			    
			    //break if there's no end tag
			    if ($fin == false) break;
			    
			    //extract the content between the tags
			    $content = substr($text, $content_start,$fin-$content_start);
			    
			    //Store the content and replace it with a marker
			    $this->wsh_raw_parts[]=$content;
			    $replacement = "!ITHEORABLOCK".(count($this->wsh_raw_parts)-1)."!";
			    $text = substr_replace($text, $replacement, $start, 
				    $fin+strlen($end_tag)-$start
			     );
			    
			    //Have we reached the end of the string yet?
			    if ($start + strlen($replacement) > strlen($text)) break;
			    
			    //Find the next start tag
			    $start = stripos($text, $start_tag, $start + strlen($replacement));
		    }
	    }
	    return $text;
    }

    protected function wp_itheora_insertion_callback($matches){
	    return $this->wsh_raw_parts[intval($matches[1])];
    }

    function wp_itheora_insert_exclusions($text){
	    if(!isset($this->wsh_raw_parts)) return $text;
	    return preg_replace_callback("/!ITHEORABLOCK(\d+?)!/", array(&$this, "wp_itheora_insertion_callback"), $text);
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

register_activation_hook(__FILE__, array(&$WPItheora, 'wp_itheora_activation'));
register_deactivation_hook(__FILE__, array(&$WPItheora, 'wp_itheora_deactivation'));

add_action('init', array(&$WPItheora, 'itheora_admin'));

if( is_admin() )
    add_action('wp_ajax_change_reduce_redundacy', array(&$WPItheora, 'change_reduce_redundacy'));

add_filter('the_content', array(&$WPItheora, 'wp_itheora_exclusions'), 2);
add_filter('the_content', array(&$WPItheora, 'wp_itheora_insert_exclusions'), 1001);
