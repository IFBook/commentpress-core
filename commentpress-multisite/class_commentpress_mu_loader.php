<?php /*
================================================================================
Class CommentpressMultisiteLoader
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====

This class loads all Multisite compatibility

--------------------------------------------------------------------------------
*/



/*
================================================================================
Class Name
================================================================================
*/

class CommentpressMultisiteLoader {



	/**
	 * Properties
	 */

	// parent object reference
	public $parent_obj;

	// admin object reference
	public $db;

	// multisite object reference
	public $mu;

	// buddypress object reference
	public $bp;



	/**
	 * Initialises this object
	 *
	 * @param object $parent_obj a reference to the parent object
	 * @return object
	 */
	function __construct( $parent_obj = null ) {

		// store reference to "parent" (calling obj, not OOP parent)
		$this->parent_obj = $parent_obj;

		// init
		$this->_init();

		// --<
		return $this;

	}



	/**
	 * Set up all items associated with this object
	 *
	 * @return void
	 */
	public function initialise() {

	}



	/**
	 * If needed, destroys all items associated with this object
	 *
	 * @return void
	 */
	public function destroy() {

	}



//##############################################################################



	/**
	 * -------------------------------------------------------------------------
	 * Public Methods
	 * -------------------------------------------------------------------------
	 */



//##############################################################################



	/**
	 * -------------------------------------------------------------------------
	 * Private Methods
	 * -------------------------------------------------------------------------
	 */



	/**
	 * Object initialisation
	 *
	 * @return void
	 */
	function _init() {

		// check for network activation
		//add_action( 'activated_plugin',  array( $this, '_network_activated' ), 10, 2 );

		// check for network deactivation
		add_action( 'deactivated_plugin',  array( $this, '_network_deactivated' ), 10, 2 );

		// ---------------------------------------------------------------------
		// load Database Wrapper object
		// ---------------------------------------------------------------------

		// define filename
		$class_file = 'commentpress-multisite/class_commentpress_mu_admin.php';

		// get path
		$class_file_path = commentpress_file_is_present( $class_file );

		// we're fine, include class definition
		require_once( $class_file_path );

		// init autoload database object
		$this->db = new CommentpressMultisiteAdmin( $this );

		// ---------------------------------------------------------------------
		// load standard Multisite object
		// ---------------------------------------------------------------------

		// define filename
		$class_file = 'commentpress-multisite/class_commentpress_mu_ms.php';

		// get path
		$class_file_path = commentpress_file_is_present( $class_file );

		// we're fine, include class definition
		require_once( $class_file_path );

		// init multisite object
		$this->mu = new CommentpressMultisite( $this );

		// ---------------------------------------------------------------------
		// load Post Revisions object (merge this into Core as an option)
		// ---------------------------------------------------------------------

		// define filename
		$_class_file = 'commentpress-multisite/class_commentpress_mu_revisions.php';

		// get path
		$_class_file_path = commentpress_file_is_present( $_class_file );

		// we're fine, include class definition
		require_once( $_class_file_path );

		// instantiate it
		$this->revisions = new CommentpressMultisiteRevisions( $this );

		// ---------------------------------------------------------------------
		// call initialise() on admin object
		// ---------------------------------------------------------------------

		// initialise db for multisite
		$this->db->initialise( 'multisite' );

		// ---------------------------------------------------------------------
		// optionally load BuddyPress object
		// ---------------------------------------------------------------------

		// load when buddypress is loaded
		add_action( 'bp_include', array( $this, '_load_buddypress_object' ) );

	}



	/**
	 * BuddyPress object initialisation
	 *
	 * @return void
	 */
	function _load_buddypress_object() {

		// ---------------------------------------------------------------------
		// load BuddyPress object
		// ---------------------------------------------------------------------

		// define filename
		$class_file = 'commentpress-multisite/class_commentpress_mu_bp.php';

		// get path
		$class_file_path = commentpress_file_is_present( $class_file );

		// we're fine, include class definition
		require_once( $class_file_path );

		// init buddypress object
		$this->bp = new CommentpressMultisiteBuddypress( $this );

		// ---------------------------------------------------------------------
		// load Groupblog Workshop renaming object
		// ---------------------------------------------------------------------

		// define filename
		$_class_file = 'commentpress-multisite/class_commentpress_mu_workshop.php';

		// get path
		$_class_file_path = commentpress_file_is_present( $_class_file );

		// we're fine, include class definition
		require_once( $_class_file_path );

		// instantiate it
		$this->workshop = new CommentpressGroupblogWorkshop( $this );

		// ---------------------------------------------------------------------
		// call initialise() on admin object again
		// ---------------------------------------------------------------------

		// initialise db for buddypress
		$this->db->initialise( 'buddypress' );

	}



	/**
	 * This plugin has been network-activated (does not fire!)!
	 *
	 * @return void
	 */
	function _network_activated( $plugin, $network_wide = null ) {

		//print_r( array( $plugin, $network_wide ) ); die();

		// if it's our plugin...
		if ( $plugin == plugin_basename( COMMENTPRESS_PLUGIN_FILE ) ) {

			// was it network deactivated?
			if ( $network_wide == true ) {

				// if upgrading, we need to migrate each existing instance into a CommentPress Core blog

			}

		}

	}



	/**
	 * This plugin has been network-deactivated
	 *
	 * @return void
	 */
	function _network_deactivated( $plugin, $network_wide = null ) {

		// if it's our plugin...
		if ( $plugin == plugin_basename( COMMENTPRESS_PLUGIN_FILE ) ) {

			// was it network deactivated?
			if ( $network_wide == true ) {

				//print_r( array( $plugin, $network_wide ) ); die();

				// do we want to trigger deactivation_hook for all sub-blogs?
				// or do we want to convert each instance into a self-contained
				// CommentPress Core blog?

			}

		}

	}



//##############################################################################



} // class ends



