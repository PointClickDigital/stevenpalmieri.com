<?php
/**
 * NMI_Gateway_Woocommerce
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@woocommerce.com so we can send you a copy immediately.
 *
 * @package   NMI_Gateway_Woocommerce/Gateway/API/Requests/Payment-Method
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginFramework\v5_2_1 as NMI_Gateway_Woocommerce_Framework;

/**
 * NMI_Gateway_Woocommerce API Payment Method Request class
 *
 * Handles creating, updating, and deleting individual payment methods
 *
 * @since 1.0.0
 */
class NMI_Gateway_Woocommerce_API_Payment_Method_Request extends NMI_Gateway_Woocommerce_API_Vault_Request {

	public $processor_type;
	public $environment;

	public function __construct( WC_Order $order = null, $processor_type, $environment ) {
		$this->processor_type = $processor_type;
		$this->environment    = $environment;

		parent::__construct( $order );
	}

	/**
	 * @param WC_Order $order
	 *
	 * @throws NMI_Gateway_Woocommerce_Framework\SV_WC_Payment_Gateway_Exception
	 */
	public function create_payment_method( WC_Order $order ) {

		$this->set_resource( $this->processor_type );
		$this->set_callback( $this->processor_type );

		$this->order        = $order;
		$description        = sprintf( __( ' New Payment Method from my-account screen to customer: %s', 'woofunnels-woocommerce-nmi-gateway' ), get_current_user_id() );
		$this->request_data = array(
			'orderid'           => '',
			'order_description' => $description,
			'type'              => $this->processor_type,
		);

		if ( 'auth' === $this->processor_type ) {
			$this->request_data['amount'] = ( 'sandbox' === $this->environment ) ? '1.0' : '0.01';
		}

		// set customer data
		$this->set_customer();

		// set billing data
		$this->set_billing();

		// set payment method, either existing token or nonce
		$this->set_payment_method();

	}


	/**
	 * Set the customer data for the transaction
	 * @since 1.0.0
	 */
	protected function set_customer() {

		// set customer info
		// a customer will only be created if tokenization is required and
		// storeInVaultOnSuccess is set to true, see get_options() below
		$this->request_data['customer'] = array(
			'phone' => $this->get_order_prop( 'billing_phone' ),
			'email' => $this->get_user_detail( 'billing_email' ),
		);
	}

