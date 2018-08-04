<?php
/**
 * Plugin Name:     Easy Digital Downloads - Payout Receipts
 * Plugin URI:      https://sellcomet.com/downloads/edd-payout-receipts
 * Description:     Easily send payout receipts to your commission recipients for set date ranges.
 * Version:         1.0.0
 * Author:          Sell Comet
 * Author URI:      https://sellcomet.com
 * Text Domain:     edd-payout-receipts
 *
 * @package         EDD\Payout_Receipts
 * @author          Sell Comet
 * @copyright       Copyright (c) Sell Comet
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'EDD_Payout_Receipts' ) ) {

	/**
	 * Main EDD_Payout_Receipts class
	 *
	 * @since       1.0.0
	 */
	class EDD_Payout_Receipts {

		/**
		 * @var         EDD_Payout_Receipts $instance The one true EDD_Payout_Receipts
		 * @since       1.0.0
		 */
		private static $instance;


		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      object self::$instance The one true EDD_Payout_Receipts
		 */
		public static function instance() {
			if ( ! self::$instance ) {
				self::$instance = new EDD_Payout_Receipts();
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->load_textdomain();
			}

			return self::$instance;
		}


		/**
		 * Setup plugin constants
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function setup_constants() {
			// Plugin version
			define( 'EDD_PAYOUT_RECEIPTS_VER', '1.0.0' );

			// Plugin path
			define( 'EDD_PAYOUT_RECEIPTS_DIR', plugin_dir_path( __FILE__ ) );

			// Plugin URL
			define( 'EDD_PAYOUT_RECEIPTS_URL', plugin_dir_url( __FILE__ ) );
		}


		/**
		 * Include necessary files
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function includes() {
			// Include Enqueue Script Functions
			require_once EDD_PAYOUT_RECEIPTS_DIR . 'includes/enqueue-scripts.php';

			// Include Commission Functions
			require_once EDD_PAYOUT_RECEIPTS_DIR . 'includes/commission-functions.php';

			// Include Helper Functions
			require_once EDD_PAYOUT_RECEIPTS_DIR . 'includes/helper-functions.php';

			// Include User Functions
			require_once EDD_PAYOUT_RECEIPTS_DIR . 'includes/user-functions.php';

			// Include User Meta Functions
			require_once EDD_PAYOUT_RECEIPTS_DIR . 'includes/user-meta.php';

			// Include Commission Notifications class
			require_once EDD_PAYOUT_RECEIPTS_DIR . 'includes/admin/classes/class-commission-notifications.php';

			// Include Commission Payout Notifications class
			require_once EDD_PAYOUT_RECEIPTS_DIR . 'includes/admin/classes/class-commission-payout-receipts.php';

			// Include Export Functions
			require_once EDD_PAYOUT_RECEIPTS_DIR . 'includes/admin/export-functions.php';

			// Include Export Action Functions
			require_once EDD_PAYOUT_RECEIPTS_DIR . 'includes/admin/export-actions.php';

			// Include Admin Commissions Functions
			require_once EDD_PAYOUT_RECEIPTS_DIR . 'includes/admin/commissions.php';
		}


		/**
		 * Internationalization
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      void
		 */
		public function load_textdomain() {
			// Set filter for language directory
			$lang_dir = EDD_PAYOUT_RECEIPTS_DIR . '/languages/';
			$lang_dir = apply_filters( 'edd_payout_receipts_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), 'edd-payout-receipts' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'edd-payout-receipts', $locale );

			// Setup paths to current locale file
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/edd-payout-receipts/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/edd-plugin-name/ folder
				load_textdomain( 'edd-payout-receipts', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/edd-plugin-name/languages/ folder
				load_textdomain( 'edd-payout-receipts', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'edd-payout-receipts', false, $lang_dir );
			}
		}


	}
} // End if class_exists check


/**
 * The main function responsible for returning the one true EDD_Payout_Receipts
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \EDD_Payout_Receipts The one true EDD_Payout_Receipts
 */
function EDD_Payout_Receipts_load() {
	if ( ! class_exists( 'Easy_Digital_Downloads' ) || ! class_exists( 'EDDC' ) ) {
		if ( ! class_exists( 'EDD_Extension_Activation' ) || ! class_exists( 'EDD_Payout_Receipts_Activation' ) ) {
			require_once 'includes/classes/class-extension-activation.php';
		}

		// Easy Digital Downloads activation
		if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
			$edd_activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
			$edd_activation = $edd_activation->run();
		}

		// Commissions activation
		if ( ! class_exists( 'EDDC' ) ) {
			$edd_commissions_activation = new EDD_Commissions_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
			$edd_commissions_activation = $edd_commissions_activation->run();
		}

	} else {

		return EDD_Payout_Receipts::instance();
	}
}

add_action( 'plugins_loaded', 'EDD_Payout_Receipts_load' );
