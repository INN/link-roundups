=== Link Roundups ===
Contributors: inn_nerds
Donate link: https://inn.org/donate
Tags: newsletters, mailchimp, links, curation, aggregation
Requires at least: 4.1
Tested up to: 4.2.3
Stable tag: 0.4.1
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Collect links from around the web, turn them into roundup posts and streamline the production of daily/weekly roundup newsletters using MailChimp.

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

= 0.4.1 =

- The default query for Saved Links in the roundup editor is now for the last 30 days
- Fixed bug with Saved Links RSS feed not displaying to logged-out users

= 0.4.0 =

- Added "roundup block" shortcode and accompanying post editor user interface for editing blocks of saved links
- Manage front-end Javascript dependencies using Bower
- Manage PHP dependencies using Composer
- Uses the wordpress-mailchimp-tools PHP package, brought to you by INN
- Added MailChimp campaign editor meta box to the post editor
- Added MailChimp campaign settings for roundup posts
- Moved MailChimp API Key settings to Settings > MailChimp Settings menu

= 0.3.2 =

- Submitted the plugin to WordPress.org
- Removed all references to Argo Links from the code (except where necessary for migrations and updates)
- Push to MailChimp functionality: if a campaign for a Link Roundup was deleted in Mailchimp, don't show the "Edit in MailChimp" link in the post editor
- Automated flushing permalink settings when setting custom slugs for Link Roundups
- If we're able to find a featured image for a Saved Link, display it alongside the link on the front end
- Display an admin notice when the plugin fails to import a featured image for a Saved Link
- Add an option to dequeue Link Roundups front end CSS
- Provide a general way of adding classes to individual Saved Link shortcodes to make customization for different types of links possible
- Use TinyMCE editor for Saved Link descriptions
- Provide a link to edit Saved Links in the "Recent Saved Links" meta box on Link Roundup editor pages
- Fix display of Saved Link archive pages
- Note on the update/upgrade page that instances of "Argo Links Widget" must be replaced with "Saved Links Widget" after update

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