	/**
	 * Set the payment method for the transaction, either a previously saved payment
	 * method (token) or a new payment method
	 * @throws NMI_Gateway_Woocommerce_Framework\SV_WC_Payment_Gateway_Exception
	 */
	protected function set_payment_method() {
		$xl_nmi_js_token = isset( $_POST['xl_wc_nmi_js_token'] ) ? $_POST['xl_wc_nmi_js_token'] : '';
		$wc_pre_30       = version_compare( WC_VERSION, '3.0.0', '<' );

		$js_response = isset( $_POST['xl_wc_nmi_js_response'] ) ? json_decode( stripslashes( $_POST['xl_wc_nmi_js_response'] ), true ) : [];
		$card_data   = ( isset( $js_response['card'] ) && is_array( $js_response['card'] ) ) ? $js_response['card'] : [];

		$account_number = isset( $card_data['number'] ) ? $card_data['number'] : '';
		$last_four      = isset( $card_data['number'] ) ? substr( $card_data['number'], '-4' ) : '';
		$card_type      = isset( $card_data['type'] ) ? $card_data['type'] : '';
		$exp_month      = isset( $card_data['exp'] ) ? substr( $card_data['exp'], 0, 2 ) : '00';
		$exp_year       = isset( $card_data['exp'] ) ? substr( $card_data['exp'], 2, 2 ) : '00';

		$this->get_order()->payment->account_number = ( isset( $this->get_order()->payment->account_number ) && ! empty( $this->get_order()->payment->account_number ) ) ? $this->get_order()->payment->account_number : $account_number;
		$this->get_order()->payment->last_four      = ( isset( $this->get_order()->payment->last_four ) && ! empty( $this->get_order()->payment->last_four ) ) ? $this->get_order()->payment->last_four : $last_four;
		$this->get_order()->payment->card_type      = ( isset( $this->get_order()->payment->card_type ) && ! empty( $this->get_order()->payment->card_type ) ) ? $this->get_order()->payment->card_type : $card_type;
		$this->get_order()->payment->exp_month      = ( isset( $this->get_order()->payment->exp_month ) && ! empty( $this->get_order()->payment->exp_month ) ) ? $this->get_order()->payment->exp_month : $exp_month;
		$this->get_order()->payment->exp_year       = ( isset( $this->get_order()->payment->exp_year ) && ! empty( $this->get_order()->payment->exp_year ) ) ? $this->get_order()->payment->exp_year : $exp_year;

		$this->request_data['payment'] = array(
			'customer_vault_id' => '',
			'customer_vault'    => 'add_customer',
			'currency'          => $wc_pre_30 ? $this->get_order_prop( 'order_currency' ) : $this->get_order_prop( 'currency' ),
		);

		if ( ! empty( $xl_nmi_js_token ) ) {
			$this->request_data['payment']['payment_token'] = $xl_nmi_js_token;
		} else {
			if ( empty( $_POST[ 'wc-' . str_replace( '_', '-', NMI_Gateway_Woocommerce::CREDIT_CARD_GATEWAY_ID ) . '-account-number' ] ) || empty( $_POST[ 'wc-' . str_replace( '_', '-', NMI_Gateway_Woocommerce::CREDIT_CARD_GATEWAY_ID ) . '-expiry' ] ) || empty( $_POST[ 'wc-' . str_replace( '_', '-', NMI_Gateway_Woocommerce::CREDIT_CARD_GATEWAY_ID ) . '-csc' ] ) ) {
				$message = __( 'Please enter all credit card details.', 'woofunnels-woocommerce-nmi-gateway' );
				NMI_Gateway_Woocommerce_Logger::log( "$message Posted data: " . print_r( $_POST, true ) );
				throw new NMI_Gateway_Woocommerce_Framework\SV_WC_Payment_Gateway_Exception( $message );
			}

			$expiry = explode( ' / ', $_POST[ 'wc-' . str_replace( '_', '-', NMI_Gateway_Woocommerce::CREDIT_CARD_GATEWAY_ID ) . '-expiry' ] );

			$exp_year = isset( $expiry[1] ) ? $expiry[1] : '';

			if ( empty( $exp_year ) || ! $exp_year || ( strlen( $exp_year ) !== 2 && strlen( $exp_year ) !== 4 ) ) {
				$message = __( 'Please enter a valid card expiry year to proceed', 'woofunnels-woocommerce-nmi-gateway' );
				throw new NMI_Gateway_Woocommerce_Framework\SV_WC_Payment_Gateway_Exception( $message );
			}

			$ex_year = date_create_from_format( 'Y', $expiry[1] )->format( 'y' );

			if ( empty( $expiry ) || ! isset( $expiry['0'] ) || empty( $expiry[0] ) || '00' === $expiry[0] ) {
				$message = __( 'The card expiration month is invalid, please re-enter and try again. Error in function: ' . __FUNCTION__ . ' on line: ' . __LINE__, 'woofunnels-woocommerce-nmi-gateway' );
				throw new NMI_Gateway_Woocommerce_Framework\SV_WC_Payment_Gateway_Exception( $message );
			}
			if ( gmdate( 'y' ) > $ex_year ) {
				$message = __( 'The card expiration year is invalid, please re-enter and try again.', 'woofunnels-woocommerce-nmi-gateway' );
				throw new NMI_Gateway_Woocommerce_Framework\SV_WC_Payment_Gateway_Exception( $message );
			}
			$this->request_data['payment']['ccnumber'] = $_POST[ 'wc-' . str_replace( '_', '-', NMI_Gateway_Woocommerce::CREDIT_CARD_GATEWAY_ID ) . '-account-number' ];
			$this->request_data['payment']['ccexp']    = $expiry[0] . $ex_year;
			$this->request_data['payment']['cvv']      = $_POST[ 'wc-' . str_replace( '_', '-', NMI_Gateway_Woocommerce::CREDIT_CARD_GATEWAY_ID ) . '-csc' ];
		}
	}

