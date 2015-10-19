<?php
/**
 * Main Class
 *
 * @package SimpleCalendar
 */
namespace SimpleCalendar;

use SimpleCalendar\Admin\License_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Simple Calendar plugin.
 */
final class Plugin {

	/**
	 * Plugin name.
	 *
	 * @access public
	 * @var string
	 */
	public static $name = 'Simple Calendar';

	/**
	 * Plugin version.
	 *
	 * @access public
	 * @var string
	 */
	public static $version = SIMPLE_CALENDAR_VERSION;

	/**
	 * Plugin homepage.
	 *
	 * @access public
	 * @var string
	 */
	protected static $homepage = 'https://simplecalendar.io';

	/**
	 * Locale.
	 *
	 * @access public
	 * @var string
	 */
	public $locale = 'en_US';

	/**
	 * Objects factory.
	 *
	 * @access public
	 * @var Objects
	 */
	public $objects = null;

	/**
	 * The single instance of this class.
	 *
	 * @access protected
	 * @var Plugin
	 */
	protected static $_instance = null;

	/**
	 * Get the plugin instance.
	 *
	 * @return Plugin
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, 'Cloning the main instance of this plugin is forbidden.', '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, 'Unserializing instances of this plugin is forbidden.', '1.0.0' );
	}

	/**
	 * Plugin constructor.
	 *
	 * @final
	 */
	public function __construct() {

		// Load plugin.
		require_once 'autoload.php';
		$this->locale = apply_filters( 'plugin_locale', get_locale(), 'google-calendar-events' );
		$this->load();

		// Installation hooks.
		register_activation_hook( SIMPLE_CALENDAR_MAIN_FILE, array( 'SimpleCalendar\Installation', 'activate' ) );
		register_deactivation_hook( SIMPLE_CALENDAR_MAIN_FILE, array( 'SimpleCalendar\Installation', 'deactivate' ) );

		// Init hooks.
		add_action( 'init', array( $this, 'init' ), 5 );
		add_action( 'admin_init', array( $this, 'register_settings' ), 5 );

		// Upon plugin loaded action hook.
		do_action( 'simcal_loaded' );
	}

	/**
	 * Load plugin.
	 *
	 * @since 3.0.0
	 */
	public function load() {

		// Functions shared in both back end and front end.
		include_once 'functions/shared.php';

		// Init custom post types and taxonomies.
		new Post_Types();

		// Load back end.
		if ( is_admin() ) {
			$this->load_admin();
		} else {
			// Load front end scripts and styles.
			new Assets();
		}

		// Front facing ajax callbacks.
		new Ajax();

		// Add Shortcodes.
		new Shortcodes();

		// Add Widgets.
		new Widgets();

		// Deprecated functions for backwards compatibility.
		include_once 'functions/deprecated.php';
	}

	/**
	 * Load plugin admin.
	 *
	 * @since 3.0.0
	 */
	public function load_admin() {

		// Back end only functions.
		include_once 'functions/admin.php';

		// Display admin notices.
		new Admin\Notices();

		// Load back end scripts and styles.
		new Admin\Assets();

		// Custom content handling.
		new Admin\Post_Types();

		// Init menus and settings.
		new Admin\Menus();

		if ( defined( 'DOING_AJAX' ) ) {
			// Admin ajax callbacks.
			new Admin\Ajax();
		}
	}

	/**
	 * Init plugin when WordPress initializes.
	 *
	 * @since 3.0.0
	 */
	public function init() {

		// Before init action hook.
		do_action( 'before_simcal_init' );

		// Set up localization.
		load_plugin_textdomain( 'google-calendar-events', false, dirname( plugin_basename( SIMPLE_CALENDAR_MAIN_FILE ) ) . '/languages/' );

		// Init objects factory.
		$this->objects = new Objects();

		// Upon init action hook.
		do_action( 'simcal_init' );
	}

	/**
	 * Register plugin settings.
	 *
	 * @since 3.0.0
	 */
	public function register_settings() {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			$settings = new Admin\Pages();
			$settings->register_settings( $settings->get_settings() );
		}
	}

	/**
	 * Get Ajax URL.
	 *
	 * @since  3.0.0
	 *
	 * @return string
	 */
	public function ajax_url() {
		return admin_url( 'admin-ajax.php', 'relative' );
	}

	/**
	 * Get URL.
	 *
	 * @since  3.0.0
	 *
	 * @param  string $case Requested url.
	 *
	 * @return string
	 */
	public function get_url( $case ) {
		switch ( $case ) {
			case 'codex' :
			case 'apidocs' :
				return 'http://codex.simplecalendar.io';
			case 'add-ons' :
				return self::$homepage . '/addons/';
			case 'docs' :
				return 'http://docs.simplecalendar.io';
			case 'github' :
				return 'https://github.com/moonstonemedia/Simple-Calendar';
			case 'newsletter' :
				return 'https://www.getdrip.com/forms/9434542/submissions/new';
			case 'support' :
				return 'https://wordpress.org/support/plugin/google-calendar-events';
			default :
				return self::$homepage;
		}
	}

}

/**
 * Simple Calendar.
 *
 * @return Plugin
 */
function plugin() {
	return Plugin::get_instance();
}

plugin();
