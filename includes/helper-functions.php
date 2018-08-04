<?php
/**
 * Helper Functions.
 *
 * @package     EDD\Payout_Receipts
 * @subpackage  Core
 * @copyright   Copyright (c) 2018, Sell Comet
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Given an email array key from the edd_payout_receipts_get_commissions_grouped function, parse it's parts
 *
 * @param  string $key The array key
 *
 * @return array          A parsed set of values for email and currency
 */
function edd_payout_receipts_parse_email_parts( $key ) {
	$parts    = explode( '_', $key );
	$email    = isset( $parts[0] ) ? $parts[0] : false;
	$currency = isset( $parts[1] ) ? $parts[1] : false;

	return array( 'email' => $email, 'currency' => $currency );
}

/**
 * Given an user id array key from the edd_payout_receipts_get_commissions_grouped function, parse it's parts
 *
 * @param  string $key The array key
 *
 * @return array          A parsed set of values for user id and currency
 */
function edd_payout_receipts_parse_user_id_parts( $key ) {
	$parts    = explode( '_', $key );
	$user_id  = isset( $parts[0] ) ? $parts[0] : false;
	$currency = isset( $parts[1] ) ? $parts[1] : false;

	return array( 'user_id' => $user_id, 'currency' => $currency );
}

/**
 * Given an download id array key from the edd_payout_receipts_get_commissions_grouped function, parse it's parts
 *
 * @param  string $key The array key
 *
 * @return array          A parsed set of values for download id, price id and currency
 */
function edd_payout_receipts_parse_download_id_parts( $key ) {
	$parts       = explode( '_', $key );
	$download_id = isset( $parts[0] ) ? $parts[0] : false;
	$price_id    = isset( $parts[1] ) ? $parts[1] : false;
	$currency    = isset( $parts[2] ) ? $parts[2] : false;

	return array( 'download_id' => $download_id, 'price_id' => $price_id, 'currency' => $currency );
}
