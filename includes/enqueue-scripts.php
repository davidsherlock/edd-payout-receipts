<?php
/**
 * Enqueue Script Functions.
 *
 * @package     EDD\Payout_Receipts
 * @subpackage  Core
 * @copyright   Copyright (c) 2018, Sell Comet
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Load admin scripts
 *
 * @since       1.0.0
 * @global      array $edd_settings_page The slug for the EDD settings page
 * @global      string $post_type The type of post that we are editing
 * @return      void
 */
function edd_payout_receipts_admin_scripts( $hook ) {
	$screen = get_current_screen();

	if ( ! is_object( $screen ) ) {
		return;
	}

	$allowed_screens = array(
		'download_page_edd-commissions',
		'download',
		'download_page_edd-reports',
	);

	$allowed_screens = apply_filters( 'edd_payout_receipts_admin_script_screens', $allowed_screens );

	if ( ! in_array( $screen->id, $allowed_screens ) ) {
		return;
	}

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	wp_register_script( 'edd-payout-receipts-admin', EDD_PAYOUT_RECEIPTS_URL . 'assets/js/admin' . $suffix . '.js', array( 'jquery' ), EDD_PAYOUT_RECEIPTS_VER );
	wp_enqueue_script( 'edd-payout-receipts-admin' );

	wp_enqueue_style( 'edd-payout-receipts-admin', EDD_PAYOUT_RECEIPTS_URL . 'assets/css/admin' . $suffix . '.css', EDD_PAYOUT_RECEIPTS_VER );
}

add_action( 'admin_enqueue_scripts', 'edd_payout_receipts_admin_scripts', 100 );
