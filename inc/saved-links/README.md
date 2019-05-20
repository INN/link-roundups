# Updating WP_List_Table

class-wp-list-table-clone.php contains the file WordPress/wp-admin/includes/class-wp-list-table.php as seen at [Wordpress/Wordpress tag 5.1.1](https://github.com/WordPress/WordPress/blob/5.1.1/wp-admin/includes/class-wp-list-table.php).

If you would like to update this file:

1. Download that file, at the latest stable version of WordPress
2. `mv class-wp-list-table.php class-wp-list-table-clone.php`
3. Edit `class-wp-list-table-clone.php`, replacing "class WP_List_Table" with "class clone_WP_List_Table" to prevent naming conflicts
4. please check that this plugin continues to work.

## Why aren't we using WP_List_Table directly?

As [the WordPress Codex says](https://codex.wordpress.org/Function_Reference/WP_List_Table):

> This class's access is marked as private. That means it is not intended for use by plugin and theme developers as it is subject to change without warning in any future WordPress release. If you would still like to make use of the class, you should make a copy to use and distribute with your own project, or else use it at your own risk.

And as @aschweigert said on 2015-08-11:

> if they say you should make a copy of it you should probably make a copy of it

So now there's a copy of it.

It needs to be updated on occasion, because of messages like this:

> Declaration of Saved_Links_List_Table::bulk_actions($which) should be compatible with clone_WP_List_Table::bulk_actions($which = '')
