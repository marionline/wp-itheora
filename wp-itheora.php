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

    function __CONSTRUCT(){
	$this->dir = dirname(plugin_basename(__FILE__));
	load_plugin_textdomain( $this->domain, 'wp-content/plugins/'.$this->dir.'/lang/');
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
	static $conf_itheora;
	static $conf_dir;
	static $dir_cache;
	$conf_itheora = WP_PLUGIN_DIR."/".$this->dir."/itheora/admin/config/player.php";
	$conf_dir = dirname($conf_itheora);
	$dir_cache = WP_PLUGIN_DIR."/".$this->dir."/itheora/cache";

	if((file_exists($conf_itheora) && is_writable($conf_itheora)) || is_writable($conf_dir)){
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
	    $document_root=WP_PLUGIN_DIR.'/'.$this->dir.'/itheora';
	    $file_config_player .= '$document_root="'.$document_root.'";'."\n\n";
	    
	    $file_config_player .= '$blacklist = '."Array ( 0 => \"\" ); \n";
	    $file_config_player .= '$whitelist = '."Array ( 0 => \"\" ); \n";
	    
	    //$old_file_config_player= fopen("config/player.php","w");
	    $old_file_config_player= fopen($conf_itheora,"w");
	    fwrite($old_file_config_player,$file_config_player);
	    fclose($old_file_config_player);
	} elseif(!file_exists($conf_dir) && !is_writable($conf_dir)) {
	    deactivate_plugins(__FILE__);
	    die(__("Need to make directory '$conf_dir' writeable or create a writeable '$conf_itheora' file.", $this->domain));
	} else {
	    deactivate_plugins(__FILE__);
	    die(__("Need to make file '$conf_itheora' writeable.", $this->domain));
	}

	if(!is_writable($dir_cache)) {
	    deactivate_plugins(__FILE__);
	    die(__("Need to make cache directory '$dir_cache' writeable.", $this->domain));
	}
    }

    function wp_itheora_menu() {
	//minimal capability
	$mincap=9;

	$page = array();
	$page[] = add_menu_page('itheora', 'itheora', $mincap, basename(__FILE__), array(&$this, 'wp_itheora_infopage'), WP_PLUGIN_URL.'/'.$this->dir.'/img/fish_theora_org.png');
	$page[] = add_submenu_page(basename(__FILE__), __('Wordpress itheora administration', $this->domain), __('itheora info', $this->domain), $mincap, basename(__FILE__),  array(&$this, 'wp_itheora_infopage'));
	$page[] = add_submenu_page(basename(__FILE__),__('Wordpress itheora administration', $this->domain), __('Create player', $this->domain), $mincap, 'wp-itheora/create-player',  array(&$this, 'wp_itheora_create_player'));
	$page[] = add_submenu_page(basename(__FILE__),__('Wordpress itheora administration', $this->domain), __('Options', $this->domain), $mincap, 'wp-itheora/options',  array(&$this, 'wp_itheora_config_player'));
	
	for($i = 0; $i < count($page); $i++) {
	    add_action( "admin_print_scripts-".$page[$i], array(&$this, 'wp_itheora_admin_head') );
	}
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

add_action('init', array(&$WPItheora, 'itheora_admin'));

add_filter('the_content', array(&$WPItheora, 'wp_itheora_exclusions'), 2);
add_filter('the_content', array(&$WPItheora, 'wp_itheora_insert_exclusions'), 1001);
