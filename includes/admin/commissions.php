<?php
/**
 * Commissions Admin View.
 *
 * @package     EDD\Payout_Receipts
 * @subpackage  Admin
 * @copyright   Copyright (c) 2018, Sell Comet
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add new controls for sending payout receipts.
 *
 * @since       1.0.0
 * @return      void
 */
function edd_payout_receipts_commissions_list() {
	?>

    <button class="button-primary edd-payout-receipts-export-toggle"><?php _e( 'Generate Payout Receipts', 'edd-payout-receipts' ); ?></button>
    <button class="button-primary edd-payout-receipts-export-toggle"
            style="display:none"><?php _e( 'Close', 'edd-payout-receipts' ); ?></button>

    <form id="edd-payout-receipts-export-payout-receipts" class="eddc-export-form edd-export-form" method="post"
          style="display:none;">
		<?php echo EDD()->html->date_field( array( 'id'          => 'edd-payout-receipts-export-start',
		                                           'name'        => 'start',
		                                           'placeholder' => __( 'Choose start date', 'edd-payout-receipts' )
		) ); ?>
		<?php echo EDD()->html->date_field( array( 'id'          => 'edd-payout-receipts-export-end',
		                                           'name'        => 'end',
		                                           'placeholder' => __( 'Choose end date', 'edd-payout-receipts' )
		) ); ?>
        <input type="number" increment="0.01" class="eddc-medium-text" id="minimum" name="minimum"
               placeholder=" <?php _e( 'Minimum', 'edd-payout-receipts' ); ?>"/>
        <select id="edd-payout-receipts-export-status" name="status">
			<?php
			$statuses = edd_payout_receipts_get_commission_statuses();
			foreach ( $statuses as $status => $label ) {
				echo '<option value="' . $status . '">' . $label . '</option>';
			}
			?>
        </select>
        <select id="edd-payout-receipts-export-user" name="user">
			<?php
			$statuses = edd_payout_receipts_get_commissions_user_ids();
			foreach ( $statuses as $status => $label ) {
				echo '<option value="' . $status . '">' . $label . '</option>';
			}
			?>
        </select>
		<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
        <input type="hidden" name="edd-export-class" value="EDD_Batch_Commissions_Payout_Receipts"/>
        <span>
            <input type="submit" value="<?php _e( 'Generate File', 'edd-payout-receipts' ); ?>"
                   class="button-secondary"/>
            <span class="spinner"></span>
        </span>
        <p><?php _e( 'This will generate a payout receipt file for review.', 'edd-payout-receipts' ); ?></p>
    </form>

    <form id="edd-payout-receipts-send-emails" class="eddc-export-form edd-export-form" method="post"
          style="display: none;">
		<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
        <input type="hidden" name="edd-export-class" value="EDD_Batch_Commissions_Send_Payout_Receipts"/>
        <span>
            <input type="submit" value="<?php _e( 'Send Payout Receipts', 'edd-payout-receipts' ); ?>"
                   class="button-primary"/>&nbsp;
            <a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-commissions' ); ?>"
               class="button-secondary"><?php _e( 'Cancel', 'edd-payout-receipts' ); ?></a>
            <span class="spinner"></span>
        </span>
        <p><?php _e( 'This will send payout receipts to all commission recipients', 'edd-payout-receipts' ); ?></p>
    </form>

	<?php
}
add_action( 'eddc_commissions_page_buttons', 'edd_payout_receipts_commissions_list' );
