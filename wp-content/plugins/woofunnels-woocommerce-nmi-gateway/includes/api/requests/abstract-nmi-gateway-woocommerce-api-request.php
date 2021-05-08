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
 *
 * @package   NMI-Gateway-Woocommerce/Gateway/API/Request
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginFramework\v5_2_1 as NMI_Gateway_Woocommerce_Framework;

/**
 * NMI_Gateway_Woocommerce API Abstract Request Class
 *
 * Provides functionality common to all requests
 *
 * @since 1.0.0
 */
abstract class NMI_Gateway_Woocommerce_API_Request implements NMI_Gateway_Woocommerce_Framework\SV_WC_Payment_Gateway_API_Request {

	/** @var string NMI_Gateway_Woocommerce SDK resource for the request, e.g. `transaction` */
	protected $resource;

	/** @var string NMI_Gateway_Woocommerce SDK callback for the request, e.g. `generate` */
	protected $callback;

	/** @var array request data passed to the static callback */
	protected $request_data = array();

	/** @var \WC_Order order associated with the request, if any */
	protected $order;

	/**
	 * Setup request
	 *
	 * @param \WC_Order|null $order order if available
	 *
	 * @since 1.0.0
	 *
	 */
	public function __construct( $order = null ) {
		$this->order = $order;
	}

	/**
	 * Sets the NMI SDK resource for the request.
	 *
	 * @param string $resource , e.g. `transaction`
	 *
	 * @since 1.0.0
	 *
	 */
	protected function set_resource( $resource ) {

		$this->resource = $resource;
	}

	/**
	 * Gets the NMI resource for the request.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_resource() {

		return $this->resource;
	}


	/**
	 * Set the static callback for the request
	 *
	 * @param string $callback , e.g. `NMI_ClientToken::generate`
	 *
	 * @since 1.0.0
	 *
	 */
	protected function set_callback( $callback ) {

		$this->callback = $callback;
	}

	/**
	 * Get the static callback for the request
	 *
	 * @return string static callback
	 * @since 1.0.0
	 */
	public function get_callback() {

		return $this->callback;
	}

	/**
	 * Get the callback parameters for the request
	 * @return array
	 * @since 1.0.0
	 */
	public function get_callback_params() {

		return $this->get_request_data();
	}

	/**
	 * Return the string representation of the request
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function to_string() {
		return print_r( $this->get_request_data(), true ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
	}


	/**
	 * Return the string representation of the request, stripped of any
	 * confidential information
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function to_string_safe() {

		// no confidential info to mask...yet
		return $this->to_string();
	}


	/**
	 * Get the request data which is the 1st parameter passed to the static callback
	 * set
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_request_data() {
		/**
		 * NMI API Request Data.
		 *
		 * Allow actors to modify the request data before it's sent to NMI
		 *
		 * @param array|mixed $data request data to be filtered
		 * @param \WC_Order $order order instance
		 * @param \NMI_Gateway_Woocommerce_API_Request $this , API request class instance
		 *
		 * @since 1.0.0
		 *
		 */
		$this->request_data = apply_filters( NMI_Gateway_Woocommerce::PLUGIN_ID . '_request_data', $this->request_data, $this );

		$this->remove_empty_data();

		return $this->request_data;
	}


	/**
	 * Remove null or blank string values from the request data (up to 2 levels deep)
	 *
	 * @TODO: this can be improved to traverse deeper and be simpler @MR 2015-10-23
	 *
	 * @since 1.0.0
	 */
	protected function remove_empty_data() {

		foreach ( (array) $this->request_data as $key => $value ) {

			if ( is_array( $value ) ) {

				if ( empty( $value ) ) {

					unset( $this->request_data[ $key ] );

				} else {

					foreach ( $value as $inner_key => $inner_value ) {

						if ( is_null( $inner_value ) || '' === $inner_value ) {
							unset( $this->request_data[ $key ][ $inner_key ] );
						}
					}
				}

			} else {

				if ( is_null( $value ) || '' === $value ) {
					unset( $this->request_data[ $key ] );
				}
			}
		}
	}


	/**
	 * Gets a property from the associated order from database
	 *
	 * @param string $prop the desired order property
	 *
	 * @return mixed
	 * @since 1.0.0
	 *
	 */
	public function get_order_prop( $prop ) {
		return NMI_Gateway_Woocommerce_Framework\SV_WC_Order_Compatibility::get_prop( $this->get_order(), $prop );
	}

	/**
	 * Get the order associated with the request, if any
	 *
	 * @return \WC_Order|null
	 * @since 1.0.0
	 */
	public function get_order() {

		return $this->order;
	}


	/**
	 * NMI_Gateway_Woocommerce requests do not require a method per request
	 * @return string|void
	 * @since 1.0.0
	 */
	public function get_method() {
	}


	/**
	 * NMI_Gateway_Woocommerce requests do not require a path per request
	 * @return string|void
	 * @since 1.0.0
	 */
	public function get_path() {
	}

	/**
	 * @return array|void
	 */
	public function get_params() {
		// TODO: Implement get_params() method.
	}

	/**
	 * @return array|void
	 */
	public function get_data() {
		// TODO: Implement get_data() method.
	}
}
