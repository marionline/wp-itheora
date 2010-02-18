<?php
include("../fonctions.php");

$ihost = $_SERVER['SERVER_NAME']; // domaine ou se trouve ITheora
$iscript = str_replace("lib/scroll/index.php", "index.php", $_SERVER['SCRIPT_NAME']); // chemin vers index.php
$lang=getp('l');
 require("../../lang/en/player.php");
if(file_exists( "../../lang/".$lang."/player.php") && $lang!="en") {require("../../lang/".$lang."/player.php");};

$playlist=getp('url');
$x=getp('w');
$y=getp('h');

if(url_exists($playlist, "xml")) {

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<script type="text/javascript" src="mootools.js"></script>

<script type="text/javascript">
    window.addEvent(\'domready\', function(){
        var scroll = new Scroller(\'container\', {area: 150, velocity: 0.5});
        $(\'container\').addEvent(\'mouseover\', scroll.start.bind(scroll));
        $(\'container\').addEvent(\'mouseout\', scroll.stop.bind(scroll));
    });
    
	function infobulle(text) {
		if(text==null) { text= " ";}
		document.getElementById(\'info\').innerHTML = text;
	}
</script>

<style type="text/css">

body {
	position: absolute; 
	margin: 0; 
	padding: 0; 
	background-color: transparent; 
}
#container {
	position : absolute; 
	width: 600px; 
	height: 140px; 
	overflow: auto; 
	margin: 0px; 
	overflow-x: hidden; 
	overflow-y: hidden;
	background-color : transparent;
	z-index : 1;
}
.albums {
	width : 128px;
	height : 96px;
	margin : 5px;
	border :none;
}
#info {
	position : absolute;
	font-family:Arial, Geneva, Helvetica, sans-serif;
	font-size:12px;
	color:#4C4C4C;
	top : 120px;
	left : 150px;
	width : 300px;
	overflow : hidden;
	height : 18px;
	text-align : center; 
}
a { text-decoration : none;}
</style></head>
<body>
<div id="container"  />';
if(substr($playlist, -5, 5)==".xspf") { // Playlist XSPF
	$data = implode("",file($playlist)) or die("could not open XML input file");
        $xml = xmlize($data); 
	$plsxml = $xml["playlist"]["#"]["trackList"][0]["#"]["track"];

	for($i=0; $i< sizeof($plsxml); $i++) {
	// Liste des variables
		$p_location = $plsxml[$i]["#"]["location"][0]["#"];
		$p_title = isset($plsxml[$i]["#"]["title"][0]["#"]) ? $plsxml[$i]["#"]["title"][0]["#"] : "Track ".$i ;
		$p_image = isset($plsxml[$i]["#"]["image"][0]["#"]) ? $plsxml[$i]["#"]["image"][0]["#"] : "";
		if(substr($p_location,-4,3)==".og" && !strstr($p_location, "error.ogv")) {
		$size=$size+1;
		$slider=$slider.'<a href="http://'.$ihost.$iscript.'?v='.$p_location.'&amp;out=link" onclick="window.open(this.href, \'_top\'); return false;"><img src="'.$p_image.'" class="albums"  onmouseover="this.style.width=\'138px\'; this.style.height=\'106px\';this.style.margin=\'0px\'; infobulle(\''.txtjs($p_title).'\')" onmouseout="this.style.width=\'128px\'; this.style.height=\'96px\';this.style.margin=\'5px\';  infobulle()" alt="'.$p_title.'" '.linktitle(txt($p_title)).' /></a>';
		}
	}
	echo '<div class="slider" style="width : '.($size*138).'px; height : 106px;">'.$slider.'</div>';
} else if(substr($playlist, -5, 5)!=".xspf") { // PODCAST
	$data = implode("",file($playlist)) or die("could not open XML input file");
	$xml = xmlize($data); 
	$plsxml = $xml["rss"]["#"]["channel"][0]["#"]["item"]  ;
	
$size=0; $slider="";
	for($i=0; $i< sizeof($plsxml) && $i<=20; $i++) {
	// Liste des variables
		if(strstr($playlist, "blip.tv/rss")) {
			$p_location=""; 
			if(isset($plsxml[$i]["#"]["media:group"][0]["#"]["media:content"])) {
				for($j=0; $j<count($plsxml[$i]["#"]["media:group"][0]["#"]["media:content"]); $j++) { 
					if(isset($plsxml[$i]["#"]["media:group"][0]["#"]["media:content"][$j]["@"]["url"])) {
						if(substr($plsxml[$i]["#"]["media:group"][0]["#"]["media:content"][$j]["@"]["url"], -4, 3)==".og") {
							$p_location = $plsxml[$i]["#"]["media:group"][0]["#"]["media:content"][$j]["@"]["url"];
						}
					}
				}
			}
		} else {
		$p_location = isset($plsxml[$i]["#"]["enclosure"][0]["@"]["url"]) ? $plsxml[$i]["#"]["enclosure"][0]["@"]["url"] : "" ;
		}
		$p_title = isset($plsxml[$i]["#"]["title"][0]["#"]) ? $plsxml[$i]["#"]["title"][0]["#"] : "Track ".$i;
		
		if(isset($plsxml[$i]["#"]["image"][0]["#"]["url"][0]["#"])) {
			$p_image = $plsxml[$i]["#"]["image"][0]["#"]["url"][0]["#"];
		} elseif(strstr($p_location, "http://blip.tv/")) { 
			$p_image = $p_location.'.jpg';
		}
		if(substr($p_location,-4,3)==".og" && !strstr($p_location, "error.ogv")) {
		$size=$size+1;
		$slider=$slider.'<a href="http://'.$ihost.$iscript.'?v='.$p_location.'&amp;out=link" onclick="window.open(this.href, \'_top\'); return false;"><img src="'.$p_image.'" class="albums"  onmouseover="this.style.width=\'138px\'; this.style.height=\'106px\';this.style.margin=\'0px\'; infobulle(\''.txtjs($p_title).'\')" onmouseout="this.style.width=\'128px\'; this.style.height=\'96px\';this.style.margin=\'5px\';  infobulle()" alt="'.txt($p_title).'"/></a>';
		}
	}
	echo '<div class="slider" style="width : '.($size*138).'px; height : 106px;">'.$slider.'</div>';
}
echo '
</div>
<div id="info"></div>';

if($size>4) {
echo '
<img src="fast_left.png" style="position : absolute; top : 120px; left : 10px;" />
<img src="left.png" style="position : absolute; top : 120px; left : 100px;" />
<img src="right.png" style="position : absolute; top : 120px; left : 476px;" />
<img src="fast_right.png" style="position : absolute; top : 120px; left : 550px;" />
';
}
echo '</body></html>';
}
?>