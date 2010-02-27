<?php
/* 
Plugin Name: itheora in wordpress
Plugin URI: http://www.marionline.it/
Description: With this plugin you can use itheora script (included in this plugin) to add theora video on your blog.
Author: Mario Santagiuliana
Version: 0.1
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

    function WPItheora(){
	add_action('admin_menu', array(&$this, 'wp_itheora_menu'));
    }

    function wp_itheora_menu() {
	//minimal capability
	$mincap=9;

	$page = array();
	$page[] = add_menu_page('itheora', 'itheora', $mincap, basename(__FILE__), array(&$this, 'wp_itheora_infopage'), 'http://localhost/~mario/pluginwordpress/wp-content/plugins/wp-itheora/img/fish_theora_org.png');
	$page[] = add_submenu_page(basename(__FILE__), __('Wordpress itheora administration', 'wp-itheora'), __('itheora info', 'wp-itheora'), $mincap, basename(__FILE__),  array(&$this, 'wp_itheora_infopage'));
	$page[] = add_submenu_page(basename(__FILE__),__('Wordpress itheora administration', 'wp-itheora'), __('Create player', 'wp-itheora'), $mincap, 'wp-itheora/create-player',  array(&$this, 'wp_itheora_create_player'));
	$page[] = add_submenu_page(basename(__FILE__),__('Wordpress itheora administration', 'wp-itheora'), __('Options', 'wp-itheora'), $mincap, 'wp-itheora/options',  array(&$this, 'wp_itheora_config_player'));
	
	for($i = 0; $i < count($page); $i++) {
	    add_action( "admin_print_scripts-".$page[$i], array(&$this, 'wp_itheora_admin_head') );
	}
    }

    /**
     * wp_itheora_admin_head 
     * my stylesheet for wp-itheora section
     */
    function wp_itheora_admin_head() {
	echo "<link rel='stylesheet' href='".WP_PLUGIN_URL."/wp-itheora/style.css' type='text/css'/>";
    }

    /**
     * wp_itheora_header 
     * add image to the top of wp-itheora pages
     */
    function wp_itheora_header() {
	echo "\n<div class=\"itheora-admin\">\n";
	echo "<img src=\"".WP_PLUGIN_URL."/wp-itheora/img/titre.jpg\" alt=\"\" />\n";
	echo "<img src=\"".WP_PLUGIN_URL."/wp-itheora/img/logo.png\" alt=\"\" />\n";
	echo "</div>\n";
    }

    /**
     * wp_itheora_config_player 
     * call when the user whant to config the basic settings of itheora player
     */
    function wp_itheora_config_player() {
	$this->wp_itheora_header();
	include('itheora_path.php');
	require("itheora/admin/config/player.php");
	require('itheora/admin/pages/config_player.php');
    }

    /**
     * wp_itheora_create_player 
     * create html code and view preview
     */
    function wp_itheora_create_player() {
	$this->wp_itheora_header();

	//Call create_player.php
	include('itheora_path.php');
	require('itheora/admin/pages/code.php'); 
    }

    /**
     * wp_itheora_infopage 
     * Display information about itheora
     */
    function wp_itheora_infopage() {
	$this->wp_itheora_header();
    ?>
	<div id="wp-itheora-info">
	    <h1>ITheora</h1>
	    <p>ITheora is a PHP script allowing you to broadcast ogg/theora/vorbis only videos (and audios) files. It's simple to install and use. It may suit the usual blogger or the expert webmaster.</p>

	    <p>Itheora is different from other software allowing to stream videos, because it offers other features for the user visiting the website:</p>
	    <ul>
	    <li>choose between watching videos in an embedded player (much like a flash player), and watch the video in your favorite media player (using a plugin)</li>
	    <li>download the video file</li>
	    <li>share the video by using the HTML source code available</li>
	    <li>display in full screen mode</li>
	    <li>very quick display of the video.</li>

	    </ul>
	    <p>Itheora has real improvements for the webmaster :</p>
	    <ul>
	    <li>displaying a thumbnail when the player is being launched</li>
	    <li>almost complete interface customisation (skins, options, and languages)</li>
	    <li>very simple XHTML-compliant code, easy to configure</li>
	    <li>download possible by peer-to-peer (Bittorrent or Coral)</li>
	    <li>streaming in real time and playing external videos (on an other server with http or ftp protocol)</li>
	    <li>playlist (free format .xspf) or ogg podcast can be used</li>
	    <li>support the html5 tag video</li>

	    <li>a code generator make easier the configuration</li>
	    <li>fall back on flash is possible</li>
	    </ul>
	    <h1>You can tube, but I theora</h1>
	    <p>This software is like an alternative to the proprietary Flash players (file format and software), and is based on the Cortado java applet (ITheora is not a simple wrapper for Cortado), and helps the spreading of ogg/theora free (as in freedom ;) ) format.</p>
	    <p>In the same time, it allows you to be independant from online video services, such as youtube and dailymotion, because you can share the source code of the video from a blogger to another.</p>
	    <h1>Theora Sea</h1>
	    <p>Theora Sea is a sharing video area. This area is a simple list of links which target to hosted video, you cannot upload videos on this site. However, it make easier to generate podcast.</p>
	    <p style="text-align: center"><a href="http://theorasea.org"><img src="<?php echo WP_PLUGIN_URL;?>/wp-itheora/img/logo.png" alt="" /></a></p>
	    <p>So you can submit videos that you host yourself, yet know that you are the unique liable of what you broadcast. Check that you respect copyright low of your country.</p>
	</div>
    <?php
    } /** end wp_ithoera_infopage() */

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
     *  Start wp-itheora section for TinyMCE integration
     *
     *
     *****************************************************/

    function wp_itheora_addbutton() {
	   // Don't bother doing this stuff if the current user lacks permissions
	   if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
		    return;
	    
	      // Add only in Rich Editor mode
	      if ( get_user_option('rich_editing') == 'true') {
		       add_filter("mce_external_plugins", array(&$this, "add_wp_itheora_tinymce_plugin"));
		       add_filter('mce_buttons', array(&$this, 'register_wp_itheora_button'));
	      }
    }
     
    function register_wp_itheora_button($buttons) {
	   array_push($buttons, "|", "WPitheora");
	   return $buttons;
    }
     
    // Load the TinyMCE plugin : editor_plugin.js (wp2.5)
    function add_wp_itheora_tinymce_plugin($plugin_array) {
	   $plugin_array['WPitheora'] = WP_PLUGIN_URL."/wp-itheora/tinymce3/editor_plugin.js";
	   return $plugin_array;
    }
     
    /*****************************************************
     *
     *
     *  End wp-itheora section for TinyMCE integration
     *
     *
     *****************************************************/
}

