<?php
/**
 * Checkout markup.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Checkout Markup
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Checkout_Markup {



	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var is_divi_enabled
	 */
	public $divi_status = false;

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 *  Constructor
	 */
	public function __construct() {

		$this->include_required_class();

		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_checkout_fields' ), 10, 2 );

		/* Scripts */
		add_action( 'cartflows_checkout_scripts', array( $this, 'checkout_order_scripts' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'load_compatibility_scripts_for_pro' ), 102 );

		/** Filter
		add_filter( 'cartflows_checkout_layout_template', array( $this, 'include_checkout_template' ), 10, 1 ); */

		add_action( 'cartflows_checkout_form_before', array( $this, 'two_step_actions' ), 10, 1 );

		add_filter( 'woocommerce_checkout_fields', array( $this, 'cartflows_one_column_checkout_fields' ) );

		add_filter( 'woocommerce_billing_fields', array( $this, 'billing_fields_customization' ), 1000, 2 );

		add_filter( 'woocommerce_shipping_fields', array( $this, 'shipping_fields_customization' ), 1000, 2 );

		add_filter( 'woocommerce_default_address_fields', array( $this, 'woo_default_address_fields' ), 1000 );

		add_filter( 'woocommerce_get_country_locale_default', array( $this, 'prepare_country_locale' ) );

		add_filter( 'woocommerce_get_country_locale', array( $this, 'woo_get_country_locale' ) );

		/* Hide/Show Order notes  */
		add_filter( 'woocommerce_checkout_fields', array( $this, 'additional_fields_customization' ), 1000 );

		add_action( 'cartflows_checkout_after_configure_cart', array( $this, 'after_configure_cart' ), 10, 1 );

		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_billing_custom_order_meta' ), 10, 1 );
		add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'display_shipping_custom_order_meta' ), 10, 1 );

		add_filter( 'woocommerce_email_order_meta_fields', array( $this, 'custom_woo_email_order_meta_fields' ), 10, 3 );

		add_filter( 'cartflows_show_coupon_field', array( $this, 'show_hide_coupon_field_on_checkout' ), 10, 2 );

		add_filter( 'global_cartflows_js_localize', array( $this, 'add_frontend_localize_scripts' ) );

		add_filter( 'woocommerce_form_field_hidden', array( $this, 'wcf_form_field_hidden' ), 10, 4 );

		add_action( 'cartflows_checkout_before_shortcode', array( $this, 'apply_url_coupon' ), 10 );

		add_action( 'cartflows_checkout_before_shortcode', array( $this, 'add_proudcts_using_url' ), 10 );

		add_filter( 'cartflows_selected_checkout_products', array( $this, 'update_the_checkout_products_data' ), 10, 2 );

		add_filter( 'cartflows_skip_configure_cart', array( $this, 'skip_cart_configuration' ), 10, 2 );
	}

	/**
	 * Skip the cart configuration when product is added from URL.
	 *
	 * @param bool $skip_cart is skip cart.
	 * @param int  $checkout_id checkout id.
	 */
	public function skip_cart_configuration( $skip_cart, $checkout_id ) {

		if( isset( $_GET['wcf-add-to-cart'] ) && ! empty( $_GET['wcf-add-to-cart'] ) ){ //phpcs:ignore
			WC()->cart->empty_cart();
			$skip_cart = true;
		}

		return $skip_cart;
	}

	/**
	 * Add the product in cart through URL.
	 */
	public function add_proudcts_using_url() {

		$url_products = apply_filters( 'cartflows_add_to_cart_products_from_url', true );

		if ( $url_products ) {

			$products = isset( $_GET['wcf-add-to-cart'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_GET['wcf-add-to-cart'] ) ) ) : array(); //phpcs:ignore
			$quantity = isset( $_GET['wcf-qty'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_GET['wcf-qty'] ) ) ) : array(); //phpcs:ignore

			if ( ! empty( $products ) ) {
				foreach ( $products as $key => $product_id ) {

					$product = wc_get_product( $product_id );

					// Add first variation of product if product type is variable.
					if ( $product && $product->is_type( 'variable' ) ) {

						$default_attributes = $product->get_default_attributes();

						if ( ! empty( $default_attributes ) ) {

							foreach ( $product->get_children() as $c_in => $variation_id ) {

								if ( 0 === $c_in ) {
									$product_id = $variation_id;
								}

								$single_variation = new WC_Product_Variation( $variation_id );

								if ( $default_attributes == $single_variation->get_attributes() ) {

									$product_id = $variation_id;
									break;
								}
							}
						} else {

							$product_childrens = $product->get_children();

							if ( is_array( $product_childrens ) ) {

								foreach ( $product_childrens  as $c_in => $c_id ) {

									$product_id = $c_id;
									break;
								}
							}
						}
					}

					$product_qunatity = isset( $quantity[ $key ] ) && ! empty( $quantity[ $key ] ) ? $quantity[ $key ] : 1;
					WC()->cart->add_to_cart( $product_id, $product_qunatity );
				}
			}
		}
	}

	/**
	 * Merge product options array with checkout products.
	 *
	 * @param array $products products.
	 * @param int   $checkout_id checkout id.
	 */
	public function update_the_checkout_products_data( $products, $checkout_id ) {

		$products_options = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-enable-product-options' );

		if ( 'yes' === $products_options && $products ) {

			$products_data             = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-product-options-data' );
			$products_option_condition = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-product-options' );

			foreach ( $products as $key => $data ) {

				$unique_key = $data['unique_id'];

				if ( $unique_key && isset( $products_data[ $unique_key ] ) ) {
					$products[ $key ] = wp_parse_args( $products_data[ $unique_key ], $data );
				}

				if ( ! isset( $products_data[ $unique_key ]['add_to_cart'] ) && 'single-selection' === $products_option_condition ) {
					$products[ $key ]['add_to_cart'] = 'no';
				}
			}

			$cart_value = array_column( $products, 'add_to_cart' );

			if ( ! in_array( 'yes', $cart_value, true ) ) {
				$products[0]['add_to_cart'] = 'yes';
			}
		}

		return $products;

	}

	/**
	 * Apply the coupon if available in url.
	 */
	public function apply_url_coupon() {

		$url_coupon = apply_filters( 'cartflows_apply_coupon_from_url', true );

		if ( $url_coupon ) {

			$coupon = isset( $_GET['coupon'] ) ? sanitize_text_field( wp_unslash( $_GET['coupon'] ) ) : false; //phpcs:ignore

			if ( $coupon ) {
				if ( WC()->cart->has_discount( $coupon ) ) {
					return;
				}
				WC()->cart->apply_coupon( $coupon );
			}
		}
	}

	/**
	 * Two Step Layout Actions.
	 *
	 * @param int $checkout_id checkout id.
	 * @since 1.1.9
	 */
	public function two_step_actions( $checkout_id ) {

		$checkout_layout = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-layout' );

		if ( 'two-step' == $checkout_layout ) {
			add_action( 'cartflows_add_before_main_section', array( $this, 'get_checkout_form_note' ), 10, 1 );

			add_action( 'woocommerce_checkout_before_customer_details', array( $this, 'add_two_step_first_step_wrapper' ), 13 );

			add_action( 'cartflows_add_before_main_section', array( $this, 'add_two_step_second_step_wrapper' ), 11 );

			add_action( 'cartflows_add_before_main_section', array( $this, 'add_two_step_nav_menu' ), 12, 1 );

			add_action( 'woocommerce_checkout_after_customer_details', array( $this, 'add_two_step_next_btn' ), 12 );

			add_action( 'woocommerce_checkout_after_customer_details', array( $this, 'add_two_step_closing_div' ), 13 );

			add_action( 'cartflows_add_after_main_section', array( $this, 'add_two_step_closing_div' ), 14 );
		}
	}

	/**
	 * Hidden Field Actions.
	 *
	 * @param string $field field.
	 * @param string $key key.
	 * @param array  $args args.
	 * @param string $value value.
	 *
	 * @since 1.1.9
	 */
	public function wcf_form_field_hidden( $field = '', $key, $args, $value ) {
		$field = '<input type="hidden" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" class="input-hidden" /> ';
		return $field;
	}

	/**
	 * Send custom fields in the order email.
	 *
	 * @param array  $fields of fields.
	 * @param string $sent_to_admin domain name to send.
	 * @param array  $order of order details.
	 */
	public function custom_woo_email_order_meta_fields( $fields, $sent_to_admin, $order ) {

		// Return if order not found.
		if ( ! $order ) {
			return $fields;
		}

		$order_id    = $order->get_id();
		$checkout_id = get_post_meta( $order_id, '_wcf_checkout_id', true );

		if ( ! $checkout_id ) {
			return $fields;
		}

		// Get custom fields.
		$custom_fields = get_post_meta( $checkout_id, 'wcf-custom-checkout-fields', true );

		if ( 'yes' === $custom_fields ) {
			// Billing Fields & Values.
			$billing_fields = get_post_meta( $checkout_id, 'wcf_fields_billing', true );

			foreach ( $billing_fields as $field => $data ) {
				if ( isset( $data['custom'] ) && $data['custom'] ) {
					$fields[ $field ] = array(
						'label' => $data['label'],
						'value' => get_post_meta( $order_id, '_' . $field, true ),
					);
				}
			}

			// Shipping Fields & Values.
			$shipping_fields = get_post_meta( $checkout_id, 'wcf_fields_shipping', true );

			foreach ( $shipping_fields as $field => $data ) {
				if ( isset( $data['custom'] ) && $data['custom'] ) {
					$fields[ $field ] = array(
						'label' => $data['label'],
						'value' => get_post_meta( $order_id, '_' . $field, true ),
					);
				}
			}
		}

		return $fields;
	}

	/**
	 * After configure cart.
	 *
	 * @param int $checkout_id checkout id.
	 */
	public function after_configure_cart( $checkout_id ) {

		$discount_coupon = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-discount-coupon' );

		if ( is_array( $discount_coupon ) && ! empty( $discount_coupon ) ) {
			$discount_coupon = reset( $discount_coupon );
		}

		if ( ! empty( $discount_coupon ) ) {
			$show_coupon_msg = apply_filters( 'cartflows_show_applied_coupon_message', true );

			if ( ! $show_coupon_msg ) {
				add_filter( 'woocommerce_coupon_message', '__return_empty_string' );
			}

			WC()->cart->add_discount( $discount_coupon );

			if ( ! $show_coupon_msg ) {
				remove_filter( 'woocommerce_coupon_message', '__return_empty_string' );
			}
		}
	}

	/**
	 *  Add markup classes
	 *
	 * @return void
	 */
	public function include_required_class() {

		include_once CARTFLOWS_PRO_CHECKOUT_DIR . 'classes/class-cartflows-pro-order-bump-product.php';
		include_once CARTFLOWS_PRO_CHECKOUT_DIR . 'classes/class-cartflows-pre-checkout-offer-product.php';
		include_once CARTFLOWS_PRO_CHECKOUT_DIR . 'classes/class-cartflows-pro-variation-product.php';
		include_once CARTFLOWS_PRO_CHECKOUT_DIR . 'classes/class-cartflows-pro-checkout-field-optimization.php';
	}

	/**
	 * Load shortcode scripts.
	 *
	 * @return void
	 */
	public function checkout_order_scripts() {

		global $post;

		if ( Cartflows_Compatibility::get_instance()->is_divi_enabled() ||
			Cartflows_Compatibility::get_instance()->is_divi_builder_enabled( $post->ID )
		) {
			$this->divi_status = true;
		}

		wp_enqueue_style( 'wcf-pro-checkout', wcf_pro()->utils->get_css_url( 'checkout-styles' ), '', CARTFLOWS_PRO_VER );

		wp_enqueue_script(
			'wcf-pro-checkout',
			wcf_pro()->utils->get_js_url( 'checkout' ),
			array( 'jquery' ),
			CARTFLOWS_PRO_VER,
			true
		);

		$checkout_id = $post->ID;

		$pre_checkout_offer = get_post_meta( $checkout_id, 'wcf-pre-checkout-offer', true );

		if ( 'yes' === $pre_checkout_offer ) {
			wp_enqueue_script(
				'wcf-pro-pre-checkout',
				wcf_pro()->utils->get_js_url( 'pre-checkout' ),
				array( 'jquery', 'jquery-ui-dialog' ),
				CARTFLOWS_PRO_VER,
				true
			);
		}

		wp_enqueue_style( 'dashicons' );

		$style = get_post_meta( $checkout_id, 'wcf-pro-dynamic-css', true );

		$css_version = get_post_meta( $checkout_id, 'wcf-pro-dynamic-css-version', true );

		if ( empty( $style ) || CARTFLOWS_ASSETS_VERSION !== $css_version ) {
			$style = $this->generate_style();
			update_post_meta( $checkout_id, 'wcf-pro-dynamic-css', $style );
			update_post_meta( $checkout_id, 'wcf-pro-dynamic-css-version', CARTFLOWS_ASSETS_VERSION );
		}

		wp_add_inline_style( 'wcf-pro-checkout', $style );
	}

	/**
	 * Load compatibility scripts.
	 *
	 * @return void
	 */
	public function load_compatibility_scripts_for_pro() {

		if ( ! _is_wcf_checkout_type() ) {
			return;
		}

		// Add DIVI Compatibility css if DIVI theme is enabled.
		if ( $this->divi_status ) {
			wp_enqueue_style( 'wcf-checkout-styles-divi', wcf_pro()->utils->get_css_url( 'checkout-styles-divi' ), '', CARTFLOWS_PRO_VER );
		}
	}

	/**
	 * Generate styles.
	 *
	 * @return string
	 */
	public function generate_style() {

		if ( ! _is_wcf_checkout_type() ) {
			return;
		}

		global $post;

		$checkout_id = $post->ID;

		$output = '';

		$enable_design_setting = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-enable-design-settings' );

		if ( 'yes' !== $enable_design_setting ) {
			return $output;
		}

		$primary_color    = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-primary-color' );
		$base_font_family = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-base-font-family' );

		/* For single product quick view lightbox popup*/
		$r = '';
		$g = '';
		$b = '';

		$submit_tb_padding = '';
		$submit_lr_padding = '';

		$field_heading_color = '';
		$field_color         = '';
		$field_input_size    = '';
		$field_bg_color      = '';
		$field_border_color  = '';
		$field_tb_padding    = '';
		$field_lr_padding    = '';

		$input_font_family = '';
		$input_font_weight = '';

		$submit_button_height      = '';
		$submit_color              = '';
		$submit_bg_color           = $primary_color;
		$submit_border_color       = $primary_color;
		$submit_hover_color        = '';
		$submit_bg_hover_color     = $primary_color;
		$submit_border_hover_color = $primary_color;
		$section_heading_color     = $primary_color;
		$section_bg_color          = $primary_color;

		$is_advance_option = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-advance-options-fields' );

		$button_font_family  = '';
		$button_font_weight  = '';
		$heading_font_family = '';
		$heading_font_weight = '';
		$base_font_family    = $base_font_family;

		if ( 'yes' == $is_advance_option ) {
			// Buttons, inputs, title : size, font, color, width options.
			$section_heading_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-heading-color' );

			$section_bg_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-section-bg-color' );

			$field_heading_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-field-heading-color' );

			$submit_tb_padding = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-tb-padding' );

			$submit_lr_padding = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-lr-padding' );

			$field_input_size = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-input-field-size' );

			$field_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-field-color' );

			$field_bg_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-field-bg-color' );

			$field_border_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-field-border-color' );

			$field_tb_padding = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-field-tb-padding' );

			$field_lr_padding = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-field-lr-padding' );

			$submit_button_height = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-input-button-size' );

			$submit_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-color' );

			$submit_bg_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-bg-color', $primary_color );

			$submit_border_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-border-color', $primary_color );

			$submit_hover_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-hover-color' );

			$submit_bg_hover_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-bg-hover-color', $primary_color );

			$submit_border_hover_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-border-hover-color', $primary_color );

			// Font and weight options.
			$button_font_family = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-button-font-family' );

			$button_font_weight = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-button-font-weight' );

			$input_font_family = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-input-font-family' );

			$input_font_weight = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-input-font-weight' );

			$heading_font_family = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-heading-font-family' );

			$heading_font_weight = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-heading-font-weight' );

		}

		if ( isset( $primary_color ) ) {
			list($r, $g, $b) = sscanf( $primary_color, '#%02x%02x%02x' );
		}

		$enable_product_options = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-enable-product-options' );
		$product_option         = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-product-options' );
		$variation_option       = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-product-variation-options' );

		// Remove margin for perticular product variation option.
		if ( 'force-all' == $product_option && 'popup' == $variation_option ) {
			$output .= '.wcf-product-option-wrap .wcf-qty-options .wcf-qty-row .wcf-item-choose-options{
				margin: 5px 0 0 0px;
			}';
		}
		// Remove margin for perticular product variation option.

		// Highlight products styles.
		$yp_text_color      = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-yp-text-color' );
		$yp_bg_color        = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-yp-bg-color' );
		$yp_hl_text_color   = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-yp-hl-text-color' );
		$yp_hl_bg_color     = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-yp-hl-bg-color' );
		$yp_hl_border_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-yp-hl-border-color' );
		$yp_flag_text_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-yp-hl-flag-text-color' );
		$yp_flag_bg_color   = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-yp-hl-flag-bg-color' );

		// Bump Order.
		$bump_border_style         = '';
		$bump_border_color         = '';
		$bump_bg_color             = '';
		$bump_label_color          = '';
		$bump_label_bg_color       = '';
		$bump_desc_text_color      = '';
		$bump_hl_text_color        = '';
		$bump_blinking_arrow_color = '';
		$order_bump_enabled        = get_post_meta( $post->ID, 'wcf-order-bump', true );

		if ( 'yes' === $order_bump_enabled ) {

			$bump_border_style_value = get_post_meta( $checkout_id, 'wcf-bump-border-style', true );

			// Define bump order layout.
			if ( 'inherit' !== $bump_border_style_value ) {
				$bump_border_style = $bump_border_style_value;
			}

			// Get bump order styling values.
			$bump_border_color = get_post_meta( $checkout_id, 'wcf-bump-border-color', true );
			$bump_bg_color     = get_post_meta( $checkout_id, 'wcf-bump-bg-color', true );

			$bump_label_color    = get_post_meta( $checkout_id, 'wcf-bump-label-color', true );
			$bump_label_bg_color = get_post_meta( $checkout_id, 'wcf-bump-label-bg-color', true );

			$bump_desc_text_color = get_post_meta( $checkout_id, 'wcf-bump-desc-text-color', true );

			$bump_hl_text_color = get_post_meta( $checkout_id, 'wcf-bump-hl-text-color', true );

			$bump_blinking_arrow_color = get_post_meta( $checkout_id, 'wcf-bump-blinking-arrow-color', true );

			$enabled_bump_image_mobile = get_post_meta( $checkout_id, 'wcf-show-bump-image-mobile', true );
		}

		/*
		 * Two step Layout
		 */

		// Get checkout page layout.
		$checkout_layout = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-layout' );

		if ( 'two-step' === $checkout_layout ) {

			$checkout_note_enabled = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-box-note', false );

			$step_two_width = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-two-step-section-width' );

			$two_step_box_text_color = '';

			$two_step_box_bg_color = '';

			$two_step_section_border = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-two-step-section-border' );

			if ( 'yes' == $checkout_note_enabled ) {
				$two_step_box_text_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-box-note-text-color', '' );

				$two_step_box_bg_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-box-note-bg-color', $primary_color );
			}
		}
		/* Two step Layout */

		/* Pre-checkout offer*/
		$is_pre_checkut_upsell = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-pre-checkout-offer' );
		$pre_checkout_bg_color = '';

		if ( 'yes' === $is_pre_checkut_upsell ) {
			$pre_checkout_bg_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-pre-checkout-offer-bg-color' );
		}
		/* Pre-checkout offer*/

		if ( $this->divi_status ) {

			include CARTFLOWS_PRO_CHECKOUT_DIR . 'includes/checkout-pro-dynamic-divi-css.php';

		} else {

			include CARTFLOWS_PRO_CHECKOUT_DIR . 'includes/checkout-pro-dynamic-css.php';
		}

		return $output;
	}

	/**
	 * Save checkout fields.
	 *
	 * @param int   $order_id order id.
	 * @param array $posted posted data.
	 * @return void
	 */
	public function save_checkout_fields( $order_id, $posted ) {

		if ( isset( $_POST['_wcf_bump_product'] ) ) {
			$bump_product_id = wc_clean( sanitize_text_field( wp_unslash( $_POST['_wcf_bump_product'] ) ) );

			update_post_meta( $order_id, '_wcf_bump_product', $bump_product_id );
		}
	}

	/**
	 * Save checkout fields.
	 *
	 * @param string $layout_style layout style.
	 * @return link
	 */
	public function include_checkout_template( $layout_style ) {

		if ( ( 'two-step' === $layout_style ) || ( 'one-column' === $layout_style ) ) {
			return CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/embed/checkout-template-simple.php';
		}

		return $layout_style;
	}

	/**
	 * Display Two Step Nav Menu.
	 *
	 * @param string $layout_style layout style.
	 * @return markup
	 */
	public function add_two_step_nav_menu( $layout_style ) {

		if ( 'two-step' === $layout_style ) {
			// Get Checkout ID.
			global $post;

			$checkout_id = false;

			if ( _is_wcf_checkout_type() ) {
				$checkout_id = $post->ID;
			} else {

				if ( is_admin() && isset( $_POST['id'] ) ) {
					$checkout_id = intval( $_POST['id'] );// phpcs:ignore
				}
			}

			if ( ! $checkout_id ) {
				return;
			}

			// Get/Set default values.
			$is_note_enabled         = '';
			$checkout_note           = '';
			$step_one_title          = '';
			$step_one_sub_title      = '';
			$step_two_title          = '';
			$step_two_sub_title      = '';
			$two_step_section_border = '';

			// Get default values from the default meta to show if the advance option is not enabled.
			$all_fields = Cartflows_Default_Meta::get_instance()->get_checkout_fields( $checkout_id );

			$step_one_title     = $all_fields['wcf-checkout-step-one-title']['default'];
			$step_one_sub_title = $all_fields['wcf-checkout-step-one-sub-title']['default'];
			$step_two_title     = $all_fields['wcf-checkout-step-two-title']['default'];
			$step_two_sub_title = $all_fields['wcf-checkout-step-two-sub-title']['default'];

			// Get the values form the applied settings.
			// Get step titles.
			$step_one_title     = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-step-one-title', '' );
			$step_one_sub_title = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-step-one-sub-title', '' );
			$step_two_title     = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-step-two-title', '' );
			$step_two_sub_title = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-step-two-sub-title', '' );

			$two_step_section_border = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-two-step-section-border' );

			$two_step_html = '';

				$two_step_html .= "<div class='wcf-embed-checkout-form-nav wcf-border-" . $two_step_section_border . " '>";

				$two_step_html             .= "<ul class='wcf-embed-checkout-form-steps'>";
					$two_step_html         .= "<div class='steps step-one wcf-current'>";
						$two_step_html     .= "<a href='#customer_details'>";
							$two_step_html .= "<div class='step-number'>1</div>";

							$two_step_html .= "<div class='step-heading'>";

								$two_step_html .= "<div class='step-name'>" . $step_one_title . '</div>';
								$two_step_html .= "<div class='step-sub-name'>" . $step_one_sub_title . '</div>';

							$two_step_html .= '</div>';

						$two_step_html .= '</a>';
					$two_step_html     .= '</div>';

					$two_step_html         .= "<div class='steps step-two'>";
						$two_step_html     .= "<a href='#wcf-order-wrap'>";
							$two_step_html .= "<div class='step-number'>2</div>";

								$two_step_html .= "<div class='step-heading'>";

									$two_step_html .= "<div class='step-name'>" . $step_two_title . '</div>';
									$two_step_html .= "<div class='step-sub-name'>" . $step_two_sub_title . '</div>';

								$two_step_html .= '</div>';

						$two_step_html .= '</a>';
					$two_step_html     .= '</div>';

				$two_step_html .= '</ul>';
			$two_step_html     .= '</div>';

			echo $two_step_html;
		}

		return $layout_style;
	}

	/**
	 * Display Two Step note box.
	 *
	 * @param string $layout_style layout style.
	 * @return void
	 */
	public function get_checkout_form_note( $layout_style ) {

		// Get Checkout ID.
		$checkout_id = false;

		global $post;

		if ( $post ) {
			$checkout_id = $post->ID;
		} else {

			if ( is_admin() && isset( $_POST['id'] ) ) {
				$checkout_id = intval( $_POST['id'] );// phpcs:ignore
			}
		}

		if ( ! $checkout_id ) {
			return;
		}

		$is_note_enabled = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-box-note', false );

		if ( 'yes' == $is_note_enabled ) {

			$checkout_note = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-box-note-text', '' );

			$two_step_note = '';

			$two_step_note .= "<div class='wcf-embed-checkout-form-note'>";

			$two_step_note .= '<p>' . $checkout_note . '</p>';

			$two_step_note .= '</div>';

			echo $two_step_note;
		}
	}

	/**
	 * Display Two Step Nav Next Button.
	 */
	public function add_two_step_next_btn() {

		global $post;

		$checkout_id = false;

		if ( _is_wcf_checkout_type() ) {
			$checkout_id = $post->ID;
		} else {

			if ( is_admin() && isset( $_POST['id'] ) ) {
				$checkout_id = intval( $_POST['id'] );// phpcs:ignore
			}
		}

		if ( ! $checkout_id ) {
			return;
		}

		$checkout_layout = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-layout' );

		$button_title = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-offer-button-title', '' );

		$button_sub_title = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-offer-button-sub-title', '' );

		if ( 'two-step' === $checkout_layout ) {
			$two_step_next_btn_html = '';

			$two_step_next_btn_html .= '<div class="wcf-embed-checkout-form-nav-btns">';

				$two_step_next_btn_html     .= '<a href="#wcf-order-wrap" class="button wcf-next-button" >';
					$two_step_next_btn_html .= '<span class="wcf-next-button-content">';

			if ( '' != $button_title ) {
						$two_step_next_btn_html     .= '<span class="wcf-next-button-icon-wrap">';
							$two_step_next_btn_html .= '<span class="dashicons dashicons-arrow-right-alt"></span>';
							$two_step_next_btn_html .= '<span class="wcf-button-text">' . $button_title . '</span>';
						$two_step_next_btn_html     .= '</span>';
			}

			if ( '' != $button_sub_title ) {
						$two_step_next_btn_html .= '<span class="wcf-button-sub-text">' . $button_sub_title . '</span>';
			}
					$two_step_next_btn_html .= '</span>';
				$two_step_next_btn_html     .= '</a>';

			$two_step_next_btn_html .= '</div>';

			echo $two_step_next_btn_html;
		}
	}

	/**
	 * Change order comments placeholder and label, and set billing phone number to not required.
	 *
	 * @param array $fields checkout fields.
	 * @return fields
	 */
	public function cartflows_one_column_checkout_fields( $fields ) {

		if ( _is_wcf_checkout_type() ) {

			global $post;

			$checkout_id = $post->ID;
		} else {
			if ( _is_wcf_doing_checkout_ajax() ) {
				$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
			} else {
				return $fields;
			}
		}

		$fields_skins = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-fields-skins' );

		if ( 'style-one' == $fields_skins ) {
			/* Unset the label class of billing address 2 */
			if ( isset( $fields['billing']['billing_address_2']['label_class'] ) ) {
				unset( $fields['billing']['billing_address_2']['label_class'] );
			}

			/* Unset the label class of Shipping address 2*/
			if ( isset( $fields['shipping']['shipping_address_2']['label_class'] ) ) {
				unset( $fields['shipping']['shipping_address_2']['label_class'] );
			}

			if ( isset( $fields['billing'] ) && is_array( $fields['billing'] ) ) {
				foreach ( $fields['billing'] as $key => $field ) {
					$fields['billing'][ $key ]['placeholder'] = '';
				}
			}

			if ( isset( $fields['shipping'] ) && is_array( $fields['shipping'] ) ) {
				foreach ( $fields['shipping'] as $key => $field ) {
					$fields['shipping'][ $key ]['placeholder'] = '';
				}
			}

			if ( isset( $fields['account'] ) && is_array( $fields['account'] ) ) {
				foreach ( $fields['account'] as $key => $field ) {
					$fields['account'][ $key ]['placeholder'] = '';
				}
			}

			if ( isset( $fields['order'] ) && is_array( $fields['order'] ) ) {
				foreach ( $fields['order'] as $key => $field ) {
					$fields['order'][ $key ]['placeholder'] = '';
				}
			}
		}

		return $fields;
	}

	/**
	 * Billing field customization.
	 *
	 * @param array  $fields fields data.
	 * @param string $country country name.
	 * @return array
	 */
	public function billing_fields_customization( $fields, $country ) {

		if ( _is_wcf_checkout_type() ) {

			global $post;

			$checkout_id = $post->ID;
		} else {
			if ( _is_wcf_doing_checkout_ajax() ) {
				$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
			} else {
				return $fields;
			}
		}

		if ( ! _is_wcf_meta_custom_checkout( $checkout_id ) ) {
			return $fields;
		}

		if ( is_wc_endpoint_url( 'edit-address' ) ) {
			return $fields;
		}

		$saved_fields = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf_field_order_billing' );

		if ( '' == $saved_fields ) {
			$saved_fields = Cartflows_Helper::get_checkout_fields( 'billing', $checkout_id );
		}

		return $this->prepare_address_fields( $saved_fields, $country, $checkout_id, $fields, 'billing' );
	}

	/**
	 * Shipping field customization.
	 *
	 * @param array  $fields fields data.
	 * @param string $country country name.
	 * @return array
	 */
	public function shipping_fields_customization( $fields, $country ) {

		if ( _is_wcf_checkout_type() ) {

			global $post;

			$checkout_id = $post->ID;
		} else {
			if ( _is_wcf_doing_checkout_ajax() ) {
				$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
			} else {
				return $fields;
			}
		}

		if ( ! _is_wcf_meta_custom_checkout( $checkout_id ) ) {
			return $fields;
		}

		if ( is_wc_endpoint_url( 'edit-address' ) ) {
			return $fields;
		}

		$saved_fields = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf_field_order_shipping' );

		if ( '' == $saved_fields ) {
			$saved_fields = Cartflows_Helper::get_checkout_fields( 'shipping', $checkout_id );
		}

		return $this->prepare_address_fields( $saved_fields, $country, $checkout_id, $fields, 'shipping' );
	}


	/**
	 * Additional fields customization
	 *
	 * @param array $fields fields.
	 * @return array fields
	 */
	public function additional_fields_customization( $fields ) {

		if ( _is_wcf_checkout_type() ) {

			global $post;

			$checkout_id = $post->ID;
		} else {
			if ( _is_wcf_doing_checkout_ajax() ) {
				$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
			} else {
				return $fields;
			}
		}

		/**
		If ( ! _is_wcf_meta_custom_checkout( $checkout_id ) ) {
		return $fields;
		}
		*/

		$show_shipto_diff_addr = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-shipto-diff-addr-fields' );

		$show = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-additional-fields' );

		if ( 'no' === $show ) {
			if ( isset( $fields['order']['order_comments'] ) ) {
				unset( $fields['order']['order_comments'] );
				add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );
			}
		}

		if ( 'no' === $show_shipto_diff_addr ) {
			add_filter( 'woocommerce_cart_needs_shipping_address', '__return_false' );
		}

		return $fields;
	}


	/**
	 * Prepare address fields.
	 *
	 * @param array  $fieldset fieldset data.
	 * @param string $country country name.
	 * @param int    $checkout_id checkout ID.
	 * @param bool   $original_fieldset is original fieldset.
	 * @param string $type address type.

	 * @return array
	 */
	public function prepare_address_fields( $fieldset, $country, $checkout_id, $original_fieldset = false, $type = 'billing' ) {

		if ( is_array( $fieldset ) && ! empty( $fieldset ) ) {
			$priority = 0;

			$locale = WC()->countries->get_country_locale();

			if ( isset( $locale[ $country ] ) && is_array( $locale[ $country ] ) ) {
				foreach ( $locale[ $country ] as $key => $value ) {
					if ( is_array( $value ) && isset( $fieldset[ $type . '_' . $key ] ) ) {
						if ( isset( $value['required'] ) ) {
							$fieldset[ $type . '_' . $key ]['required'] = $value['required'];
						}
					}
				}
			}

			$all_original_fields = array_merge( $original_fieldset, $fieldset );
			$original_fieldset   = $this->prepare_checkout_fields_lite( $fieldset, $all_original_fields, $checkout_id );

			if ( ! empty( $original_fieldset ) ) {
				foreach ( $original_fieldset as $fieldset_key => $fieldset_value ) {
					if ( ! isset( $fieldset_value['priority'] ) ) {
						$new_priority                                   = $priority + 10;
						$original_fieldset[ $fieldset_key ]['priority'] = $new_priority;
						$priority                                       = $new_priority;
					} else {
						$priority = $fieldset_value['priority'];
					}
				}
			}
		}
		return $original_fieldset;
	}

	/**
	 * Prepare checkout fields.
	 *
	 * @param array $fields fields data.
	 * @param bool  $original_fields is original fields.
	 * @param int   $checkout_id checkout ID.
	 * @return array
	 */
	public function prepare_checkout_fields_lite( $fields, $original_fields, $checkout_id ) {

		if ( is_array( $fields ) && ! empty( $fields ) ) {

			$get_ordered_billing_fields  = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf_field_order_billing' );
			$get_ordered_shipping_fields = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf_field_order_shipping' );

			/* Check for array before merged. */
			$order_checkout_fields = array_merge( $get_ordered_billing_fields, $get_ordered_shipping_fields );

			foreach ( $fields as $name => $field ) {

				// Backword compatibility with field enabled.
				if ( isset( $order_checkout_fields[ $name ]['enabled'] ) ) {
					$is_enabled = $order_checkout_fields[ $name ]['enabled'];
				} else {
					$is_enabled = get_post_meta( $checkout_id, 'wcf-' . $name, true );
					$is_enabled = 'yes' === $is_enabled ? true : false;
				}

				// Backword compatibility with field width.
				if ( isset( $order_checkout_fields[ $name ]['width'] ) ) {
					$field_widths = $order_checkout_fields[ $name ]['width'];
				} else {
					$field_widths = get_post_meta( $checkout_id, 'wcf-field-width_' . $name, true );
				}

				// Set/Unset field if checked/unchecked.
				if ( ! $is_enabled ) {
					unset( $original_fields[ $name ] );
					unset( $fields[ $name ] );
				} else {
					if ( ! isset( $original_fields[ $name ] ) ) {
						$original_fields[ $name ] = $field;
					}

					$field_widths = apply_filters( 'cartflows_checkout_fields_width', $field_widths, $name );

					// Add Custom class if set.
					if ( '' != $field_widths ) {

						$original_fields[ $name ]['class'][] = 'wcf-column-' . $field_widths;

					}
				}
			}
		}

		return $original_fields;
	}

	/**
	 * Prepare country locale.
	 *
	 * @param array $fields country locale fields.
	 * @return array
	 */
	public function prepare_country_locale( $fields ) {

		if ( _is_wcf_checkout_type() ) {

			global $post;

			$checkout_id = $post->ID;
		} else {
			if ( _is_wcf_doing_checkout_ajax() ) {
				$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
			} else {
				return $fields;
			}
		}

		if ( is_array( $fields ) ) {
			$type = apply_filters( 'wcf_address_field_override_with', 'billing' );

			$address_fields = get_option( 'wcf_fields_' . $type, array() );

			$is_custom_fields_enabled = get_post_meta( $checkout_id, 'wcf-custom-checkout-fields', true );

			$override_required    = apply_filters( 'wcf_address_field_override_required', false );
			$override_placeholder = apply_filters( 'wcf_field_override_placeholder', true );
			$override_label       = apply_filters( 'wcf_field_override_label', true );

			if ( 'yes' == $is_custom_fields_enabled ) {
				foreach ( $fields as $key => $props ) {
					if ( isset( $props['priority'] ) ) {
						unset( $fields[ $key ]['priority'] );
					}

					if ( $override_placeholder && isset( $props['placeholder'] ) ) {
						unset( $fields[ $key ]['placeholder'] );
					}
					if ( $override_label && isset( $props['label'] ) ) {
						unset( $fields[ $key ]['label'] );
					}
				}
			} else {
				$fields_skins = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-fields-skins' );

				if ( 'style-one' == $fields_skins ) {
					foreach ( $fields as $key => $props ) {
						if ( $override_placeholder && isset( $props['placeholder'] ) ) {
							unset( $fields[ $key ]['placeholder'] );
						}
					}
				}
			}
		}
		return $fields;
	}

	/**
	 * Prepare default country locale.
	 *
	 * @param array $fields country locale fields.
	 * @return array
	 */
	public function woo_default_address_fields( $fields ) {

		if ( _is_wcf_checkout_type() ) {

			global $post;

			$checkout_id = $post->ID;
		} else {

			if ( _is_wcf_doing_checkout_ajax() ) {

				$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
			} else {
				return $fields;
			}
		}

		$is_custom_fields_enabled = get_post_meta( $checkout_id, 'wcf-custom-checkout-fields', true );

		if ( 'yes' == $is_custom_fields_enabled ) {

			$sname = apply_filters( 'wcf_address_field_override_with', 'billing' );

			if ( 'billing' === $sname || 'shipping' === $sname ) {

				$address_fields = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf_field_order_billing' );

				if ( '' == $address_fields ) {

					$address_fields = Cartflows_Helper::get_checkout_fields( $sname, $checkout_id );
				}

				if ( is_array( $address_fields ) && ! empty( $address_fields ) && ! empty( $fields ) ) {
					$override_required = apply_filters( 'wcf_address_field_override_required', true );

					foreach ( $fields as $name => $field ) {
						$fname = $sname . '_' . $name;

						if ( $this->is_locale_field( $fname ) && $override_required ) {
							$custom_field = isset( $address_fields[ $fname ] ) ? $address_fields[ $fname ] : false;

							if ( $custom_field && ! ( isset( $custom_field['enabled'] ) && false == $custom_field['enabled'] ) ) {
								$fields[ $name ]['required'] = isset( $custom_field['required'] ) && $custom_field['required'] ? true : false;
							}
						}
					}
				}
			}
		}

		return $fields;
	}


	/**
	 * Get country locale.
	 *
	 * @param array $locale country locale.
	 * @return array
	 */
	public function woo_get_country_locale( $locale ) {

		if ( _is_wcf_checkout_type() ) {

			global $post;

			$checkout_id = $post->ID;
		} else {
			if ( _is_wcf_doing_checkout_ajax() ) {
				$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
			} else {
				return $locale;
			}
		}

		if ( is_array( $locale ) ) {
			foreach ( $locale as $country => $fields ) {
				$locale[ $country ] = $this->prepare_country_locale( $fields );
			}
		}

		return $locale;
	}

	/**
	 * Set locale fields.
	 *
	 * @param string $field_name field name.
	 * @return bool
	 */
	public function is_locale_field( $field_name ) {
		if ( ! empty( $field_name ) && in_array(
			$field_name,
			array(
				'billing_address_1',
				'billing_address_2',
				'billing_state',
				'billing_postcode',
				'billing_city',
				'billing_country',
				'shipping_address_1',
				'shipping_address_2',
				'shipping_state',
				'shipping_postcode',
				'shipping_city',
				'shipping_country',
			),
			true
		)
		) {
			return true;
		}
		return false;
	}

	/**
	 * Show/Hide coupon field on checkout page
	 *
	 * @param bool $is_field true.
	 * @param bool $optimized_field true.
	 * @return bool
	 */
	public function show_hide_coupon_field_on_checkout( $is_field, $optimized_field = false ) {

		if ( _is_wcf_checkout_type() ) {

			global $post;

			$checkout_id = $post->ID;
		} else {
			if ( _is_wcf_doing_checkout_ajax() ) {
				$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
			} else {
				return $is_field;
			}
		}

		$show = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-show-coupon-field' );

		if ( $optimized_field ) {
			$optimized_show = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-optimize-coupon-field' );

			if ( 'yes' === $show && 'yes' === $optimized_show ) {
				return true;
			}
		} else {
			if ( 'yes' === $show ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Display billing custom field data on order page
	 *
	 * @param obj $order Order object.
	 * @return void
	 */
	public function display_billing_custom_order_meta( $order ) {

		if ( ! $order ) {
			return;
		}

		$order_id    = $order->get_id();
		$checkout_id = get_post_meta( $order_id, '_wcf_checkout_id', true );

		/* Custom Field To Do */
		$custom_fields = get_post_meta( $checkout_id, 'wcf-custom-checkout-fields', true );

		if ( 'yes' === $custom_fields ) {
			$output = '';

			$billing_fields = get_post_meta( $checkout_id, 'wcf_fields_billing', true );

			foreach ( $billing_fields as $field => $data ) {
				if ( isset( $data['custom'] ) && $data['custom'] ) {
					$output .= '<p><strong>' . $data['label'] . ':</strong> ' . get_post_meta( $order_id, '_' . $field, true ) . '</p>';
				}
			}

			if ( '' !== $output ) {
				$output = '<h3>' . __( 'Billing Custom Fields', 'cartflows-pro' ) . '</h3>' . $output;
			}

			echo $output;
		}
	}

	/**
	 * Display shipping custom field data on order page
	 *
	 * @param obj $order Order object.
	 * @return void
	 */
	public function display_shipping_custom_order_meta( $order ) {

		if ( ! $order ) {
			return;
		}

		$order_id    = $order->get_id();
		$checkout_id = get_post_meta( $order_id, '_wcf_checkout_id', true );

		/* Custom Field To Do */
		$custom_fields = get_post_meta( $checkout_id, 'wcf-custom-checkout-fields', true );

		if ( 'yes' === $custom_fields ) {
			$output = '';

			$shipping_fields = get_post_meta( $checkout_id, 'wcf_fields_shipping', true );

			foreach ( $shipping_fields as $field => $data ) {
				if ( isset( $data['custom'] ) && $data['custom'] ) {
					$output .= '<p><strong>' . $data['label'] . ':</strong> ' . get_post_meta( $order_id, '_' . $field, true ) . '</p>';
				}
			}

			if ( '' !== $output ) {
				$output = '<h3>' . __( 'Shipping Custom Fields', 'cartflows-pro' ) . '</h3>' . $output;
			}

			echo $output;
		}
	}

	/**
	 * Add second step opening dev
	 *
	 * @since 1.1.9
	 */
	public function add_two_step_second_step_wrapper() {
		echo "<div class='wcf-two-step-wrap'> ";
	}

	/**
	 * Add first step opening dev
	 *
	 * @since X.X.X
	 */
	public function add_two_step_first_step_wrapper() {
		echo '<div class="wcf-checkout-fields-wrapper">';
	}

	/**
	 * Add Startng & closing dev
	 *
	 * @since 1.1.9
	 */
	public function add_two_step_closing_div() {
		echo '</div> ';
	}


	/**
	 * Add localize variables.
	 *
	 * @since 1.1.5
	 * @param array $localize settings.
	 * @return array $localize settings.
	 */
	public function add_frontend_localize_scripts( $localize ) {

		$localize['allow_autocomplete_zipcode'] = apply_filters( 'cartflows_autocomplete_zip_data', 'no' );
		$localize['add_to_cart_text']           = __( 'Processing...', 'cartflows-pro' );
		return $localize;
	}

}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Checkout_Markup::get_instance();
