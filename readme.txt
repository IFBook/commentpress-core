=== CommentPress Core ===
Contributors:      needle, commentpress
Donate link:       https://www.paypal.me/interactivist
Tags:              commentpress, buddypress, groups, blogs, groupblogs, comments, commenting, debate, collaboration
Requires PHP:      7.4
Requires at least: 4.9
Tested up to:      6.9
Stable tag:        4.0.5
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

CommentPress Core allows fine-grained commenting in the margins of a text. Use it to annotate, workshop or debate a social text in a social context.



== Description ==

CommentPress Core is an open source theme and plugin for WordPress that allows readers to comment in the margins of a text. Readers may comment paragraph-by-paragraph, line-by-line, block-by-block or by selecting text (coming soon to touch devices).

Annotate, gloss, workshop, debate: with CommentPress Core you can do all of these things on a finer-grained level, turning a document into a conversation. It can be applied to a fixed document (paper/essay/book etc.) or to a running blog. Use it in combination with BuddyPress and Groupblogs to create communities around your documents.

**Please note:** for the time-being, this plugin works best when the Gutenberg editor is *disabled*. If you want the simplest CommentPress experience in WordPress 5.x please install the *Classic Editor* or *Gutenberg Ramp* plugin to manage the post types on which the Gutenberg editor is enabled.

**Please note:** this plugin comes bundled with three official themes, one of which must be active for CommentPress Core to function. The "CommentPress Modern Theme" will be auto-activated when the plugin is first activated. The old "CommentPress Default Theme" is still included for those who wish to stay with it or have built their own child themes for it. Since version 3.9 a new "CommentPress Flat Theme" is included for those who want an alternative layout. If you are upgrading from a previous version of CommentPress (3.0.x - 3.3.x), please follow the instructions in the Installation section before doing so.

