<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Cartflows Pro Checkout Form extend File
 *
 * @since x.x.x
 * @package cartflows-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Cartflows_Pro_BB_Checkout_Form_Extend' ) ) {

	/**
	 * This class initializes Checkout Form
	 *
	 * @class Cartflows_Pro_BB_Checkout_Form_Extend
	 */
	final class Cartflows_Pro_BB_Checkout_Form_Extend {

		/**
		 * Initializes module.
		 *
		 * @since x.x.x
		 * @return void
		 */
		public static function init() {

			add_action( 'init', __CLASS__ . '::dynamic_options_ajax_filters', 1 );

			// Apply dynamic option filters.
			add_action( 'cartflows_bb_checkout_options_filters', __CLASS__ . '::bb_checkout_dynamic_filters', 10, 1 );
			add_filter( 'fl_builder_register_settings_form', __CLASS__ . '::bb_checkout_form_extend', 1010, 2 );
			add_filter( 'fl_builder_render_css', __CLASS__ . '::bb_checkout_form_css', 10, 3 );
			add_filter( 'fl_builder_render_js', __CLASS__ . '::bb_checkout_form_js', 10, 3 );
		}

		/**
		 * Dynamic options ajax filters actions.
		 */
		public static function dynamic_options_ajax_filters() {

			add_action(
				'cartflows_woo_checkout_update_order_review_init',
				function( $post_data ) {

					if ( ! empty( $post_data['_wcf_order_bump_skin'] ) ) {

						$ob_options = array(
							// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
							// array(
							// 'filter_slug'  => 'wcf-order-bump-position',
							// 'setting_name' => 'order_bump_position',
							// ),.
							array(
								'filter_slug'  => 'wcf-order-bump-style',
								'setting_name' => 'order_bump_skin',
							),
							// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
							// array(
							// 'filter_slug'  => 'wcf-order-bump-label',
							// 'setting_name' => 'order_bump_checkbox_label',
							// ),
							// array(
							// 'filter_slug'  => 'wcf-order-bump-hl-text',
							// 'setting_name' => 'order_bump_highlight_text',
							// ),
							// array(
							// 'filter_slug'  => 'wcf-order-bump-desc',
							// 'setting_name' => 'order_bump_product_description',
							// ),.
							array(
								'filter_slug'  => 'wcf-show-bump-arrow',
								'setting_name' => 'order_bump_checkbox_arrow',
							),
							array(
								'filter_slug'  => 'wcf-show-bump-animate-arrow',
								'setting_name' => 'order_bump_checkbox_arrow_animation',
							),
						);

						foreach ( $ob_options as $ob_option ) {

							$setting_name = '_wcf_' . $ob_option['setting_name'];

							if ( ! empty( $post_data[ $setting_name ] ) ) {

								$setting_value = $post_data[ $setting_name ];

								add_filter(
									'cartflows_checkout_meta_' . $ob_option['filter_slug'],
									function ( $value ) use ( $setting_value ) {

										$value = sanitize_textarea_field( wp_unslash( $setting_value ) );

										return $value;
									},
									10,
									1
								);
							}
						}
					}
				},
				1,
				1
			);

		}

		/**
		 * Dynamic filters.
		 *
		 * @param array $settings Settings array.
		 *
		 * @since x.x.x
		 */
		public static function bb_checkout_dynamic_filters( $settings ) {

			$checkout_id            = get_the_ID();
			$enable_product_options = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-enable-product-options' );
			$enable_order_bump      = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump' );
			$enable_checkout_offer  = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-pre-checkout-offer' );

			// Controls filters.
			$checkout_fields = array(
				array(
					'filter_slug'  => 'wcf-checkout-layout',
					'setting_name' => 'checkout_layout',
				),
				// Input Fields.
				array(
					'filter_slug'  => 'wcf-fields-skins',
					'setting_name' => 'input_skins',
				),
				// Two step texts.
				array(
					'filter_slug'  => 'wcf-checkout-step-one-title',
					'setting_name' => 'step_one_title_text',
				),
				array(
					'filter_slug'  => 'wcf-checkout-step-one-sub-title',
					'setting_name' => 'step_one_sub_title_text',
				),
				array(
					'filter_slug'  => 'wcf-checkout-step-two-title',
					'setting_name' => 'step_two_title_text',
				),
				array(
					'filter_slug'  => 'wcf-checkout-step-two-sub-title',
					'setting_name' => 'step_two_sub_title_text',
				),

				array(
					'filter_slug'  => 'wcf-checkout-offer-button-title',
					'setting_name' => 'offer_button_title_text',
				),
				array(
					'filter_slug'  => 'wcf-checkout-offer-button-sub-title',
					'setting_name' => 'offer_button_subtitle_text',
				),
			);

			if ( isset( $checkout_fields ) && is_array( $checkout_fields ) ) {

				foreach ( $checkout_fields as $key => $field ) {

					$setting_name  = $field['setting_name'];
					$setting_value = $settings->$setting_name;

					if ( '' !== $setting_value ) {

						add_filter(
							'cartflows_checkout_meta_' . $field['filter_slug'],
							function ( $value ) use ( $setting_value ) {

								$value = $setting_value;

								return $value;
							},
							10,
							1
						);
					}
				}
			}

			add_filter(
				'cartflows_checkout_meta_wcf-checkout-box-note',
				function ( $is_note_enabled ) use ( $settings ) {

					$is_note_enabled = ( 'yes' === $settings->enable_note ) ? 'yes' : 'no';
					return $is_note_enabled;
				},
				10,
				1
			);

			if ( 'yes' === $settings->enable_note && '' !== $settings->note_text ) {

				add_filter(
					'cartflows_checkout_meta_wcf-checkout-box-note-text',
					function ( $checkout_note_text ) use ( $settings ) {
						$checkout_note_text = $settings->note_text;
						return $checkout_note_text;
					},
					10,
					1
				);
			}

			if ( 'yes' === $enable_order_bump ) {

				/* Order bump fields */
				$order_bump_fields = array(
					// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
					// Order Bump.
					// array(
					// 'filter_slug'  => 'wcf-order-bump-position',
					// 'setting_name' => 'order_bump_position',
					// ),.
					array(
						'filter_slug'  => 'wcf-order-bump-style',
						'setting_name' => 'order_bump_skin',
					),
					// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
					// array(
					// 'filter_slug'  => 'wcf-order-bump-label',
					// 'setting_name' => 'order_bump_checkbox_label',
					// ),
					// array(
					// 'filter_slug'  => 'wcf-order-bump-hl-text',
					// 'setting_name' => 'order_bump_highlight_text',
					// ),
					// array(
					// 'filter_slug'  => 'wcf-order-bump-desc',
					// 'setting_name' => 'order_bump_product_description',
					// ),.
					array(
						'filter_slug'  => 'wcf-show-bump-arrow',
						'setting_name' => 'order_bump_checkbox_arrow',
					),
					array(
						'filter_slug'  => 'wcf-show-bump-animate-arrow',
						'setting_name' => 'order_bump_checkbox_arrow_animation',
					),
				);

				if ( isset( $order_bump_fields ) && is_array( $order_bump_fields ) ) {

					foreach ( $order_bump_fields as $key => $field ) {

						$setting_name  = $field['setting_name'];
						$setting_value = $settings->$setting_name;

						if ( '' !== $setting_value ) {
							add_filter(
								'cartflows_checkout_meta_' . $field['filter_slug'],
								function ( $value ) use ( $setting_value ) {

									$value = $setting_value;

									return $value;
								},
								10,
								1
							);
						}
					}
				}

				add_action(
					'woocommerce_after_order_notes',
					function () use ( $settings ) {

						$ob_options = array(
							// 'order_bump_position',
							'order_bump_skin',
							// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
							// 'order_bump_checkbox_label',
							// 'order_bump_highlight_text',
							// 'order_bump_product_description',
							'order_bump_checkbox_arrow',
							'order_bump_checkbox_arrow_animation',
						);

						foreach ( $ob_options as $ob_option ) {
							if ( ! empty( $settings->$ob_option ) ) {
								echo '<input type="hidden" class="input-hidden" name="_wcf_' . $ob_option . '" value="' . esc_attr( $settings->$ob_option ) . '">';
							}
						}
					},
					99
				);
			}

			// Product options.
			if ( 'yes' === $enable_product_options ) {

				$product_options_fields = array(
					array(
						'filter_slug'  => 'wcf-product-options-skin',
						'setting_name' => 'product_options_skin',
					),
					array(
						'filter_slug'  => 'wcf-show-product-images',
						'setting_name' => 'product_options_images',
					),
					array(
						'filter_slug'  => 'wcf-product-opt-title',
						'setting_name' => 'product_option_section_title_text',
					),
					array(
						'filter_slug'  => 'wcf-your-products-position',
						'setting_name' => 'product_options_position',
					),
				);

				if ( isset( $product_options_fields ) && is_array( $product_options_fields ) ) {

					foreach ( $product_options_fields as $key => $field ) {

						$setting_name  = $field['setting_name'];
						$setting_value = $settings->$setting_name;

						if ( '' !== $setting_value ) {

							add_filter(
								'cartflows_checkout_meta_' . $field['filter_slug'],
								function ( $value ) use ( $setting_value ) {

									$value = $setting_value;

									return $value;
								},
								10,
								1
							);
						}
					}
				}
			}

			// Checkout offer.
			if ( 'yes' === $enable_checkout_offer ) {
				$checkout_offer_fields = array(
					array(
						'filter_slug'  => 'wcf-pre-checkout-offer-button-title',
						'setting_name' => 'checkout_offer_button_title_text',
					),
					array(
						'filter_slug'  => 'wcf-pre-checkout-offer-button-sub-title',
						'setting_name' => 'checkout_offer_button_subtitle_text',
					),
					// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
					// array(
					// 'filter_slug'  => 'wcf-pre-checkout-offer-popup-title',
					// 'setting_name' => 'checkout_offer_title_text',
					// ),
					// array(
					// 'filter_slug'  => 'wcf-pre-checkout-offer-popup-sub-title',
					// 'setting_name' => 'checkout_offer_subtitle_text',
					// ),
					// array(
					// 'filter_slug'  => 'wcf-pre-checkout-offer-product-title',
					// 'setting_name' => 'checkout_offer_product_name',
					// ),
					// array(
					// 'filter_slug'  => 'wcf-pre-checkout-offer-desc',
					// 'setting_name' => 'checkout_offer_product_desc',
					// ),
					// array(
					// 'filter_slug'  => 'wcf-pre-checkout-offer-popup-btn-text',
					// 'setting_name' => 'checkout_offer_accept_button_text',
					// ),
					// array(
					// 'filter_slug'  => 'wcf-pre-checkout-offer-popup-skip-btn-text',
					// 'setting_name' => 'checkout_offer_skip_button_text',
					// ),.
				);

				if ( isset( $checkout_offer_fields ) && is_array( $checkout_offer_fields ) ) {

					foreach ( $checkout_offer_fields as $key => $field ) {

						$setting_name  = $field['setting_name'];
						$setting_value = $settings->$setting_name;

						if ( isset( $setting_value ) && ( '' !== $setting_value ) ) {
							add_filter(
								'cartflows_checkout_meta_' . $field['filter_slug'],
								function ( $value ) use ( $setting_value ) {

									$value = $setting_value;

									return $value;
								},
								10,
								1
							);
						}
					}
				}
			}

			add_filter(
				'cartflows_bb_checkout_settings',
				function ( $data_settings ) use ( $settings, $enable_checkout_offer, $enable_product_options, $enable_order_bump ) {

					$data_settings = array(
						// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
						// 'title_text'             => $settings->checkout_offer_title_text,
						// 'subtitle_text'          => $settings->checkout_offer_subtitle_text,
						// 'product_name'           => $settings->checkout_offer_product_name,
						// 'product_desc'           => $settings->checkout_offer_product_desc,
						// 'accept_button_text'     => $settings->checkout_offer_accept_button_text,
						// 'skip_button_text'       => $settings->checkout_offer_skip_button_text,
						'enable_checkout_offer'  => $enable_checkout_offer,
						'enable_product_options' => $enable_product_options,
						'enable_order_bump'      => $enable_order_bump,
					);

					return $data_settings;
				},
				10,
				1
			);

		}

		/**
		 * Extending row setting.
		 *
		 * @param array  $form Settings array.
		 * @param string $id module id.
		 *
		 * @since x.x.x
		 */
		public static function bb_checkout_form_extend( $form, $id ) {

			if ( 'cartflows-bb-checkout-form' == $id ) {

				// Content related options.
				$form['general']['sections']['two_step'] = array(
					'title'  => 'Two Step',
					'fields' => array(
						'enable_note'                => array(
							'type'    => 'select',
							'label'   => __( 'Enable Checkout Note', 'cartflows-pro' ),
							'default' => 'yes',
							'options' => array(
								'yes' => __( 'Yes', 'cartflows-pro' ),
								'no'  => __( 'No', 'cartflows-pro' ),
							),
							'toggle'  => array(
								'yes' => array(
									'fields' => array( 'note_text' ),
								),
							),
						),
						'note_text'                  => array(
							'type'        => 'text',
							'label'       => __( 'Note Text', 'cartflows-pro' ),
							'placeholder' => __( 'Get Your FREE copy of CartFlows in just few steps.', 'cartflows-pro' ),
							'connections' => array( 'string' ),
						),
						'step_one_title_text'        => array(
							'type'        => 'text',
							'label'       => __( 'Step One Title', 'cartflows-pro' ),
							'placeholder' => __( 'Shipping', 'cartflows-pro' ),
							'connections' => array( 'string' ),
						),
						'step_one_sub_title_text'    => array(
							'type'        => 'text',
							'label'       => __( 'Step One Sub Title', 'cartflows-pro' ),
							'placeholder' => __( 'Where to ship it?', 'cartflows-pro' ),
							'connections' => array( 'string' ),
						),
						'step_two_title_text'        => array(
							'type'        => 'text',
							'label'       => __( 'Step Two Title', 'cartflows-pro' ),
							'placeholder' => __( 'Payment', 'cartflows-pro' ),
							'connections' => array( 'string' ),
						),
						'step_two_sub_title_text'    => array(
							'type'        => 'text',
							'label'       => __( 'Step Two Sub Title', 'cartflows-pro' ),
							'placeholder' => __( 'Of your order', 'cartflows-pro' ),
							'connections' => array( 'string' ),
						),
						'offer_button_title_text'    => array(
							'type'        => 'text',
							'label'       => __( 'Offer Button Title', 'cartflows-pro' ),
							'placeholder' => __( 'For Special Offer Click Here', 'cartflows-pro' ),
							'connections' => array( 'string' ),
						),
						'offer_button_subtitle_text' => array(
							'type'        => 'text',
							'label'       => __( 'Offer Button Sub Title', 'cartflows-pro' ),
							'placeholder' => __( 'Yes! I want this offer!', 'cartflows-pro' ),
							'connections' => array( 'string' ),
						),
					),
				);

				$form['general']['sections']['product_options'] = array(
					'title'  => 'Product Options',
					'fields' => array(
						'product_options_position' => array(
							'type'        => 'select',
							'label'       => __( 'Select Position', 'cartflows-pro' ),
							'default'     => 'after-customer',
							'options'     => array(
								'before-customer' => __( 'Before Checkout', 'cartflows-pro' ),
								'after-customer'  => __( 'After Customer Details', 'cartflows-pro' ),
								'before-order'    => __( 'Before Order Review', 'cartflows-pro' ),
							),
							/* translators: %s: link */
							'description' => sprintf(
								/* translators: %1$1s, %2$2s Link to meta */
								__( 'Please enable "Product-Option" from %1$1shere%2$2s to edit options.', 'cartflows-pro' ),
								'<a href="' . Cartflows_Pro_Helper::get_current_page_edit_url( 'product-option' ) . '" target="_blank" class="cartflows-bb-note">',
								'</a>'
							),
						),
						'product_options_skin'     => array(
							'type'    => 'select',
							'label'   => __( 'Skin', 'cartflows-pro' ),
							'default' => 'classic',
							'options' => array(
								'classic' => __( 'Classic', 'cartflows-pro' ),
								'cards'   => __( 'Cards', 'cartflows-pro' ),
							),
						),
						'product_options_images'   => array(
							'type'    => 'select',
							'label'   => __( 'Show Product Images', 'cartflows-pro' ),
							'default' => 'no',
							'options' => array(
								'yes' => __( 'Yes', 'cartflows-pro' ),
								'no'  => __( 'No', 'cartflows-pro' ),
							),
						),
						'product_option_section_title_text' => array(
							'type'        => 'text',
							'label'       => __( 'Section Title Text', 'cartflows-pro' ),
							'placeholder' => __( 'Your Products', 'cartflows-pro' ),
							'connections' => array( 'string' ),
						),
					),
				);
				// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
				// $form['general']['sections']['order_bump'] = array(
				// 'title'  => 'Order Bump',
				// 'fields' => array(
						// 'order_bump_position'            => array(
						// 'type'        => 'select',
						// 'label'       => __( 'Select Position', 'cartflows-pro' ),
						// 'default'     => 'after-order',
						// 'options'     => array(
						// 'before-checkout' => __( 'Before Checkout', 'cartflows-pro' ),
						// 'after-customer'  => __( 'After Customer Details', 'cartflows-pro' ),
						// 'after-order'     => __( 'After Order', 'cartflows-pro' ),
						// 'after-payment'   => __( 'After Payment', 'cartflows-pro' ),
						// ),
						// * translators: %s: link */
						// 'description' => sprintf(
						// * translators: %1$1s, %2$2s Link to meta */
						// __( 'Please enable "Order Bump" from %1$1shere%2$2s to edit options.', 'cartflows-pro' ),
						// '<a href="' . Cartflows_Pro_Helper::get_current_page_edit_url( 'order-bump' ) . '" target="_blank" class="cartflows-bb-note">',
						// '</a>'
						// ),
						// ),
						// 'order_bump_checkbox_label'      => array(
						// 'type'        => 'text',
						// 'label'       => __( 'Checkbox Label', 'cartflows-pro' ),
						// 'placeholder' => __( 'Yes, I will take it!', 'cartflows-pro' ),
						// 'connections' => array( 'string' ),
						// ),
						// 'order_bump_highlight_text'      => array(
						// 'type'        => 'text',
						// 'label'       => __( 'Highlight Text', 'cartflows-pro' ),
						// 'placeholder' => __( 'ONE TIME OFFER', 'cartflows-pro' ),
						// 'connections' => array( 'string' ),
						// ),
						// 'order_bump_product_description' => array(
						// 'type'        => 'editor',
						// 'label'       => __( 'Product Description', 'cartflows-pro' ),
						// 'placeholder' => __( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut, quod hic expedita consectetur vitae nulla sint adipisci cupiditate at. Commodi, dolore hic eaque tempora a repudiandae obcaecati deleniti mollitia possimus.', 'cartflows-pro' ),
						// 'connections' => array( 'string' ),
						// ),
						// 'order_bump_checkbox_arrow' => array(
						// 'type'    => 'select',
						// 'label'   => __( 'Enable Arrow', 'cartflows-pro' ),
						// 'default' => 'no',
						// 'options' => array(
						// 'yes' => __( 'Yes', 'cartflows-pro' ),
						// 'no'  => __( 'No', 'cartflows-pro' ),
						// ),
						// ),
						// 'order_bump_checkbox_arrow_animation' => array(
						// 'type'    => 'select',
						// 'label'   => __( 'Enable Arrow Animation', 'cartflows-pro' ),
						// 'default' => 'no',
						// 'options' => array(
						// 'yes' => __( 'Yes', 'cartflows-pro' ),
						// 'no'  => __( 'No', 'cartflows-pro' ),
						// ),
						// ),
				// ),
				// );
				// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
				// $form['general']['sections']['pre_checkout_offer_section'] = array(
				// 'title'  => 'Pre Checkout Offer',
				// 'fields' => array(
				// 'pre_checkout_enable_preview'     => array(
				// 'type'        => 'select',
				// 'label'       => __( 'Enable Preview', 'cartflows-pro' ),
				// 'default'     => 'no',
				// 'options'     => array(
				// 'yes' => __( 'Yes', 'cartflows-pro' ),
				// 'no'  => __( 'No', 'cartflows-pro' ),
				// ),
				// 'preview'     => array(
				// 'type' => 'refresh',
				// ),
				// * translators: %s: link */
				// 'description' => sprintf(
				// * translators: %1$1s, %2$2s Link to meta */
				// __( 'Please enable "Pre Checkout Offer" from %1$1shere%2$2s to edit options.', 'cartflows-pro' ),
				// '<a href="' . Cartflows_Pro_Helper::get_current_page_edit_url( 'checkout-offer' ) . '" target="_blank" class="cartflows-bb-note">',
				// '</a>'
				// ),
				// ),
						// 'checkout_offer_title_text'       => array(
						// 'type'        => 'text',
						// 'label'       => __( 'Title Text', 'cartflows-pro' ),
						// 'placeholder' => __( '{first_name}, Wait! Your Order Is Almost Complete...', 'cartflows-pro' ),
						// 'connections' => array( 'string' ),
						// 'preview'     => array(
						// 'type' => 'refresh',
						// ),
						// ),
						// 'checkout_offer_subtitle_text'    => array(
						// 'type'        => 'text',
						// 'label'       => __( 'Sub-Title Text', 'cartflows-pro' ),
						// 'placeholder' => __( 'We have a special one time offer just for you.', 'cartflows-pro' ),
						// 'connections' => array( 'string' ),
						// 'preview'     => array(
						// 'type' => 'refresh',
						// ),
						// ),
						// 'checkout_offer_product_name'     => array(
						// 'type'        => 'text',
						// 'label'       => __( 'Product Title', 'cartflows-pro' ),
						// 'placeholder' => __( 'Product Name', 'cartflows-pro' ),
						// 'connections' => array( 'string' ),
						// 'preview'     => array(
						// 'type' => 'refresh',
						// ),
						// ),
						// 'checkout_offer_product_desc'     => array(
						// 'type'        => 'text',
						// 'label'       => __( 'Product Description', 'cartflows-pro' ),
						// 'placeholder' => __( 'Write a few words about this awesome product and tell shoppers why they must get it. You may highlight this as "one time offer" and make it irresistible.', 'cartflows-pro' ),
						// 'connections' => array( 'string' ),
						// 'preview'     => array(
						// 'type' => 'refresh',
						// ),
						// ),
						// 'checkout_offer_accept_button_text' => array(
						// 'type'        => 'text',
						// 'label'       => __( 'Order Button Text', 'cartflows-pro' ),
						// 'placeholder' => __( 'Yes, Add to My Order!', 'cartflows-pro' ),
						// 'connections' => array( 'string' ),
						// 'preview'     => array(
						// 'type' => 'refresh',
						// ),
						// ),
						// 'checkout_offer_skip_button_text' => array(
						// 'type'        => 'text',
						// 'label'       => __( 'Skip Button Text', 'cartflows-pro' ),
						// 'placeholder' => __( 'No, thanks!', 'cartflows-pro' ),
						// 'connections' => array( 'string' ),
						// 'preview'     => array(
						// 'type' => 'refresh',
						// ),
						// ),
				// ),
				// );

				// Style options.
				$form['style']['sections']['two_step_style'] = array(
					'title'  => __( 'Two Step', 'cartflows-pro' ),
					'fields' => array(
						'note_text_color' => array(
							'type'        => 'color',
							'label'       => __( 'Note Text Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-embed-checkout-form-note',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'note_bg_color'   => array(
							'type'        => 'color',
							'label'       => __( 'Note Background Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-embed-checkout-form-note',
								'property' => 'background-color',
								'unit'     => 'px',
							),
						),
						'note_typography' => array(
							'type'       => 'typography',
							'label'      => __( 'Note Typography', 'cartflows-pro' ),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.wcf-embed-checkout-form-note',
							),
						),
					),
				);

				$form['style']['sections']['product_style'] = array(
					'title'  => __( 'Product Options', 'cartflows-pro' ),
					'fields' => array(
						'product_text_color'              => array(
							'type'        => 'color',
							'label'       => __( 'Text Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-qty-options .wcf-qty-row',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'product_bg_color'                => array(
							'type'        => 'color',
							'label'       => __( 'Background Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-product-option-wrap.wcf-yp-skin-classic .wcf-qty-options,
    							.wcf-product-option-wrap.wcf-yp-skin-cards .wcf-qty-options .wcf-qty-row',
								'property' => 'background-color',
								'unit'     => 'px',
							),
						),
						'product_option_border_style'     => array(
							'type'    => 'select',
							'label'   => __( 'Border Style', 'cartflows-pro' ),
							'default' => 'none',
							'help'    => __( 'The type of border to use. Double borders must have a width of at least 3px to render properly.', 'cartflows-pro' ),
							'options' => array(
								'none'   => __( 'None', 'cartflows-pro' ),
								'solid'  => __( 'Solid', 'cartflows-pro' ),
								'dashed' => __( 'Dashed', 'cartflows-pro' ),
								'dotted' => __( 'Dotted', 'cartflows-pro' ),
								'double' => __( 'Double', 'cartflows-pro' ),
							),
							'toggle'  => array(
								'solid'  => array(
									'fields' => array( 'product_option_border_size', 'product_option_border_radius', 'product_option_border_color' ),
								),
								'dashed' => array(
									'fields' => array( 'product_option_border_size', 'product_option_border_radius', 'product_option_border_color' ),
								),
								'dotted' => array(
									'fields' => array( 'product_option_border_size', 'product_option_border_radius', 'product_option_border_color' ),
								),
								'double' => array(
									'fields' => array( 'product_option_border_size', 'product_option_border_radius', 'product_option_border_color' ),
								),
							),
							'preview' => array(
								'type'     => 'css',
								'selector' => '.wcf-product-option-wrap.wcf-yp-skin-classic .wcf-qty-options,
    							.wcf-product-option-wrap.wcf-yp-skin-cards .wcf-qty-options .wcf-qty-row',
								'property' => 'border-style',
							),
						),
						'product_option_border_size'      => array(
							'type'        => 'unit',
							'label'       => __( 'Border Width', 'cartflows-pro' ),
							'slider'      => true,
							'units'       => array( 'px' ),
							'maxlength'   => '3',
							'size'        => '6',
							'placeholder' => '1',
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-product-option-wrap.wcf-yp-skin-classic .wcf-qty-options,
    							.wcf-product-option-wrap.wcf-yp-skin-cards .wcf-qty-options .wcf-qty-row',
								'property' => 'border-width',
								'unit'     => 'px',
							),
						),
						'product_option_border_color'     => array(
							'type'       => 'color',
							'label'      => __( 'Border Color', 'cartflows-pro' ),
							'default'    => '',
							'show_reset' => true,
							'show_alpha' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.wcf-product-option-wrap.wcf-yp-skin-classic .wcf-qty-options,
    							.wcf-product-option-wrap.wcf-yp-skin-cards .wcf-qty-options .wcf-qty-row',
								'property' => 'border-color',
								'unit'     => 'px',
							),
						),
						'product_option_border_radius'    => array(
							'type'        => 'unit',
							'label'       => __( 'Border Radius', 'cartflows-pro' ),
							'slider'      => true,
							'units'       => array( 'px' ),
							'maxlength'   => '3',
							'size'        => '6',
							'placeholder' => '0',
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-product-option-wrap.wcf-yp-skin-classic .wcf-qty-options,
    							.wcf-product-option-wrap.wcf-yp-skin-cards .wcf-qty-options .wcf-qty-row',
								'property' => 'border-radius',
								'unit'     => 'px',
							),
						),
						'highlight_product_bg_color'      => array(
							'type'        => 'color',
							'label'       => __( 'Highlight Product Background Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-qty-options .wcf-qty-row.wcf-highlight',
								'property' => 'background-color',
								'unit'     => 'px',
							),
						),
						'highlight_product_text_color'    => array(
							'type'        => 'color',
							'label'       => __( 'Highlight Product Text Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-qty-options .wcf-qty-row.wcf-highlight',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'highlight_product_border_style'  => array(
							'type'    => 'select',
							'label'   => __( 'Highlight Product Border Style', 'cartflows-pro' ),
							'default' => 'none',
							'help'    => __( 'The type of border to use. Double borders must have a width of at least 3px to render properly.', 'cartflows-pro' ),
							'options' => array(
								'none'   => __( 'None', 'cartflows-pro' ),
								'solid'  => __( 'Solid', 'cartflows-pro' ),
								'dashed' => __( 'Dashed', 'cartflows-pro' ),
								'dotted' => __( 'Dotted', 'cartflows-pro' ),
								'double' => __( 'Double', 'cartflows-pro' ),
							),
							'toggle'  => array(
								'solid'  => array(
									'fields' => array( 'highlight_product_border_size', 'highlight_product_border_radius', 'highlight_product_border_color' ),
								),
								'dashed' => array(
									'fields' => array( 'highlight_product_border_size', 'highlight_product_border_radius', 'highlight_product_border_color' ),
								),
								'dotted' => array(
									'fields' => array( 'highlight_product_border_size', 'highlight_product_border_radius', 'highlight_product_border_color' ),
								),
								'double' => array(
									'fields' => array( 'highlight_product_border_size', 'highlight_product_border_radius', 'highlight_product_border_color' ),
								),
							),
							'preview' => array(
								'type'     => 'css',
								'selector' => '.wcf-qty-options .wcf-qty-row.wcf-highlight',
								'property' => 'border-style',
							),
						),
						'highlight_product_border_size'   => array(
							'type'        => 'unit',
							'label'       => __( 'Border Width', 'cartflows-pro' ),
							'slider'      => true,
							'units'       => array( 'px' ),
							'maxlength'   => '3',
							'size'        => '6',
							'placeholder' => '1',
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-qty-options .wcf-qty-row.wcf-highlight',
								'property' => 'border-width',
								'unit'     => 'px',
							),
						),
						'highlight_product_border_color'  => array(
							'type'       => 'color',
							'label'      => __( 'Border Color', 'cartflows-pro' ),
							'default'    => '',
							'show_reset' => true,
							'show_alpha' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.wcf-qty-options .wcf-qty-row.wcf-highlight',
								'property' => 'border-color',
								'unit'     => 'px',
							),
						),
						'highlight_product_border_radius' => array(
							'type'        => 'unit',
							'label'       => __( 'Border Radius', 'cartflows-pro' ),
							'slider'      => true,
							'units'       => array( 'px' ),
							'maxlength'   => '3',
							'size'        => '6',
							'placeholder' => '0',
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-qty-options .wcf-qty-row.wcf-highlight',
								'property' => 'border-radius',
								'unit'     => 'px',
							),
						),
						'highlight_flag_text_color'       => array(
							'type'        => 'color',
							'label'       => __( 'Highlight Flag Text Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-qty-options .wcf-qty-row.wcf-highlight .wcf-highlight-head',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'highlight_flag_bg_color'         => array(
							'type'        => 'color',
							'label'       => __( 'Highlight Flag Background Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-qty-options .wcf-qty-row.wcf-highlight .wcf-highlight-head',
								'property' => 'background-color',
								'unit'     => 'px',
							),
						),
					),
				);

				$form['style']['sections']['order_bump_style'] = array(
					'title'  => __( 'Order Bump', 'cartflows-pro' ),
					'fields' => array(
						'order_bump_skin'           => array(
							'type'    => 'select',
							'label'   => __( 'Skin', 'cartflows-pro' ),
							'default' => 'style-1',
							'options' => array(
								'style-1' => __( 'Style 1', 'cartflows-pro' ),
								'style-2' => __( 'Style 2', 'cartflows-pro' ),
							),
						),
						'order_bump_checkbox_arrow' => array(
							'type'    => 'select',
							'label'   => __( 'Enable Arrow', 'cartflows-pro' ),
							'default' => 'no',
							'options' => array(
								'yes' => __( 'Yes', 'cartflows-pro' ),
								'no'  => __( 'No', 'cartflows-pro' ),
							),
						),
						'order_bump_checkbox_arrow_animation' => array(
							'type'    => 'select',
							'label'   => __( 'Enable Arrow Animation', 'cartflows-pro' ),
							'default' => 'no',
							'options' => array(
								'yes' => __( 'Yes', 'cartflows-pro' ),
								'no'  => __( 'No', 'cartflows-pro' ),
							),
						),
						'order_bump_bg_color'       => array(
							'type'        => 'color',
							'label'       => __( 'Background Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-bump-order-wrap',
								'property' => 'background-color',
								'unit'     => 'px',
							),
						),
						'order_bump_label_color'    => array(
							'type'        => 'color',
							'label'       => __( 'Label Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-bump-order-field-wrap label',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'order_bump_label_bg_color' => array(
							'type'        => 'color',
							'label'       => __( 'Label Background Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-bump-order-wrap .wcf-bump-order-field-wrap',
								'property' => 'background-color',
								'unit'     => 'px',
							),
						),
						'order_bump_hl_text_color'  => array(
							'type'        => 'color',
							'label'       => __( 'Highlight Text Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-bump-order-wrap .wcf-bump-order-bump-highlight',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'order_bump_desc_color'     => array(
							'type'        => 'color',
							'label'       => __( 'Description Text Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-bump-order-wrap .wcf-bump-order-desc',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'order_bump_border_style'   => array(
							'type'    => 'select',
							'label'   => __( 'Border Style', 'cartflows-pro' ),
							'default' => 'none',
							'help'    => __( 'The type of border to use. Double borders must have a width of at least 3px to render properly.', 'cartflows-pro' ),
							'options' => array(
								'default' => __( 'Default', 'cartflows-pro' ),
								'solid'   => __( 'Solid', 'cartflows-pro' ),
								'dashed'  => __( 'Dashed', 'cartflows-pro' ),
								'dotted'  => __( 'Dotted', 'cartflows-pro' ),
								'double'  => __( 'Double', 'cartflows-pro' ),
							),
							'toggle'  => array(
								'solid'  => array(
									'fields' => array( 'order_bump_border_size', 'order_bump_border_color' ),
								),
								'dashed' => array(
									'fields' => array( 'order_bump_border_size', 'order_bump_border_color' ),
								),
								'dotted' => array(
									'fields' => array( 'order_bump_border_size', 'order_bump_border_color' ),
								),
								'double' => array(
									'fields' => array( 'order_bump_border_size', 'order_bump_border_color' ),
								),
							),
						),
						'order_bump_border_size'    => array(
							'type'        => 'unit',
							'label'       => __( 'Border Width', 'cartflows-pro' ),
							'slider'      => true,
							'units'       => array( 'px' ),
							'maxlength'   => '3',
							'size'        => '6',
							'placeholder' => '1',
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-bump-order-wrap,
    							.wcf-bump-order-wrap .wcf-bump-order-field-wrap',
								'property' => 'border-width',
								'unit'     => 'px',
							),
						),
						'order_bump_border_color'   => array(
							'type'       => 'color',
							'label'      => __( 'Border Color', 'cartflows-pro' ),
							'default'    => '',
							'show_reset' => true,
							'show_alpha' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.wcf-bump-order-wrap,
    							.wcf-bump-order-wrap .wcf-bump-order-field-wrap',
								'property' => 'border-color',
								'unit'     => 'px',
							),
						),
						'order_bump_border_radius'  => array(
							'type'        => 'unit',
							'label'       => __( 'Border Radius', 'cartflows-pro' ),
							'slider'      => true,
							'units'       => array( 'px' ),
							'maxlength'   => '3',
							'size'        => '6',
							'placeholder' => '0',
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-bump-order-wrap',
								'property' => 'border-radius',
								'unit'     => 'px',
							),
						),
					),
				);

				$form['style']['sections']['pre_checkout_offer_style'] = array(
					'title'  => __( 'Pre Checkout Offer', 'cartflows-pro' ),
					'fields' => array(
						'pre_checkout_enable_preview'   => array(
							'type'        => 'select',
							'label'       => __( 'Enable Preview', 'cartflows-pro' ),
							'default'     => 'no',
							'options'     => array(
								'yes' => __( 'Yes', 'cartflows-pro' ),
								'no'  => __( 'No', 'cartflows-pro' ),
							),
							'preview'     => array(
								'type' => 'refresh',
							),
							/* translators: %s: link */
							'description' => sprintf(
								/* translators: %1$1s, %2$2s Link to meta */
								__( 'Please enable "Pre Checkout Offer" from %1$1shere%2$2s to edit options.', 'cartflows-pro' ),
								'<a href="' . Cartflows_Pro_Helper::get_current_page_edit_url( 'checkout-offer' ) . '" target="_blank" class="cartflows-bb-note">',
								'</a>'
							),
						),
						'pre_checkout_overlay_bg_color' => array(
							'type'        => 'color',
							'label'       => __( 'Overlay Background Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
						),
						'pre_checkout_bg_color'         => array(
							'type'        => 'color',
							'label'       => __( 'Modal Background Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
						),
						'pre_checkout_title_color'      => array(
							'type'        => 'color',
							'label'       => __( 'Title Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
						),
						'pre_checkout_sub_title_color'  => array(
							'type'        => 'color',
							'label'       => __( 'Subtitle Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
						),
						'pre_checkout_desc_color'       => array(
							'type'        => 'color',
							'label'       => __( 'Description Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
						),
					),
				);
			}

			return $form;
		}

		/**
		 * Extending row setting.
		 *
		 * @param file  $css gets the Modules CSS.
		 * @param array $nodes Modules array.
		 * @param array $global_settings module global settings.
		 *
		 * @since x.x.x
		 */
		public static function bb_checkout_form_css( $css, $nodes, $global_settings ) {

			foreach ( $nodes['modules'] as $module_object ) {

				if ( 'cartflows-bb-checkout-form' === $module_object->slug ) {

					$module_id       = $module_object->node;
					$module_settings = $module_object->settings;
					$module_slug     = $module_object->slug;

					ob_start();
					include CARTFLOWS_PRO_DIR . 'modules/beaver-builder/cartflows-pro-bb-checkout-form-extend/includes/frontend-css.php';
					$css .= ob_get_clean();
				}
			}

			return $css;
		}

		/**
		 * Render Global uabb-layout-builder js
		 *
		 * @since x.x.x
		 * @param file   $js Gets the js file contents.
		 * @param array  $nodes Gets the nodes of the layout builder.
		 * @param object $global_settings Gets the object for the Layout builder.
		 */
		public static function bb_checkout_form_js( $js, $nodes, $global_settings ) {

			return $js;
		}

		/**
		 * Need to look into it. Render Global uabb-layout-builder js
		 *
		 * @since x.x.x
		 * @param file   $js Gets the js file contents.
		 * @param array  $nodes Gets the nodes of the layout builder.
		 * @param object $global_settings Gets the object for the Layout builder.
		 */
		public static function del_bb_checkout_form_js( $js, $nodes, $global_settings ) {
			// Causing problem at front.
			foreach ( $nodes['modules'] as $key => $cf_module ) {

				if ( 'cartflows-bb-checkout-form' === $cf_module->slug ) {

					ob_start();
					include CARTFLOWS_PRO_DIR . 'modules/beaver-builder/cartflows-pro-bb-checkout-form-extend/js/settings.js';
					$js .= ob_get_clean();

					ob_start();
					include CARTFLOWS_PRO_DIR . 'modules/beaver-builder/cartflows-pro-bb-checkout-form-extend/js/settings-js.php';
					$js .= ob_get_clean();

				}
			}

			return $js;

		}

	}

	Cartflows_Pro_BB_Checkout_Form_Extend::init();
}
