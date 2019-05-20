# Installation

If you would like to use the [Saved Links "Save to Site" bookmarklet](saved-links.md), you may need to [install additional dependencies not included in this plugin](bookmarklet-dependencies.md), regardless of the method you use to install this plugin.

## WordPress Plugin Directory

Install from https://wordpress.org/plugins/link-roundups/ !

## Your WordPress Dashboard

From your site's dashboard, go to Plugins, then "Add New", and search for "Link Roundups" by INN Labs.

## Manual Install

1. Download the latest master [.zip archive](https://github.com/INN/link-roundups/archive/master.zip) from GitHub, or `git clone https://github.com/INN/link-roundups.git`.
2. Extract the .zip archive. 
3. Rename the plugin directory to `link-roundups`. If the directory the plugin files reside in is named anything else, the plugin will not work.
4. Run `composer install` &mdash; this requires [composer](https://getcomposer.org/), the PHP dependency manager.
5. Upload the plugin directory to you server's `wp-content/plugins` directory.
6. Activate the plugin through the [`Plugins` menu](https://codex.wordpress.org/Plugins_Screen) in WordPress.


