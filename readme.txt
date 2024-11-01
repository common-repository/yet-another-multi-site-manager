=== Plugin Name ===
Contributors: hazardcell
Tags: wordpress mu, multi-site
Requires at least: 2.7
Tested up to: 2.8.6
Stable tag: 0.1.1
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3561852

Allows blogs to be created on multiple domain names while maintaining only one main site for all domain names.

== Description ==

**This plugin is for WordPress MU**

This plugin basically allows you to host blogs on multiple domain names while maintaining one main site.

For example, the main site could be example.com but users will also be able to sign up for blogs on example.net, example.org or any other domain name that you choose to add.

* All the sites will have the same settings
* No extra blogs/tables are created for the additional domain names
* All blogs will be viewable on the Site Admin blog list

Alternatively, if you would like to request a feature and/or report a bug, please visit the [forum](http://wordpress.org/tags/yet-another-multi-site-manager) or the [trac repository](http://plugins.trac.wordpress.org/browser/yet-another-multi-site-manager/). You will have to login with your WordPress.org username and password to access these.

== Installation ==

1. Create a new directory in `/wp-content/plugins/` called `yamm`.
2. Upload `yet-another-multi-site-manager.php` & `yamm-signup.php` into the directory you just created
3. Upload `sunrise.php` to `/wp-content/`. If there already is a `sunrise.php` file there you will have to merge the two files.
4. Edit `wp-config.php` and uncomment the SUNRISE definition line: `define( 'SUNRISE', 'on' );`
5. As the site admin activate the plugin through the 'Plugins' menu in WordPress MU.
6. Go to 'Site Admin->Yet Another Multi-Site Manager' and add domain names/change the signup page slug.

**NOTE:** If you are using the WordPress MU Domain Mapping plugin, use `dm-sunrise.php` instead. You will have to rename it `sunrise.php` once you have moved it to `/wp-content/`.

== Changelog ==

= 0.1.1 =
* Changed table names to variables to accomodate custom table prefixes 
* Updated dm-sunrise.php for compatibility with WordPress MU Domain Mapping 0.4.3

= 0.1 =
* Initial public release

== Known Issues/Limitations ==

* Remote login does not work with this version
* Only tested with VHOST installs in root directory
