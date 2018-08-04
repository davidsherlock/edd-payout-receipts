<?php
/**
 * Export Functions.
 *
 * These are functions related to exporting data from EDD Commissions.
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
 * Loads the commissions payouts batch process if needed
 *
 * @since       1.0.0
 *
 * @param       string $class The class being requested to run for the batch export
 *
 * @return      void
 */
function edd_payout_receipts_include_payout_receipts_batch_processor( $class ) {
	if ( 'EDD_Batch_Commissions_Payout_Receipts' === $class ) {
		require_once EDD_PAYOUT_RECEIPTS_DIR . 'includes/admin/classes/class-batch-commissions-payout-receipts.php';
	}
}


/**
 * Loads the commissions mark paid batch process if needed
 *
 * @since       1.0.0
 *
 * @param       string $class The class being requested to run for the batch export
 *
 * @return      void
 */
function edd_payout_receipts_include_send_receipts_processor( $class ) {
	if ( 'EDD_Batch_Commissions_Send_Payout_Receipts' === $class ) {
		require_once EDD_PAYOUT_RECEIPTS_DIR . 'includes/admin/classes/class-batch-commissions-send-payout-receipts.php';
	}
}
