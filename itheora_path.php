<?php 
/**
 * Get wordpress language 
 */
// $lang = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']); $lang =substr($lang[0], 0, 2);
$lang = substr(WPLANG,0,2);
require('itheora/lang/en/code.php');
require('itheora/lang/en/config_player.php');
if(file_exists( WP_PLUGIN_DIR.'/wp-itheora/itheora/lang/'.$lang.'/code.php' ) && $lang!="en") {require('itheora/lang/'.$lang.'/code.php');};
if(file_exists( WP_PLUGIN_DIR.'/wp-itheora/itheora/lang/'.$lang.'/config_player.php' ) && $lang!="en") {require('itheora/lang/'.$lang.'/config_player.php');};
/**
 * Not needed in wp-itheora 
 *
 * require('itheora/lang/en/menu.php');
 * require('itheora/lang/en/index.php');
 * if(file_exists( WP_PLUGIN_DIR.'/wp-itheora/itheora/lang/'.$lang.'/menu.php' ) && $lang!="en") {require('itheora/lang/'.$lang.'/menu.php');};
 * if(file_exists( WP_PLUGIN_DIR.'/wp-itheora/itheora/lang/'.$lang.'/index.php' ) && $lang!="en") {require('itheora/lang/'.$lang.'/index.php');};
 *
 */

$ihost = $_SERVER['SERVER_NAME']; // domaine où se trouve ITheora

//$apath = $_SERVER['SCRIPT_NAME']; // chemin vers "admin/index.php"
/**
 * Not necessary in wp-itheora 
 */
//$apath = WP_PLUGIN_URL; // chemin vers "admin/index.php"

//$ipath = str_replace('admin/index.php', '', $apath); // chemin vers "itheora/"
$ipath = str_replace('http://'.$ihost, '', WP_PLUGIN_URL.'/wp-itheora/itheora/'); // chemin vers "itheora/"

//$iscript = $ipath."index.php"; // chemin vers "index.php"
$iscript = $ipath."index.php"; // chemin vers "index.php"

//$document_root=rtrim($_SERVER['DOCUMENT_ROOT'],"/"); // chemin pour les fichier de l'arborescence du serveur
$document_root=WP_PLUGIN_DIR.'/wp-itheora/itheora'; // chemin pour les fichier de l'arborescence du serveur

//$cacheogg=$document_root.$ipath.'cache';
$cacheogg=$document_root.'/itheora/cache';


include ('itheora/lib/fonctions.php');
// include ('itheora/admin/config/admin.php'); <---not needed now, maybe in future
?>
