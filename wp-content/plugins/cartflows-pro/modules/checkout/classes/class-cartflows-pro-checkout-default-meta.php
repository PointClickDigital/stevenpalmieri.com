<?php
/**
 * Checkout default post meta
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Meta Boxes setup
 */
class Cartflows_Pro_Checkout_Default_Meta {

	/**
	 * Instance
	 *
	 * @var $instance
	 */
	private static $instance;


	/**
	 * Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {

		add_filter( 'cartflows_checkout_meta_options', array( $this, 'meta_fields' ), 10, 2 );
	}

	/**
	 * Pro all meta fields
	 *
	 * @param array $fields checkout fields.
	 * @param int   $post_id post ID.
	 */
	public function meta_fields( $fields, $post_id ) {

		if ( ! cartflows_pro_is_active_license() && is_admin() ) {
			return $fields;
		}

		$fields['wcf-product-options-data'] = array(
			'default'  => array(),
			'sanitize' => 'FILTER_CARTFLOWS_PRO_CHECKOUT_PRODUCT_OPTIONS',
		);

		$fields['wcf-enable-product-options'] = array(
			'default'  => 'no',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-product-opt-title'] = array(
			'default'  => __( 'Your Products', 'cartflows-pro' ),
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-your-products-position'] = array(
			'default'  => 'after-customer',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		/* Product Selection */
		$fields['wcf-product-options'] = array(
			'default'  => 'force-all',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-enable-product-variation'] = array(
			'default'  => 'no',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-product-variation-options'] = array(
			'default'  => 'inline',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-enable-product-quantity']  = array(
			'default'  => 'no',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-show-product-images']      = array(
			'default'  => 'no',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-checkout-discount-coupon'] = array(
			'default'  => array(),
			'sanitize' => 'FILTER_CARTFLOWS_ARRAY',
		);

		/* pre-checkout meta fields*/
		$fields['wcf-pre-checkout-offer'] = array(
			'default'  => 'no',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-animate-browser-tab'] = array(
			'default'  => 'no',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-animate-browser-tab-title'] = array(
			'default'  => __( '___Don\'t miss out the offer___', 'cartflows-pro' ),
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-pre-checkout-offer-product']         = array(
			'default'  => array(),
			'sanitize' => 'FILTER_CARTFLOWS_ARRAY',
		);
		$fields['wcf-pre-checkout-offer-desc']            = array(
			'default'  => __( 'Write a few words about this awesome product and tell shoppers why they must get it. You may highlight this as "one time offer" and make it irresistible.', 'cartflows-pro' ),
			'sanitize' => 'FILTER_WP_KSES_POST',
		);
		$fields['wcf-pre-checkout-offer-popup-title']     = array(
			'default'  => __( '{first_name}, Wait! Your Order Is Almost Complete...', 'cartflows-pro' ),
			'sanitize' => 'FILTER_WP_KSES_POST',
		);
		$fields['wcf-pre-checkout-offer-popup-sub-title'] = array(
			'default'  => __( 'We have a special one time offer just for you.', 'cartflows-pro' ),
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-pre-checkout-offer-product-title']   = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-pre-checkout-offer-popup-btn-text']  = array(
			'default'  => __( 'Yes, Add to My Order!', 'cartflows-pro' ),
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-pre-checkout-offer-popup-skip-btn-text'] = array(
			'default'  => __( 'No, thanks!', 'cartflows-pro' ),
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-pre-checkout-offer-discount']            = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-pre-checkout-offer-discount-value']      = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_NUMBER_FLOAT',
		);
		$fields['wcf-pre-checkout-offer-bg-color']            = array(
			'default'  => '#eee',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);

		/* Order Bump Options */
		$fields['wcf-order-bump-style']    = array(
			'default'  => 'default',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-order-bump']          = array(
			'default'  => 'no',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-order-bump-position'] = array(
			'default'  => 'after-payment',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-order-bump-image']    = array(
			'default'  => '',
			'sanitize' => 'FILTER_CARTFLOWS_IMAGES',
		);
		$fields['wcf-order-bump-product']  = array(
			'default'  => array(),
			'sanitize' => 'FILTER_CARTFLOWS_ARRAY',
		);

		$fields['wcf-order-bump-product-quantity'] = array(
			'default'  => 1,
			'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
		);
		$fields['wcf-order-bump-label']            = array(
			'default'  => __( 'Yes, I will take it!', 'cartflows-pro' ),
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-order-bump-hl-text']          = array(
			'default'  => __( 'ONE TIME OFFER', 'cartflows-pro' ),
			'sanitize' => 'FILTER_WP_KSES_POST',
		);
		$fields['wcf-order-bump-desc']             = array(
			'default'  => __( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut, quod hic expedita consectetur vitae nulla sint adipisci cupiditate at. Commodi, dolore hic eaque tempora a repudiandae obcaecati deleniti mollitia possimus.', 'cartflows-pro' ),
			'sanitize' => 'FILTER_WP_KSES_POST',
		);
		$fields['wcf-order-bump-discount']         = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-order-bump-discount-value']   = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_NUMBER_FLOAT',
		);
		$fields['wcf-order-bump-discount-coupon']  = array(
			'default'  => array(),
			'sanitize' => 'FILTER_CARTFLOWS_ARRAY',
		);
		$fields['wcf-order-bump-replace']          = array(
			'default'  => 'no',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-ob-yes-next-step']            = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
		);

		/* Order Bump Style */
		$fields['wcf-bump-border-color']      = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);
		$fields['wcf-bump-border-style']      = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-bump-bg-color']          = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);
		$fields['wcf-bump-label-color']       = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);
		$fields['wcf-bump-label-bg-color']    = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);
		$fields['wcf-bump-desc-text-color']   = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);
		$fields['wcf-bump-hl-text-color']     = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);
		$fields['wcf-bump-hl-bg-color']       = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);
		$fields['wcf-bump-hl-tb-padding']     = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
		);
		$fields['wcf-bump-hl-lr-padding']     = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
		);
		$fields['wcf-show-bump-image-mobile'] = array(
			'default'  => 'yes',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		/* Highlight product styles*/
		$fields['wcf-product-options-skin']  = array(
			'default'  => 'classic',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-yp-text-color']         = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);
		$fields['wcf-yp-bg-color']           = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);
		$fields['wcf-yp-hl-text-color']      = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);
		$fields['wcf-yp-hl-bg-color']        = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);
		$fields['wcf-yp-hl-border-color']    = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);
		$fields['wcf-yp-hl-flag-text-color'] = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);
		$fields['wcf-yp-hl-flag-bg-color']   = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);
		/* Custom Fields Options*/
		$fields['wcf-show-coupon-field'] = array(
			'default'  => 'yes',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-show-bump-arrow']            = array(
			'default'  => 'no',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-show-bump-animate-arrow']    = array(
			'default'  => 'no',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-checkout-additional-fields'] = array(
			'default'  => 'yes',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-advance-options-fields']     = array(
			'default'  => 'no',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-custom-checkout-fields']     = array(
			'default'  => 'no',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-shipto-diff-addr-fields']    = array(
			'default'  => 'yes',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf_field_order_billing']        = array(
			'default'  => array(),
			'sanitize' => 'FILTER_CARTFLOWS_PRO_CHECKOUT_FIELDS',
		);
		$fields['wcf_field_order_shipping']       = array(
			'default'  => array(),
			'sanitize' => 'FILTER_CARTFLOWS_PRO_CHECKOUT_FIELDS',
		);
		/* Two Step Default Options */

		$fields['wcf-checkout-box-note']      = array(
			'default'  => 'yes',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-checkout-box-note-text'] = array(
			'default'  => __( 'Get Your FREE copy of CartFlows in just few steps.', 'cartflows-pro' ),
			'sanitize' => 'FILTER_WP_KSES_POST',
		);

		$fields['wcf-checkout-box-note-text-color'] = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);

		$fields['wcf-checkout-box-note-bg-color'] = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);

		$fields['wcf-checkout-step-one-title']     = array(
			'default'  => __( 'Shipping', 'cartflows-pro' ),
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-checkout-step-one-sub-title'] = array(
			'default'  => __( 'Where to ship it?', 'cartflows-pro' ),
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-checkout-step-two-title']         = array(
			'default'  => __( 'Payment', 'cartflows-pro' ),
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-checkout-step-two-sub-title']     = array(
			'default'  => __( 'Of your order', 'cartflows-pro' ),
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-checkout-offer-button-title']     = array(
			'default'  => __( 'For Special Offer Click Here', 'cartflows-pro' ),
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-checkout-offer-button-sub-title'] = array(
			'default'  => __( 'Yes! I want this offer!', 'cartflows-pro' ),
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		/** Comment
		$fields['wcf-checkout-two-step-title-text-color'] = array(
		'default'  => '',
		'sanitize' => 'FILTER_DEFAULT',
		);
		$fields['wcf-checkout-step-bg-color']             = array(
		'default'  => '',
		'sanitize' => 'FILTER_DEFAULT',
		);
		$fields['wcf-checkout-two-step-section-bg-color'] = array(
		'default'  => '#ffffff',
		'sanitize' => 'FILTER_DEFAULT',
		);
		$fields['wcf-checkout-active-step-bg-color']      = array(
		'default'  => '',
		'sanitize' => 'FILTER_DEFAULT',
		); */
		$fields['wcf-checkout-two-step-section-width'] = array(
			'default'  => '500',
			'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
		);

		$fields['wcf-checkout-two-step-section-border'] = array(
			'default'  => 'solid',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-optimize-coupon-field']            = array(
			'default'  => 'no',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-optimize-order-note-field'] = array(
			'default'  => 'no',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		return $fields;
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Checkout_Default_Meta::get_instance();
