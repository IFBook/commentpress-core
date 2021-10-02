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



// Define version
define( 'COMMENTPRESS_MU_PLUGIN_VERSION', '1.0' );



// Sanity check.
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
	 * @var object $db The database object.
	 */
	public $db;

	/**
	 * Multisite object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $mu The multisite object reference.
	 */
	public $multisite;

	/**
	 * Revisions object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $revisions The revisions object reference.
	 */
	public $revisions;

	/**
	 * BuddyPress compatibility object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $bp The BuddyPress object reference.
	 */
	public $bp;

	/**
	 * BuddyPress Groupblog compatibility object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $workshop The workshop object reference.
	 */
	public $workshop;



	/**
	 * Initialises this object.
	 *
	 * @since 3.3
	 */
	public function __construct() {

		// Init.
		$this->initialise();

	}



	/**
	 * Set up all items associated with this object.
	 *
	 * @since 3.3
	 */
	public function initialise() {

		// Check for network activation.
		//add_action( 'activated_plugin',  [ $this, 'network_activated' ], 10, 2 );

		// Check for network deactivation.
		add_action( 'deactivated_plugin',  [ $this, 'network_deactivated' ], 10, 2 );

		// ---------------------------------------------------------------------
		// Load Database Wrapper object
		// ---------------------------------------------------------------------

		// Define filename.
		$class_file = 'commentpress-multisite/class_commentpress_mu_admin.php';

		// Get path.
		$class_file_path = commentpress_file_is_present( $class_file );

		// Allow plugins to override this and supply their own.
		$class_file_path = apply_filters(
			'class_commentpress_mu_admin',
			$class_file_path
		);

		// We're fine, include class definition.
		require_once( $class_file_path );

		// Init autoload database object.
		$this->db = new Commentpress_Multisite_Admin( $this );

		// ---------------------------------------------------------------------
		// Load standard Multisite object.
		// ---------------------------------------------------------------------

		// Define filename.
		$class_file = 'commentpress-multisite/class_commentpress_mu_ms.php';

		// Get path.
		$class_file_path = commentpress_file_is_present( $class_file );

		// Allow plugins to override this and supply their own.
		$class_file_path = apply_filters(
			'class_commentpress_mu_ms',
			$class_file_path
		);

		// We're fine, include class definition.
		require_once( $class_file_path );

		// Init multisite object
		$this->multisite = new Commentpress_Multisite_Wordpress( $this );

		// ---------------------------------------------------------------------
		// Load Post Revisions object (merge this into Core as an option).
		// ---------------------------------------------------------------------

		// Define filename.
		$class_file = 'commentpress-multisite/class_commentpress_mu_revisions.php';

		// Get path.
		$class_file_path = commentpress_file_is_present( $class_file );

		// Allow plugins to override this and supply their own.
		$class_file_path = apply_filters(
			'class_commentpress_mu_revisions',
			$class_file_path
		);

		// We're fine, include class definition.
		require_once( $class_file_path );

		// Instantiate it.
		$this->revisions = new Commentpress_Multisite_Revisions( $this );

		// ---------------------------------------------------------------------
		// Call initialise() on admin object.
		// ---------------------------------------------------------------------

		// Initialise db for multisite.
		$this->db->initialise( 'multisite' );

		// ---------------------------------------------------------------------
		// Optionally load BuddyPress object.
		// ---------------------------------------------------------------------

		// Load when BuddyPress is loaded.
		add_action( 'bp_include', [ $this, 'load_buddypress_object' ] );

	}



	/**
	 * BuddyPress object initialisation.
	 *
	 * @since 3.3
	 */
	public function load_buddypress_object() {

		// ---------------------------------------------------------------------
		// Load BuddyPress object.
		// ---------------------------------------------------------------------

		// Define filename.
		$class_file = 'commentpress-multisite/class_commentpress_mu_bp.php';

		// Get path.
		$class_file_path = commentpress_file_is_present( $class_file );

		// Allow plugins to override this and supply their own.
		$class_file_path = apply_filters(
			'class_commentpress_mu_bp',
			$class_file_path
		);

		// We're fine, include class definition.
		require_once( $class_file_path );

		// Init BuddyPress object.
		$this->bp = new Commentpress_Multisite_Buddypress( $this );

		// ---------------------------------------------------------------------
		// Load Groupblog Workshop renaming object.
		// ---------------------------------------------------------------------

		// Define filename.
		$class_file = 'commentpress-multisite/class_commentpress_mu_workshop.php';

		// Get path.
		$class_file_path = commentpress_file_is_present( $class_file );

		// Allow plugins to override this and supply their own.
		$class_file_path = apply_filters(
			'class_commentpress_mu_workshop',
			$class_file_path
		);

		// We're fine, include class definition.
		require_once( $class_file_path );

		// Instantiate it.
		$this->workshop = new Commentpress_Multisite_Buddypress_Groupblog( $this );

		// ---------------------------------------------------------------------
		// Call initialise() on admin object again.
		// ---------------------------------------------------------------------

		// Initialise db for BuddyPress.
		$this->db->initialise( 'buddypress' );

	}



	/**
	 * This plugin has been network-activated. (does not fire!)!
	 *
	 * @since 3.3
	 */
	public function network_activated( $plugin, $network_wide = null ) {

		// If it's our plugin.
		if ( $plugin == plugin_basename( COMMENTPRESS_PLUGIN_FILE ) ) {

			// Was it network deactivated?
			if ( $network_wide == true ) {

				// If upgrading, we need to migrate each existing instance into a CommentPress Core blog.

			}

		}

	}



	/**
	 * This plugin has been network-deactivated.
	 *
	 * @since 3.3
	 */
	public function network_deactivated( $plugin, $network_wide = null ) {

		// If it's our plugin.
		if ( $plugin == plugin_basename( COMMENTPRESS_PLUGIN_FILE ) ) {

			// Was it network deactivated?
			if ( $network_wide == true ) {

				// Do we want to trigger deactivation_hook for all sub-blogs?
				// Or do we want to convert each instance into a self-contained
				// CommentPress Core blog?

			}

		}

	}



//##############################################################################



} // Class ends.

endif; // Class_exists.



// Define as global.
global $commentpress_mu;

// Instantiate it.
$commentpress_mu = new Commentpress_Multisite_Loader();



