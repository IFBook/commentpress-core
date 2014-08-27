=== CommentPress Core ===
Contributors: needle, commentpress
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=PZSKM8T5ZP3SC
Tags: commentpress, buddypress, groups, blogs, groupblogs, comments, commenting, debate, collaboration
Requires at least: 3.4
Tested up to: 3.9
Stable tag: 3.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

CommentPress Core allows fine-grained commenting in the margins of a text. Use it to annotate, workshop or debate a social text in a social context.



== Description ==

CommentPress Core is an open source theme and plugin for WordPress that allows readers to comment paragraph-by-paragraph, line-by-line or block-by-block in the margins of a text.

Annotate, gloss, workshop, debate: with CommentPress Core you can do all of these things on a finer-grained level, turning a document into a conversation. It can be applied to a fixed document (paper/essay/book etc.) or to a running blog. Use it in combination with BuddyPress and Groupblogs to create communities around your documents.

**The name has been changed from "CommentPress" to "CommentPress Core" for two reasons:** (a) because it serves as the basis for extending it for your purposes and (b) to safeguard historical installations, which could break if they upgrade. Newer "CommentPress" installations (versions 3.0.x - 3.3.x) can upgrade to the current version.

**Please note:** this plugin comes bundled with two official themes. The new "CommentPress Modern Theme" will be auto-activated when the plugin is first activated. The old "CommentPress Default Theme" is still included for those who wish to stay with it or have built their own child themes for it. If you are upgrading from a previous version of *CommentPress* (3.0.x - 3.3.x), please follow the instructions in the Installation section before doing so.

