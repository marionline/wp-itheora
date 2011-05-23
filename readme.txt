=== Plugin Name ===
Contributors: Mario Santagiuliana
Donate link: http://www.marionline.it/
Tags: video, streaming, theora, ogg, itheora
Requires at least: 2.5
Tested up to: 2.9.1
Stable tag: v0.2.1

== Description ==

With this plugin you can use itheora script (included in this plugin) to add theora video on your blog.
ITheora is a PHP script allowing you to broadcast ogg/theora/vorbis only videos (and audios) files. It may suit the usual blogger or the expert webmaster.

== Installation ==

1. Backup your previous videos if you use wp-itheora before version v0.1.2
2. Upload `wp-itheora` to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure wp-itheora

== Frequently Asked Questions ==
= Usage? =
1. Configure wp-itheora from the wp-itheora settings page.
2. Upload your videos on your server or in the amazon s3 cloud. You can use the upload forms from wp-itheora video page.
3. Use [wp-itheora] tag in your post. It is not necessary to disable WordPress visual editor, like in previous version.

= [wp-itheora] tag options? =
You can set:
* video, witch video you want to use (just the filename of your video);
* width and height, dimension of video;
* remote, tell to use video store in the cloud (true or false, default is false);
* skin, which video-js skin to use default (no skin provide), vim, hu or tube;
* alternativeName, if you embend multiple video it is better to tell alternative name to your video;

= How videos are stored? =
Videos are stored in folder with the same filename and with the same folder name. If you change a filename of a video and the filename doesn't match the folder name, you cannot view this video.
In your wp-itheora tag you need to specify just the folder name, wp-itheora will search in the folder to retrive all the files.
For example:
example/     <--is the folder
example.jpg  <-- is the picture in the example folder
example.ogv  <-- is the ogg video
example.webm <-- is the webm video
In wp-itheora you should specify just the name: "example".

= Example =
[wp-itheora]
Present default video with default skin with haight and width of the video poster.

[wp-itheora video=myvideo remote=true]
Present "myvideo" video that is stored in the cloud.

== Screenshots ==
1. WP-Itheora info page
2. WP-Itheora config settings page
3. WP-Itheora manage local file in WP-Itheora video page administration
4. WP-Itheora manage remote file in the cloud in WP-ITheora video page administration

== Changelog ==
= v0.1 =
This is the first version, there are some bugs to solve but it works fine for me.
= v0.1.1 =
This is for me a stable version with a basic feature. With this version I can create in a easy way an html code for my video to embend in my article, simply copy and paste the code.
= v0.1.2 =
wp-itheora is on Wordpress Plugins Directory too, this version include little change in version name.
= v0.1.3 =
wp-itheora use itheora3-fork and video-js to view video. Add support to Amazon S3 cloud storage and now is possible to manage local and remote file. Add shortcode tag to insert video using wp-itheora.
= v0.2.0 =
Fix some mistake in previous version. This is the real new version of wp-itherora.
= v0.2.1 =
Use itheora3-fork version 0.1-beta.

== Upgrade Notice ==
Backup your previous videos if you use wp-itheora before version v0.1.2

== TODO ==
* Add integration to wordpress editor (need a workaround to prevent the modification of code by visual mode)
* Add thumbnail features
* Add Create playlist option
* Correct some mistake in version release

== FIXED ==
* Resolve bug to fix: error when I give only the filename of a video without extension. Now work for me.
* Wp-itheora is i18n.
* Add send file and list file on "data" directory (in future list file on amazon s3)
* Add tag modification features of video
