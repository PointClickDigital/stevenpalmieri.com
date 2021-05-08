<?php
/**
 * WooCommerce NMI_Gateway_Woocommerce Gateway
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@woocommerce.com so we can send you a copy immediately.
 *
 *
 * @package   NMI_Gateway_Woocommerce/Gateway/API/Requests/Customer
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */
defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginFramework\v5_2_1 as NMI_Gateway_Woocommerce_Framework;


/**
 * NMI_Gateway_Woocommerce API Customer Request class
 *
 * Handles creating customers and retrieving their payment methods
 *
 * @since 1.0.0
 */
class NMI_Gateway_Woocommerce_API_Customer_Request extends NMI_Gateway_Woocommerce_API_Vault_Request {

	public $processor_type;
	public $environment;

	public function __construct( WC_Order $order = null, $processor_type, $environment ) {
		$this->processor_type = $processor_type;
		$this->environment    = $environment;
		parent::__construct( $order );
	}

	/**
	 * Create a new customer and associate payment method
	 *
	 * @param WC_Order $order
	 *
	 * @throws NMI_Gateway_Woocommerce_Framework\SV_WC_Payment_Gateway_Exception
	 */
	public function create_customer( WC_Order $order ) {
		$this->set_resource( $this->processor_type );
		$this->set_callback( $this->processor_type );

		$this->order        = $order;
		$customer_name      = sprintf( __( 'Creating a Customer: %1$s %2$s', 'woofunnels-woocommerce-nmi-gateway' ), NMI_Gateway_Woocommerce_Framework\SV_WC_Order_Compatibility::get_prop( $order, 'billing_first_name' ), NMI_Gateway_Woocommerce_Framework\SV_WC_Order_Compatibility::get_prop( $order, 'billing_last_name' ) );
		$this->request_data = array(
			'orderid'           => $order->get_id(),
			'order_description' => $customer_name,
			'type'              => $this->processor_type,
		);

		if ( 'auth' === $this->processor_type ) {
			$auth_amount                  = ( 'sandbox' === $this->environment ) ? '1' : '0.01';
			$this->request_data['amount'] = ( $order->get_total() > 0 ) ? $order->get_total() : $auth_amount;
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
	 *
	 * @since 1.0.0
	 */
	protected function set_customer() {

		// set customer info
		// a customer will only be created if tokenization is required and
		// storeInVaultOnSuccess is set to true, see get_options() below
		$this->request_data['customer'] = array(
			'phone' => $this->get_order_prop( 'billing_phone' ),
			'email' => $this->get_order_prop( 'billing_email' ),

		);

	}

	/**
	 * Get the billing address for the transaction
	 * @since 1.0.0
	 */
	protected function set_billing() {
		// otherwise just set the billing address directly
		$order                         = $this->order;
		$state                         = NMI_Gateway_Woocommerce_Framework\SV_WC_Order_Compatibility::get_prop( $order, 'billing_state' );
		$this->request_data['billing'] = array(
			'firstName' => NMI_Gateway_Woocommerce_Framework\SV_WC_Order_Compatibility::get_prop( $order, 'billing_first_name' ),
			'lastName'  => NMI_Gateway_Woocommerce_Framework\SV_WC_Order_Compatibility::get_prop( $order, 'billing_last_name' ),
			'company'   => NMI_Gateway_Woocommerce_Framework\SV_WC_Order_Compatibility::get_prop( $order, 'billing_company' ),
			'address1'  => NMI_Gateway_Woocommerce_Framework\SV_WC_Order_Compatibility::get_prop( $order, 'billing_address_1' ),
			'address2'  => NMI_Gateway_Woocommerce_Framework\SV_WC_Order_Compatibility::get_prop( $order, 'billing_address_2' ),
			'city'      => NMI_Gateway_Woocommerce_Framework\SV_WC_Order_Compatibility::get_prop( $order, 'billing_city' ),
			'state'     => empty( $state ) ? 'NA' : $state,
			'country'   => NMI_Gateway_Woocommerce_Framework\SV_WC_Order_Compatibility::get_prop( $order, 'billing_country' ),
			'zip'       => NMI_Gateway_Woocommerce_Framework\SV_WC_Order_Compatibility::get_prop( $order, 'billing_postcode' ),
		);
	}

	/**
	 * Set the payment method for a create_customer transaction
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
			$nmi_csc = isset( $this->get_order()->payment->csc ) ? $this->get_order()->payment->csc : '';
			$nmi_csc = ( empty( $nmi_csc ) && isset( $_POST[ 'wc-' . str_replace( '_', '-', NMI_Gateway_Woocommerce::CREDIT_CARD_GATEWAY_ID ) . '-csc' ] ) ) ? $_POST[ 'wc-' . str_replace( '_', '-', NMI_Gateway_Woocommerce::CREDIT_CARD_GATEWAY_ID ) . '-csc' ] : $nmi_csc;

			$expiry = isset( $_POST[ 'wc-' . str_replace( '_', '-', NMI_Gateway_Woocommerce::CREDIT_CARD_GATEWAY_ID ) . '-expiry' ] ) ? explode( ' / ', $_POST[ 'wc-' . str_replace( '_', '-', NMI_Gateway_Woocommerce::CREDIT_CARD_GATEWAY_ID ) . '-expiry' ] ) : '';

			$exp_month = isset( $this->get_order()->payment->exp_month ) ? $this->get_order()->payment->exp_month : '';
			$exp_month = ( empty( $exp_month ) && isset( $expiry[0] ) ) ? $expiry[0] : $exp_month;
			if ( empty( $exp_month ) || '00' === $exp_month ) {
				$message = __( 'The card expiration month is invalid, please re-enter and try again. Error in function: ' . __FUNCTION__ . ' on line: ' . __LINE__, 'woofunnels-woocommerce-nmi-gateway' );
				throw new NMI_Gateway_Woocommerce_Framework\SV_WC_Payment_Gateway_Exception( $message );
			}
			$exp_year = isset( $this->get_order()->payment->exp_year ) ? $this->get_order()->payment->exp_year : '';
			$exp_year = ( ! empty( $exp_year ) && isset( $expiry[1] ) ) ? $expiry[1] : $exp_year;

			if ( empty( $exp_year ) || ! $exp_year || ( strlen( $exp_year ) !== 2 && strlen( $exp_year ) !== 4 ) ) {
				$message = __( 'Please enter a valid card expiry year to proceed', 'woofunnels-woocommerce-nmi-gateway' );
				throw new NMI_Gateway_Woocommerce_Framework\SV_WC_Payment_Gateway_Exception( $message );
			}

			$ex_year = date_create_from_format( 'Y', $exp_year )->format( 'y' );
			if ( date( 'y' ) > $ex_year ) {
				$message = __( 'The card expiration year is invalid, please re-enter and try again.', 'woofunnels-woocommerce-nmi-gateway' );
				throw new NMI_Gateway_Woocommerce_Framework\SV_WC_Payment_Gateway_Exception( $message );
			}
			$acc_no                                    = isset( $this->get_order()->payment->account_number ) ? $this->get_order()->payment->account_number : '';
			$acc_no                                    = ( empty( $acc_no ) && isset( $_POST[ 'wc-' . str_replace( '_', '-', NMI_Gateway_Woocommerce::CREDIT_CARD_GATEWAY_ID ) . '-account-number' ] ) ) ? $_POST[ 'wc-' . str_replace( '_', '-', NMI_Gateway_Woocommerce::CREDIT_CARD_GATEWAY_ID ) . '-account-number' ] : $acc_no;
			$this->request_data['payment']['ccnumber'] = $acc_no;
			$this->request_data['payment']['ccexp']    = $exp_month . $ex_year;
			$this->request_data['payment']['cvv']      = $nmi_csc;
		}
	}

	/**
	 * Get the payment methods for a given customer
	 *
	 *
	 * @param string $customer_id NMI_Gateway_Woocommerce customer ID
	 *
	 * @since 1.0.0
	 *
	 */
	public function get_payment_methods( $customer_id ) {

		$this->request_data = $customer_id;
	}

}
