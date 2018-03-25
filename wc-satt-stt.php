<?php
/*
 * Plugin Name: WooCommerce Subscribe All the Things: Sign-up and Trial Add-on
 * Plugin URI:  https://github.com/seb86/woocommerce-subscribe-to-all-the-things-signup-trial-add-on
 * Description: Adds a sign-up fee and free trial option for each subscription scheme. Requires WooCommerce Subscribe All the Things extension.
 * Author:      Sébastien Dumont
 * Author URI:  https://sebastiendumont.com
 * Version:     2.0.0
 * Text Domain: wc-satt-stt
 * Domain Path: /languages/
 *
 * Copyright: © 2018 Sébastien Dumont (mailme@sebastiendumont.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WooCommerce Subscribe All the Things: Sign-up and Trail Add-on
 * @author    Sébastien Dumont
 * @copyright Copyright © 2018, Sébastien Dumont
 * @license   GNU General Public License v3.0 http://www.gnu.org/licenses/gpl-3.0.html
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 3.3.4
 */
if ( ! defined('ABSPATH') ) exit; // Exit if accessed directly.

/**
 * Main WCSATT_STT class.
 *
 * The main instance of the plugin.
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'WCSATT_STT' ) ) {
	class WCSATT_STT {

		/**
		 * @var WCSATT_STT - the single instance of the class.
		 *
		 * @access protected
		 * @static
		 * @since  1.0.0
		 */
		protected static $_instance = null;

		/**
		 * Plugin Version
		 *
		 * @access public
		 * @static
		 * @since  1.0.0
		 * @var    string
		 */
		public static $version = '2.0.0';

		/**
		 * Required WooCommerce Version
		 *
		 * @access public
		 * @static
		 * @since  1.0.0
		 * @var    string
		 */
		public static $required_woo = '3.0.0';

		/**
		 * Required WooCommerce SATT Version
		 *
		 * @access public
		 * @static
		 * @since  2.0.0
		 * @var    string
		 */
		public static $required_wcsatt = '2.0.0';

		/**
		 * Main WCSATT_STT Instance.
		 *
		 * Ensures only one instance of WCSATT_STT is loaded or can be loaded.
		 *
		 * @access public
		 * @static
		 * @since 1.0.0
		 * @see WCSATT_STT()
		 * @return WCSATT_STT - Single instance
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Cloning is forbidden.
		 *
		 * @access  public
		 * @since   1.0.0
		 * @version 2.0.0
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cloning this object is forbidden.', 'wc-satt-stt' ), self::$version );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @access  public
		 * @since   1.0.0
		 * @version 2.0.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'wc-satt-stt' ), self::$version );
		}

		/**
		 * WCSATT_STT Constructor.
		 *
		 * @access public
		 * @since  1.0.0
		 * @return WCSATT_STT
		 */
		public function __construct() {
			// Load translation files.
			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

			// Include required files.
			add_action( 'init', array( $this, 'includes' ) );

			// Filter the plugin meta links.
			add_filter( 'plugin_row_meta', array( $this, 'plugin_meta_links' ), 10, 4 );
		}

		/*-----------------------------------------------------------------------------------*/
		/*  Helper Functions                                                                 */
		/*-----------------------------------------------------------------------------------*/

		/**
		 * Get the Plugin URL.
		 *
		 * @access public
		 * @static
		 * @since  1.0.0
		 * @return string
		 */
		public static function plugin_url() {
			return plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
		} // END plugin_url()

		/**
		 * Get the Plugin Path.
		 *
		 * @access public
		 * @static
		 * @since  1.0.0
		 * @return string
		 */
		public static function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		} // END plugin_path()

		/*-----------------------------------------------------------------------------------*/
		/*  Load Files                                                                       */
		/*-----------------------------------------------------------------------------------*/

		/**
		 * Includes required core files used in admin and on the frontend.
		 *
		 * @access  public
		 * @since   1.0.0
		 * @version 2.0.0
		 * @return  void
		 */
		public function includes() {
			// Check we're running the required version of WooCommerce.
			if ( ! defined( 'WC_VERSION' ) || version_compare( WC_VERSION, self::$required_woo, '<' ) ) {
				add_action( 'admin_notices', array( $this, 'admin_notice' ) );
				return false;
			}

			// Checks that WooCommerce Subscribe All the Things is running or is less than the required version.
			if ( ! class_exists( 'WCS_ATT' ) || version_compare( self::$version, self::$required_wcsatt, '<' ) ) {
				add_action( 'admin_notices', array( $this, 'wcsatt_stt_admin_notice' ) );
				return false;
			}

			require_once( 'includes/class-wcsatt-stt-cart.php' );
			require_once( 'includes/class-wcsatt-stt-display.php' );

			// Legacy Stuff
			require_once( 'includes/legacy/class-wcsatt-stt-display.php' );

			// Admin includes
			if ( is_admin() ) {
				$this->admin_includes();
			}
		} // END includes()

		/**
		 * Admin & AJAX functions and hooks.
		 *
		 * @access public
		 * @since  2.0.0
		 * @return void
		 */
		public function admin_includes() {
			require_once( 'includes/admin/meta-boxes/class-wcsatt-stt-meta-box-product-data.php' );
			require_once( 'includes/admin/class-wcsatt-stt-admin.php' );
		}

		/**
		 * Display a warning message if minimum version of WooCommerce check fails.
		 *
		 * @return void
		 */
		public function admin_notice() {
			echo '<div class="error"><p>' . sprintf( __( '%1$s requires at least %2$s v%3$s in order to function. Please upgrade %2$s.', 'wc-satt-stt' ), 'WooCommerce Subscribe All the Things: Sign-up and Trial Add-on', 'WooCommerce', self::$required_woo ) . '</p></div>';
		} // END admin_notice()

		/**
		 * Display a warning message if minimum version of WooCommerce Subscribe All the Things check fails.
		 *
		 * @return void
		 */
		public function wcsatt_stt_admin_notice() {
			echo '<div class="error"><p>' . sprintf( __( '%1$s requires at least %2$s v%3$s in order to function. Please upgrade %2$s.', 'wc-satt-stt' ), 'WooCommerce Subscribe All the Things: Sign-up and Trial Add-on', 'WooCommerce Subscribe All the Things', self::$required_wcsatt ) . '</p></div>';
		} // END wcsatt_stt_admin_notice()

		/*-----------------------------------------------------------------------------------*/
		/*  Localization                                                                     */
		/*-----------------------------------------------------------------------------------*/

		/**
		 * Make the plugin translation ready.
		 *
		 * Translations should be added in the WordPress language directory:
		 *  - WP_LANG_DIR/plugins/wc-satt-stt-LOCALE.mo
		 *
		 * @access public
		 * @since  1.0.0
		 * @return void
		 */
		public function load_plugin_textdomain() {
			// Load text domain.
			load_plugin_textdomain( 'wc-satt-stt', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		} // END load_plugin_textdomain()

		/**
		 * Show plugin meta links on the plugin screen.
		 *
		 * @access public
		 * @since  1.0.0
		 * @param  mixed $links Plugin Row Meta
		 * @param  mixed $file  Plugin Base file
		 * @param  array $data  Plugin Data
		 * @return array
		 */
		public function plugin_meta_links( $links, $file, $data, $status ) {
			if ( $file == plugin_basename( __FILE__ ) ) {
				$author1 = '<a href="' . $data[ 'AuthorURI' ] . '">' . $data[ 'Author' ] . '</a>';
				$author2 = '<a href="http://www.subscriptiongroup.co.uk/">Subscription Group Limited</a>';
				$links[ 1 ] = sprintf( __( 'By %s', 'wc-satt-stt' ), sprintf( __( '%s and %s', 'wc-satt-stt' ), $author1, $author2 ) );
			}

			return $links;
		} // END plugin_meta_links()

	} // END class

} // END if class exists

return WCSATT_STT::instance();
