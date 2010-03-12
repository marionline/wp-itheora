=== Plugin Name ===
Contributors: Mario Santagiuliana
Donate link: http://www.marionline.it/
Tags: video, streaming, theora, ogg, itheora
Requires at least: 2.5
Tested up to: 2.9.1
Stable tag: v0.1.2

== Description ==

With this plugin you can use itheora script (included in this plugin) to add theora video on your blog.
ITheora is a PHP script allowing you to broadcast ogg/theora/vorbis only videos (and audios) files. It may suit the usual blogger or the expert webmaster.

== Installation ==

1. Upload `wp-itheora` to the `/wp-content/plugins/` directory
2. Make wp-itheora/itheora/admin/config/player.php writeable or wp-itheora/itheora/admin/config/ writable
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Videos must be stored in wp-itheora/itheora/data/

== Frequently Asked Questions ==
= Usage? =
Just create your player from admin menu and copy the html code in your Wordpress html editor.
You need to use "<!--start_itheora-->" and "<!--end_itheora-->" tags to prevent html modifcation from wordpress.
The code generated from wp-itheora contain these tags.
Follow the official documentation for more info, you can skip "Prerequisites" and "Installation"
http://itheora.org/en/install
= Prevent visual editor modification =
The wordpress visual editor modify itheora object code: turn off the visual editor for all your edits, uncheck the visual editor checkbox in your profile.
I'm working to resolve this problem, I want to add a special tag for itheora.

== Screenshots ==
1. Create player
2. Create player: view the code and preview

== Changelog ==
= v0.1 =
This is the first version, there are some bugs to solve but it works fine for me.
= v0.1.1 =
This is for me a stable version with a basic feature. With this version I can create in a easy way an html code for my video to embend in my article, simply copy and paste the code.
= v0.1.2 =
wp-itheora is on Wordpress Plugins Directory too, this version include little change in version name.

== Upgrade Notice ==
Nothing to upgrade.

== TODO ==
* Add integration to wordpress editor (need a workaround to prevent the modification of code by visual mode)
* Add thumbnail features
* Add tag modification features of video
* Add Create playlist option
* Add send file and list file on "data" directory (in future list file on amazon s3)

== FIXED ==
* Resolve bug to fix: error when I give only the filename of a video without extension. Now work for me.
* Wp-itheora is i18n.
