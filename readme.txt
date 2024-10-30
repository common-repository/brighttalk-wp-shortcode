=== brighttalk-wp-shortcode ===

Contributors: brighttalk
Tags: video, webinar, webcast, brighttalk, lead generation, audience, b2b
Requires at least: 4.0.0
Tested up to: 5.5.1
Stable tag: 5.5.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WordPress shortcode support for BrightTALK channel

== Description ==

This plugin allows a wordpress author to embed the BrightTALK media player in their site using a short code.

The BrightTALK Media Player deals with the registration and playback in page for live, recorded and upcoming presentations.

The BrightTALK registration process has been updated providing single sign on and registration powered by BrightTALK smert form processes.

To embed content you must specify the BrightTALK channel ID, you can also specify the comm ID of the presentation.

> `[BrightTALK channelid=1166 commid=0 displaymode=standalone track='tracking data']`

- channelid = BrightTALK channel that is to be used
- commid = BrightTALK communication ID that is to be displayed or featured
- displaymode = standalone (content only) or channellist (content plus listing of other content in channel)
- track = optional tracking data, passed into reporting

This will display the most recent piece of content and a listing of upcoming and recorded events.

The BrightTALK Platform has over 50,000 webinars and videos from leading companies and thought leaders.

For More information see https://developer.brighttalk.com/integrations/wordpress/brighttalk-wp-short-code/

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress

There are no settings options at this time

== Frequently Asked Questions ==

= What happens if I just specify a channel id? =
Then the presentation that is either most recently recorded or about to start will be featured.

== Screenshots ==
1. BrightTALK media player

== Changelog ==
= 2.3.1 =
* Fixed the player hostname

= 2.1.2 =
* Bug fixes

= 2.1.0 =
* Added tracking support
* Tested WP4.7

= 2.0.1 =
* Fixed height for channel listing
* New HTML5 based player
* Ability to show content and channel details or just content

= 2.0.0 =
* BrightTALK HTML5 Responsive Player
* Support for displaying content + channel listing or content only

= 1.0.1 =
* Fixed example in documentation
* Tested WP4.6

= 1.0 =
* Initial shortcode release - combined meida player and channel listing
