<?php
//require_once('lib/ogg.class.php');
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/../lib');
require_once(dirname(__FILE__) . '/../lib/Zend/Cache.php');
/**
 * itheora 
 * 
 * @package 
 * @copyright 2011
 * @author Mario Santagiuliana <mario@marionline.it>
 */
class itheora {

    protected $_videoName = 'example'; // Default value of videoName
    protected $_videoErrorName = 'error';
    protected $_videoStoreDir;
    protected $_baseUrl;
    protected $_videoUrl;
    protected $_files = array();
    protected $_externalVideo = false; // Is set external video?
    protected $_externalUrl = ''; // External video URL if video is not locally
    protected $_supported_video = array('ogg', 'ogv', 'webm', 'mp4');
    protected $_supported_image = array('png', 'jpg', 'gif');
    protected $_mimetype_video = array();
    protected $_mimetype_image = array();
    protected $_cache;
    protected $_s3 = null;
    protected $_s3_config;

    /**
     * __construct 
     * 
     * @param float $cache_lifetime Pass cache lifetime in seconds default 60
     * @param string $cache_dir Provide another cache directory
     * @param AmazonS3 $s3 Provide AmazonS3 object
     * @param array $s3_config Provide itheora_config to retrive s3 bucket_name 
     * @access protected
     * @return void
     */
    function __construct($cache_lifetime = 43200 , $cache_dir = null, &$s3 = null, array $s3_config = array()) {
	// Create supported mimetype image and video
	foreach($this->_supported_image as $extension){
	    if($extension == 'jpg')
		$extension = 'jpeg';
	    $this->_mimetype_image[] = 'image/' . $extension;
	}
	foreach($this->_supported_video as $extension){
	    $this->_mimetype_video[] = 'video/' . $extension;
	}

	// Local video store directory
	$this->_videoStoreDir = dirname(__FILE__) . '/../video';

	// The name of the server host
	$this->_baseUrl = strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, 5)) == 'https://' ? 'https://' : 'http://' 
	    . $_SERVER['HTTP_HOST']
	    . pathinfo($_SERVER['PHP_SELF'], PATHINFO_DIRNAME);
	$this->_videoUrl = $this->_baseUrl . '/video';

	// Start cache
	$frontendOptions = array(
	    'lifetime' => $cache_lifetime,
	    'automatic_serialization' => true,
	    'automatic_cleaning_factor' => 10,
	);

	if($cache_dir === null) {
	    $backendOptions = array('cache_dir' => dirname(__FILE__) . '/../cache/');
	} else {
	    $backendOptions = array('cache_dir' => $cache_dir);
	}

	$this->_cache = Zend_Cache::factory('Core',
	    'File',
	    $frontendOptions,
	    $backendOptions);

	// Set AmazonS3 object and config
	if($s3 !== null){
	    $this->_s3 = $s3;
	}
	$this->_s3_config = $s3_config;

	// Get file, default is example
	//$this->getFiles();
    }

    /**
     * completeUrl 
     * Return the complete Url to a file
     * 
     * @param string $file 
     * @access protected
     * @return string 
     */
    protected function completeUrl($file) {
	if($this->_externalVideo){
	    return $this->_externalUrl . $file;
	} else {
	    return $this->_videoUrl . '/' . $this->_videoName . '/' . $file;
	}
    }

    /**
     * check_founded_files 
     * Check if $this->_files store some files or not
     * 
     * @access protected
     * @return false | true if a file is founded
     */
    protected function check_founded_files() {
        if (count($this->_files) == 0)
	    return false;
	else
	    return true;
    }

    /**
     * is_supported_image 
     * 
     * @param mixed $file_headers Pass headers with get_headers($file, 1) 
     * @access protected
     * @return bool
     */
    protected function is_supported_image($file_headers) {
	// Use in_array function to check if mime-type is supported_video
	if(isset($file_headers['Content-Type'])){
	    return in_array($file_headers['Content-Type'], $this->_mimetype_image);
	} else {
	    throw new Exception('$file_headers[\'Content-Type\'] not set, please pass $file_headers with get_headers($file, 1).');
	}
    }
    /**
     * is_supported_video 
     * 
     * @param mixed $file_headers Pass headers with get_headers($file, 1)
     * @access protected
     * @return bool
     */
    protected function is_supported_video($file_headers) {
	// Use in_array function to check if mime-type is supported_video
	if(isset($file_headers['Content-Type'])){
	    return in_array($file_headers['Content-Type'], $this->_mimetype_video);
	} else {
	    throw new Exception('$file_headers[\'Content-Type\'] not set, please pass $file_headers with get_headers($file, 1).');
	}
    }

    /**
     * getExternalFiles
     * Retrive the files stored remotely via http, return true on
     * success 
     * 
     * @access protected
     * @return bool
     */
    protected function getExternalFiles() {
	$id = str_replace(array('/', '\\', ':', '.', '-'), '_', $this->_externalUrl . $this->_videoName);
	if(!($files = $this->_cache->load($id))) {
	    $extensions = array_merge($this->_supported_video, $this->_supported_image);
	    foreach($extensions as $extension) {
		// Basename file
		$file = $this->_videoName . '.' . $extension;
		// Get headers of $file
		$headers = get_headers($this->completeUrl($file), 1);
		// Check if HTTP respons is not a 404 error
		if(substr($headers[0], 9, 3) != '404') {
		    // Check if file is supported
		    if($this->is_supported_image($headers) || $this->is_supported_video($headers)) {
			$this->_files[$extension] = $file;
		    }
		} elseif($this->_videoName == $this->_videoErrorName) {
		    throw new Exception('No error video is founded');
		}
	    }
	    $this->_cache->save($this->_files, $id, array('external'));
	} else {
	    $this->_files = $files;
	}

	if(!$this->check_founded_files()) {
	    return $this->setVideoName($this->_videoErrorName);
	} else {
	    return true;
	}
    }

    /**
     * video
     * Return the path of a video 
     * 
     * @access protected
     * @return string
     */
    protected function video() {
	return $this->_videoStoreDir . '/' . $this->_videoName;
    }

    /**
     * getLocalFiles 
     * Retrive the files stored locally, return true on success 
     * 
     * @access protected
     * @return bool False if no file are found
     */
    protected function getLocalFiles() {
	$id = str_replace(array('/', '\\', ':', '.', '-'), '_', $this->_videoStoreDir . $this->_videoName);
	if(!($files = $this->_cache->load($id))) {
	    if(is_dir($this->video())){
		if ( $handle = opendir($this->video()) ) {
		    while (false !== ($file = readdir($handle))) {
			$this->_files[pathinfo($file, PATHINFO_EXTENSION)] = $file;
		    }
		    closedir($handle);
		}
		$this->_cache->save($this->_files, $id, array('internal'));
	    } else {
		if($this->_videoName != $this->_videoErrorName) {
		    $this->setVideoName($this->_videoErrorName);
		    return $this->getLocalFiles();
		}
	    }
	} else {
	    $this->_files = $files;
	}

	return $this->check_founded_files();
    }

    /**
     * getFilesFromCloud 
     * Retrive the files stored in the cloud Amazon S3, return true on
     * success 
     * 
     * @access protected
     * @return bool False if no file are found
     */
    protected function getFilesFromCloud() {
	// If bucket_name is wrong
	if(!$this->_s3->if_bucket_exists($this->_s3_config['bucket_name'])) {
	    throw new Exception('The bucket ' . $this->_s3_config['bucket_name'] . ' doesn\'t exists');
	}
	$id = str_replace(array('/', '\\', ':', '.', '-'), '_', $this->_s3_config['bucket_name'] . $this->_videoName);
	if(!($files = $this->_cache->load($id))) {
	    // Get the objects list
	    if($this->_videoName == $this->_videoErrorName) {
		$respons = $this->_s3->list_objects($this->_s3_config['bucket_name'], array('prefix' => $this->_videoName . '/'));
		if(!$respons->isOK())
		    throw new Exception('No error video is founded');
	    }
	    $objects = $this->_s3->get_object_list($this->_s3_config['bucket_name'], array('prefix' => $this->_videoName . '/'));
	    foreach($objects as $i => $file) {
		if($i > 0)
		    $this->_files[pathinfo($file, PATHINFO_EXTENSION)] = $file;
	    }
	    $this->_cache->save($this->_files, $id, array('cloudfiles'));
	} else {
	    $this->_files = $files;
	}

	if(!$this->check_founded_files()) {
	    return $this->setVideoName($this->_videoErrorName);
	} else {
	    return true;
	}
    }

    /**
     * getFiles 
     * Retrive the files
     * 
     * @access public
     * @return bool
     */
    public function getFiles() {
	// Get the files, video and picture to use
	if($this->_externalVideo){
	    // Videos are store remotely
	    return $this->getExternalFiles();
	} else {
	    if($this->_s3 === null){
		// Videos are store locally
		return $this->getLocalFiles();
	    } else {
		// Videos are store in the Cloud access with Zend_Cloud
		return $this->getFilesFromCloud();
	    }
	}
    }

    /**
     * Check if an url is existed
     * Take from here:
     * http://www.php.net/manual/en/function.file-exists.php#76420
     *
     * @param  string    $url
     * @return bool      True if the url is accessible and false if the url is unaccessible or does not exist
     * @throws Exception An exception will be thrown when Curl session fails to start
     */
    protected function url_exists($url)
    {
        if (null === $url || '' === trim($url))
        {
            throw new Exception('The url to check must be a not empty string');
        }
       
	$pattern='|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i';
	if(preg_match($pattern, $url) > 0) 
	    return true;
	else 
	    return false;

        $handle   = curl_init($url);

        if (false === $handle)
        {
            throw new Exception('Fail to start Curl session');
        }

        curl_setopt($handle, CURLOPT_HEADER, false);
        curl_setopt($handle, CURLOPT_NOBODY, true);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, false);

        // grab Url
        $connectable = curl_exec($handle);

        // close Curl resource, and free up system resources
        curl_close($handle);   
        return $connectable;
    }

    /**
     * setVideoName 
     * 
     * @param string $videoName 
     * @access public
     * @return bool             True if videoName is set, false if there are some errors
     */
    public function setVideoName($videoName) {
	$this->_files = array();
	if($this->url_exists($videoName)){
	    // Check if is supported video
	    if($this->is_supported_video(get_headers($videoName, 1))) {
		// Ok url is valid, the video is supported and store remotely
		$this->_externalVideo = true;
		$url = parse_url($videoName);
		$this->_videoName = pathinfo($url['path'], PATHINFO_FILENAME);
		$this->_externalUrl = str_replace(pathinfo($url['path'], PATHINFO_BASENAME), '', $url['scheme'] . '://' . $url['host'] . $url['path']);
	    } else {
		$this->_videoName = $this->_videoErrorName;
	    }
	    // Now get and search other files
	    return $this->getFiles();
	} else {
	    // It is not a valid url, check if it is a valid name and get file
	    if(preg_match('/^[a-z0-9._-]+$/i', $videoName)){
		$this->_videoName = $videoName;
	    } else {
		$this->_videoName = $this->_videoErrorName;
	    }
	    return $this->getFiles();
	}

	return false;
    }

    /**
     * getVideoName 
     * 
     * @access public
     * @return string
     */
    public function getVideoName() {
	return $this->_videoName;
    }

    /**
     * setVideoDir 
     * 
     * @param string $videoDir 
     * @access public
     * @return void
     */
    public function setVideoDir($videoDir) {
	$this->_videoStoreDir = $videoDir;
    }
    
    /**
     * getVideoDir 
     * 
     * @access public
     * @return string
     */
    public function getVideoDir() {
	return $this->_videoStoreDir;
    }
    
    /**
     * setBaseUrl 
     * 
     * @param string $baseUrl 
     * @access public
     * @return void
     */
    public function setBaseUrl($baseUrl) {
	$this->_baseUrl = $baseUrl;
    }

    /**
     * getBaseUrl 
     * 
     * @access public
     * @return string
     */
    public function getBaseUrl() {
	return $this->_baseUrl;
    }

    /**
     * setVideoUrl 
     * 
     * @access public
     * @return string
     */
    public function setVideoUrl($videoUrl) {
	$this->_videoUrl = $videoUrl;
    }

    /**
     * getVideoUrl 
     * 
     * @access public
     * @return string
     */
    public function getVideoUrl() {
	return $this->_videoUrl;
    }

    /**
     * getPoster 
     * Return the URL to the picture to use like a postern in the video
     * player
     * 
     * @param array $filetypes default value ('png', 'jpg', 'gif')  
     * @access public
     * @return string | false
     */
    public function getPoster( $filetypes = null ) {
	if($filetypes === null){
	    $filetypes = $this->_supported_image;
	}
	if($this->_externalVideo){
	    $string = implode('_', $filetypes) . $this->_externalUrl . $this->_videoName . 'external_url';
	} elseif($this->_s3 !== null) {
	    $string = implode('_', $filetypes) . $this->_s3_config['bucket_name'] . $this->_videoName . 'external_url';
	} else {
	    $string = implode('_', $filetypes) . $this->_videoStoreDir . $this->_videoName . 'internal_url';
	}
	$id = str_replace(array('/', '\\', ':', '.', '-'), '_', $string);

	if(!($return = $this->_cache->load($id))) {
	    if(is_array($filetypes)){
		foreach( $filetypes as $filetype ) {
		    if(isset($this->_files[$filetype])) {
			if($this->_s3 !== null) {
			    return $this->_s3->get_object_url($this->_s3_config['bucket_name'], $this->_files[$filetype]);
			} else {
			    return $this->completeUrl($this->_files[$filetype]);
			}
		    }
		}
	    }
	} else {
	    return $return;
	}

	// If no pictures are found return false
	return false;
    }

    /**
     * getPosterSize 
     * return the getimagesize array, need GD Library, of the picture use
     * like poster
     * 
     * @param array $filetypes default value ('png', 'jpg', 'gif')  
     * @access public
     * @return array | false if no pictures are found
     */
    public function getPosterSize( $filetypes = null ) {
	if($filetypes == null){
	    $filetypes = $this->_supported_image;
	}
	if($this->_externalVideo){
	    $string = implode('_', $filetypes) . $this->_externalUrl . $this->_videoName . 'external_size';
	} elseif($this->_s3 !== null) {
	    $string = implode('_', $filetypes) . $this->_s3_config['bucket_name'] . $this->_videoName . 'external_size';
	} else {
	    $string = implode('_', $filetypes) . $this->_videoStoreDir . $this->_videoName . 'internal_size';
	}
	$id = str_replace(array('/', '\\', ':', '.', '-'), '_', $string);
	if(!($return = $this->_cache->load($id))) {
	    if(is_array($filetypes)){
		foreach( $filetypes as $filetype ) {
		    if(isset($this->_files[$filetype]))
			if($this->_externalVideo){
			    $image_size = getimagesize($this->completeUrl($this->_files[$filetype]));
			    $this->_cache->save($image_size, $id, array('imagesize'));
			    return $image_size;
			} elseif($this->_s3 !== null) {
			    $image_size = getimagesize($this->getPoster($filetypes));
			    $this->_cache->save($image_size, $id, array('imagesize'));
			    return $image_size;
			} else {
			    $image_size = getimagesize($this->_videoStoreDir . '/' . $this->_videoName .  '/' .$this->_files[$filetype]);
			    $this->_cache->save($image_size, $id, array('imagesize'));
			    return $image_size;
			}
		}
	    }
	} else {
	    return $return;
	}

	// If no pictures are found return false
	return false;
    }

    /**
     * getVideo 
     * Return the URL of the video with provided extension
     * 
     * @param string $extension 
     * @access protected
     * @return string | false if the extension provided is not correct
     */
    protected function getVideo($extension) {
	if(isset($this->_files[$extension]))
	    if($this->_s3 !== null) {
		return $this->_s3->get_object_url($this->_s3_config['bucket_name'], $this->_files[$extension]);
	    } else {
		return $this->completeUrl($this->_files[$extension]);
	    }
	else
	    return false;
    }

    /**
     * getOggVideo 
     * 
     * @access public
     * @return string | false
     */
    public function getOggVideo() {
	if($video = $this->getVideo('ogg'))
	    return $video;
	elseif($video = $this->getVideo('ogv'))
	    return $video;
	else
	    return false;
    }

    /**
     * getMP4Video 
     * 
     * @access public
     * @return string | false
     */
    public function getMP4Video() {
	return $this->getVideo('mp4');
    }

    /**
     * getWebMVideo 
     * 
     * @access public
     * @return string | false
     */
    public function getWebMVideo() {
	return $this->getVideo('webm');
    }

    /**
     * getSupportedMimetype 
     * 
     * @access public
     * @return array of supported mimetype
     */
    public function getSupportedMimetype() {
	return array_merge($this->_mimetype_video, $this->_mimetype_image);
    }

    /**
     * useFilesInCloud 
     * are itheora using files from Amazon S3?
     * 
     * @access public
     * @return bool
     */
    public function useFilesInCloud() {
	if( $this->_s3 === null ) {
	    return false;
	} else {
	    return true;
	}
    }
}
