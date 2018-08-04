<?php
/**
 * Commissions Functions.
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
 * Get the store commission amount for a single commission
 *
 * @param       integer $commission_id The commission ID
 *
 * @return      float $amount The calculated store commission amount
 */
function edd_payout_receipts_get_store_commission_amount( $commission_id ) {
	if ( empty( $commission_id ) ) {
		return false;
	}

	$commission = eddc_get_commission( $commission_id );

	if ( $commission ) {

		$payment = edd_get_payment( $commission->payment_id );

		if ( false !== $payment ) {
			$cart_item = isset( $payment->cart_details[ $commission->cart_index ] ) ? $payment->cart_details[ $commission->cart_index ] : false;
			if ( $cart_item ) {
				$subtotal = (float) $cart_item['subtotal'];
				$tax      = (float) $cart_item['tax'];
				$price    = (float) $cart_item['price'];

				$calc_base = edd_get_option( 'edd_commissions_calc_base', 'subtotal' );

				switch ( $calc_base ) {
					case 'subtotal':
						$amount = $subtotal - $commission->amount;
						break;
					case 'total_pre_tax':
						$amount = $price - $tax - $commission->amount;
						break;
					default:
						$amount = $price - $commission->amount;
						break;
				}

				return apply_filters( 'edd_payout_receipts_get_store_commission_amount', $amount, $commission_id, $commission, $payment, $cart_item );
			}
		}

	}

	return false;
}


/**
 * Get the cart item price for a single commission
 *
 * @param       integer $commission_id The commission ID
 * @param       string $type The cart item price 'type'
 *
 * @return      float $amount The
 */
function edd_payout_receipts_get_cart_item_price( $commission_id, $type = 'item_price' ) {
	if ( empty( $commission_id ) ) {
		return false;
	}

	$commission = eddc_get_commission( $commission_id );

	if ( $commission ) {

		$payment = edd_get_payment( $commission->payment_id );

		if ( false !== $payment ) {
			$cart_item = isset( $payment->cart_details[ $commission->cart_index ] ) ? $payment->cart_details[ $commission->cart_index ] : false;
			if ( $cart_item ) {

				switch ( $type ) {
					case 'discount':
						$amount = (float) $cart_item['discount'];
						break;
					case 'subtotal':
						$amount = (float) $cart_item['subtotal'];
						break;
					case 'tax':
						$amount = (float) $cart_item['tax'];
						break;
					case 'price':
						$amount = (float) $cart_item['price'];
						break;
					case 'item_price':
					default:
						$amount = (float) $cart_item['item_price'];
						break;
				}

				return apply_filters( 'edd_payout_receipts_get_cart_item_price', $amount, $commission_id, $commission, $payment, $cart_item, $type );
			}
		}

	}

	return false;
}


/**
 * Retrieve the unique user display names from the commissions table
 *
 * @return      array $options An array of unique user display names
 */
function edd_payout_receipts_get_commissions_user_ids() {
	global $wpdb;

	$table_name = edd_commissions()->commissions_db->table_name;

	$user_ids = $wpdb->get_col( "SELECT DISTINCT user_id FROM {$table_name};" );

	if ( $user_ids ) {

		$options        = array();
		$options['all'] = __( 'All Users', 'edd-payout-receipts' );

		foreach ( $user_ids as $user_id ) {
			$user                 = get_userdata( $user_id );
			$options[ $user->ID ] = esc_html( $user->display_name );
		}

	} else {

		$options[0] = __( 'No users found', 'edd-payout-receipts' );

	}

	return apply_filters( 'edd_payout_receipts_get_commissions_user_ids', $options, $table_name, $user_ids );
}


/**
 * Retrieves all available commission statuses.
 *
 * @return array $commission_statuses The available commission statuses
 */
