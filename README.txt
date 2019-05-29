=== Link Roundups ===
Contributors: innlabs
Donate link: https://inn.org/donate
Tags: newsletters, mailchimp, links, curation, aggregation
Requires at least: 4.2
Tested up to: 5.2
Stable tag: 1.0.1
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 5.6
Text Domain: link-roundups


Collect links from around the web, turn them into roundup posts and streamline the production of daily/weekly roundup newsletters using MailChimp.

== Description ==

= Current plugin features =

* **Link Roundups** Create curated lists of links based on Saved Links via the WordPress dashboard
* **Saved Links** Add and edit links to places around the web via the WordPress dashboard
* **Custom HTML for links** Customize the presentation of Saved Links in Link Roundups
* **Link Roundups Widget** Display most recent Link Roundup posts by date
* **Saved Links Widget** Display a feed of your recent Saved Links, optionally filtered by tags
* **Browser Bookmark** Create new Saved Links via a browser bookmarklet, on supported WordPress configurations
* **MailChimp Integration** Create new MailChimp campaigns based on a Link Roundup

== Installation ==

Via WordPress.org:

1. Install the Link Roundups plugin via the Wordpress.org plugin directory
2. Activate the plugin
3. Navigate to the Admin -> Link Roundups -> Options page to configure the plugin
4. Done!

For other installation methods, [see this plugin's installation methods on GitHub](https://github.com/INN/link-roundups/blob/master/docs/installation.md).

[Read more about installing plugins here](https://codex.wordpress.org/Managing_Plugins#Installing_Plugins).

== Changelog ==

= 1.0.1 =

This release contains minor bug fixes for the 1.0.1 release.

- Fixes a syntax error discovered via static analysis that blocked wordpress.org release of version 1.0.0. Pull request [#171](https://github.com/INN/link-roundups/pull/171) for issue [#170](https://github.com/INN/link-roundups/issues/170).
- Fixes a number of [WordPress code standards](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards#rulesets) issues and undefined variables. Pull request [#175](https://github.com/INN/link-roundups/pull/175) for issues [#174](https://github.com/INN/link-roundups/issues/174) and [#169](https://github.com/INN/link-roundups/issues/169).
- Updates Grunt developer tools with updated LESS-to-CSS pipeline, new translation pipeline using [`wp i18n`](https://developer.wordpress.org/cli/commands/i18n/make-pot/). Pull request [#181](https://github.com/INN/link-roundups/pull/181) for issue [#180](https://github.com/INN/link-roundups/issues/180).
- Adds translation notes for several strings that contained placeholders. Pull request [#181](https://github.com/INN/link-roundups/pull/181) for issue [#156](https://github.com/INN/link-roundups/issues/156).

= 1.0.0 =

- Tested up to WordPress 5.2 and PHP 7.3
- Reenables the "Save to Site" bookmarklet for saving links for Link Roundups, if the site admin is willing to use the [Press This](https://wordpress.org/plugins/press-this/) plugin to enable this feature. Pull request [#159](https://github.com/INN/link-roundups/pull/159) for issue [#130](https://github.com/INN/link-roundups/issues/130).
- Updates the [WordPress MailChimp Tools](https://github.com/INN/wordpress-mailchimp-tools) submodule to use version 3 of the MailChimp API, and gain various compatibility updates for modern PHP versions. Upgrading to the new MailChimp API means that users of the MailChimp campaign functionality in the Link Roundups plugin can now use MailChimp editable content areas in their templates, as described [in our documentation](https://github.com/INN/link-roundups/blob/master/docs/mailchimp.md).
- Updates installation documentation. Pull request [#139](https://github.com/INN/link-roundups/pull/139) for issue [#145](https://github.com/INN/link-roundups/issues/145).
- Applies the `'widget_title'` filter to the title of the Link Roundups widget. Pull request [#152](https://github.com/INN/link-roundups/pull/152) for issue [#104](https://github.com/INN/link-roundups/issues/104).
- Wraps the `Source:` label in the Saved Links List widget in a `span.source-label`. Pull request [#139](https://github.com/INN/link-roundups/pull/139) for issue [#144](https://github.com/INN/link-roundups/issues/144).
- Sets HTML5 input types on widget forms. Pull request [#139](https://github.com/INN/link-roundups/pull/139) for issue [#143](https://github.com/INN/link-roundups/issues/143).
- Fixes the saved_links_widget constructor for error-free PHP7 compatibility. Pull request [#137](https://github.com/INN/link-roundups/pull/137) for issue [#132](https://github.com/INN/link-roundups/issues/132).
- Updates the local clone of WP_List_Table, fixing compatibility, and updating docs for that process. Pull request [#139](https://github.com/INN/link-roundups/pull/139) for issues [#128](https://github.com/INN/link-roundups/issues/128) and [#118](https://github.com/INN/link-roundups/issues/118).
- Updates Travis automated testing to support PHP 5.6, 7.1, 7.2. Pull request [#138](https://github.com/INN/link-roundups/pull/138) for issue [#135](https://github.com/INN/link-roundups/issues/135).
- Improves maintainer docs. Pull request [#139](https://github.com/INN/link-roundups/pull/139) for issue [#140](https://github.com/INN/link-roundups/issues/140).


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