For further information and instructions please see the [CommentPress site](http://www.futureofthebook.org/commentpress/) or contact the developers by email at [cpdev@futureofthebook.org](mailto:cpdev@futureofthebook.org)

Many thanks to the following for translations: 

* French - [Pouhiou](http://wordpress.org/support/profile/pouhiou)
* Spanish - Andrew Kurtis from [WebHostingHub](http://www.webhostinghub.com/)



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
2. This should have enabled "CommentPress Core" on your main blog
3. Network Enable the "CommentPress Modern Theme" and/or the "CommentPress Default Theme" UNLESS
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
4. Install and DO NOT Network Activate "BuddyPress Template Pack"

Optional Plugins:

1. Network Install "BuddyPress Group Email Subscription"
2. Network Install "Invite Anyone"
3. Network Install "My Page Order"
4. Network Install "Co-Authors Plus"
5. Network Install "Simple Footnotes" (recommended), "FD Footnotes" or "WP-Footnotes"

Okay, we're ready to install "CommentPress Core":

1. Install and Network Activate "CommentPress Core"
2. This should have enabled "CommentPress Core" on your main blog
3. Network Enable the "CommentPress Modern Theme" and/or the "CommentPress Default Theme" UNLESS
4. You have "CommentPress Core"-compatible child themes you want to use instead:
5. Network Enable any "CommentPress Core"-compatible child themes you want to use

Go to your main blog's Dashboard:

1. Activate "BuddyPress Template Pack"
2. Go to the "BP Compatibility" page
3. Click "Finish"
4. Tick "Disable BP Template Pack CSS"
5. Click "Save Settings"
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

**For sites hosted in the European Union, please note:** the "CommentPress Default Theme" makes use of cookies, but for presentational purposes only. If you intend to use the "CommentPress Default Theme" on a public site, you may need to inform visitors of this.



== Other Notes ==

Upgrades from previous versions of "CommentPress" are possible. Please follow the following procedures for your context.


<h4>Upgrade to CommentPress Core</h4>

It is recommended that you upgrade to the latest versions of WordPress as well as the latest versions of the old "Commentpress" plugins and theme before upgrading to "CommentPress Core", but "CommentPress Core" will do its best if this is not possible. A minimum of WordPress 3.3 is required, but upgrades under WordPress 3.4+ work much better. The old "Commentpress" plugins and theme can be found on Github:

1. Get the latest [Commentpress Plugin](https://github.com/IFBook/CommentPressPlugin)
2. Get the latest [Commentpress for Multisite](https://github.com/IFBook/CommentPressMultisite)
3. Get the latest [Commentpress Ajaxified](https://github.com/IFBook/CommentPressAjaxified)
4. Get the latest [Commentpress Theme](https://github.com/IFBook/CommentPressTheme)


<h4>Upgrade to CommentPress Core in WordPress Standalone</h4>

1. Activate "CommentPress Core" plugin
2. "CommentPress Core" will try and deactivate the "Commentpress Ajaxified" plugin. Deactivate it if it is still active.
3. "CommentPress Core" will try and deactivate the "Commentpress" plugin. Deactivate it if it is still active.
4. Delete "Commentpress Ajaxified" plugin
5. Delete "Commentpress" plugin


<h4>Upgrade to CommentPress Core in WordPress Multisite (NOT network-activated)</h4>

On each site:

1. Activate "CommentPress Core" plugin
2. "CommentPress Core" will try and deactivate the "Commentpress Ajaxified" plugin. Deactivate it if it is still active.
3. "CommentPress Core" will try and deactivate the "Commentpress" plugin. Deactivate it if it is still active.

When EVERY site has done this, go to Network Admin -> Plugins:

1. Delete "Commentpress Ajaxified" plugin
2. Delete "Commentpress" plugin


<h4>Upgrade to CommentPress Core (network-activated OR with BuddyPress Groupblogs)</h4>

To upgrade to CommentPress Core.

1. Install, but DO NOT activate OR network-activate CommentPress Core.
2. Network Deactivate "Commentpress for Multisite Extras", if present
3. Network Deactivate "Commentpress for Multisite"
4. Network Disable all old "Commentpress" child themes
5. Now Network Activate "CommentPress Core"

"CommentPress Core" will now be active on your main site.

On each site:

1. Activate "CommentPress Core" plugin
2. "CommentPress Core" will try and deactivate the "Commentpress Ajaxified" plugin. Deactivate it if it is still active.
3. "CommentPress Core" will try and deactivate the "Commentpress" plugin. Deactivate it if it is still active.

When EVERY site has done this, go to "Network Admin" -> "Plugins":

1. Delete "Commentpress Ajaxified" plugin
2. Delete "Commentpress" plugin (NOT "CommentPress Core"!)
3. Delete "Commentpress for Multisite Extras", if present
4. Delete "Commentpress for Multisite"



== Changelog == 


<h4>3.6</h4>

* Compatibility with latest BuddyPress
* avoid AJAX errors for suspected spam comments
* respect password-protected post comment visibility
* additional hooks for plugins



<h4>3.5.7</h4>

* Critical fix to account for the change in the way comments are "walked" in WordPress 3.8



<h4>3.5.6</h4>

* Restores compatibility with JetPack 2.7 which parses content in the document head



<h4>3.5.5</h4>

* Introduces media insertion into comments via Add Media button when logged in
* Allows switching between Visual and HTML editor in comment form
* Introduces featured images to pages and posts


<h4>3.4 onwards</h4>

The merged plugins and theme.

* See the [commits on GitHub](https://github.com/IFBook/commentpress-core/commits/master)


<h4>Merging 3.3.6 to 3.4</h4>

The merging process for the plugins and theme.

* See the [commits on GitHub](https://github.com/IFBook/commentpress/commits/master)


<h4>Up to 3.3.6</h4>

Up to this version, "CommentPress" was a collection of separate plugins and a theme.

* See the [plugin commits on GitHub](https://github.com/IFBook/CommentPressPlugin/commits/master)
* See the [theme commits on GitHub](https://github.com/IFBook/CommentPressTheme/commits/master)
* See the [ajax plugin commits on GitHub](https://github.com/IFBook/CommentPressAjaxified/commits/master)
* See the [multisite plugin commits on GitHub](https://github.com/IFBook/CommentPressMultisite/commits/master)


