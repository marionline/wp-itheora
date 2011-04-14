<?php
/**
 * Usefull helper function to use with itheora3-fork 
 */
require_once(dirname(__FILE__) . '/../lib/itheora.class.php');
require_once(dirname(__FILE__) . '/../lib/aws-sdk/sdk.class.php');


/**
 * createObjectTag 
 * 
 * @param array $options 
 * @param 'width' => null
 * @param 'height' => null 
 * @param 'useFilesInCloud' => false
 * @param 'skin' => null          Skin to be use (default video-js), other hu, vim, tube
 * @param 'alternativeName' => null Alternative name and id to use in object tag
 * @param array $itheora_config If you want to use files store in cloud need to pass the configuration
 * @access public
 * @return html code
 */
function createObjectTag(array $options = array('video' => 'example', 'width' => null, 'height' => null, 'useFilesInCloud' => false, 'skin' => null, 'alternativeName' => null), array $itheora_config = array()) {
    if(!isset($options['video']))
	$options['video'] = 'example';

    if(!isset($options['width']))
	$options['width'] = null;

    if(!isset($options['height']))
	$options['height'] = null;

    if(!isset($options['useFilesInCloud']))
	$options['useFilesInCloud'] = false;

    // If no width or height are passed I use the image width and height
    if( $options['width'] === null || $options['height'] === null ) {
	if($options['useFilesInCloud']) {
	    // Inizialise AmazonS3 and itheora
	    $s3 = new AmazonS3($itheora_config['aws_key'], $itheora_config['aws_secret_key']);
	    if(isset($itheora_config['s3_vhost']) && $itheora_config['s3_vhost'] != '')
		$s3->set_vhost($itheora_config['s3_vhost']);
	    $itheora = new itheora(60, null, $s3, $itheora_config);
	} else {
	    $itheora = new itheora();
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

    if($options['useFilesInCloud']) {
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

    return '<object id="' . $id . '" name="' . $name . '" class="itheora3-fork" type="application/xhtml+xml" data="itheora.php?' . $key . '=' . $options['video'] . $width_url . $height_url . $skin . '" style="' . $width_style . ' ' . $height_style . '"> 
	</object>';
}

/**
 * createVideoJS 
 * 
 * @param itheora $itheora      Pass itheora object
 * @param array $itheora_config Pass itheora configuration
 * @param mixed $width          Force width 
 * @param mixed $height         Force height 
 * @param string $skin          Skin to be use (default video-js), other hu, vim, tube
 * @access public
 * @return void
 */
function createVideoJS(itheora &$itheora, array &$itheora_config, $width = null, $height = null, $skin = null) {
    if($width === null || $height === null)
	$posterSize = $itheora->getPosterSize();
    if($width === null)
	$width = $posterSize[0].'px';
    if($height === null)
	$height = $posterSize[1].'px';

    if($itheora->useFilesInCloud()) {
	$key = 'r';
    } else {
	$key = 'v';
    }

?>
      <!-- Begin VideoJS -->
      <div class="video-js-box<?php if($skin) echo ' ' . $skin . '-css'; ?>">
	<!-- Using the Video for Everybody Embed Code http://camendesign.com/code/video_for_everybody -->
	<video id="<?php echo $itheora->getVideoName(); ?>" class="video-js" width="<?php echo $width; ?>" height="<?php echo $height; ?>" controls="controls" preload="auto" poster="<?php echo $itheora->getPoster(); ?>">
	  <?php if(($itheora_config['MP4_source'] || $itheora_config['flash_fallback']) && $video = $itheora->getMP4Video()): ?>
	  <source src="<?php echo $video; ?>" type='video/mp4; codecs="avc1.42E01E, mp4a.40.2"' />
	  <?php endif; ?>
	  <?php if($itheora_config['WEBM_source'] && $video = $itheora->getWebMVideo()): ?>
	  <source src="<?php echo $video; ?>" type='video/webm; codecs="vp8, vorbis"' />
	  <?php endif; ?>
	  <source src="<?php echo $itheora->getOggVideo(); ?>" type='video/ogg; codecs="theora, vorbis"' />
	  <?php if($itheora_config['flash_fallback'] && $video = $itheora->getMP4Video()): ?>
	  <!-- Flash Fallback. Use any flash video player here. Make sure to keep the vjs-flash-fallback class. -->
	  <object id="flash_fallback_1" class="vjs-flash-fallback" width="<?php echo $width; ?>" height="<?php echo $height; ?>" type="application/x-shockwave-flash"
	    data="http://releases.flowplayer.org/swf/flowplayer-3.2.1.swf">
	    <param name="movie" value="http://releases.flowplayer.org/swf/flowplayer-3.2.1.swf" />
	    <param name="allowfullscreen" value="true" />
	    <param name="flashvars" value='config={"playlist":["<?php echo $itheora->getPoster(); ?>", {"url": "<?php echo $video; ?>","autoPlay":false,"autoBuffering":true}]}' />
	    <!-- Image Fallback. Typically the same as the poster image. -->
	    <img src="<?php echo $itheora->getPoster(); ?>" width="<?php echo $width; ?>" height="<?php echo $height; ?>" alt="Poster Image"
	      title="No video playback capabilities." />
	  </object>
	  <?php endif; ?>
	</video>
	<!-- Download links provided for devices that can't play video in the browser. -->
	<p class="vjs-no-video"><strong>Download Video:</strong>
	  <?php if(($itheora_config['MP4_source']) && $video = $itheora->getMP4Video()): ?>
	  <a href="<?php echo $video; ?>" target="_parent">MP4</a>,
	  <?php endif; ?>
	  <?php if($itheora_config['WEBM_source'] && $video = $itheora->getWebMVideo()): ?>
	  <a href="<?php echo $video; ?>" target="_parent">WebM</a>,
	  <?php endif; ?>
	  <a href="<?php echo $itheora->getOggVideo(); ?>" target="_parent">Ogg</a><br>
	  <!-- Share script -->
	  <strong>Share this video:</strong>
	  <br />
	  <span><?php echo htmlspecialchars('<object id="' . $itheora->getVideoName() . '" name="' . $itheora->getVideoName() . '" type="application/xhtml+xml" data="' . $itheora->getBaseUrl() . '/itheora.php?' . $key. '=' . $itheora->getVideoName() . '&amp;w=' . $width . '&amp;h=' . $height . '" style="width:' . $width . '; height:' . $height . '"></object>'); ?></span>
	  <br />
	  <?php if($itheora->useFilesInCloud()): ?>
	      <strong>Download using torrent:</strong>
	      <?php if(($itheora_config['MP4_source']) && $video = $itheora->getMP4Video()): ?>
	      <a href="<?php echo $video; ?>?torrent" target="_parent">MP4.torrent</a>,
	      <?php endif; ?>
	      <?php if($itheora_config['WEBM_source'] && $video = $itheora->getWebMVideo()): ?>
	      <a href="<?php echo $video; ?>?torrent" target="_parent">WebM.torrent</a>,
	      <?php endif; ?>
	      <a href="<?php echo $itheora->getOggVideo(); ?>?torrent" target="_parent">Ogg.torrent</a><br>
	  <?php endif; ?>
	  <!-- Support VideoJS by keeping this link. -->
	    <small>Powered by <a href="http://videojs.com" target="_parent">VideoJS</a> and <a href="https://github.com/marionline/itheora3-fork" target="_parent">itheora3-fork</a></small>
	</p>
      </div>
      <!-- End VideoJS -->
<?php

}

/**
 * getBaseUrl 
 * 
 * @access public
 * @return string
 */
function getBaseUrl() {
    return strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, 5)) == 'https://' ? 'https://' : 'http://' 
	. $_SERVER['HTTP_HOST']
	. pathinfo($_SERVER['PHP_SELF'], PATHINFO_DIRNAME);
}

/**
 * rrmdir 
 * Remove directory recursivley
 * http://www.php.net/manual/en/function.rmdir.php#98622
 * 
 * @param mixed $dir 
 * @access public
 * @return void
 */
function rrmdir($dir) {
    if (is_dir($dir)) {
	$objects = scandir($dir);
	foreach ($objects as $object) {
	    if ($object != "." && $object != "..") {
		if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
	    }
	}
	reset($objects);
	rmdir($dir);
    }
} 
