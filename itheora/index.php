<?php
/**
 * Use WP configuration
 * http://wpquestions.com/question/show/id/1670
 */
// Include configuration
//include_once('config/config.inc.php');
/* FindWPConfig - searching for a root of wp */
function FindWPConfig($dirrectory){
	global $confroot;

	foreach(glob($dirrectory."/*") as $f){
		if (basename($f) == 'wp-config.php' ){
			$confroot = str_replace("\\", "/", dirname($f));
			return true;
		}
		if (is_dir($f)){
			$newdir = dirname(dirname($f));
		}
	}

	if (isset($newdir) && $newdir != $dirrectory){
		if (FindWPConfig($newdir)){
			return false;
		}	
	}
	return false;
}
if (!isset($table_prefix)){
	global $confroot;
	FindWPConfig(dirname(dirname(__FILE__)));
	@include_once $confroot."/wp-config.php";
	@include_once $confroot."/wp-load.php";
}
$itheora_config = get_option('wp_itheora_options');
/* End FindWPConfig */
require_once('lib/itheora.class.php');
require_once('lib/functions.php');
require_once('lib/aws-sdk/sdk.class.php');

// Get parameters from $_GET array
if(isset($_GET['v']) && $_GET['v'] != ''){
    // Use pathinfo to get old version compatibility
    $video = pathinfo($_GET['v'], PATHINFO_FILENAME);
}

if(isset($_GET['r'])){
    if($_GET['r'] != '')
	$video  = $_GET['r'];

    // Inizialise AmazonS3 and itheora
    $s3 = new AmazonS3($itheora_config['aws_key'], $itheora_config['aws_secret_key']);
    if(isset($itheora_config['s3_vhost']) && $itheora_config['s3_vhost'] != '')
	$s3->set_vhost($itheora_config['s3_vhost']);
    $itheora = new itheora(60, null, $s3, $itheora_config);
} else {
    $itheora = new itheora();
}
if(isset($video))
    $itheora->setVideoName($video);
else
    // Because no video is set, retrive the default video
    $itheora->getFiles();

$posterSize = $itheora->getPosterSize();

$width  = isset($_GET['w']) ? ((int)$_GET['w']) : $posterSize[0];
$width  = $width.'px';

$height = isset($_GET['h']) ? ((int)$_GET['h']) : $posterSize[1];
$height = $height.'px';

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title>HTML5 Video Player</title>

  <!-- Include the VideoJS Library -->
  <script src="<?php echo $itheora->getBaseUrl(); ?>/video-js/video.js" type="text/javascript" charset="utf-8"></script>

  <script type="text/javascript">
    // Must come after the video.js library
    // Add VideoJS to all video tags on the page when the DOM is ready
    VideoJS.setupAllWhenReady();
  </script>

  <!-- Include the VideoJS Stylesheet -->
  <link rel="stylesheet" href="<?php echo $itheora->getBaseUrl(); ?>/video-js/video-js.css" type="text/css" media="screen" title="Video JS">
      <?php
      if(isset($_GET['skin']) && is_readable(dirname(__FILE__) . '/video-js/skins/' . $_GET['skin'] . '.css'))
	  $skin = $_GET['skin'];
      else
	  $skin = null;

      if($skin !== null)
	  echo '<link rel="stylesheet" href="' . $itheora->getBaseUrl() . '/video-js/skins/' . $_GET['skin'] . '.css" type="text/css" media="screen" title="Video JS">';
      ?>

      <style type="text/css">
	    html, body {
		margin: 0px;
	    }
      </style>
</head>
<body>
    <?php
      createVideoJS($itheora, $itheora_config, $width, $height, $skin);
    ?>
</body>
</html>
