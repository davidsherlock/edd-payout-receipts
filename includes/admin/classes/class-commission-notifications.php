<?php
/**
 * Commission Notifications.
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


// Extends the default EDD REST API to provide an endpoint for commissions
class EDD_Payout_Receipts_Commission_Notifications {

	/**
	 * Holds the instance
	 *
	 * Ensures that only one instance of EDD_Payout_Receipts_Commission_Notifications exists in memory at any one
	 * time and it also prevents needing to define globals all over the place.
	 *
	 * TL;DR This is a static property that holds the singleton instance.
	 *
	 * @var object
	 * @static
	 * @since 1.0.0
	 */
	private static $instance;


	/**
	 * Main EDD_Payout_Receipts_Commission_Notifications Instance
	 *
	 * Insures that only one instance of EDD_Payout_Receipts_Commission_Notifications exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 1.0.0
	 * @static var array $instance
	 * @return The one true EDD_Payout_Receipts_Commission_Notifications
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof EDD_Payout_Receipts_Commission_Notifications ) ) {
			self::$instance = new self;
			self::$instance->hooks();
		}

		return self::$instance;
	}


	/**
	 * Constructor Function
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function __construct() {
		self::$instance = $this;
	}


	/**
	 * Setup the default hooks and actions
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function hooks() {

		// Unhook existing commissions email functinoality so we can override it
		remove_filter( 'edd_settings_emails', 'eddc_settings_emails' );

		// Conditionally unhook the existing "Commission Notification" sales alert
		add_action( 'init', array( $this, 'disable_sales_alert' ), 5 );

		// Register "Grouped Notifications" commissions setting
		add_filter( 'eddc_settings', array( $this, 'notification_settings' ), 10, 1 );

		// Register our updated "Commissions" email settings below
		add_filter( 'edd_settings_emails', array( $this, 'settings_emails' ), 10, 1 );

		// Add additional email template tags to the "Commission Notifications" email
		add_filter( 'eddc_email_template_tags', array( $this, 'email_template_tags' ), 10, 1 );

		// Parse additional template tags
		add_filter( 'eddc_sale_alert_email', array( $this, 'process_email_alert' ), 10, 7 );

		// Send grouped email notification
		add_action( 'edd_complete_purchase', array( $this, 'send_grouped_email_alert' ), 999, 3 );

	}

	/**
	 * Add new "Grouped Notifications" settings to Commissions
	 *
	 * @since       1.0.0
	 *
	 * @param       array $commission_settings The array of settings for the Commissions settings page.
	 *
	 * @return      array $commission_settings The array of settings for the Commissions settings page.
	 */
	public function notification_settings( $commission_settings ) {
		$commission_settings[] = array(
			'id'            => 'edd_payout_receipts_grouped_notifications',
			'name'          => __( 'Grouped Notifications', 'edd-payout-receipts' ),
			'desc'          => __( 'This option determines whether or not commission notification emails are grouped into a single message or sent individually.', 'edd-payout-receipts' ),
			'type'          => 'radio',
			'std'           => 'no',
			'options'       => array(
				'yes' => __( 'Yes, group commission notifications by payment into a single email message', 'edd-payout-receipts' ),
				'no'  => __( 'No, send invididual commission notification messages on a per-commission basis', 'edd-payout-receipts' ),
			),
			'tooltip_title' => __( 'Grouped Notifications', 'edd-payout-receipts' ),
			'tooltip_desc'  => __( 'By default, Commissions sends email notifications on a per-commission basis to recipients. By enabling grouped notifications, a single message will be sent with all commission information combined into a single email. Please note, alternative email tags are used for the group notification message.', 'edd-payout-receipts' ),
		);

		return $commission_settings;
	}


	/**
	 * Disables the individual "Commissions Notifications" if grouped notifications are enabled
	 *
	 * @since       1.0.0
	 * @return      void
	 */
	public function disable_sales_alert() {
		if ( edd_get_option( 'edd_payout_receipts_grouped_notifications', 'no' ) == 'yes' ) {
			remove_action( 'eddc_insert_commission', 'eddc_email_alert', 10, 5 );
		}
	}


	/**
	 * Registers the new Commissions options in Emails
	 *
	 * @since       1.0.0
	 *
	 * @param       $settings array the existing plugin settings
	 *
	 * @return      array
	 */
	public function settings_emails( $settings ) {
		$commission_settings = array(
			array(
				'id'   => 'eddc_header',
				'name' => '<strong>' . __( 'Commission Notifications', 'edd-payout-receipts' ) . '</strong>',
				'desc' => '',
				'type' => 'header',
				'size' => 'regular'
			),
			array(
				'id'   => 'edd_commissions_disable_sale_alerts',
				'name' => __( 'Disable New Sale Alerts', 'edd-payout-receipts' ),
				'desc' => __( 'Check this box to disable the New Sale notification emails sent to commission recipients.', 'edd-payout-receipts' ),
				'type' => 'checkbox'
			),
			array(
				'id'   => 'edd_payout_receipts_disable_free_purchase_sale_alerts',
				'name' => __( 'Disable Free Purchase Alerts', 'edd-payout-receipts' ),
				'desc' => __( 'Check this box to disable the New Sale notification emails sent to commission recipients for free purchases.', 'edd-payout-receipts' ),
				'type' => 'checkbox'
			),
			array(
				'id'   => 'edd_commissions_email_subject',
				'name' => __( 'Email Subject', 'edd-payout-receipts' ),
				'desc' => __( 'Enter the subject for commission emails.', 'edd-payout-receipts' ),
				'type' => 'text',
				'size' => 'regular',
				'std'  => __( 'New Sale!', 'edd-payout-receipts' )
			),
			array(
				'id'   => 'edd_commissions_email_message',
				'name' => __( 'Email Body', 'edd-payout-receipts' ),
				'desc' => __( 'Enter the content for commission emails. HTML is accepted. Available template tags:', 'edd-payout-receipts' ) . '<br />' . $this->display_email_template_tags(),
				'type' => 'rich_editor',
				'std'  => eddc_get_email_default_body()
			)
		);

		if ( version_compare( EDD_VERSION, 2.5, '>=' ) ) {
			$commission_settings = array( 'commissions' => $commission_settings );
		}

		return array_merge( $settings, $commission_settings );

	}


	/**
	 * Retrieve default email body
	 *
	 * @since       3.0
	 * @return      string $body The default email
	 */
	function get_grouped_email_default_body() {
		$from_name = edd_get_option( 'from_name', get_bloginfo( 'name' ) );
		$message   = __( 'Hello {name},', 'edd-payout-receipts' ) . "\n\n" . sprintf( __( 'You have made a new sale for a total of {amount} on %s!', 'edd-payout-receipts' ), stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) ) . "\n\n";
		$message   .= __( 'Items sold: ', 'edd-payout-receipts' ) . "{commissions}\n\n";
		$message   .= __( 'Thank you', 'edd-payout-receipts' );

		return apply_filters( 'edd_payout_receipts_get_grouped_email_default_body', $message );
	}


	/**
	 * Parse template tags for display
	 *
	 * @since       3.0
	 * @return      string $tags The parsed template tags
	 */
	function display_email_template_tags() {
		if ( edd_get_option( 'edd_payout_receipts_grouped_notifications', 'no' ) == 'yes' ) {
			$template_tags = $this->get_grouped_email_template_tags();
		} else {
			$template_tags = eddc_get_email_template_tags();
		}

		$tags = '';

		foreach ( $template_tags as $template_tag ) {
			$tags .= '{' . $template_tag['tag'] . '} - ' . $template_tag['description'] . '<br />';
		}

		return $tags;
	}


	/**
	 * Retrieve email template tags
	 *
	 * @since       3.0
	 * @return      array $tags The email template tags
	 */
	public function get_grouped_email_template_tags() {
		$tags = array(
			array(
				'tag'         => 'commissions',
				'description' => sprintf( __( 'A list of purchased %s with associated commission amounts and rates', 'edd-payout-receipts' ), edd_get_label_plural() ),
			),
			array(
				'tag'         => 'amount',
				'description' => sprintf( __( 'The total value of the purchased %s', 'edd-payout-receipts' ), edd_get_label_plural() ),
			),
			array(
				'tag'         => 'date',
				'description' => __( 'The date of the purchase', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'name',
				'description' => __( 'The first name of the user', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'fullname',
				'description' => __( 'The full name of the user', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'user_id',
				'description' => __( 'The user id the user', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'username',
				'description' => __( 'The user name of the user', 'edd-payment-receipts' ),
			),
			array(
				'tag'         => 'address',
				'description' => __( 'The users address', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'price',
				'description' => __( 'The total price of the item solds', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'tax',
				'description' => sprintf( __( 'The amount of tax calculated for the purchased %s', 'edd-payout-receipts' ), edd_get_label_plural() ),
			),
			array(
				'tag'         => 'payment_id',
				'description' => __( 'The unique ID number for this purchase', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'store_commission',
				'description' => __( 'The total store commission accured for this purchase', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'sitename',
				'description' => __( 'Your site name', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'buyer_name',
				'description' => __( "The buyer's first name", 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'buyer_fullname',
				'description' => __( "The buyer's full name, first and last", 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'buyer_username',
				'description' => __( "The buyer's user name on the site, if they registered an account", 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'buyer_user_email',
				'description' => __( "The buyer's email address", 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'buyer_address',
				'description' => __( "The buyer's billing address", 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'payment_id',
				'description' => __( 'The unique ID number for this purchase', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'receipt_id',
				'description' => __( 'The unique ID number for the buyer purchase receipt', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'payment_method',
				'description' => __( 'The method of payment used for this purchase', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'ip_address',
				'description' => __( "The buyer's IP Address", 'edd-payout-receipts' ),
			),
		);

		return apply_filters( 'edd_payout_receipts_get_grouped_email_template_tags', $tags );
	}


	/**
	 * Add addition email template tags to the "Commission Notifications" email
	 *
	 * @since       1.0
	 * @return      array $tags The original email template tags
	 * @return      array $tags The updated email template tags
	 */
	public function email_template_tags( $tags ) {
		$new_tags = array(
			array(
				'tag'         => 'address',
				'description' => __( 'The users address', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'item_discount',
				'description' => __( 'The cart item discount amount of the purchased Download', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'item_subtotal',
				'description' => __( 'The cart item subtotal amount of the purchased Download', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'price',
				'description' => __( 'The price amount of the purchased Download', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'tax',
				'description' => __( 'The tax amount of the purchased Download', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'user_id',
				'description' => __( 'The user id the user', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'username',
				'description' => __( 'The user name of the user', 'edd-payment-receipts' ),
			),
			array(
				'tag'         => 'buyer_name',
				'description' => __( "The buyer's first name", 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'buyer_fullname',
				'description' => __( "The buyer's full name, first and last", 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'buyer_username',
				'description' => __( "The buyer's user name on the site, if they registered an account", 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'buyer_user_email',
				'description' => __( "The buyer's email address", 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'buyer_address',
				'description' => __( "The buyer's billing address", 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'payment_id',
				'description' => __( 'The unique ID number for this purchase', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'receipt_id',
				'description' => __( 'The unique ID number for the buyer purchase receipt', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'payment_method',
				'description' => __( 'The method of payment used for this purchase', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'ip_address',
				'description' => __( "The buyer's IP Address", 'edd-payout-receipts' ),
			),
		);

		return array_merge( $tags, $new_tags );
	}


	/**
	 * Filter Email Sale Alert template tags
	 *
	 * @since       1.0.0
	 * @return      void
	 */
	public function process_email_alert( $message, $user_id, $commission_amount, $rate, $download_id, $commission_id ) {
		return $this->parse_notification_template_tags( $message, $download_id, $commission_id, $commission_amount, $rate );
	}


	/**
	 * Parse "Commission Notifications" additional email template tags
	 *
	 * @since       1.0.0
	 *
	 * @param       string $message The email body
	 * @param       int $download_id The ID for a given download
	 * @param       int $commission_id The ID of this commission
	 * @param       int $commission_amount The amount of the commission
	 * @param       int $rate The commission rate of the user
	 *
	 * @return      string $message The email body
	 */
	public function parse_notification_template_tags( $message, $download_id, $commission_id, $commission_amount, $rate ) {
		$commission = new EDD_Commission( $commission_id );

		$payment = false;
		if ( ! empty( $commission->payment_id ) ) {
			$payment = edd_get_payment( $commission->payment_id );
		}

		// Get the commission receipient details
		$user    = get_userdata( $commission->user_id );
		$address = edd_payout_receipts_get_user_address( $commission->user_id );

		// Get the buyer's details
		$buyer_user_info  = $payment->user_info;
		$buyer_email_name = edd_get_email_names( $buyer_user_info, $payment );
		$name             = isset( $buyer_email_name['name'] ) ? $buyer_email_name['name'] : '';
		$fullname         = isset( $buyer_email_name['fullname'] ) ? $buyer_email_name['fullname'] : '';
		$username         = isset( $buyer_email_name['username'] ) ? $buyer_email_name['username'] : '';
		$billing_address  = edd_payout_receipts_get_payment_address( $commission->payment_id );

		// Get the payment method
		$payment_method = edd_get_gateway_checkout_label( $payment->gateway );

		// Get the cart detail prices
		$item_discount    = '';
		$item_subtotal    = '';
		$price            = '';
		$tax              = '';
		$store_commission = '';
		if ( false !== $payment ) {
			$cart_item = isset( $payment->cart_details[ $commission->cart_index ] ) ? $payment->cart_details[ $commission->cart_index ] : false;
			if ( $cart_item ) {
				$item_discount    = html_entity_decode( edd_currency_filter( edd_format_amount( $cart_item['discount'] ) ) );
				$item_subtotal    = html_entity_decode( edd_currency_filter( edd_format_amount( $cart_item['subtotal'] ) ) );
				$price            = html_entity_decode( edd_currency_filter( edd_format_amount( $cart_item['price'] ) ) );
				$tax              = html_entity_decode( edd_currency_filter( edd_format_amount( $cart_item['tax'] ) ) );
				$store_commission = html_entity_decode( edd_currency_filter( edd_format_amount( edd_payout_receipts_get_store_commission_amount( $commission_id ) ) ) );
			}
		}

		$message = str_replace( '{user_id}', $user->id, $message );
		$message = str_replace( '{username}', $user->user_login, $message );
		$message = str_replace( '{address}', $address, $message );
		$message = str_replace( '{download_id}', $commission->download_id, $message );
		$message = str_replace( '{buyer_name}', $name, $message );
		$message = str_replace( '{buyer_fullname}', $fullname, $message );
		$message = str_replace( '{buyer_username}', $username, $message );
		$message = str_replace( '{buyer_user_email}', $payment->email, $message );
		$message = str_replace( '{buyer_address}', $billing_address, $message );
		$message = str_replace( '{buyer_username}', $username, $message );
		$message = str_replace( '{store_commission}', $store_commission, $message );
		$message = str_replace( '{item_discount}', $item_discount, $message );
		$message = str_replace( '{item_subtotal}', $item_subtotal, $message );
		$message = str_replace( '{price}', $price, $message );
		$message = str_replace( '{tax}', $tax, $message );
		$message = str_replace( '{payment_method}', $payment_method, $message );
		$message = str_replace( '{payment_id}', $payment->number, $message );
		$message = str_replace( '{receipt_id}', $payment->receipt_id, $message );
		$message = str_replace( '{ip_address}', $payment->ip, $message );

		return $message;
	}


	/**
	 * Grouped Email Sale Alert
	 *
	 * @param int $payment_id Payment ID.
	 * @param EDD_Payment $payment Payment object for payment ID.
	 * @param EDD_Customer $customer Customer object for associated payment.
	 *
	 * @return void
	 */
	public function send_grouped_email_alert( $payment_id = 0, $payment = null, $customer = null ) {
		// Make sure we don't send a purchase receipt while editing a payment
		if ( isset( $_POST['edd-action'] ) && 'edit_payment' == $_POST['edd-action'] ) {
			return;
		}

		// Bail if $payment_id is empty
		if ( empty( $payment_id ) ) {
			return;
		}

		// Bail early if grouped notifications are set to "No"
		if ( edd_get_option( 'edd_payout_receipts_grouped_notifications', 'no' ) == 'no' ) {
			return;
		}

		// Bail early if sales alerts are globally disabled
		if ( edd_get_option( 'edd_commissions_disable_sale_alerts', false ) ) {
			return;
		}

		// Make sure we have the current payment object
		$payment = edd_get_payment( $payment_id );

		// Since commissions are grouped, we use the payment completed date as the notification date
		$date = date_i18n( get_option( 'date_format' ), strtotime( $payment->completed_date ) );

		// Get the sitename
		$sitename = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

		// Get the commissions for the payment, grouped by user ID
		$commissions = edd_payout_receipts_get_commissions_grouped( array( 'payment_id' => $payment_id ), 'user_id' );

		if ( $commissions ) {

			foreach ( $commissions as $key => $commission ) {

				// Get the user_id from the array key
				$user_id = edd_payout_receipts_parse_user_id_parts( $key )['user_id'];

				// Skip user if user sales alerts are disabled
				if ( get_user_meta( $user_id, 'eddc_disable_user_sale_alerts', true ) ) {
					continue;
				}

				$user  = get_userdata( $user_id );
				$email = $user->user_email; // set address here

				// Get email subject and message
				$subject = edd_get_option( 'edd_commissions_email_subject', __( 'New Sale!', 'edd-payout-receipts' ) );
				$message = edd_get_option( 'edd_commissions_email_message', $this->get_grouped_email_default_body() );

				// Get name and fullname from userdata
				if ( ! empty( $user->first_name ) ) {
					$name = $user->first_name;

					if ( ! empty( $user->last_name ) ) {
						$fullname = $name . ' ' . $user->last_name;
					} else {
						$fullname = $name;
					}
				} else {
					$name     = $user->display_name;
					$fullname = $name;
				}

				// Get the users address if present
				$address = edd_payout_receipts_get_user_address( $user_id );

				// Get the customers details
				$customer_user_info  = $payment->user_info;
				$customer_email_name = edd_get_email_names( $buyer_user_info, $payment );
				$customer_name       = isset( $customer_email_name['name'] ) ? $customer_email_name['name'] : '';
				$customer_fullname   = isset( $customer_email_name['fullname'] ) ? $customer_email_name['fullname'] : '';
				$customer_username   = isset( $customer_email_name['username'] ) ? $customer_email_name['username'] : '';
				$billing_address     = edd_payout_receipts_get_payment_address( $payment_id );

				// Get the payment method
				$payment_method = edd_get_gateway_checkout_label( $payment->gateway );

				// Get per-user commission totals
				$amount           = html_entity_decode( edd_currency_filter( edd_format_amount( $commission['amount'] ) ) );
				$item_price       = html_entity_decode( edd_currency_filter( edd_format_amount( $commission['item_price'] ) ) );
				$tax              = html_entity_decode( edd_currency_filter( edd_format_amount( $commission['tax'] ) ) );
				$store_commission = html_entity_decode( edd_currency_filter( edd_format_amount( $commission['store_commission'] ) ) );

				// Skip user if global sales alerts are disabled and the amount is 0
				if ( ! floatval( $commission['amount'] ) > 0 && (bool) edd_get_option( 'edd_payout_receipts_disable_free_purchase_sale_alerts' ) ) {
					return;
				}

				// Skip user if user sales alerts are disabled for free purchases
				if ( ! floatval( $commission['amount'] ) > 0 && get_user_meta( $user_id, 'eddc_disable_free_purchase_user_sale_alerts', true ) ) {
					return;
				}

				if ( $commission['commissions'] ) {

					$show_amounts = apply_filters( 'edd_payout_receipts_grouped_email_alert_grouped_show_names', true );
					$show_rates   = apply_filters( 'edd_payout_receipts_grouped_email_alert_grouped_show_rates', true );

					$commissions_list = '<ul>';

					foreach ( $commission['commissions'] as $item ) {

						$download = new EDD_Download( $item->download_id );

						$item_purchased = '<strong>' . $download->get_name() . '</strong>';
						if ( $download->has_variable_prices() ) {
							$prices = $download->get_prices();
							if ( isset( $prices[ $item->price_id ] ) ) {
								$item_purchased .= ' - ' . $prices[ $item->price_id ]['name'];
							}
						}

						if ( $show_amounts ) {
							$item_purchased .= ' - ' . html_entity_decode( edd_currency_filter( edd_format_amount( $item->amount ) ) );
						}

						if ( $show_rates ) {
							if ( 'percentage' === $item->type ) {
								$item_purchased .= ' (' . $item->rate . '%' . ')';
							} else {
								$item_purchased .= ' (' . __( 'Flat rate', 'edd-payout-receipts' ) . ')';
							}
						}

						$commissions_list .= '<li>' . apply_filters( 'edd_payout_receipts_email_alert_grouped_list_title', $item_purchased, $item, $price_id, $payment_id ) . '</li>';
					}

					$commissions_list .= '</ul>';
				}

				$message = str_replace( '{commissions}', $commissions_list, $message );
				$message = str_replace( '{amount}', $amount, $message );
				$message = str_replace( '{date}', $date, $message );
				$message = str_replace( '{name}', $name, $message );
				$message = str_replace( '{user_id}', $user->id, $message );
				$message = str_replace( '{username}', $user->user_login, $message );
				$message = str_replace( '{fullname}', $fullname, $message );
				$message = str_replace( '{price}', $item_price, $message );
				$message = str_replace( '{tax}', $tax, $message );
				$message = str_replace( '{sitename}', $sitename, $message );
				$message = str_replace( '{address}', $address, $message );
				$message = str_replace( '{payment_id}', $payment_id, $message );
				$message = str_replace( '{store_commission}', $store_commission, $message );
				$message = str_replace( '{payment_method}', $payment_method, $message );
				$message = str_replace( '{payment_id}', $payment->number, $message );
				$message = str_replace( '{receipt_id}', $payment->receipt_id, $message );
				$message = str_replace( '{ip_address}', $payment->ip, $message );
				$message = str_replace( '{buyer_name}', $customer_name, $message );
				$message = str_replace( '{buyer_fullname}', $customer_fullname, $message );
				$message = str_replace( '{buyer_username}', $customer_username, $message );
				$message = str_replace( '{buyer_user_email}', $payment->email, $message );
				$message = str_replace( '{buyer_address}', $billing_address, $message );
				$message = str_replace( '{buyer_username}', $username, $message );

				// Make message content filterable
				$message = apply_filters( 'edd_payout_receipts_email_alert_grouped_message', $message, $commissions, $user_id, $commission, $payment_id, $payment, $customer );

				if ( class_exists( 'EDD_Emails' ) ) {
					EDD()->emails->__set( 'heading', $subject );
					EDD()->emails->send( $email, $subject, $message );
				} else {
					$from_name = apply_filters( 'edd_payout_receipts_email_alert_grouped_from_name', $from_name, $commissions, $user_id, $commission, $payment_id, $payment, $customer );

					$from_email = edd_get_option( 'from_email', get_option( 'admin_email' ) );
					$from_email = apply_filters( 'edd_payout_receipts_email_alert_grouped_from_email', $from_email, $commissions, $user_id, $commission, $payment_id, $payment, $customer );

					$headers = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";

					wp_mail( $email, $subject, $message, $headers );
				}

			}

		}

	}

}


/**
 * The main function responsible for returning the one true EDD_Payout_Receipts_Commission_Notifications instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing to declare the global.
 *
 * Example: <?php $themedd_search = EDD_Payout_Receipts_Commission_Notifications(); ?>
 *
 * @since 1.0.0
 * @return object The one true EDD_Payout_Receipts_Commission_Notifications Instance.
 */
function EDD_Payout_Receipts_Commission_Notifications() {
	return EDD_Payout_Receipts_Commission_Notifications::instance();
}

EDD_Payout_Receipts_Commission_Notifications();
