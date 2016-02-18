<?php /*
================================================================================
CommentPress Core for Multisite
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====

This used to be the CommentPress for Multisite plugin, but is now merged into
a unified plugin that covers all situations.

--------------------------------------------------------------------------------
*/



// define version
define( 'COMMENTPRESS_MU_PLUGIN_VERSION', '1.0' );



// sanity check
if ( ! class_exists( 'Commentpress_Multisite_Loader' ) ) :

/**
 * CommentPress Core Multisite Loader Class.
 *
 * This class loads all Multisite compatibility.
 *
 * @since 3.3
 */
class Commentpress_Multisite_Loader {

	/**
	 * Database interaction object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $db The database object
	 */
	public $db;

	/**
	 * Multisite object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $mu The multisite object reference
	 */
	public $multisite;

	/**
	 * Revisions object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $revisions The revisions object reference
	 */
	public $revisions;

	/**
	 * BuddyPress compatibility object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $bp The BuddyPress object reference
	 */
	public $bp;

	/**
	 * BuddyPress Groupblog compatibility object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $workshop The workshop object reference
	 */
	public $workshop;



	/**
	 * Initialises this object.
	 *
	 * @since 3.3
	 */
	function __construct() {

		// init
		$this->initialise();

	}



	/**
	 * Set up all items associated with this object.
	 *
	 * @return void
	 */
	public function initialise() {

		// check for network activation
		//add_action( 'activated_plugin',  array( $this, 'network_activated' ), 10, 2 );

		// check for network deactivation
		add_action( 'deactivated_plugin',  array( $this, 'network_deactivated' ), 10, 2 );

		// ---------------------------------------------------------------------
		// load Database Wrapper object
		// ---------------------------------------------------------------------

		// define filename
		$class_file = 'commentpress-multisite/class_commentpress_mu_admin.php';

		// get path
		$class_file_path = commentpress_file_is_present( $class_file );

		// allow plugins to override this and supply their own
		$class_file_path = apply_filters(
			'class_commentpress_mu_admin',
			$class_file_path
		);

		// we're fine, include class definition
		require_once( $class_file_path );

		// init autoload database object
		$this->db = new Commentpress_Multisite_Admin( $this );

		// ---------------------------------------------------------------------
		// load standard Multisite object
		// ---------------------------------------------------------------------

		// define filename
		$class_file = 'commentpress-multisite/class_commentpress_mu_ms.php';

		// get path
		$class_file_path = commentpress_file_is_present( $class_file );

		// allow plugins to override this and supply their own
		$class_file_path = apply_filters(
			'class_commentpress_mu_ms',
			$class_file_path
		);

		// we're fine, include class definition
		require_once( $class_file_path );

		// init multisite object
		$this->multisite = new Commentpress_Multisite_Wordpress( $this );

		// ---------------------------------------------------------------------
		// load Post Revisions object (merge this into Core as an option)
		// ---------------------------------------------------------------------

		// define filename
		$class_file = 'commentpress-multisite/class_commentpress_mu_revisions.php';

		// get path
		$class_file_path = commentpress_file_is_present( $class_file );

		// allow plugins to override this and supply their own
		$class_file_path = apply_filters(
			'class_commentpress_mu_revisions',
			$class_file_path
		);

		// we're fine, include class definition
		require_once( $class_file_path );

		// instantiate it
		$this->revisions = new Commentpress_Multisite_Revisions( $this );

		// ---------------------------------------------------------------------
		// call initialise() on admin object
		// ---------------------------------------------------------------------

		// initialise db for multisite
		$this->db->initialise( 'multisite' );

		// ---------------------------------------------------------------------
		// optionally load BuddyPress object
		// ---------------------------------------------------------------------

		// load when BuddyPress is loaded
		add_action( 'bp_include', array( $this, 'load_buddypress_object' ) );

	}



	/**
	 * BuddyPress object initialisation.
	 *
	 * @return void
	 */
	public function load_buddypress_object() {

		// ---------------------------------------------------------------------
		// load BuddyPress object
		// ---------------------------------------------------------------------

		// define filename
		$class_file = 'commentpress-multisite/class_commentpress_mu_bp.php';

		// get path
		$class_file_path = commentpress_file_is_present( $class_file );

		// allow plugins to override this and supply their own
		$class_file_path = apply_filters(
			'class_commentpress_mu_bp',
			$class_file_path
		);

		// we're fine, include class definition
		require_once( $class_file_path );

		// init BuddyPress object
		$this->bp = new Commentpress_Multisite_Buddypress( $this );

		// ---------------------------------------------------------------------
		// load Groupblog Workshop renaming object
		// ---------------------------------------------------------------------

		// define filename
		$class_file = 'commentpress-multisite/class_commentpress_mu_workshop.php';

		// get path
		$class_file_path = commentpress_file_is_present( $class_file );

		// allow plugins to override this and supply their own
		$class_file_path = apply_filters(
			'class_commentpress_mu_workshop',
			$class_file_path
		);

		// we're fine, include class definition
		require_once( $class_file_path );

		// instantiate it
		$this->workshop = new Commentpress_Multisite_Buddypress_Groupblog( $this );

		// ---------------------------------------------------------------------
		// call initialise() on admin object again
		// ---------------------------------------------------------------------

		// initialise db for BuddyPress
		$this->db->initialise( 'buddypress' );

	}



	/**
	 * This plugin has been network-activated. (does not fire!)!
	 *
	 * @return void
	 */
	public function network_activated( $plugin, $network_wide = null ) {

		// if it's our plugin
		if ( $plugin == plugin_basename( COMMENTPRESS_PLUGIN_FILE ) ) {

			// was it network deactivated?
			if ( $network_wide == true ) {

				// if upgrading, we need to migrate each existing instance into a CommentPress Core blog

			}

		}

	}



	/**
	 * This plugin has been network-deactivated.
	 *
	 * @return void
	 */
	public function network_deactivated( $plugin, $network_wide = null ) {

		// if it's our plugin
		if ( $plugin == plugin_basename( COMMENTPRESS_PLUGIN_FILE ) ) {

			// was it network deactivated?
			if ( $network_wide == true ) {

				// do we want to trigger deactivation_hook for all sub-blogs?
				// or do we want to convert each instance into a self-contained
				// CommentPress Core blog?

			}

		}

	}



//##############################################################################



} // class ends

endif; // class_exists



// define as global
global $commentpress_mu;

// instantiate it
$commentpress_mu = new Commentpress_Multisite_Loader;



