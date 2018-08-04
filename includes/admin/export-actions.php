<?php
/**
 * Export Actions.
 *
 * These are actions related to exporting data from EDD Commissions.
 *
 * @package     EDD\Payout_Receipts
 * @subpackage  Admin/Export
 * @copyright   Copyright (c) 2018, Sell Comet
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Register the payout receipts batch exporter
 *
 * @since       1.0.0
 * @return      void
 */
function edd_payout_receipts_register_payout_receipts_batch_export() {
	add_action( 'edd_batch_export_class_include', 'edd_payout_receipts_include_payout_receipts_batch_processor', 10, 1 );
}

add_action( 'edd_register_batch_exporter', 'edd_payout_receipts_register_payout_receipts_batch_export', 10 );


/**
 * Register the send receipt emails batch exporter
 *
 * @since       1.0.0
 * @return      void
 */
function edd_payout_receipts_register_send_emails_batch_export() {
	add_action( 'edd_batch_export_class_include', 'edd_payout_receipts_include_send_receipts_processor', 10, 1 );
}

add_action( 'edd_register_batch_exporter', 'edd_payout_receipts_register_send_emails_batch_export', 10 );
