<?php
/**
 * Commission Payout Receipts Notifications.
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
class EDD_Payout_Receipts_Payout_Notifications {

	/**
	 * Holds the instance
	 *
	 * Ensures that only one instance of EDD_Payout_Receipts_Payout_Notifications exists in memory at any one
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
	 * Main EDD_Payout_Receipts_Payout_Notifications Instance
	 *
	 * Insures that only one instance of EDD_Payout_Receipts_Payout_Notifications exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since  1.0.0
	 * @static var array $instance
	 * @return The one true EDD_Payout_Receipts_Payout_Notifications
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof EDD_Payout_Receipts_Payout_Notifications ) ) {
			self::$instance = new self;
			self::$instance->hooks();
		}

		return self::$instance;
	}


	/**
	 * Constructor Function
	 *
	 * @since  1.0.0
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

		// Add email settings section
		add_filter( 'edd_settings_sections_emails', array( $this, 'email_settings_section' ), 10, 1 );

		// Add email settings
		add_filter( 'edd_settings_emails', array( $this, 'email_settings' ), 10, 1 );

	}


	/**
	 * Add the Payout Receipts emails subsection to the settings
	 *
	 * @since       1.0.0
	 *
	 * @param       array $sections Sections for the emails settings tab
	 *
	 * @return      array
	 */
	public function email_settings_section( $sections ) {
		$sections['payout_receipts'] = __( 'Payout Receipts', 'edd-payout-receipts' );

		return $sections;
	}


	/**
	 * Registers the new Commissions options in Emails
	 *
	 * @since       1.0.0
	 *
	 * @param       array $settings Sections for the emails settings tab
	 *
	 * @return      array $settings
	 */
	public function email_settings( $settings ) {
		$email_settings = array(
			array(
				'id'   => 'edd_payout_receipts_email_header',
				'name' => '<strong>' . __( 'Payout Receipts', 'edd-payout-receipts' ) . '</strong>',
				'desc' => '',
				'type' => 'header',
				'size' => 'regular'
			),
			array(
				'id'   => 'edd_payout_receipts_disable_payout_receipts',
				'name' => __( 'Disable Payout Receipt Alerts', 'eddc' ),
				'desc' => __( 'Check this box to disable the Payout Receipt notification emails sent to commission recipients.', 'edd-payout-receipts' ),
				'type' => 'checkbox'
			),
			array(
				'id'   => 'edd_payout_receipts_email_subject',
				'name' => __( 'Email Subject', 'edd-payout-receipts' ),
				'desc' => __( 'Enter the subject for payout receipts notification emails.', 'edd-payout-receipts' ),
				'type' => 'text',
				'size' => 'regular',
				'std'  => __( 'Payout Receipt', 'edd-payout-receipts' )
			),
			array(
				'id'   => 'edd_payout_receipts_email_message',
				'name' => __( 'Email Body', 'edd-payout-receipts' ),
				'desc' => __( 'Enter the content for payout receipt notification emails. HTML is accepted. Available template tags:', 'edd-payout-receipts' ) . '<br />' . self::display_email_template_tags(),
				'type' => 'rich_editor',
				'std'  => self::get_email_default_body()
			),
			array(
				'id'   => 'edd_payout_receipts_admin_email_header',
				'name' => '<strong>' . __( 'Admin Notification', 'edd-payout-receipts' ) . '</strong>',
				'desc' => '',
				'type' => 'header',
				'size' => 'regular'
			),
			array(
				'id'   => 'edd_payout_receipts_admin_disable_payout_report',
				'name' => __( 'Disable Payout Report Alerts', 'eddc' ),
				'desc' => __( 'Check this box to disable the Payout Report notification emails sent to the site administrator.', 'edd-payout-receipts' ),
				'type' => 'checkbox'
			),
			array(
				'id'   => 'edd_payout_receipts_admin_email_subject',
				'name' => __( 'Email Subject', 'edd-payout-receipts' ),
				'desc' => __( 'Enter the subject for admin payout receipts notification emails.', 'edd-payout-receipts' ),
				'type' => 'text',
				'size' => 'regular',
				'std'  => __( 'Payout Receipt', 'edd-payout-receipts' )
			),
			array(
				'id'   => 'edd_payout_receipts_admin_email_message',
				'name' => __( 'Email Body', 'edd-payout-receipts' ),
				'desc' => __( 'Enter the content for admin payout receipt notification emails. HTML is accepted. Available template tags:', 'edd-payout-receipts' ) . '<br />' . self::display_admin_email_template_tags(),
				'type' => 'rich_editor',
				'std'  => self::get_admin_email_default_body()
			),
		);

		if ( version_compare( EDD_VERSION, 2.5, '>=' ) ) {
			$email_settings = array( 'payout_receipts' => $email_settings );
		}

		return array_merge( $settings, $email_settings );

	}


	/**
	 * Retrieve default email body
	 *
	 * @since       1.0.0
	 * @return      string $body The default email
	 */
	public function get_email_default_body() {
		$from_name = edd_get_option( 'from_name', get_bloginfo( 'name' ) );
		$message   = __( 'Hello {name},', 'edd-payout-receipts' ) . "\n\n" . sprintf( __( 'You have received a payout of {amount} for the period of {start_date} - {end_date} from %s!', 'edd-payout-receipts' ), stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) ) . "\n\n";
		$message   .= __( 'Items sold: ', 'edd-payout-receipts' ) . "{commissions}\n\n";
		$message   .= __( 'Thank you', 'edd-payout-receipts' );

		return apply_filters( 'edd_payout_receipts_get_email_default_body', $message );
	}


	/**
	 * Retrieve default email body
	 *
	 * @since       1.0.0
	 * @return      string $body The default email
	 */
	public function get_admin_email_default_body() {
		$from_name = edd_get_option( 'from_name', get_bloginfo( 'name' ) );
		$message   = __( 'Hello,', 'edd-payout-receipts' ) . "\n\n" . sprintf( __( 'You have sent payout of {amount} for the period of {start_date} - {end_date} from %s!', 'edd-payout-receipts' ), stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) ) . "\n\n";
		$message   .= __( 'Recipients: ', 'edd-payout-receipts' ) . "{recipients}\n\n";
		$message   .= __( 'Thank you', 'edd-payout-receipts' );

		return apply_filters( 'edd_payout_receipts_get_admin_email_default_body', $message );
	}


	/**
	 * Parse template tags for display
	 *
	 * @since       1.0.0
	 * @return      string $tags The parsed template tags
	 */
	public function display_email_template_tags() {
		$template_tags = self::get_email_template_tags();

		$tags = '';

		foreach ( $template_tags as $template_tag ) {
			$tags .= '{' . $template_tag['tag'] . '} - ' . $template_tag['description'] . '<br />';
		}

		return $tags;
	}


	/**
	 * Parse template tags for display
	 *
	 * @since       1.0.0
	 * @return      string $tags The parsed template tags
	 */
	public function display_admin_email_template_tags() {
		$template_tags = self::get_admin_email_template_tags();

		$tags = '';

		foreach ( $template_tags as $template_tag ) {
			$tags .= '{' . $template_tag['tag'] . '} - ' . $template_tag['description'] . '<br />';
		}

		return $tags;
	}


	/**
	 * Retrieve email template tags
	 *
	 * @since       1.0.0
	 * @return      array $tags The email template tags
	 */
	public function get_email_template_tags() {
		$tags = array(
			array(
				'tag'         => 'commissions',
				'description' => sprintf( __( 'A list of purchased %s with associated commission amounts and rates', 'edd-payout-receipts' ), edd_get_label_plural() ),
			),
			array(
				'tag'         => 'amount',
				'description' => sprintf( __( 'The total earnings for the purchased %s', 'edd-payout-receipts' ), edd_get_label_plural() ),
			),
			array(
				'tag'         => 'date',
				'description' => __( 'The sent date of the payout receipt', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'user_id',
				'description' => __( 'The user id of the user', 'edd-payout-receipts' ),
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
				'tag'         => 'username',
				'description' => __( 'The user name of the user', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'address',
				'description' => __( 'The address of the user', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'subtotal',
				'description' => sprintf( __( 'The subtotal of the %s sold', 'edd-payout-receipts' ), edd_get_label_plural() ),
			),
			array(
				'tag'         => 'price',
				'description' => sprintf( __( 'The total value of the %s sold', 'edd-payout-receipts' ), edd_get_label_plural() ),
			),
			array(
				'tag'         => 'tax',
				'description' => sprintf( __( 'The amount of tax calculated for the purchased %s', 'edd-payout-receipts' ), edd_get_label_plural() ),
			),
			array(
				'tag'         => 'store_commission',
				'description' => __( 'The total store commission accured for this date range', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'sitename',
				'description' => __( 'Your site name', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'start_date',
				'description' => __( 'The date range start date', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'end_date',
				'description' => __( 'The date range end date', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'payout_email',
				'description' => __( 'The user PayPal email address', 'edd-payout-receipts' ),
			),
		);

		return apply_filters( 'edd_payout_receipts_get_email_template_tags', $tags );
	}


	/**
	 * Retrieve email template tags
	 *
	 * @since       1.0.0
	 * @return      array $tags The email template tags
	 */
	public function get_admin_email_template_tags() {
		$tags = array(
			array(
				'tag'         => 'recipients',
				'description' => __( 'A list of commission recipient names with associated amounts', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'amount',
				'description' => __( 'The total earnings accrued for the commission recipients', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'date',
				'description' => __( 'The sent date of the payout report', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'subtotal',
				'description' => __( 'The subtotal of the items sold', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'price',
				'description' => __( 'The total value of the items sold', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'tax',
				'description' => sprintf( __( 'The amount of tax calculated for the purchased %s', 'edd-payout-receipts' ), edd_get_label_plural() ),
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
				'tag'         => 'start_date',
				'description' => __( 'The date range start date', 'edd-payout-receipts' ),
			),
			array(
				'tag'         => 'end_date',
				'description' => __( 'The date range end date', 'edd-payout-receipts' ),
			),
		);

		return apply_filters( 'edd_payout_receipts_get_admin_email_template_tags', $tags );
	}


	/**
	 * Send Email
	 *
	 * @since       1.0.0
	 * @return      void
	 */
	public function send_email( $user_id = 0, $args = array(), $minimum = 0, $start_date, $end_date ) {
		if ( empty( $args ) ) {
			return false;
		}

		// Make sure we have the user_id
		if ( ! empty( $user_id ) ) {
			$args['user_id'] = $user_id;
		} else {
			return false;
		}

		// Bail early if payout receipts are globally disabled
		if ( (bool) edd_get_option( 'edd_payout_receipts_disable_payout_receipts', false ) ) {
			return;
		}

		$commissions = edd_payout_receipts_get_commissions_grouped( $args, 'download_id' );

		if ( $commissions ) {

			// Since commissions are grouped, we use the payment completed date as the notification date
			$date     = date_i18n( get_option( 'date_format' ), strtotime( 'now' ) );
			$sitename = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

			$user  = get_userdata( $user_id );
			$email = $user->user_email; // set address here

			$custom_paypal = get_user_meta( $user_id, 'eddc_user_paypal', true );
			$payout_email  = is_email( $custom_paypal ) ? $custom_paypal : $user->user_email;

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

			// Get the buyer address from the payment
			$address = edd_payout_receipts_get_user_address( $user_id );

			// Format start and end dates
			$start_date = date_i18n( get_option( 'date_format' ), strtotime( implode( '/', $start_date ) ) );
			$end_date   = date_i18n( get_option( 'date_format' ), strtotime( implode( '/', $end_date ) ) );

			// Get email subject and message
			$subject = edd_get_option( 'edd_payout_receipts_payout_email_subject', __( 'Payout Receipt', 'edd-payout-receipts' ) );
			$message = edd_get_option( 'edd_payout_receipts_payout_email_message', self::get_email_default_body() );

			// Build {commissions} email tag
			$commissions_list = '<ul>';
			foreach ( $commissions as $key => $commission ) {

				$show_amounts  = apply_filters( 'edd_payout_receipts_payout_email_show_names', true );
				$show_currency = apply_filters( 'edd_payout_receipts_payout_email_show_currency', true );
				$show_sales    = apply_filters( 'edd_payout_receipts_payout_email_show_sales', true );

				$amount           += $commission['amount'];
				$item_price       += $commission['item_price'];
				$subtotal         += $commission['subtotal'];
				$tax              += $commission['tax'];
				$price            += $commission['price'];
				$discount         += $commission['discount'];
				$store_commission += $commission['store_commission'];

				$parts    = edd_payout_receipts_parse_download_id_parts( $key );
				$download = edd_get_download( $parts['download_id'] );

				$item_purchased = '<strong>' . $download->get_name() . '</strong>';
				if ( $download->has_variable_prices() ) {
					$prices = $download->get_prices();
					if ( isset( $prices[ $parts['price_id'] ] ) ) {
						$item_purchased .= ' - ' . $prices[ $parts['price_id'] ]['name'];
					}
				}

				if ( $show_amounts ) {
					$item_purchased .= ' - ' . html_entity_decode( edd_currency_filter( edd_format_amount( $commission['amount'] ), $parts['currency'] ) );

					if ( $show_currency ) {
						$item_purchased .= ' ' . $parts['currency'];
					}

					if ( $show_sales ) {
						$item_purchased .= ' (' . count( $commission['commissions'] ) . ' ' . __( 'Sales', 'edd-payout-receipts' ) . ')';
					}
				}

				$commissions_list .= '<li>' . apply_filters( 'edd_payout_receipts_email_payout_commissions_list_title', $item_purchased, $commission ) . '</li>';
			}
			$commissions_list .= '</ul>';

			// Skip if minimum threshold not met
			if ( $minimum > $amount ) {
				return;
			}

			$amount           = html_entity_decode( edd_currency_filter( edd_format_amount( $amount ) ) );
			$item_price       = html_entity_decode( edd_currency_filter( edd_format_amount( $item_price ) ) );
			$subtotal         = html_entity_decode( edd_currency_filter( edd_format_amount( $subtotal ) ) );
			$price            = html_entity_decode( edd_currency_filter( edd_format_amount( $price ) ) );
			$discount         = html_entity_decode( edd_currency_filter( edd_format_amount( $discount ) ) );
			$tax              = html_entity_decode( edd_currency_filter( edd_format_amount( $tax ) ) );
			$store_commission = html_entity_decode( edd_currency_filter( edd_format_amount( $store_commission ) ) );

			$message = str_replace( '{commissions}', $commissions_list, $message );
			$message = str_replace( '{amount}', $amount, $message );
			$message = str_replace( '{date}', $date, $message );
			$message = str_replace( '{name}', $name, $message );
			$message = str_replace( '{address}', $address, $message );
			$message = str_replace( '{user_id}', $user->id, $message );
			$message = str_replace( '{username}', $user->user_login, $message );
			$message = str_replace( '{fullname}', $fullname, $message );
			$message = str_replace( '{subtotal}', $subtotal, $message );
			$message = str_replace( '{item_price}', $item_price, $message );
			$message = str_replace( '{tax}', $tax, $message );
			$message = str_replace( '{price}', $price, $message );
			$message = str_replace( '{discount}', $discount, $message );
			$message = str_replace( '{sitename}', $sitename, $message );
			$message = str_replace( '{store_commission}', $store_commission, $message );
			$message = str_replace( '{start_date}', $start_date, $message );
			$message = str_replace( '{end_date}', $end_date, $message );
			$message = str_replace( '{payout_email}', $payout_email, $message );

			// Make message content filterable
			$message = apply_filters( 'edd_payout_receipts_payout_email_message', $message, $user_id, $args, $commissions );

			if ( class_exists( 'EDD_Emails' ) ) {
				EDD()->emails->__set( 'heading', $subject );
				EDD()->emails->send( $email, $subject, $message );
			} else {
				$from_name = apply_filters( 'edd_payout_receipts_payout_email_from_name', $from_name, $email, $subject, $message, $user_id, $args, $commissions );

				$from_email = edd_get_option( 'from_email', get_option( 'admin_email' ) );
				$from_email = apply_filters( 'edd_payout_receipts_payout_email_from_email', $from_email, $from_name, $email, $subject, $message, $user_id, $args, $commissions );

				$headers = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";

				wp_mail( $email, $subject, $message, $headers );
			}

		}

	}


	/**
	 * Retrieve email template tags
	 *
	 * @since       1.0.0
	 * @return      array $tags The email template tags
	 */
	public function send_admin_email( $args = array(), $minimum = 0, $start_date = '', $end_date = '' ) {
		if ( empty( $args ) ) {
			return;
		}

		// Bail early if payout receipts are globally disabled
		if ( (bool) edd_get_option( 'edd_payout_receipts_admin_disable_payout_report', false ) ) {
			return;
		}

		$commissions = edd_payout_receipts_get_commissions_grouped( $args, 'user_id' );

		if ( $commissions ) {

			// Since commissions are grouped, we use the payment completed date as the notification date
			$date     = date_i18n( get_option( 'date_format' ), strtotime( 'now' ) );
			$sitename = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

			// Format start and end dates
			$start_date = date_i18n( get_option( 'date_format' ), strtotime( implode( '/', $start_date ) ) );
			$end_date   = date_i18n( get_option( 'date_format' ), strtotime( implode( '/', $end_date ) ) );

			// Build {commissions} email tag
			$recipients_list = '<ul>';
			foreach ( $commissions as $key => $commission ) {

				$user_id = edd_payout_receipts_parse_user_id_parts( $key )['user_id'];

				// Verify minimum payment threshold
				if ( $minimum > $commission['amount'] ) {
					continue;
				}

				$show_currency     = apply_filters( 'edd_payout_receipts_admin_email_show_currency', true );
				$show_sales        = apply_filters( 'edd_payout_receipts_admin_email_show_sales', true );
				$show_payout_email = apply_filters( 'edd_payout_receipts_admin_email_show_payout_email', false );

				// Get email subject and message
				$subject = edd_get_option( 'edd_payout_receipts_admin_email_subject', __( 'Payout Report', 'edd-payout-receipts' ) );
				$message = edd_get_option( 'edd_payout_receipts_admin_email_message', self::get_admin_email_default_body() );

				$user  = get_userdata( $user_id );
				$email = $user->user_email; // set address here

				$custom_paypal = get_user_meta( $user_id, 'eddc_user_paypal', true );
				$payout_email  = is_email( $custom_paypal ) ? $custom_paypal : $user->user_email;

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

				$amount           += $commission['amount'];
				$item_price       += $commission['item_price'];
				$subtotal         += $commission['subtotal'];
				$tax              += $commission['tax'];
				$price            += $commission['price'];
				$discount         += $commission['discount'];
				$store_commission += $commission['store_commission'];

				$item_purchased = '<strong>' . $fullname . '</strong>';

				if ( $show_payout_email ) {
					$item_purchased .= ' (' . $payout_email . ')';
				}

				$item_purchased .= ' - ' . html_entity_decode( edd_currency_filter( edd_format_amount( $commission['amount'] ) ) );

				if ( $show_currency ) {
					$item_purchased .= ' ' . $commission['currency'];
				}

				if ( $show_sales ) {
					$item_purchased .= ' (' . count( $commission['commissions'] ) . ' ' . __( 'Sales', 'edd-payout-receipts' ) . ')';
				}

				$recipients_list .= '<li>' . apply_filters( 'edd_payout_receipts_admin_email_recipients_list_title', $item_purchased, $commission ) . '</li>';
			}
			$recipients_list .= '</ul>';

			$amount           = html_entity_decode( edd_currency_filter( edd_format_amount( $amount ) ) );
			$item_price       = html_entity_decode( edd_currency_filter( edd_format_amount( $item_price ) ) );
			$subtotal         = html_entity_decode( edd_currency_filter( edd_format_amount( $subtotal ) ) );
			$price            = html_entity_decode( edd_currency_filter( edd_format_amount( $price ) ) );
			$discount         = html_entity_decode( edd_currency_filter( edd_format_amount( $discount ) ) );
			$tax              = html_entity_decode( edd_currency_filter( edd_format_amount( $tax ) ) );
			$store_commission = html_entity_decode( edd_currency_filter( edd_format_amount( $store_commission ) ) );

			$message = str_replace( '{recipients}', $recipients_list, $message );
			$message = str_replace( '{amount}', $amount, $message );
			$message = str_replace( '{date}', $date, $message );
			$message = str_replace( '{subtotal}', $subtotal, $message );
			$message = str_replace( '{price}', $item_price, $message );
			$message = str_replace( '{tax}', $tax, $message );
			$message = str_replace( '{sitename}', $sitename, $message );
			$message = str_replace( '{store_commission}', $store_commission, $message );
			$message = str_replace( '{start_date}', $start_date, $message );
			$message = str_replace( '{end_date}', $end_date, $message );

			// Make message content filterable
			$message = apply_filters( 'edd_payout_receipts_admin_email_message', $message, $args, $commissions );

			if ( class_exists( 'EDD_Emails' ) ) {
				EDD()->emails->__set( 'heading', $subject );
				EDD()->emails->send( $email, $subject, $message );
			} else {
				$from_name = apply_filters( 'edd_payout_receipts_admin_email_from_name', $from_name, $email, $subject, $message, $args, $commissions );

				$from_email = edd_get_option( 'from_email', get_option( 'admin_email' ) );
				$from_email = apply_filters( 'edd_payout_receipts_admin_email_from_email', $from_email, $from_name, $email, $subject, $message, $args, $commissions );

				$headers = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";

				wp_mail( $email, $subject, $message, $headers );
			}

		}

	}

}


/**
 * The main function responsible for returning the one true EDD_Payout_Receipts_Payout_Notifications instance to
 * functions everywhere.
 *
 * Use this function like you would a global variable, except without needing to declare the global.
 *
 * Example: <?php $themedd_search = EDD_Payout_Receipts_Payout_Notifications(); ?>
 *
 * @since 1.0.0
 * @return object The one true EDD_Payout_Receipts_Payout_Notifications Instance.
 */
function EDD_Payout_Receipts_Payout_Notifications() {
	return EDD_Payout_Receipts_Payout_Notifications::instance();
}

EDD_Payout_Receipts_Payout_Notifications();
