<?php
/**
 * Bump order
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Order Bump Product
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Order_Bump_Product {

	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

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
		add_action( 'cartflows_checkout_form_before', array( $this, 'load_actions' ) );
		/* For wc ajax actions to filter it's value user priortiy before 499 */
		add_action( 'cartflows_woo_checkout_update_order_review_init', array( $this, 'dynamic_order_bump' ), 499 );
		/* Add or Cancel Bump Product */
		add_action( 'wp_ajax_wcf_bump_order_process', array( $this, 'order_bump_process' ) );
		add_action( 'wp_ajax_nopriv_wcf_bump_order_process', array( $this, 'order_bump_process' ) );
		add_shortcode( 'cartflows_bump_product_title', array( $this, 'bump_product_title' ) );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'custom_price_to_cart_item' ), 9999 );
	}

	/**
	 * To show order bump dynamically after update order review.
	 */
	public function dynamic_order_bump() {
		add_action( 'cartflows_checkout_before_shortcode', array( $this, 'load_actions' ) );

	}

	/**
	 * Load Actions
	 *
	 * @param int $checkout_id checkout id.
	 */
	public function load_actions( $checkout_id ) {

		if ( empty( $checkout_id ) && is_admin() && isset( $_POST['id'] ) ) {
			$checkout_id = intval( $_POST['id'] );// phpcs:ignore
		}

		$order_bump = get_post_meta( $checkout_id, 'wcf-order-bump', true );

		if ( 'yes' !== $order_bump ) {
			return;
		}

		$position = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-position' );

		if ( 'before-checkout' === $position ) {
			/* Before CHeckout Form */
			add_action( 'woocommerce_checkout_before_customer_details', array( $this, 'bump_order' ), 14 );
		}

		if ( 'after-customer' === $position ) {
			/* After customer details */
			add_action( 'woocommerce_checkout_after_customer_details', array( $this, 'bump_order' ) );
		}

		if ( 'after-payment' === $position ) {
			/* After payment Selection */
			add_action( 'woocommerce_review_order_before_submit', array( $this, 'bump_order' ) );
		}

		if ( 'after-order' === $position ) {
			/* Position After Order */
			add_action( 'woocommerce_checkout_order_review', array( $this, 'bump_order' ), 11 );
		}

		add_action( 'woocommerce_checkout_after_order_review', array( $this, 'add_order_bump_hidden_fields' ), 99 );
	}

	/**
	 *  Display bump offer box html.
	 */
	public function add_order_bump_hidden_fields() {
		echo '<input type="hidden" name="_wcf_bump_product_action" value="">';
		echo '<input type="hidden" name="_wcf_bump_product" value="">';
	}

	/**
	 * Get order bump hidden data.
	 *
	 * @param int     $product_id product id.
	 * @param boolean $order_bump_checked checked value.
	 */
	public function get_order_bump_hidden_data( $product_id, $order_bump_checked ) {

		$bump_product_id = $order_bump_checked ? $product_id : '';

		echo '<input type="hidden" name="wcf_bump_product_id" class="wcf-bump-product-id" value="' . $product_id . '">';
		echo '<input type="hidden" name="_wcf_bump_product" value="' . $bump_product_id . '">';
	}

	/**
	 *  Display bump offer box html.
	 */
	public function bump_order() {

		global $post;

		$order_bump_id = 0;

		if ( $post ) {
			$order_bump_id = $post->ID;
		} elseif ( is_admin() && isset( $_POST['id'] ) ) {
			$order_bump_id = intval( $_POST['id'] );// phpcs:ignore
		}

		$output = '';

		if ( _is_wcf_checkout_type() || wcf()->utils->check_is_checkout_page( $order_bump_id ) ) {

			$order_bump                  = get_post_meta( $order_bump_id, 'wcf-order-bump', true );
			$order_bump_product          = get_post_meta( $order_bump_id, 'wcf-order-bump-product', true );
			$order_bump_product_quantity = get_post_meta( $order_bump_id, 'wcf-order-bump-product-quantity', true );

			if ( 'yes' !== $order_bump || empty( $order_bump_product[0] ) ) {
				return;
			}

			if ( empty( $order_bump_product ) ) {

				$flow_id = wcf()->utils->get_flow_id_from_step_id( $order_bump_id );

				if ( wcf()->flow->is_flow_testmode( $flow_id ) ) {
					$order_bump_product = $this->get_bump_test_product( $order_bump_id );
				} else {
					return;
				}
			}

			$bump_layout        = wcf()->options->get_checkout_meta_value( $order_bump_id, 'wcf-order-bump-style' );
			$order_bump_label   = wcf()->options->get_checkout_meta_value( $order_bump_id, 'wcf-order-bump-label' );
			$order_bump_hl_text = wcf()->options->get_checkout_meta_value( $order_bump_id, 'wcf-order-bump-hl-text' );
			$order_bump_desc    = wcf()->options->get_checkout_meta_value( $order_bump_id, 'wcf-order-bump-desc' );

			$order_bump_prd_title = get_post_meta( $order_bump_id, 'wcf-checkout-products', true );

			$product_id         = reset( $order_bump_product );
			$order_bump_checked = false;

			$discount_type    = get_post_meta( $order_bump_id, 'wcf-order-bump-discount', true );
			$discount_value   = get_post_meta( $order_bump_id, 'wcf-order-bump-discount-value', true );
			$discount_coupon  = get_post_meta( $order_bump_id, 'wcf-order-bump-discount-coupon', true );
			$bump_order_image = Cartflows_Helper::get_image_url( $order_bump_id, 'wcf-order-bump-image', 'medium' );

			if ( ! empty( $_POST['post_data'] ) ) {

				$post_data = array();

				$post_raw_data = sanitize_text_field( wp_unslash( $_POST['post_data'] ) );

				parse_str( $post_raw_data, $post_data );

				if ( ! empty( $post_data['wcf-bump-order-cb'] ) ) {
					$order_bump_checked = true;
				}

				$post_data = null;
			}

			// Chcek if bump order already added in the cart.
			if ( $this->cart_has_product( $product_id, true ) ) {
				$order_bump_checked = true;
			}

			$bump_offer_arr = array(
				'product_id'       => $product_id,
				'product_quantity' => $order_bump_product_quantity,
				'discount_type'    => $discount_type,
				'discount_value'   => $discount_value,
				'discount_coupon'  => $discount_coupon,
				'parent_id'        => $product_id,
				'is_variable'      => 'no',
				'is_variation'     => 'no',
			);

			$_product = wc_get_product( $product_id );

			if ( ! empty( $_product ) ) {

				if ( $_product->is_type( 'variable' ) ) {

					$bump_offer_arr['is_variable'] = 'yes';
					$bump_offer_arr['parent_id']   = $product_id;

					$default_attributes = $_product->get_default_attributes();

					if ( ! empty( $default_attributes ) ) {

						foreach ( $_product->get_children() as $c_in => $variation_id ) {

							if ( 0 === $c_in ) {
								$bump_offer_arr['product_id'] = $variation_id;
							}

							$single_variation = new WC_Product_Variation( $variation_id );

							if ( $default_attributes == $single_variation->get_attributes() ) {

								$bump_offer_arr['product_id'] = $variation_id;
								break;
							}
						}
					} else {

						$product_childrens = $_product->get_children();

						if ( is_array( $product_childrens ) ) {

							foreach ( $product_childrens  as $c_in => $c_id ) {

								$bump_offer_arr['product_id'] = $c_id;
								break;
							}
						}
					}
				}

				if ( $_product->is_type( 'variation' ) ) {

					$bump_offer_arr['is_variation'] = 'yes';
					$bump_offer_arr['parent_id']    = $_product->get_parent_id();
				}
			}

			$bump_offer_data = wp_json_encode( $bump_offer_arr );

			/* Set new ids based on variation */
			$product_id = $bump_offer_arr['product_id'];
			$parent_id  = $bump_offer_arr['parent_id'];

			$order_bump_pos = wcf()->options->get_checkout_meta_value( $order_bump_id, 'wcf-order-bump-position' );

			/* bump order blinking arrow */
			$is_order_bump_arrow_enabled      = wcf()->options->get_checkout_meta_value( $order_bump_id, 'wcf-show-bump-arrow' );
			$is_order_bump_arrow_anim_enabled = wcf()->options->get_checkout_meta_value( $order_bump_id, 'wcf-show-bump-animate-arrow' );

			$bump_order_blinking_arrow = '';
			$bump_order_arrow_animate  = '';

			if ( 'yes' === $is_order_bump_arrow_enabled ) {

				if ( 'yes' === $is_order_bump_arrow_anim_enabled ) {
					$bump_order_arrow_animate = 'wcf-blink';
				}

				$bump_order_blinking_arrow = '<svg version="1.1" class="wcf-pointing-arrow ' . $bump_order_arrow_animate . '" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="20px" height="15px" fill="red" viewBox="310 253 90 70" enable-background="new 310 253 90 70" xml:space="preserve"><g><g><path d="M364.348,253.174c-0.623,0.26-1.029,0.867-1.029,1.54v18.257h-51.653c-0.919,0-1.666,0.747-1.666,1.666v26.658
								c0,0.92,0.747,1.666,1.666,1.666h51.653v18.327c0,0.673,0.406,1.28,1.026,1.54c0.623,0.257,1.34,0.116,1.816-0.36l33.349-33.238 c0.313-0.313,0.49-0.737,0.49-1.18c0-0.443-0.177-0.866-0.487-1.179l-33.349-33.335 C365.688,253.058,364.971,252.915,364.348,253.174z"/></g></g></svg>';
			}
			/* bump order blinking arrow */

			/* Execute */
			$order_bump_desc = do_shortcode( $order_bump_desc );

			ob_start();
			if ( ! empty( $bump_layout ) && 'style-1' === $bump_layout ) {

				include CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/bump-order/wcf-bump-order-style-1.php';
			} else {
				include CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/bump-order/wcf-bump-order-style-2.php';
			}

			$output .= ob_get_clean();

			/** $output .= '<h1>Bump Order</h1>'; */
		}

		echo $output;
	}

	/**
	 * Process bump order.
	 */
	public function order_bump_process() {

		$nonce = filter_input( INPUT_POST, 'security', FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, 'wcf_bump_order_process' ) ) {
			return;
		}

		$post_data = $_POST;

		if ( ! isset( $post_data['_wcf_bump_product_action'] ) ||
			( isset( $post_data['_wcf_bump_product_action'] ) && empty( $post_data['_wcf_bump_product_action'] ) )
		) {
			return;
		}

		$checkout_id = intval( $post_data['_wcf_checkout_id'] );
		$bump_action = sanitize_text_field( $post_data['_wcf_bump_product_action'] );

		// Check if checkout page is global checkout.
		$common             = Cartflows_Helper::get_common_settings();
		$is_global_checkout = false;
		if ( intval( $common['global_checkout'] ) === $checkout_id ) {
			$is_global_checkout = true;
		}

		if ( 'add_bump_product' === $bump_action ) {
			$checked = true;
		} elseif ( 'remove_bump_product' === $bump_action ) {
			$checked = false;
		} else {
			return;
		}

		$order_bump_data = $this->get_order_bump_data( $checkout_id );

		if ( empty( $order_bump_data ) ) {
			return;
		}

		/* Set new ids based on variation */
		$product_id = intval( $order_bump_data['product_id'] );
		$parent_id  = intval( $order_bump_data['parent_id'] );
		$_product   = wc_get_product( $product_id );

		$discount_coupon = $order_bump_data['discount_coupon'];
		$new_key         = '';

		if ( is_array( $discount_coupon ) && ! empty( $discount_coupon ) ) {
			$discount_coupon = reset( $discount_coupon );
		}

		$ob_data = array(
			'checkout_id'     => $checkout_id,
			'product_id'      => $product_id,
			'parent_id'       => $parent_id,
			'is_variable'     => sanitize_text_field( $order_bump_data['is_variable'] ),
			'is_variation'    => sanitize_text_field( $order_bump_data['is_variation'] ),

			'_product'        => $_product,
			'_product_price'  => floatval( $_product->get_regular_price( 'edit' ) ),

			'discount_type'   => $order_bump_data['discount_type'],
			'discount_value'  => floatval( $order_bump_data['discount_value'] ),
			'discount_coupon' => $discount_coupon,
			'custom_price'    => '',
			'order_bump_qty'  => intval( $order_bump_data['product_quantity'] ),
			'is_replace'      => $order_bump_data['is_replace'],
			'index'           => 0,
			'checked'         => $checked,
		);

		// If replace main product with order bump option is enabled.
		if ( 'yes' === $ob_data['is_replace'] && ! $is_global_checkout ) {
			$this->replace_main_product_with_order_bump( $ob_data );
		}

		// Loop over cart items.
		$found_data       = $this->get_item_key_for_order_bump( $ob_data );
		$found_item_key   = $found_data['found_item_key'];
		$found_item       = $found_data['found_item'];
		$discount_enabled = $found_data['discount_enabled'];

		// Bump offer product found in cart and we need to add it.
		if ( null != $found_item_key && $checked ) {
			$this->order_bump_found_in_cart( $ob_data, $found_item_key, $found_item, $discount_enabled );
		}

		// add - if not found, remove/reduce - if found.
		if ( $checked && null === $found_item_key ) {
			$this->order_bump_not_found_in_cart( $ob_data );

		} elseif ( ! $checked && null != $found_item_key ) {
			$this->order_bump_remove_or_reduce( $ob_data, $found_item_key, $found_item );
		}
		wp_send_json( wcf_pro()->utils->get_fragments( $new_key ) );
	}
	/**
	 * Order bump remove or reduce.
	 *
	 * @param array  $ob_data order bump data.
	 * @param string $found_item_key cart key.
	 * @param array  $found_item item data.
	 */
	public function order_bump_remove_or_reduce( $ob_data, $found_item_key, $found_item ) {

		$new_qty = $found_item['quantity'] - $ob_data['order_bump_qty'];

		WC()->cart->remove_cart_item( $found_item_key );

		do_action( 'wcf_order_bump_item_removed', $ob_data['product_id'] );

		if ( $new_qty > 0 ) {

			if ( 'yes' === $ob_data['is_variable'] || 'yes' === $ob_data['is_variation'] ) {
				WC()->cart->add_to_cart( $ob_data['parent_id'], $new_qty, $ob_data['product_id'] );
			} else {
				WC()->cart->add_to_cart( $ob_data['parent_id'], $new_qty );
			}
		}

		if ( ! empty( $ob_data['discount_coupon'] ) ) {
			if ( WC()->cart->has_discount( $ob_data['discount_coupon'] ) ) {
				WC()->cart->remove_coupon( $ob_data['discount_coupon'] );
			}
		}
	}

	/**
	 * If order bump not found in cart.
	 *
	 * @param array $ob_data order bump data.
	 */
	public function order_bump_not_found_in_cart( $ob_data ) {

		$custom_price = '';

		if ( 'coupon' === $ob_data['discount_type'] ) {
			$apply_coupon = wcf_pro()->utils->apply_discount_coupon( $ob_data['discount_type'], $ob_data['discount_coupon'] );
		} else {
			$custom_price = wcf_pro()->utils->get_calculated_discount( $ob_data['discount_type'], $ob_data['discount_value'], $ob_data['_product_price'] );
		}

		$cart_item_data = array(
			'cartflows_bump' => true,
		);

		if ( isset( $custom_price ) && ( '' !== $custom_price ) ) {

			$cart_item_data = array(
				'custom_price'   => $custom_price,
				'cartflows_bump' => true,
			);
		}

		if ( 'yes' === $ob_data['is_variable'] || 'yes' === $ob_data['is_variation'] ) {
			WC()->cart->add_to_cart( $ob_data['parent_id'], $ob_data['order_bump_qty'], $ob_data['product_id'], array(), $cart_item_data );
		} else {
			WC()->cart->add_to_cart( $ob_data['product_id'], $ob_data['order_bump_qty'], 0, array(), $cart_item_data );
		}

		do_action( 'wcf_order_bump_item_added', $ob_data['product_id'] );
	}

	/**
	 * If order bump found in cart..
	 *
	 * @param array  $ob_data order bump data.
	 * @param string $found_item_key key.
	 * @param array  $found_item item data.
	 * @param bool   $discount_enabled is discount.
	 */
	public function order_bump_found_in_cart( $ob_data, $found_item_key, $found_item, $discount_enabled ) {

		// Case for discount enabled bump offer product.
		if ( $discount_enabled && 'coupon' !== $ob_data['discount_type'] ) {

			$custom_price = wcf_pro()->utils->get_calculated_discount( $ob_data['discount_type'], $ob_data['discount_value'], $ob_data['_product_price'] );

			$cart_item_data = array(
				'cartflows_bump' => true,
			);

			if ( isset( $custom_price ) ) {

				$cart_item_data = array(
					'custom_price'   => $custom_price,
					'cartflows_bump' => true,
				);
			}
			$new_key = '';
			if ( 'yes' === $ob_data['is_variable'] || 'yes' === $ob_data['is_variation'] ) {
				WC()->cart->add_to_cart( $ob_data['parent_id'], $ob_data['order_bump_qty'], $ob_data['product_id'], array(), $cart_item_data );
			} else {
				WC()->cart->add_to_cart( $ob_data['product_id'], $ob_data['order_bump_qty'], 0, array(), $cart_item_data );
			}

			do_action( 'wcf_order_bump_item_added', $ob_data['product_id'] );

		} else {

			if ( $discount_enabled && 'coupon' === $ob_data['discount_type'] ) {
				$apply_coupon = wcf_pro()->utils->apply_discount_coupon( $ob_data['discount_type'], $ob_data['discount_coupon'] );
			}

			$quantity = isset( $found_item['quantity'] ) ? $found_item['quantity'] : 0;
			$new_qty  = $quantity + $ob_data['order_bump_qty'];

			// If item is already in cart, increase quantity for product in cart.
			WC()->cart->remove_cart_item( $found_item_key );

			if ( $ob_data['_product']->is_in_stock() ) {

				$cart_item_data = array(
					'cartflows_bump' => true,
				);

				if ( 'yes' === $ob_data['is_variable'] || 'yes' === $ob_data['is_variation'] ) {
					WC()->cart->add_to_cart( $ob_data['parent_id'], $new_qty, $ob_data['product_id'], array(), $cart_item_data );
				} else {
					WC()->cart->add_to_cart( $ob_data['product_id'], $new_qty, 0, array(), $cart_item_data );
				}

				do_action( 'wcf_order_bump_item_added', $ob_data['product_id'] );
			}
		}
	}

	/**
	 * Get the item keu for order bump.
	 *
	 * @param array $ob_data order bump data.
	 */
	public function get_item_key_for_order_bump( $ob_data ) {

		$discount_enabled = false;
		$found_item_key   = null;
		$found_item       = null;

		foreach ( WC()->cart->get_cart() as $key => $item ) {

			// For variable product.
			if ( 'yes' === $ob_data['is_variable'] || 'yes' === $ob_data['is_variation'] ) {

				// Check if bump product is variation OR variable.
				if ( ( $item['product_id'] === $ob_data['parent_id'] && $item['variation_id'] === $ob_data['product_id'] )
				|| ( $item['product_id'] === $ob_data['product_id'] && $item['variation_id'] === $ob_data['product_id'] ) ) {

					if ( ! $ob_data['checked'] ) {

						if ( isset( $item['cartflows_bump'] ) ) {

							$found_item_key = $key;
							$found_item     = $item;

							if ( ! empty( $ob_data['discount_type'] ) ) {
								$discount_enabled = true;
							}
							break;
						}
					} else {

						$found_item_key = $key;
						$found_item     = $item;

						if ( ! empty( $ob_data['discount_type'] ) ) {
							$discount_enabled = true;
						}

						break;
					}
				}
			} else {

				// if same product is already in cart.
				if ( $item['product_id'] === $ob_data['product_id'] ) {

					if ( ! $ob_data['checked'] ) {

						if ( isset( $item['cartflows_bump'] ) ) {

							$found_item_key = $key;
							$found_item     = $item;

							if ( ! empty( $ob_data['discount_type'] ) ) {
								$discount_enabled = true;
							}

							break;
						}
					} else {

						$found_item_key = $key;
						$found_item     = $item;

						if ( ! empty( $ob_data['discount_type'] ) ) {
							$discount_enabled = true;
						}

						break;
					}
				}
			}
		}

		$found_data = array(
			'found_item_key'   => $found_item_key,
			'found_item'       => $found_item,
			'discount_enabled' => $discount_enabled,

		);

		return $found_data;
	}

	/**
	 * Replace the main product with order bump.
	 *
	 * @param array $ob_data order bump data.
	 */
	public function replace_main_product_with_order_bump( $ob_data ) {

		$main_products    = Cartflows_Pro_Variation_Product::get_instance()->get_all_main_products( $ob_data['checkout_id'] );
		$first_product    = $main_products[ $ob_data['index'] ];
		$first_product_id = intval( $first_product['product'] );
		$_product_data    = wc_get_product( $first_product['product'] );
		$cart_item_data   = array();

		if ( $ob_data['checked'] ) {

			// remove first product.
			foreach ( WC()->cart->get_cart() as $key => $item ) {
				if ( $first_product_id === $item['product_id'] || $first_product_id === $item['variation_id'] ) {
					WC()->cart->remove_cart_item( $key );
				}
			}
		} else {
			// add first product.
			$custom_price = wcf_pro()->utils->get_calculated_discount( $first_product['discount_type'], $first_product['discount_value'], $_product_data->get_price() );

			if ( ! empty( $custom_price ) ) {

				$cart_item_data = array(
					'custom_price' => $custom_price,
				);
			}
			if ( true === $first_product['variable'] || true === $first_product['variation'] ) {

				if ( true === $first_product['variable'] ) {
					$children_ids = $_product_data->get_children();
					$child        = $children_ids[0];
					$new_key      = WC()->cart->add_to_cart( $ob_data['parent_id'], $first_product['quantity'], $child, array(), $cart_item_data );
				} else {
					$parent_id = $_product_data->get_parent_id();
					$new_key   = WC()->cart->add_to_cart( $parent_id, $first_product['quantity'], $first_product['product'], array(), $cart_item_data );
				}
			} else {
				$new_key = WC()->cart->add_to_cart( $first_product['product'], $first_product['quantity'], 0, array(), $cart_item_data );
			}
		}
	}

	/**
	 * Preserve the custom item price added by Variations & Quantity feature
	 *
	 * @param array $cart_object cart object.
	 * @since 1.0.0
	 */
	public function custom_price_to_cart_item( $cart_object ) {

		if ( wp_doing_ajax() && ! WC()->session->__isset( 'reload_checkout' ) ) {

			foreach ( $cart_object->cart_contents as $key => $value ) {

				if ( isset( $value['custom_price'] ) ) {

					$custom_price = floatval( $value['custom_price'] );
					$value['data']->set_price( $custom_price );
				}
			}
		}
	}

	/**
	 * Bump order product title shortcode.
	 *
	 * @param array $atts shortcode atts.
	 * @return string shortcode output.
	 * @since 1.0.0
	 */
	public function bump_product_title( $atts ) {

		$output = '';
		if ( _is_wcf_checkout_type() ) {

			global $post;

			$order_bump_product = get_post_meta( $post->ID, 'wcf-order-bump-product', true );

			if ( ! empty( $order_bump_product ) ) {

				$product_id = reset( $order_bump_product );

				$output = get_the_title( $product_id );
			}
		}

		return $output;
	}

	/**
	 * Bump order product title shortcode.
	 *
	 * @param int $step_id step id.
	 * @return array bump order product.
	 * @since 1.0.0
	 */
	public function get_bump_test_product( $step_id ) {

		$bump_product = array();

		$args = array(
			'posts_per_page' => 1,
			'orderby'        => 'rand',
			'post_type'      => 'product',
			'meta_query'     => array(// phpcs:ignore
				// Exclude out of stock products.
				array(
					'key'     => '_stock_status',
					'value'   => 'outofstock',
					'compare' => 'NOT IN',
				),
			),
			'tax_query'      => array( //phpcs:ignore
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => 'simple',
				),
			),
		);

		$random_product = get_posts( $args );

		if ( isset( $random_product[0]->ID ) ) {
			$bump_product = array(
				$random_product[0]->ID,
			);
		}

		return $bump_product;
	}

	/**
	 * Check in Cart if product exists.
	 *
	 * @since 1.1.5
	 * @param int  $product_id product_id.
	 * @param bool $is_bump is bump product.
	 * @return bool.
	 * */
	public function cart_has_product( $product_id, $is_bump = false ) {

		$get_cart = WC()->cart->get_cart();

		foreach ( $get_cart as $cart_item_key => $cart_item ) {

			if ( $cart_item['product_id'] == $product_id ) {

				if ( $is_bump ) {

					if ( isset( $cart_item['cartflows_bump'] ) && $cart_item['cartflows_bump'] ) {
						return true;
					}
				} else {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get order bump data
	 *
	 * @param int $checkout_id checkout ID.
	 * @return array
	 */
	public function get_order_bump_data( $checkout_id ) {

		$bump_data = array();

		$is_bump                     = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump' );
		$order_bump_product          = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-product' );
		$order_bump_product_quantity = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-product-quantity' );
		$discount_type               = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-discount' );
		$discount_value              = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-discount-value' );
		$discount_coupon             = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-discount-coupon' );
		$is_replace                  = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-replace' );

		if ( empty( $order_bump_product ) ) {
			return $bump_data;
		}

		$product_id = reset( $order_bump_product );

		$bump_data = array(
			'product_id'       => $product_id,
			'product_quantity' => $order_bump_product_quantity,
			'discount_type'    => $discount_type,
			'discount_value'   => $discount_value,
			'discount_coupon'  => $discount_coupon,
			'parent_id'        => $product_id,
			'is_variable'      => 'no',
			'is_variation'     => 'no',
			'is_replace'       => $is_replace,
		);

		$_product = wc_get_product( $product_id );

		if ( ! empty( $_product ) ) {

			if ( $_product->is_type( 'variable' ) ) {

				$bump_data['is_variable'] = 'yes';
				$bump_data['parent_id']   = $product_id;

				$default_attributes = $_product->get_default_attributes();

				if ( ! empty( $default_attributes ) ) {

					foreach ( $_product->get_children() as $c_in => $variation_id ) {

						if ( 0 === $c_in ) {
							$bump_data['product_id'] = $variation_id;
						}

						$single_variation = new WC_Product_Variation( $variation_id );

						if ( $default_attributes == $single_variation->get_attributes() ) {

							$bump_data['product_id'] = $variation_id;
							break;
						}
					}
				} else {

					$product_childrens = $_product->get_children();

					if ( is_array( $product_childrens ) ) {

						foreach ( $product_childrens  as $c_in => $c_id ) {

							$bump_data['product_id'] = $c_id;
							break;
						}
					}
				}
			}

			if ( $_product->is_type( 'variation' ) ) {

				$bump_data['is_variation'] = 'yes';
				$bump_data['parent_id']    = $_product->get_parent_id();
			}
		}

		return $bump_data;
	}
}


/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Order_Bump_Product::get_instance();
