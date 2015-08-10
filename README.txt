=== Link Roundups ===
Contributors: inn_nerds
Donate link: http://bit.ly/1N4nSJJ
Tags: newsletters, mailchimp, links, curation, aggregation
Requires at least: 4.1
Tested up to: 4.2.3
Stable tag: 0.3.1
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A WordPress plugin to make it easy to collect links from around the web, turn them into roundup posts and streamline the production of daily/weekly roundup newsletters using MailChimp. Built and maintained by INN Nerds.

== Description ==

= Current plugin features =

* **Link Roundups** Create curated lists of links based on Saved Links via the WordPress dashboard
* **Saved Links** Add and edit links to places around the web via the WordPress dashboard
* **Custom HTML for links** Customize the presentation of Saved Links in Link Roundups
* **Link Roundups Widget** Display most recent Link Roundup posts by date
* **Saved Links Widget** Display a feed of your recent Saved Links, optionally filtered by tags
* **Browser Bookmark** Create new Saved Links via a browser bookmarklet
* **MailChimp Integration** Create new MailChimp campaigns based on a Link Roundup

== Installation ==

1. Install the Link Roundups plugin via the Wordpress.org plugin directory
2. Activate the plugin
3. Navigate to the Admin -> Link Roundups -> Options page to configure the plugin
4. Done!

[Read more about installing plugins here](https://codex.wordpress.org/Managing_Plugins#Installing_Plugins).

== Changelog ==

= 0.3.1 =

- Update readme.txt 'requires at least' to match readme.md
- Add [release.sh](https://github.com/INN/link-roundups/blob/master/release.sh) for pushing changes to wordpress.org
- Fix the browser bookmarklet

= 0.3.0 =

- Initial public release
- Added a Gruntfile.js to manage compiling and minfying CSS and Javascript assets
- Renamed the plugin from Argo Links to Link Roundups
- Removed (most) traces of Argo Links from source code
- Reorganized the project's file, directory layout
- Added an update framework to help move from Argo Links to Link Roundups
- Added ability to rename Link Roundup posts
- Added ability to modify Link Roundup URL slug
- Changed label for "Argo Link This!" button to "Save to Site"
- Added ability to create a MailChimp campaign based on a Link Roundup post
- Prepared the plugin for release on wordpress.org