function edd_payout_receipts_get_commission_statuses() {
	$commission_statuses = array(
		'unpaid'  => __( 'Unpaid', 'edd-payout-receipts' ),
		'paid'    => __( 'Paid', 'edd-payout-receipts' ),
		'revoked' => __( 'Revoked', 'edd-payout-receipts' )
	);

	return apply_filters( 'edd_payout_receipts_get_commission_statuses', $commission_statuses );
}


/**
 * Retrieve an array of commissions grouped with totals by unique array key
 *
 * @param       array $args Arguments to pass to the query
 * @param       string $type The array key type
 *
 * @return      array $grouped The array of grouped commissions
 */
function edd_payout_receipts_get_commissions_grouped( $args = array(), $type = 'download_id' ) {
	$defaults = array(
		'number' => - 1,
		'status' => array( 'paid', 'unpaid' ),
	);

	$args = wp_parse_args( $args, $defaults );

	$args = apply_filters( 'edd_payout_receipts_get_commissions_grouped_args', $args, $type );

	$commissions = eddc_get_commissions( $args );

	if ( $commissions ) {

		$grouped = array();
		foreach ( $commissions as $commission ) {

			$payment = false;
			if ( ! empty( $commission->payment_id ) ) {
				$payment = edd_get_payment( $commission->payment_id );
			}

			if ( false !== $payment ) {
				$cart_item = isset( $payment->cart_details[ $commission->cart_index ] ) ? $payment->cart_details[ $commission->cart_index ] : false;
				if ( $cart_item ) {
					$item_price = (float) $cart_item['item_price'];
					$subtotal   = (float) $cart_item['subtotal'];
					$tax        = (float) $cart_item['tax'];
					$price      = (float) $cart_item['price'];
					$discount   = (float) $cart_item['discount'];

					// Calculate the
					$calc_base = edd_get_option( 'edd_commissions_calc_base', 'subtotal' );

					switch ( $calc_base ) {
						case 'subtotal':
							$store_commission = $subtotal - $commission->amount;
							break;
						case 'total_pre_tax':
							$store_commission = $price - $tax - $commission->amount;
							break;
						default:
							$store_commission = $price - $commission->amount;
							break;
					}

				}
			}

			$user          = get_userdata( $commission->user_id );
			$custom_paypal = get_user_meta( $commission->user_id, 'eddc_user_paypal', true );
			$email         = is_email( $custom_paypal ) ? $custom_paypal : $user->user_email;

			// Set the key based on the type set
			switch ( $type ) {
				case 'hash':
					$key = md5( $email . $commission->currency );
					break;
				case 'email':
					$key = $email . '_' . $commission->currency;
					break;
				case 'user_id':
					$key = $commission->user_id . '_' . $commission->currency;
					break;
				case 'download_id':
				default:
					$key = $commission->download_id . '_' . $commission->price_id . '_' . $commission->currency;
					break;
			}

			if ( array_key_exists( $key, $grouped ) ) {
				$grouped[ $key ]['amount']           += $commission->amount;
				$grouped[ $key ]['item_price']       += $item_price;
				$grouped[ $key ]['subtotal']         += $subtotal;
				$grouped[ $key ]['tax']              += $tax;
				$grouped[ $key ]['price']            += $price;
				$grouped[ $key ]['discount']         += $discount;
				$grouped[ $key ]['store_commission'] += $store_commission;
				$grouped[ $key ]['commissions'][]    = $commission;
			} else {
				$grouped[ $key ] = array(
					'amount'           => $commission->amount,
					'item_price'       => isset( $item_price ) ? $item_price : 0,
					'subtotal'         => isset( $subtotal ) ? $subtotal : 0,
					'tax'              => isset( $tax ) ? $tax : 0,
					'price'            => isset( $price ) ? $price : 0,
					'discount'         => isset( $discount ) ? $discount : 0,
					'store_commission' => isset( $store_commission ) ? $store_commission : 0,
					'commissions'      => array( $commission ),
				);
			}

		}

		return apply_filters( 'edd_payout_receipts_get_commissions_grouped', $grouped, $key );
	}

	return false;
}
