<?php
/*
Plugin Name: WP NMI Gateway PCI for WooCommerce
Plugin URI: https://bitbucket.org/pledged/wc-nmi-pci-pro
Description: A PCI compliant payment gateway for NMI. An NMI account and a server with cURL, SSL support, and a valid SSL certificate is required (for security reasons) for this gateway to function. Requires WC 3.0.0+
Version: 1.0.3
Author: Pledged Plugins
Author URI: https://pledgedplugins.com
Text Domain: wc-nmi
Domain Path: /languages
WC requires at least: 3.3
WC tested up to: 4.8

	Copyright: © Pledged Plugins.
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WC_NMI_PCI_VERSION', '1.0.3' );
define( 'WC_NMI_PCI_TEMPLATE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/' );
define( 'WC_NMI_PCI_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
define( 'WC_NMI_PCI_MAIN_FILE', __FILE__ );

/**
 * Main NMI class which sets the gateway up for us
 */
class WC_NMI_PCI {

	/**
     * @var Singleton The reference the *Singleton* instance of this class
     */
    private static $instance;

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Notices (array)
     * @var array
	 */
    public $notices = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'check_environment' ), 11 );
		add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );
		add_action( 'plugins_loaded', array( $this, 'init' ), 11 );
	}

	public function settings_url() {
        return admin_url( 'admin.php?page=wc-settings&tab=checkout&section=nmi' );
    }

	/**
	 * Add relevant links to plugins page
	 * @param  array $links
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=nmi' ) . '">' . __( 'Settings', 'wc-nmi' ) . '</a>',
			'<a href="https://pledgedplugins.com/support/" target="_blank">' . __( 'Support', 'wc-nmi' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}

	/**
     * Initialize the gateway. Called very early - in the context of the plugins_loaded action
     *
     * @since 1.0.0
     */
    public function init_gateways() {

		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
            return;
        }

        // Includes
		if ( is_admin() ) {
			require_once( dirname( __FILE__ ) . '/includes/class-wc-nmi-privacy.php' );
		}

        include_once( dirname( __FILE__ ) . '/includes/class-wc-gateway-nmi.php' );

        load_plugin_textdomain( 'wc-nmi', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
        add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateways' ) );

    }

	/**
	 * Register the gateway for use
	 */
	public function add_gateways( $methods ) {
		$methods[] = 'WC_Gateway_NMI';
		return $methods;
	}

	/**
	 * Init localisations and files
	 */
	public function init() {

        if( class_exists( 'WC_NMI_License_Updates' ) ) {
            return;
        }

        // Init the gateway itself
        $this->init_gateways();

        require_once( 'includes/class-wc-gateway-nmi-logger.php' );

        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ), 11 );
		add_action( 'woocommerce_order_status_on-hold_to_processing', array( $this, 'capture_payment' ) );
		add_action( 'woocommerce_order_status_on-hold_to_completed', array( $this, 'capture_payment' ) );
		add_action( 'woocommerce_order_status_on-hold_to_cancelled', array( $this, 'cancel_payment' ) );
		add_action( 'woocommerce_order_status_on-hold_to_refunded', array( $this, 'cancel_payment' ) );

	}

	/**
     * Allow this class and other classes to add slug keyed notices (to avoid duplication)
     */
    public function add_admin_notice( $slug, $class, $message ) {
        $this->notices[ $slug ] = array(
            'class'   => $class,
            'message' => $message,
        );
    }

	/**
     * The backup sanity check, in case the plugin is activated in a weird way,
     * or the environment changes after activation. Also handles upgrade routines.
     */
    public function check_environment() {
        $environment_warning = self::get_environment_warning();

        if ( $environment_warning && is_plugin_active( plugin_basename( __FILE__ ) ) ) {
            $this->add_admin_notice( 'bad_environment', 'error', $environment_warning );
            return;
        }

        if ( ! defined( 'IFRAME_REQUEST' ) && ( WC_NMI_PCI_VERSION !== get_option( 'wc_nmi_pci_version', '3.0.0' ) ) ) {
            $this->install();

            do_action( 'woocommerce_nmi_updated' );
        }

        $gateway = new WC_Gateway_NMI();

		$setting_prompt = ( ! $gateway->api_keys && ! $gateway->username ) || ( $gateway->api_keys && ! $gateway->private_key );
        if ( $setting_prompt && ! ( isset( $_GET['page'], $_GET['section'] ) && 'wc-settings' === $_GET['page'] && 'nmi' === $_GET['section'] ) ) {
            $setting_link = $this->settings_url();
            $this->add_admin_notice( 'prompt_connect', 'notice notice-warning', sprintf( __( 'NMI is almost ready. To get started, <a href="%s">set your NMI account keys</a>.', 'wc-nmi' ), $setting_link ) );
        }
    }

	/**
     * Updates the plugin version in db
     *
     * @since 3.1.0
     * @version 3.1.0
     * @return bool
     */
    private static function _update_plugin_version() {
        delete_option( 'wc_nmi_pci_version' );
        update_option( 'wc_nmi_pci_version', WC_NMI_PCI_VERSION );

        return true;
    }

	/**
     * Handles upgrade routines.
     *
     * @since 3.1.0
     * @version 3.1.0
     */
    public function install() {
        if ( ! defined( 'WC_NMI_INSTALLING' ) ) {
            define( 'WC_NMI_INSTALLING', true );
        }
        $this->_update_plugin_version();
    }

    /**
     * Checks the environment for compatibility problems.  Returns a string with the first incompatibility
     * found or false if the environment has no problems.
     */
    static function get_environment_warning() {

        if ( ! defined( 'WC_VERSION' ) ) {
            return __( 'WooCommerce NMI extension requires WooCommerce to be activated to work.', 'wc-nmi' );
        }

        if ( ! function_exists( 'curl_init' ) ) {
            return __( 'WooCommerce NMI - cURL is not installed.', 'wc-nmi' );
        }

        if( class_exists( 'WC_NMI_License_Updates' ) ) {
            return __( 'There are two WooCommerce NMI extensions installed from Pledged Plugins which can cause conflicts. Please deactivate one of them.', 'wc-nmi' );
        }

        return false;
    }

    /**
	 * Display any notices we've collected thus far (e.g. for connection, disconnection)
	 */
	public function admin_notices() {

		foreach ( (array) $this->notices as $notice_key => $notice ) {
			echo "<div class='" . esc_attr( $notice['class'] ) . "'><p>";
			echo wp_kses( $notice['message'], array( 'a' => array( 'href' => array() ) ) );
			echo '</p></div>';
		}
	}

	/**
	 * Capture payment when the order is changed from on-hold to complete or processing
	 *
	 * @param  int $order_id
	 */
	public function capture_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		$gateway = new WC_Gateway_NMI();

		$gateway->log( "Info: Beginning capture payment for order $order_id for the amount of {$order->get_total()}" );

		if ( $order->get_payment_method() == 'nmi' ) {
			$charge   = $order->get_meta( '_nmi_charge_id' );
			$captured = $order->get_meta( '_nmi_charge_captured' );

			if ( $charge && $captured == 'no' ) {
				$args = array(
					'amount'		=> $order->get_total(),
					'transactionid'	=> $order->get_transaction_id(),
					'type' 			=> 'capture',
					'email' 		=> $order->get_billing_email(),
					'currency'		=> $gateway->get_payment_currency( $order_id ),
				);
				$args = apply_filters( 'wc_nmi_request_args', $args, $order );

				$response = $gateway->nmi_request( $args );

				if ( is_wp_error( $response ) ) {
					$order->add_order_note( __( 'Unable to capture charge!', 'wc-nmi' ) . ' ' . $response->get_error_message() );
				} else {
					$complete_message = sprintf( __( 'NMI charge complete (Charge ID: %s)', 'wc-nmi' ), $response['transactionid'] );
 					$order->add_order_note( $complete_message );
					$gateway->log( "Success: $complete_message" );
					$order->update_meta_data( '_nmi_charge_captured', 'yes' );
					$order->update_meta_data( 'NMI Payment ID', $response['transactionid'] );
					$order->set_transaction_id( $response['transactionid'] );
					$order->save();
				}
			}
		}
	}

	/**
	 * Cancel pre-auth on refund/cancellation
	 *
	 * @param  int $order_id
	 */
	public function cancel_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		$gateway = new WC_Gateway_NMI();

		$gateway->log( "Info: Beginning cancel payment for order $order_id for the amount of {$order->get_total()}" );

		if ( $order->get_payment_method() == 'nmi' ) {
			$charge = $order->get_meta( '_nmi_charge_id' );

			if ( $charge ) {
				$args = array(
					'amount'			=> $order->get_total(),
					'transactionid'		=> $order->get_transaction_id(),
					'type' 				=> 'void',
					'email' 			=> $order->get_billing_email(),
					'currency'			=> $gateway->get_payment_currency( $order_id ),
				);
				$args = apply_filters( 'wc_nmi_request_args', $args, $order );

				$response = $gateway->nmi_request( $args );

				if ( is_wp_error( $response ) ) {
					$order->add_order_note( __( 'Unable to refund charge!', 'wc-nmi' ) . ' ' . $response->get_error_message() );
				} else {
					$cancel_message = sprintf( __( 'NMI charge refunded (Charge ID: %s)', 'wc-nmi' ), $response['transactionid'] );
 					$order->add_order_note( $cancel_message );
					$gateway->log( "Success: $cancel_message" );
					$order->delete_meta_data( '_nmi_charge_captured' );
					$order->delete_meta_data( '_nmi_charge_id' );
					$order->save();
				}
			}
		}
	}

}
$GLOBALS['wc_nmi_pci'] = WC_NMI_PCI::get_instance();