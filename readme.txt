=== mySimpleAds Wordpress Ad Manager ===
Contributors: clippersoft
Donate link: http://www.clippersoft.net/
Tags: ad, ads, advertising, adserver, mysimpleads, clippersoft, adsense, rotator, advertisement, affiliate, commercial
Requires at least: 2.5
Tested up to: 3.51
Stable tag: 1.1

The wordpress plugin will allow you to easily place your mySimpleAds Ads anywhere into posts, pages, or templates.

== Description ==

The mySimpleAds Wordpress Ad Manager allows you to easily place your mySimpleAds Ads inside your wordpress posts, pages, or even right in your templates.

Please Note: This requires mySimpleAds from [ClipperSoft](http://www.clippersoft.net/ "ClipperSoft's mySimpleAds").

To use ...

Posts/Pages:
Place [msa_aid=x] for an Ad ID or [msa_gid=x] for an Ad Group ID in your post, where 'x' is the Ad or Ad Group ID.
You can also optionally specify the type of ad code to use - (p) PHP Remote Read, (j) Javascript Injection, (a) Ajax Javascript, like:
[msa_gid=x,c=a] for Ajax Javascript. If you do not specify, it will use PHP Remote Read by default.

Templates:
Place msa_show_ad_id($aid,$code); for an Ad ID or msa_show_group_id($gid,$code); for an Ad Group ID in your template PHP code (you may need to surround the function by PHP tags, if it's outside an existing PHP tag section). The $aid is the Ad ID, the $gid is the Ad Group ID, and $code is type type of ad code to use - 'p' PHP Remote Read, 'j' Javascript Injection, 'a' Ajax Javascript. It defaults to PHP Remote Read.

== Installation ==

1. Upload `mysimpleads_wordpress_ad_manager` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place the code in your posts/pages or templates

In the settings, put in your mySimpleAds URL, such as...
http://www.mysite.com/mysimpleads

== Frequently Asked Questions ==

= What versions of mySimpleAds will this work with? =

It has been tested with version 1.93.

== Screenshots ==

1. The settings and usage page

== Changelog ==

= 1.0 =
* Inital Release
= 1.1 =
* Updated Ad Code

== Upgrade Notice ==

= 1.0 =
n/a
= 1.1 =
Updated Ad Code