	/**
	 * Get the billing address for the transaction
	 */
	protected function set_billing() {
		// otherwise just set the billing address directly
		$user_id  = get_current_user_id();
		$usermeta = get_user_meta( $user_id );

		$billing = [];

		$firstName = $this->get_first_name( $usermeta, $user_id );
		$lastName  = $this->get_last_name( $usermeta, $user_id );
		$address1  = $this->get_user_detail( 'billing_address_1' );
		$address2  = $this->get_user_detail( 'billing_address_2' );
		$company   = $this->get_user_detail( 'billing_company' );
		$city      = $this->get_user_detail( 'billing_city' );
		$state     = $this->get_user_detail( 'billing_state' );
		$zip       = $this->get_user_detail( 'billing_postcode' );
		$country   = $this->get_user_detail( 'billing_country' );

		if ( ! empty( $firstName ) ) {
			$billing['firstName'] = $firstName;
		}
		if ( ! empty( $lastName ) ) {
			$billing['lastName'] = $lastName;
		}
		if ( ! empty( $address1 ) ) {
			$billing['address1'] = $address1;
		}
		if ( ! empty( $address2 ) ) {
			$billing['address2'] = $address2;
		}
		if ( ! empty( $company ) ) {
			$billing['company'] = $company;
		}
		if ( ! empty( $city ) ) {
			$billing['city'] = $city;
		}
		if ( ! empty( $state ) ) {
			$billing['state'] = $state;
		}
		if ( ! empty( $zip ) ) {
			$billing['zip'] = $zip;
		}
		if ( ! empty( $country ) ) {
			$billing['country'] = $country;
		}
		$this->request_data['billing'] = $billing;
	}

	/**
	 * Delete a customer's payment method
	 *
	 * @param string $token NMI payment method token
	 *
	 * @since 1.0.0
	 *
	 */
	public function delete_payment_method( $token ) {

		$this->request_data = $token;
	}


	/**
	 * Verify the CSC for an existing saved payment method using the provided
	 * nonce
	 *
	 * @param string $token existing payment method token
	 * @param string $nonce nonce provided from client-side hosted fields
	 *
	 * @since 1.0.0
	 *
	 */
	public function verify_csc( $token, $nonce ) {

		$update_data = array(
			'billingAddress' => $this->get_billing_address(),
			'options'        => array(
				'verifyCard' => true,
			),
		);

		$this->request_data = array( $token, $update_data );
	}

	public function get_params() {
		// TODO: Implement get_params() method.
	}

	public function get_data() {
		// TODO: Implement get_data() method.
	}

	/**
	 * Finding billing first name
	 *
	 * @param $usermeta
	 * @param $user_id
	 *
	 * @return string
	 */
	public function get_first_name( $usermeta, $user_id ) {
		$fname = '';
		if ( isset( $usermeta['billing_first_name'] ) && is_array( $usermeta['billing_first_name'] ) ) {
			$fname = $usermeta['billing_first_name']['0'];
		} elseif ( isset( $usermeta['shipping_first_name'] ) && is_array( $usermeta['shipping_first_name'] ) ) {
			$fname = $usermeta['shipping_first_name']['0'];
		} elseif ( isset( $usermeta['first_name'] ) && is_array( $usermeta['first_name'] ) ) {
			$fname = $usermeta['first_name']['0'];
		} else {
			$fname = get_user_by( 'id', $user_id )->user_login;
		}

		return $fname;

	}

	/**
	 * Finding billing last name
	 *
	 * @param $usermeta
	 * @param $user_id
	 *
	 * @return string
	 */
	public function get_last_name( $usermeta, $user_id ) {
		$lname = '';
		if ( isset( $usermeta['billing_last_name'] ) && is_array( $usermeta['billing_last_name'] ) ) {
			$lname = $usermeta['billing_last_name']['0'];
		} elseif ( isset( $usermeta['shipping_last_name'] ) && is_array( $usermeta['shipping_last_name'] ) ) {
			$lname = $usermeta['shipping_last_name']['0'];
		} elseif ( isset( $usermeta['last_name'] ) && is_array( $usermeta['last_name'] ) ) {
			$lname = $usermeta['last_name']['0'];
		} else {
			$lname = get_user_by( 'id', $user_id )->user_login;
		}

		return $lname;

	}

	/**
	 * @param $key
	 *
	 * @return mixed|string
	 */
	public function get_user_detail( $key ) {
		$user_id  = get_current_user_id();
		$usermeta = get_user_meta( $user_id );

		if ( isset( $usermeta[ $key ] ) && is_array( $usermeta[ $key ] ) && ! empty( $usermeta[ $key ]['0'] ) ) {
			return $usermeta[ $key ]['0'];
		}
		$key = str_replace( 'billing_', 'shipping_', $key );
		if ( isset( $usermeta[ $key ] ) && is_array( $usermeta[ $key ] ) && ! empty( $usermeta[ $key ]['0'] ) ) {
			return $usermeta[ $key ]['0'];
		}

		return '';
	}
}
