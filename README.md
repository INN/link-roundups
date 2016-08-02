# Link Roundups

A WordPress plugin to make it easy to collect links from around the web, turn them into roundup posts and streamline the production of daily/weekly roundup newsletters using MailChimp. Built and maintained by [INN Nerds](http://nerds.inn.org).

**Contributors:** The INN Nerds (David Ryan, Ryan Nagle, Ben Keith, Will Haynes, Adam Schweigert) and Project Argo (Corey Daley, Chris Amico, Wesley Lindamood)

**Tags:** link, newsletter, widget, mailchimp

**Requires at least:** 4.1

**Tested up to:** 4.2.3

**Stable tag:** 0.4.1

**License:** GPLv2 or later

**License URI:** http://www.gnu.org/licenses/gpl-2.0.html


## Overview

The Link Roundup plugin allows you to:

- Curate links
- Create link roundup posts
- Optionally send the roundup posts to MailChimp to be distributed to your subscribers

It also includes two widgets:

- A widget to display your recently saved links
- A widget to display your recent link roundup posts (with the ability to limit by category)

The plugin includes a browser bookmark that you can drag to your browser's bookmark bar, allowing you to save links directly to your WordPress site. This bookmark attempts to pre-fill fields like link title, source, URL and featured image and also allows you to add a description to each link viewable when published on your site.

In its previous life, Link Roundups was called Argo Links.


## Installation

Link Roundups can be installed like any other WordPress plugin.

1. Download the contents of this repository.
2. Unzip the package and rename the folder to "link-roundups" (the folder will be called "link-roundups-master" but this may cause problems if you don't rename it before uploading to your site)
3. Upload the folder to your WordPress installation in the wp-content/plugins directory
4. Login to WordPress, click on Plugins in the left hand menu
5. Select the Link Roundups plugin and click "activate"
6. Review the plugin settings under the Settings > Link Roundups menu

Installation directly from the WordPress.org plugin directory coming soon!

## Features

#### Saved Links
![New Saved Link](https://raw.githubusercontent.com/INN/link-roundups/master/docs/img/new-saved-link.png)

Curate links from around the web and save them in WordPress using a handy browser bookmark. You can also add your own descriptions and organize saved links using tags.

#### Saved Links Widget

Display a feed of your recent Saved Links, optionally filtered by tags.

#### Link Roundups
![Recent Saved Links Panel in Link Roundups Editor](https://raw.githubusercontent.com/INN/link-roundups/master/docs/img/link-roundups-passthru.png)

Build Link Roundup posts using a panel that displays and filters your recent Saved Links. Select the links you want to include and send them to the editor to compose your roundup posts.

#### Link Roundups Widget

Display most recent Link Roundup posts by date. Optionally, limit the roundup posts displayed by category.

#### MailChimp API Integration

Simplify your workflow by sending Link Roundup posts directly to MailChimp. Create a template for your roundups using a number of special template tags and then create a new MailChimp campaign directly from WordPress. You can even include sponsored links.

#### Rename Link Roundups

If you would prefer to call your roundup posts something else (daily digest, for example) you can rename the singular (default: Link Roundup) and plural form (default: Link Roundups) as well as the slug for posts in the Link Roundups custom post type. 

#### Custom HTML for Displaying Links

Link Roundup posts have some default styling for your saved links to make sure your posts look great out of the box. If you'd prefer to modify the HTML output for Save Links or styling for sponsored links, you can do that from the plugin settings.


## [Documentation](docs/readme.md)

1. [Collect and tag links](docs/saved-links.md)
2. [Create link roundups](docs/link-roundups.md)
3. [Send your roundup to MailChimp](docs/mailchimp.md)
4. [Using Saved Link and Link Roundup widgets](docs/widgets.md)
5. [__More →__](docs/readme.md)


## Development

For information on the development status of this plugin, check the [development milestones on github](https://github.com/INN/link-roundups/milestones).

If you'd like to contribute to the project, please see our [contributing guidelines](contributing.md).
