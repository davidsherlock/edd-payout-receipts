<?php
/**
 * User Functions.
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
 * Get the payment billing address
 *
 * @param       integer $payment_id The payment ID
 *
 * @return      array $return The payment billing address
 */
function edd_payout_receipts_get_payment_address( $payment_id = 0 ) {
	if ( empty( $payment_id ) ) {
		return false;
	}

	$user_info    = edd_get_payment_meta_user_info( $payment_id );
	$user_address = ! empty( $user_info['address'] ) ? $user_info['address'] : array(
		'line1'   => '',
		'line2'   => '',
		'city'    => '',
		'country' => '',
		'state'   => '',
		'zip'     => ''
	);

	$return = $user_address['line1'] . "\n";
	if ( ! empty( $user_address['line2'] ) ) {
		$return .= $user_address['line2'] . "\n";
	}

	$all_states = edd_get_shop_states( $user_address['country'] );
	$return     .= $user_address['city'] . "\n";
	$return     .= $user_address['zip'] . "\n";
	$return     .= $all_states[ $user_address['state'] ] . "\n";

	$all_countries = edd_get_country_list();
	$return        .= $all_countries[ $user_address['country'] ];

	return apply_filters( 'edd_payout_receipts_get_payment_address', $return, $payment_id, $user_info, $user_address );
}


/**
 * Get the user billing address
 *
 * @param       integer $user_id The user ID
 *
 * @return      array $return The user billing address
 */
function edd_payout_receipts_get_user_address( $user_id = 0 ) {
	if ( empty( $user_id ) ) {
		return false;
	}

	$user_info    = edd_get_customer_address( $user_id );
	$user_address = ! empty( $user_info ) ? $user_info : array(
		'line1'   => '',
		'line2'   => '',
		'city'    => '',
		'country' => '',
		'state'   => '',
		'zip'     => ''
	);

	$return = $user_address['line1'] . "\n";
	if ( ! empty( $user_address['line2'] ) ) {
		$return .= $user_address['line2'] . "\n";
	}

	$all_states = edd_get_shop_states( $user_address['country'] );
	$return     .= $user_address['city'] . "\n";
	$return     .= $user_address['zip'] . "\n";
	$return     .= $all_states[ $user_address['state'] ] . "\n";

	$all_countries = edd_get_country_list();
	$return        .= $all_countries[ $user_address['country'] ];

	return apply_filters( 'edd_payout_receipts_get_user_address', $return, $user_id, $user_info, $user_address );
}