For further information and instructions please see the [CommentPress website](https://www.futureofthebook.org/commentpress/) or visit the plugin's [GitHub repository](https://github.com/IFBook/commentpress-core). Contact the developers by email at [cpdev@futureofthebook.org](mailto:cpdev@futureofthebook.org).

**For sites hosted in the European Union, please note:** the "CommentPress Default Theme" makes use of cookies, but for presentational purposes only. If you intend to use the "CommentPress Default Theme" on a public site, you may need to inform visitors of this.

Many thanks to the following for translations:

* French - [Pouhiou](https://wordpress.org/support/profile/pouhiou)
* Spanish - Andrew Kurtis from [WebHostingHub](https://www.webhostinghub.com/)
* Dutch - Gerrit Jan Dijkgraaf
* German - Chris Witte



== Installation ==

You can download and install CommentPress Core using the built in WordPress plugin installer. If you download CommentPress Core manually, make sure it is uploaded to "/wp-content/plugins/commentpress-core/".


<h4>Install CommentPress Core in WordPress Standalone</h4>

Base Install:

1. Install WordPress
2. Install "CommentPress Core"
3. Activate "CommentPress Core"

Your basic "CommentPress Core" setup is complete. At this point you can:

1. Create a custom menu for the main site
2. Use the Theme Customizer to modify the look
3. Change Background and Header
4. Customise "CommentPress Core" via its Settings Page


<h4>Install CommentPress Core in WordPress Multisite</h4>

Base Install:

1. Install WordPress
2. Create Network

Okay, we're ready to install "CommentPress Core":

1. Install and Network Activate "CommentPress Core"
2. If you want to, you can enable "CommentPress Core" on your main blog
3. Network Enable the "CommentPress Modern Theme", "CommentPress Flat Theme" and/or the "CommentPress Default Theme" UNLESS
4. You have "CommentPress Core"-compatible child themes you want to use instead:
5. Network Enable any "CommentPress Core"-compatible child themes you want to use

Go to the "CommentPress" network settings page under "Settings" in "Network Admin":

1. Configure your options as desired
2. Click "Save Changes"

Your basic "CommentPress Core" setup is complete. At this point you can:

1. Create a custom menu for the main site
2. Use the Theme Customizer to modify the look
3. Change Background and Header
4. Customise "CommentPress Core" via its Settings Page


<h4>Install CommentPress Core with BuddyPress Groupblogs</h4>

Base Install:

1. Install WordPress
2. Create Network

Essential Plugins:

1. Install, Network Activate and configure "BuddyPress" (**Please note:** "CommentPress Core" has not been tested with the Forums component)
2. Download and Network Activate the latest version of "BuddyPress Groupblog" greater than 1.8.3

Optional Plugins:

1. Network Install "BuddyPress Group Email Subscription"
2. Network Install "Invite Anyone"
3. Network Install "My Page Order"
4. Network Install "Co-Authors Plus"
5. Network Install "Simple Footnotes" (recommended), "FD Footnotes" or "WP-Footnotes"

Okay, we're ready to install "CommentPress Core":

1. Install and Network Activate "CommentPress Core"
2. If you want to, you can enable "CommentPress Core" on your main blog
3. Network Enable the "CommentPress Modern Theme", "CommentPress Flat Theme" and/or the "CommentPress Default Theme" UNLESS
4. You have "CommentPress Core"-compatible child themes you want to use instead:
5. Network Enable any "CommentPress Core"-compatible child themes you want to use
6. Optionally, activate your chosen "CommentPress Core"-compatible child theme

Go to your "Groupblog Setup" page under "Settings" in "Network Admin":

1. Select your desired "BuddyPress Groupblog" theme as your default "BuddyPress Groupblog" theme
2. Click "Save Changes"
3. Click the "Redirect" header
4. Set "Redirect Enabled to:" to "Home Page"
5. Click "Save Changes"

Go to the "CommentPress" network settings page under "Settings" in "Network Admin":

1. Select your desired "CommentPress Groupblog" theme. This will be applied to group blogs that are "CommentPress Core"-enabled
2. Configure other options as desired
3. Click "Save Changes"

Your basic "CommentPress Groupblogs" setup is complete. At this point you can:

1. Create a custom menu for the main site
2. Use the Theme Customizer to modify the look
3. Change Background and Header
4. Customise "CommentPress Core" via its Settings Page

To create a "CommentPress Core"-enabled Groupblog:

1. Begin to create a group as usual
2. At the "Groupblog" screen, click "Enable CommentPress"
3. Choose any further options
4. Check the box for "Enable member blog posting" and use the default settings unless you have reason not to
5. Continue and finish creating the group
6. To go to the groupblog, click "Blog" (or "Workshop" if you have chosen that naming scheme)
7. Start your group blogging!

<hr>

<h3>Upgrades</h3>

Upgrades from previous versions of "CommentPress" are possible. Please follow the following procedures for your context.

**The name has been changed from "CommentPress" to "CommentPress Core" for two reasons:** (a) because it serves as the basis for extending it for your purposes and (b) to safeguard historical installations, which could break if they upgrade. Newer "CommentPress" installations (versions 3.0.x - 3.3.x) can upgrade to the current version.


<h4>Upgrade to CommentPress Core</h4>

It is recommended that you upgrade to the latest versions of WordPress as well as the latest versions of the old "CommentPress" plugins and theme before upgrading to "CommentPress Core", but "CommentPress Core" will do its best if this is not possible. A minimum of WordPress 3.3 is required, but upgrades under WordPress 3.4+ work much better. The old "CommentPress" plugins and theme can be found on Github:

1. Get the latest [CommentPress Plugin](https://github.com/IFBook/CommentPressPlugin)
2. Get the latest [CommentPress for Multisite](https://github.com/IFBook/CommentPressMultisite)
3. Get the latest [CommentPress Ajaxified](https://github.com/IFBook/CommentPressAjaxified)
4. Get the latest [CommentPress Theme](https://github.com/IFBook/CommentPressTheme)


<h4>Upgrade to CommentPress Core in WordPress Standalone</h4>

1. Activate "CommentPress Core" plugin
2. "CommentPress Core" will try and deactivate the "CommentPress Ajaxified" plugin. Deactivate it if it is still active.
3. "CommentPress Core" will try and deactivate the "CommentPress" plugin. Deactivate it if it is still active.
4. Delete "CommentPress Ajaxified" plugin
5. Delete "CommentPress" plugin


<h4>Upgrade to CommentPress Core in WordPress Multisite (NOT network-activated)</h4>

On each site:

1. Activate "CommentPress Core" plugin
2. "CommentPress Core" will try and deactivate the "CommentPress Ajaxified" plugin. Deactivate it if it is still active.
3. "CommentPress Core" will try and deactivate the "CommentPress" plugin. Deactivate it if it is still active.

When EVERY site has done this, go to Network Admin -> Plugins:

1. Delete "CommentPress Ajaxified" plugin
2. Delete "CommentPress" plugin


<h4>Upgrade to CommentPress Core (network-activated OR with BuddyPress Groupblogs)</h4>

To upgrade to CommentPress Core.

1. Install, but DO NOT activate OR network-activate CommentPress Core.
2. Network Deactivate "CommentPress for Multisite Extras", if present
3. Network Deactivate "CommentPress for Multisite"
4. Network Disable all old "CommentPress" child themes
5. Now Network Activate "CommentPress Core"

"CommentPress Core" will now be active on your main site.

On each site:

1. Activate "CommentPress Core" plugin
2. "CommentPress Core" will try and deactivate the "CommentPress Ajaxified" plugin. Deactivate it if it is still active.
3. "CommentPress Core" will try and deactivate the "CommentPress" plugin. Deactivate it if it is still active.

When EVERY site has done this, go to "Network Admin" -> "Plugins":

1. Delete "CommentPress Ajaxified" plugin
2. Delete "CommentPress" plugin (NOT "CommentPress Core"!)
3. Delete "CommentPress for Multisite Extras", if present
4. Delete "CommentPress for Multisite"



== Changelog ==

= 4.0.5 =

* Fix TinyMCE loading when navigating to a comment

= 4.0.4 =

* Better support for Featured Comments in original theme

= 4.0.3 =

* Fix appearance of "Discard Comment" modal

= 4.0.2 =

* Fix front-end comment editing

= 4.0.1 =

* Fix comment moderation status
* Highlight menu items for Custom Post Types

= 4.0.0 =

* Code refactored
* Remove support for workflow

= Up to 3.9.x =

* See the [commit history on GitHub](https://github.com/IFBook/commentpress-core/commits/master)
