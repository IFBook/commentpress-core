<?php /*
================================================================================
BuddyPress Custom Settings
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====

Custom settings for BuddyPress. See:
http://codex.buddypress.org/extending-buddypress/bp-custom-php/

--------------------------------------------------------------------------------
*/



// Set which blog ID BuddyPress will run on:
//define ( 'BP_ROOT_BLOG', $blog_id );

// Disable the admin bar / buddybar at the top of every screen:
//define ( 'BP_DISABLE_ADMIN_BAR', true );

// Swap the Buddybar for the WordPress toolbar: (default in buddypress 1.6)
define ( 'BP_USE_WP_ADMIN_BAR', true );

/*
// Disable the custom header functionality on the default BuddyPress theme:
define( 'BP_DTHEME_DISABLE_CUSTOM_HEADER', true );

// Disable the “You’ll need to activate a BuddyPress compatible theme…” warning message in the WordPress administration screens:
define( 'BP_SILENCE_THEME_NOTICE', true );

// Enable support for LDAP usernames that include dots:
define( 'BP_ENABLE_USERNAME_COMPATIBILITY_MODE', true );

// Change the URL slugs of BuddyPress components:
define ( 'BP_ACTIVITY_SLUG', 'streams' );
define ( 'BP_BLOGS_SLUG', 'journals' );
define ( 'BP_MEMBERS_SLUG', 'users' );
define ( 'BP_FRIENDS_SLUG', 'peeps' );
define ( 'BP_GROUPS_SLUG', 'gatherings' );
define ( 'BP_FORUMS_SLUG', 'discussions' );
define ( 'BP_MESSAGES_SLUG', 'notes' );
define ( 'BP_WIRE_SLUG', 'pinboard' );
define ( 'BP_XPROFILE_SLUG', 'info' );
define ( 'BP_REGISTER_SLUG', 'signup' );
define ( 'BP_ACTIVATION_SLUG', 'enable' );
define ( 'BP_SEARCH_SLUG', 'find' );
define ( 'BP_HOME_BLOG_SLUG', 'news' );

// Avatar specific settings can be changed:
define ( 'BP_AVATAR_THUMB_WIDTH', 50 );
define ( 'BP_AVATAR_THUMB_HEIGHT', 50 );
define ( 'BP_AVATAR_FULL_WIDTH', 150 );
define ( 'BP_AVATAR_FULL_HEIGHT', 150 );
define ( 'BP_AVATAR_ORIGINAL_MAX_WIDTH', 640 );
define ( 'BP_AVATAR_ORIGINAL_MAX_FILESIZE', $max_in_kb );
define ( 'BP_AVATAR_DEFAULT', $img_url );
define ( 'BP_AVATAR_DEFAULT_THUMB', $img_url );

// Change the parent forum to use for all BuddyPress group forums:
define ( 'BP_FORUMS_PARENT_FORUM_ID', $forum_id );

// Set a custom user database table for BuddyPress (and WordPress to use):
define ( 'CUSTOM_USER_TABLE', $tablename );

// Set a custom usermeta database table for BuddyPress (and WordPress to use):
define ( 'CUSTOM_USER_META_TABLE', $tablename );
*/



?>