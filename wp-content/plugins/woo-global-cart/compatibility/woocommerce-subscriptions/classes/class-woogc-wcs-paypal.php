<?php
/**
 * PayPal Subscription Class.
 *
 * Filters necessary functions in the WC_Paypal class to allow for subscriptions, either via PayPal Standard (default)
 * or PayPal Express Checkout using Reference Transactions (preferred)
 *
 * @package		WooCommerce Subscriptions
 * @subpackage	Gateways/PayPal
 * @category	Class
 * @author		Prospress
 * @since		2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
 

class WooGC_WCS_PayPal extends WCS_PayPal {
  
    /**
     * Main PayPal Instance, ensures only one instance is/can be loaded
     *
     * @see wc_paypal_express()
     * @return WC_PayPal_Express
     * @since 2.0
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public static function init() {

        self::$paypal_settings = self::get_options();
        
        self::get_api();

        // wc-api handler for express checkout transactions
        if ( ! has_action( 'woocommerce_api_wcs_paypal' ) ) {
            add_action( 'woocommerce_api_wcs_paypal', __CLASS__ . '::handle_wc_api' );
        }

        // When necessary, set the PayPal args to be for a subscription instead of shopping cart
        add_action( 'woocommerce_update_options_payment_gateways_paypal', __CLASS__ . '::reload_options', 100 );

        // When necessary, set the PayPal args to be for a subscription instead of shopping cart
        add_action( 'woocommerce_update_options_payment_gateways_paypal', __CLASS__ . '::are_reference_transactions_enabled', 100 );

        // When necessary, set the PayPal args to be for a subscription instead of shopping cart
        add_filter( 'woocommerce_paypal_args', __CLASS__ . '::get_paypal_args', 10, 2 );

        // Check a valid PayPal IPN request to see if it's a subscription *before* WCS_Gateway_Paypal::successful_request()
        add_action( 'valid-paypal-standard-ipn-request', __CLASS__ . '::process_ipn_request', 0 );

        add_action( 'woocommerce_scheduled_subscription_payment_paypal', __CLASS__ . '::process_subscription_payment', 10, 2 );

        // Don't copy over PayPal details to Resubscribe Orders
        add_filter( 'wcs_resubscribe_order_created', __CLASS__ . '::remove_resubscribe_order_meta', 10, 2 );

        // Triggered by WCS_SV_API_Base::broadcast_request() whenever an API request is made
        add_action( 'wc_paypal_api_request_performed', __CLASS__ . '::log_api_requests', 10, 2 );

        add_filter( 'woocommerce_subscriptions_admin_meta_boxes_script_parameters', __CLASS__ . '::maybe_add_change_payment_method_warning' );

        // Run the IPN failure handler attach and detach functions before and after processing to catch and log any unexpected shutdowns
        add_action( 'valid-paypal-standard-ipn-request', 'WCS_PayPal_Standard_IPN_Failure_Handler::attach', -1, 1 );
        add_action( 'valid-paypal-standard-ipn-request', 'WCS_PayPal_Standard_IPN_Failure_Handler::detach', 1, 1 );

        WCS_PayPal_Supports::init();
        WCS_PayPal_Status_Manager::init();
        WCS_PayPal_Standard_Switcher::init();

        if ( is_admin() ) {
            WCS_PayPal_Admin::init();
            WCS_PayPal_Change_Payment_Method_Admin::init();
        }
    }
    
    
    
    /**
     * Override the default PayPal standard args in WooCommerce for subscription purchases when
     * automatic payments are enabled and when the recurring order totals is over $0.00 (because
     * PayPal doesn't support subscriptions with a $0 recurring total, we need to circumvent it and
     * manage it entirely ourselves.)
     *
     * @since 2.0
     */
    public static function get_paypal_args( $paypal_args, $order ) {

        if ( wcs_order_contains_subscription( $order, array( 'parent', 'renewal', 'resubscribe', 'switch' ) ) || wcs_is_subscription( $order ) ) {
            if ( self::are_reference_transactions_enabled() ) {
                $paypal_args = self::get_api()->get_paypal_args( $paypal_args, $order );
            } else {
                $paypal_args = WCS_PayPal_Standard_Request::get_paypal_args( $paypal_args, $order );
            }
        }

        return $paypal_args;
    }
    
    
         
	/**
	 * Get the API object
	 *
	 * @return WCS_PayPal_Express_API API instance
	 * @since 2.0
	 */
	public static function get_api() {

		if ( is_object( self::$api ) ) {
			return self::$api;
		}

		if ( ! class_exists( 'WC_Gateway_Paypal_Response' ) ) {
			require_once( WC()->plugin_path() . '/includes/gateways/paypal/includes/class-wc-gateway-paypal-response.php' );
		}

		$classes = array(
			'api',
			//'api-request',
			'api-response',
			'api-response-checkout',
			'api-response-billing-agreement',
			'api-response-payment',
			'api-response-recurring-payment',
		);

		foreach ( $classes as $class ) {
			require_once( WP_PLUGIN_DIR . "/woocommerce-subscriptions/includes/gateways/paypal/includes/class-wcs-paypal-reference-transaction-{$class}.php" );
		}

		$environment = ( 'yes' === self::get_option( 'testmode' ) ) ? 'sandbox' : 'production';

        include_once ( WOOGC_PATH . '/compatibility/woocommerce-subscriptions/classes/class-woogc-wcs-paypal-reference-transaction-api.php');
        include_once ( WOOGC_PATH . '/compatibility/woocommerce-subscriptions/classes/class-woogc-wcs-paypal-reference-transaction-api-request.php');
        
		return self::$api = new WooGC_WCS_PayPal_Reference_Transaction_API( 'paypal', $environment, self::get_option( 'api_username' ), self::get_option( 'api_password' ), self::get_option( 'api_signature' ) );
	}
  

}
