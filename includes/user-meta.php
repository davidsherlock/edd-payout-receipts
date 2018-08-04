<?php
/**
 * User Meta Functions.
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
 * Display the "Disable Free Purchase Notification Emails" checkbox
 *
 * @param       object $user The User Object
 *
 * @return      void
 */
function edd_payout_receipts_user_fields( $user ) {
	?>

    <tr>
        <th><label><?php _e( 'Disable Free Purchase Notification Emails', 'edd-payout-receipts' ); ?></label></th>
        <td>
            <input name="eddc_disable_free_purchase_user_sale_alerts" type="checkbox"
                   id="eddc_disable_free_purchase_user_sale_alerts"
                   value="1"<?php checked( get_user_meta( $user->ID, 'eddc_disable_free_purchase_user_sale_alerts', true ) ); ?> />
            <span class="description"><?php _e( 'Check this box if you wish to prevent sale notifications from being sent to this user for free purchases.', 'edd-payout-receipts' ); ?></span>
        </td>
    </tr>

	<?php
}

add_action( 'eddc_user_profile_table_end', 'edd_payout_receipts_user_fields', 10, 1 );

/**
 * Save the "Disable Free Purchase Notification Emails" user field if checked
 *
 * @param       integer $user_id The user ID
 *
 * @return      void
 */
function edd_payout_receipts_save_user_fields( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return false;
	}

	if ( isset( $_POST['eddc_disable_free_purchase_user_sale_alerts'] ) ) {
		update_user_meta( $user_id, 'eddc_disable_free_purchase_user_sale_alerts', true );
	} else {
		delete_user_meta( $user_id, 'eddc_disable_free_purchase_user_sale_alerts' );
	}

}

add_action( 'personal_options_update', 'edd_payout_receipts_save_user_fields' );
add_action( 'edit_user_profile_update', 'edd_payout_receipts_save_user_fields' );
