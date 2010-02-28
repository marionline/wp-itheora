=== Plugin Name ===
Contributors: Mario Santagiuliana
Donate link: http://www.marionline.it/
Tags: video, streaming, theora, ogg, itheora
Requires at least: 2.5
Tested up to: 2.9.1
Stable tag: trunk

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
Follow the official documentation, you can skip "Prerequisites" and "Installation"
http://itheora.org/?p=install

== Screenshots ==
Nothing

== Changelog ==
=0.1=
This is the first version, there are some bug to resolve but it work fine for me.

== Upgrade Notice ==
Nothing to upgrade.

== TODO ==
* And need to test all features "Create player".

Other:

* Add integration to wordpress editor (need a workaround to prevent the modification of code by visual mode)
* Add thumbnail features
* Add tag modification features
* Add Create playlist option
* Add send file and list file on "data" directory (in future list file on amazon s3)

== FIX ==
* Resolve bug to fix: error when I give only the filename of a video without extension. Now work for me.
