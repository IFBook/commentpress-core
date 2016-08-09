CommentPress Core
=================

**Please note:** this is the development repository for *CommentPress Core*. It can be found in [the WordPress Plugin Directory](http://wordpress.org/plugins/commentpress-core/), which is the best place to get it from if you're not a developer.

*CommentPress Core* is an open source theme and plugin for WordPress that allows readers to comment in the margins of a text. Readers may comment paragraph-by-paragraph, line-by-line, block-by-block or by selecting text.

Annotate, gloss, workshop, debate: with *CommentPress Core* you can do all of these things on a finer-grained level, turning a document into a conversation. It can be applied to a fixed document (paper/essay/book etc.) or to a running blog. Use it in combination with *BuddyPress* and *BuddyPress Groupblog* to create communities around your documents.

**Please note:** this plugin comes bundled with three official themes, one of which must be active for *CommentPress Core* to function. The *CommentPress Modern Theme* will be auto-activated when the plugin is first activated. The old *CommentPress Default Theme* is still included for those who wish to stay with it or have built their own child themes for it. Since version 3.9 a new "CommentPress Flat Theme" is included for those who want an alternative layout. If you are upgrading from a previous version of *CommentPress* (3.0.x - 3.3.x), please follow the instructions in the Installation section before doing so.

**For sites hosted in the European Union, please note:** the *CommentPress Default Theme* makes use of cookies, but for presentational purposes only. If you intend to use the *CommentPress Default Theme* on a public site, you may need to inform visitors of this.

For further information and instructions please see the [CommentPress site](http://www.futureofthebook.org/commentpress/) or contact the developers by email at [cpdev@futureofthebook.org](mailto:cpdev@futureofthebook.org)

Many thanks to the following for translations:

* French - [Pouhiou](http://wordpress.org/support/profile/pouhiou)
* Spanish - Andrew Kurtis from [WebHostingHub](http://www.webhostinghub.com/)
* Dutch - Gerrit Jan Dijkgraaf
* German - Chris Witte


---

## Installation ##

### GitHub ###

There are two ways to install from GitHub:

#### ZIP Download ####

If you have downloaded *CommentPress Core* as a ZIP file from the GitHub repository, do the following to install and activate the plugin and theme:

1. Unzip the .zip file and, if needed, rename the enclosing folder so that the plugin's files are located directly inside `/wp-content/plugins/commentpress-core`
2. Activate the plugin
3. Follow the setup instructions for your context below
4. You are done!

#### git clone ####

If you have cloned the code from GitHub, it is assumed that you know what you're doing.

### WordPress Plugin Repository ###

You can download and install *CommentPress Core* using the built in WordPress plugin installer. If you download *CommentPress Core* manually, make sure it is uploaded so that the plugin directory is `/wp-content/plugins/commentpress-core`.

The following are full instructions for setting up *CommentPress Core* in the contexts in which it works:


#### Install *CommentPress Core* in WordPress Standalone ####

Base Install:

1. Install WordPress
2. Install *CommentPress Core*
3. Activate *CommentPress Core*

Your basic *CommentPress Core* setup is complete. At this point you can:

1. Create a custom menu for the main site
2. Use the Theme Customizer to modify the look
3. Change Background and Header
4. Customise *CommentPress Core* via its Settings Page


#### Install *CommentPress Core* in WordPress Multisite ####

Base Install:

1. Install WordPress
2. Create Network

Okay, we're ready to install *CommentPress Core*:

1. Install and Network Activate *CommentPress Core*
2. If you want to, you can enable *CommentPress Core* on your main blog
3. Network Enable the *CommentPress Modern Theme* and/or the *CommentPress Default Theme* UNLESS
4. You have *CommentPress Core*-compatible child themes you want to use instead:
5. Network Enable any *CommentPress Core*-compatible child themes you want to use

Go to the "CommentPress" network settings page under "Network Admin" -> "Settings":

1. Configure your options as desired
2. Click "Save Changes"

Your basic *CommentPress Core* setup is complete. At this point you can:

1. Create a custom menu for the main site
2. Use the Theme Customizer to modify the look
3. Change Background and Header
4. Customise *CommentPress Core* via its Settings Page


#### Install *CommentPress Core* with *BuddyPress Groupblogs* ####

Base Install:

1. Install WordPress
2. Create Network

Essential Plugins:

1. Install, Network Activate and configure *BuddyPress* (**Please note:** *CommentPress Core* has not been tested with the Forums component)
2. Download and Network Activate the latest version of *BuddyPress Groupblog* greater than 1.8.3

Optional Plugins:

1. Network Install *BuddyPress Group Email Subscription*
2. Network Install *Invite Anyone*
3. Network Install *My Page Order*
4. Network Install *Co-Authors Plus*
5. Network Install *Simple Footnotes* (recommended), *FD Footnotes* or *WP-Footnotes*

Okay, we're ready to install *CommentPress Core*:

1. Install and Network Activate *CommentPress Core*
2. If you want to, you can enable *CommentPress Core* on your main blog
3. Network Enable the *CommentPress Modern Theme* and/or the *CommentPress Default Theme* UNLESS
4. You have *CommentPress Core*-compatible child themes you want to use instead:
5. Network Enable any *CommentPress Core*-compatible child themes you want to use
6. Optionally, activate your chosen *CommentPress Core*-compatible child theme

Go to your "Groupblog Setup" page under "Network Admin" -> "Settings":

1. Select your desired "BuddyPress Groupblog" theme as your default "BuddyPress Groupblog" theme
2. Click "Save Changes"
3. Click the "Redirect" header
4. Set "Redirect Enabled to:" to "Home Page"
5. Click "Save Changes"

Go to the "CommentPress" network settings page under "Network Admin" -> "Settings":

1. Select your desired "CommentPress Groupblog" theme. This will be applied to group blogs that are *CommentPress Core*-enabled
2. Configure other options as desired
3. Click "Save Changes"

Your basic "CommentPress Groupblogs" setup is complete. At this point you can:

1. Create a custom menu for the main site
2. Use the Theme Customizer to modify the look
3. Change Background and Header
4. Customise *CommentPress Core* via its Settings Page

To create a *CommentPress Core*-enabled Groupblog:

1. Begin to create a group as usual
2. At the "Groupblog" screen, click "Enable CommentPress"
3. Choose any further options
4. Check the box for "Enable member blog posting" and use the default settings unless you have reason not to
5. Continue and finish creating the group
6. To go to the groupblog, click "Blog" (or if you have chosen to alter the naming scheme, then whatever name you have chosen)
7. Start your group blogging!

---

## Upgrades ##

Upgrades from previous versions of *CommentPress* are possible. Please follow the following procedures for your context.

**The name has been changed from *CommentPress* to *CommentPress Core* for two reasons:** (a) because it serves as the basis for extending it for your purposes and (b) to safeguard historical installations, which could break if they upgrade. Newer *CommentPress* installations (versions 3.0.x - 3.3.x) can upgrade to the current version.


### Upgrade to *CommentPress Core* ###

It is recommended that you upgrade to the latest versions of WordPress as well as the latest versions of the old *CommentPress* plugins and theme before upgrading to *CommentPress Core*, but *CommentPress Core* will do its best if this is not possible. A minimum of WordPress 3.3 is required, but upgrades under WordPress 3.4+ work much better. The old *CommentPress* plugins and theme can all be found on Github:

1. Get the latest [CommentPress Plugin](https://github.com/IFBook/CommentPressPlugin)
2. Get the latest [CommentPress for Multisite](https://github.com/IFBook/CommentPressMultisite)
3. Get the latest [CommentPress Ajaxified](https://github.com/IFBook/CommentPressAjaxified)
4. Get the latest [CommentPress Theme](https://github.com/IFBook/CommentPressTheme)


### Upgrade to *CommentPress Core* in WordPress Standalone ###

1. Activate *CommentPress Core* plugin
2. *CommentPress Core* will try and deactivate the *CommentPress Ajaxified* plugin. Deactivate it if it is still active.
3. *CommentPress Core* will try and deactivate the *CommentPress* plugin. Deactivate it if it is still active.
4. Delete *CommentPress Ajaxified* plugin
5. Delete *CommentPress* plugin


### Upgrade to *CommentPress Core* in WordPress Multisite (NOT network-activated) ###

On each site:

1. Activate *CommentPress Core* plugin
2. *CommentPress Core* will try and deactivate the *CommentPress Ajaxified* plugin. Deactivate it if it is still active.
3. *CommentPress Core* will try and deactivate the *CommentPress* plugin. Deactivate it if it is still active.

When EVERY site has done this, go to "Network Admin" -> "Plugins":

1. Delete *CommentPress Ajaxified* plugin
2. Delete *CommentPress* plugin


### Upgrade to *CommentPress Core* (network-activated OR with *BuddyPress Groupblogs*) ###

To upgrade to *CommentPress Core*.

1. Install, but DO NOT activate OR network-activate *CommentPress Core*.
2. Network Deactivate *CommentPress for Multisite Extras*, if present
3. Network Deactivate *CommentPress for Multisite*
4. Network Disable all old *CommentPress* child themes
5. Now Network Activate *CommentPress Core*

*CommentPress Core* will now be active on your main site.

On each site:

1. Activate *CommentPress Core* plugin
2. *CommentPress Core* will try and deactivate the *CommentPress Ajaxified* plugin. Deactivate it if it is still active.
3. *CommentPress Core* will try and deactivate the *CommentPress* plugin. Deactivate it if it is still active.

When EVERY site has done this, go to "Network Admin" -> "Plugins":

1. Delete *CommentPress Ajaxified* plugin
2. Delete *CommentPress* plugin (NOT *CommentPress Core*!)
3. Delete *CommentPress for Multisite Extras*, if present
4. Delete *CommentPress for Multisite*

---

## Changelogs ##

### 3.4 onwards ###

The merged plugins and theme.

* See the [commits on GitHub](https://github.com/IFBook/commentpress-core/commits/master)

### Merging 3.3.6 to 3.4 ###

The merging process for the plugins and theme.

* See the [commits on GitHub](https://github.com/IFBook/commentpress/commits/master)


### Up to 3.3.6 ###

Up to this version, *CommentPress* was a collection of separate plugins and a theme.

* See the [plugin commits on GitHub](https://github.com/IFBook/CommentPressPlugin/commits/master)
* See the [theme commits on GitHub](https://github.com/IFBook/CommentPressTheme/commits/master)
* See the [ajax plugin commits on GitHub](https://github.com/IFBook/CommentPressAjaxified/commits/master)
* See the [multisite plugin commits on GitHub](https://github.com/IFBook/CommentPressMultisite/commits/master)

