<?php
/**
 * Send Payout Receipts
 *
 * @package     EDD\Payout_Receipts
 * @subpackage  Admin/Classes
 * @copyright   Copyright (c) 2018, Sell Comet
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * EDD_Batch_Commissions_Send_Payout_Receipts Class
 *
 * @since       1.0.0
 */
class EDD_Batch_Commissions_Send_Payout_Receipts extends EDD_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var         string
	 * @since       1.0.0
	 */
	public $export_type = 'commissions_send_payout_receipts';
	public $is_void = true;
	public $per_step = 25;

	/**
	 * Get the Export Data
	 *
	 * @access      public
	 * @since       1.0.0
	 * @global      object $wpdb Used to query the database using the WordPress Database API
	 * @return      bool
	 */
	public function get_data() {

		$items = get_option( '_edd_payout_receipts_user_ids_to_notify', array() );

		$start_date = explode( '/', $items['start'] );
		$end_date   = explode( '/', $items['end'] );
		$minimum    = (float) $items['minimum'];

		$offset     = ( $this->step - 1 ) * $this->per_step;
		$step_items = array_slice( $items['user_ids'], $offset, $this->per_step );

		if ( $step_items ) {

			foreach ( $step_items as $item ) {
				EDD_Payout_Receipts_Payout_Notifications::send_email( $item, $this->get_query_args(), $minimum, $start_date, $end_date );
			}

			return true;

		}

		return false;
	}


	/**
	 * Return the calculated completion percentage
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      int $percentage The calculated completion percentage
	 */
	public function get_percentage_complete() {
		$user_ids_to_notify = get_option( '_edd_payment_receipts_user_ids_to_notify', array() );
		$total              = count( $user_ids_to_notify['user_ids'] );

		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( $this->per_step * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}


	/**
	 * Return the query args array
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      array $args The calculated completion percentage
	 */
	public function get_query_args() {
		$items = get_option( '_edd_payout_receipts_user_ids_to_notify', array() );

		if ( $items ) {

			$start_date = explode( '/', $items['start'] );
			$end_date   = explode( '/', $items['end'] );

			$args = array(
				'number'     => - 1,
				'status'     => $items['status'],
				'query_args' => array(
					'date_query' => array(
						'after'     => array(
							'year'  => $start_date[2],
							'month' => $start_date[0],
							'day'   => $start_date[1],
						),
						'before'    => array(
							'year'  => $end_date[2],
							'month' => $end_date[0],
							'day'   => $end_date[1],
						),
						'inclusive' => true
					)
				)
			);

			return $args;
		}

		return false;
	}


	/**
	 * Send Admin Payout Report
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      void
	 */
	public function send_payout_report() {
		$items = get_option( '_edd_payout_receipts_user_ids_to_notify', array() );

		if ( $items ) {
			$start_date = explode( '/', $items['start'] );
			$end_date   = explode( '/', $items['end'] );

			EDD_Payout_Receipts_Payout_Notifications::send_admin_email( $this->get_query_args(), (float) $items['minimum'], $start_date, $end_date );
		}

		return false;
	}


	/**
	 * Process a step
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      bool
	 */
	public function process_step() {
		if ( ! $this->can_export() ) {
			wp_die( __( 'You do not have permission to export data.', 'edd-payout-receipts' ), __( 'Error', 'edd-payout-receipts' ), array( 'response' => 403 ) );
		}

		$had_data = $this->get_data();

		if ( $had_data ) {
			$this->done = false;

			return true;
		} else {
			$this->send_payout_report();
			delete_option( '_edd_payout_receipts_user_ids_to_notify' );
			$this->done    = true;
			$this->message = __( 'Commission payout receipts sent.', 'edd-payout-receipts' );
			// This allows the page to redirect to help with the UI
			$this->message .= '<script>setTimeout(function(){ location.reload(); }, 2000);</script>';

			return false;
		}
	}

}