/**
 * wp_itheora_activation 
 * 
 * When the plugin is activated this function run
 */
function wp_itheora_activation() {
    static $conf_itheora;
    static $conf_dir;
    $conf_itheora = WP_PLUGIN_DIR."/wp-itheora/itheora/admin/config/player.php";
    $conf_dir = dirname($conf_itheora);

    if(file_exists($conf_itheora) && is_writable($conf_itheora)){
	static $file_config_player;
	$file_config_player='<?php'."\n";
	$file_config_player .= '$title="ITheora, I really broadcast myself";'."\n\n"; 
	$file_config_player .= '$function_manual_play=true;'."\n"; 
	$file_config_player .= '$function_info=true;'."\n"; 
	$file_config_player .= '$function_ts=true;'."\n"; 
	$file_config_player .= '$function_name=true;'."\n\n"; 
	
	$file_config_player .= '$function_share=true;'."\n"; 
	$file_config_player .= '$function_download=true;'."\n"; 
	$file_config_player .= '$function_fullscreen=true;'."\n"; 
	$file_config_player .= '$function_options=true;'."\n\n"; 
	
	$file_config_player .= '$function_error_but=true;'."\n"; 
	$file_config_player .= '$function_podcast=true;'."\n"; 
	$file_config_player .= '$function_alt_download=true;'."\n\n"; 
	
	static $document_root;
        $document_root=WP_PLUGIN_DIR.'/wp-itheora/itheora';
	$file_config_player .= '$document_root="'.$document_root.'";'."\n\n";
	
	$file_config_player .= '$blacklist = '."\"\"; \n";
	$file_config_player .= '$whitelist = '."\"\"; \n";
	$file_config_player .= '?>';
	
	//$old_file_config_player= fopen("config/player.php","w");
	$old_file_config_player= fopen($conf_itheora,"w");
	fwrite($old_file_config_player,$file_config_player);
	fclose($old_file_config_player);
    } elseif(file_exists($conf_dir) && !is_writable($conf_dir)) {
	deactivate_plugins(__FILE__);
	die(__("Need to make directory '$conf_dir' writeable or create a writeable '$conf_itheora' file."));
    } else {
	deactivate_plugins(__FILE__);
	die(__("Need to make file '$conf_itheora' writeable."));
    }
}
register_activation_hook(__FILE__, 'wp_itheora_activation');

add_action('init', 'start_wpitheora');
function start_wpitheora() {
    global $WPItheora;
    $WPItheora = new WPItheora();
    add_action('init', $WPItheora->wp_itheora_addbutton());
}
