<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Elementor Classes.
 *
 * @package cartflows
 */

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;   // Exit if accessed directly.
}

/**
 * Checkout Form Widget
 *
 * @since x.x.x
 */
class Cartflows_Pro_Checkout_Form_Extend {


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
	 * Settings
	 *
	 * @since x.x.x
	 * @var object $settings
	 */
	public static $settings;

	/**
	 * Checkout Settings
	 *
	 * @since x.x.x
	 * @var object $checkout_settings
	 */
	public static $checkout_settings;

	/**
	 * Setup actions and filters.
	 *
	 * @since x.x.x
	 */
	private function __construct() {

		/* Init ajax options */
		add_action( 'init', array( $this, 'dynamic_options_ajax_filters' ), 20 );

		// Apply dynamic option filters.
		add_action( 'cartflows_elementor_checkout_options_filters', array( $this, 'dynamic_filters' ), 10, 2 );

		// Add two step control sections.
		add_action( 'elementor/element/checkout-form/section_general_fields/after_section_end', array( $this, 'register_two_step_section_controls' ), 10, 2 );

		// Add section for the product option.
		add_action( 'elementor/element/checkout-form/section_general_fields/after_section_end', array( $this, 'register_product_option_section_controls' ), 10, 2 );

		add_action( 'elementor/element/checkout-form/section_payment_style_fields/after_section_end', array( $this, 'register_two_step_style_controls' ), 10, 2 );

		// phpcs:ignore Squiz.PHP.CommentedOutCode.Found add_action( 'elementor/element/checkout-form/section_general_fields/after_section_end', array( $this, 'register_order_bump_section_controls' ), 10, 2 );

		// Add section for the checkout offer.
		// add_action( 'elementor/element/checkout-form/section_general_fields/after_section_end', array( $this, 'register_checkout_offer_section_controls' ), 10, 2 );.

		// Product options control sections.
		add_action( 'elementor/element/checkout-form/section_payment_style_fields/after_section_end', array( $this, 'register_product_options_style_controls' ), 10, 2 );

		// Order bump control sections.
		add_action( 'elementor/element/checkout-form/section_payment_style_fields/after_section_end', array( $this, 'register_order_bump_style_controls' ), 10, 2 );

		// Pre checkout offer control sections.
		add_action( 'elementor/element/checkout-form/section_payment_style_fields/after_section_end', array( $this, 'register_checkout_offer_style_controls' ), 10, 2 );
	}

	/**
	 * Dynamic options ajax filters actions.
	 */
	public function dynamic_options_ajax_filters() {

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

									$value = sanitize_text_field( wp_unslash( $setting_value ) );

									return $value;
								},
								10,
								1
							);
						}
					}
				}
			}
		);
	}

	/**
	 * Register Two Step Navigation Button Controls.
	 *
	 * @param array $elementor element data.
	 * @param array $args data.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	public function register_two_step_section_controls( $elementor, $args ) {

		$elementor->start_controls_section(
			'section_two_step_section_fields',
			array(
				'label'     => __( 'Two Step', 'cartflows-pro' ),
				'condition' => array(
					'layout' => 'two-step',
				),
			)
		);

		$elementor->add_control(
			'enable_note',
			array(
				'label'        => __( 'Enable Checkout Note', 'cartflows-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'YES', 'cartflows-pro' ),
				'label_off'    => __( 'NO', 'cartflows-pro' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$elementor->add_control(
			'note_text',
			array(
				'label'       => __( 'Note Text', 'cartflows-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Get Your FREE copy of CartFlows in just few steps.', 'cartflows-pro' ),
				'label_block' => false,
				'condition'   => array(
					'enable_note' => 'yes',
				),
			)
		);

		$elementor->add_control(
			'two_step_section_heading',
			array(
				'label'     => __( 'Steps', 'cartflows-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$elementor->add_control(
			'step_one_title_text',
			array(
				'label'       => __( 'Step One Title', 'cartflows-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Shipping', 'cartflows-pro' ),
			)
		);

		$elementor->add_control(
			'step_one_sub_title_text',
			array(
				'label'       => __( 'Step One Sub Title', 'cartflows-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Where to ship it?', 'cartflows-pro' ),
			)
		);

		$elementor->add_control(
			'step_two_title_text',
			array(
				'label'       => __( 'Step Two Title', 'cartflows-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Payment', 'cartflows-pro' ),
			)
		);

		$elementor->add_control(
			'step_two_sub_title_text',
			array(
				'label'       => __( 'Step Two Sub Title', 'cartflows-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Of your order', 'cartflows-pro' ),
			)
		);

		$elementor->add_control(
			'offer_button_section',
			array(
				'label'     => __( 'Offer Button', 'cartflows-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$elementor->add_control(
			'offer_button_title_text',
			array(
				'label'       => __( 'Offer Button Title', 'cartflows-pro' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => __( 'For Special Offer Click Here', 'cartflows-pro' ),
			)
		);

		$elementor->add_control(
			'offer_button_subtitle_text',
			array(
				'label'       => __( 'Offer Button Sub Title', 'cartflows-pro' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => __( 'Yes! I want this offer!', 'cartflows-pro' ),
			)
		);

		$elementor->end_controls_section();
	}

	/**
	 * Register Two Step style Controls.
	 *
	 * @param array $elementor element data.
	 * @param array $args data.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	public function register_two_step_style_controls( $elementor, $args ) {

		$elementor->start_controls_section(
			'section_two_step_style_fields',
			array(
				'label'     => __( 'Two Step', 'cartflows-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'enable_note' => 'yes',
					'layout'      => 'two-step',
				),
			)
		);

		$elementor->add_control(
			'note_text_color',
			array(
				'label'     => __( 'Note Text Color', 'cartflows-pro' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'enable_note' => 'yes',
					'layout'      => 'two-step',
				),
			)
		);

		$elementor->add_control(
			'note_bg_color',
			array(
				'label'     => __( 'Note Background Color', 'cartflows-pro' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note
						' => 'background-color: {{VALUE}} !important; border-color: {{VALUE}};',
					'{{WRAPPER}} .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note:before' => 'border-top-color: {{VALUE}};',
				),
				'condition' => array(
					'enable_note' => 'yes',
					'layout'      => 'two-step',
				),
			)
		);

		$elementor->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'note_typography',
				'label'     => __( 'Note Typography', 'cartflows-pro' ),
				'selector'  => '{{WRAPPER}} .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note',
				'condition' => array(
					'enable_note' => 'yes',
					'layout'      => 'two-step',
				),
			)
		);

		$elementor->end_controls_section();
	}

	/**
	 * Register product option section Controls.
	 *
	 * @param array $elementor element data.
	 * @param array $args data.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	public function register_product_option_section_controls( $elementor, $args ) {

		$checkout_id = get_the_id();
		$is_enabled  = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-enable-product-options' );

			$elementor->start_controls_section(
				'section_product_option_section_fields',
				array(
					'label' => __( 'Product Options', 'cartflows-pro' ),
				)
			);

		if ( 'yes' === $is_enabled ) {

			$elementor->add_control(
				'product_options_position',
				array(
					'label'   => __( 'Position', 'cartflows-pro' ),
					'type'    => Controls_Manager::SELECT,
					'default' => '',
					'options' => array(
						'before-customer' => __( 'Before Checkout', 'cartflows-pro' ),
						'after-customer'  => __( 'After Customer Details', 'cartflows-pro' ),
						'before-order'    => __( 'Before Order Review', 'cartflows-pro' ),
					),
				)
			);

			$elementor->add_control(
				'product_options_skin',
				array(
					'label'   => __( 'Skin', 'cartflows-pro' ),
					'type'    => Controls_Manager::SELECT,
					'default' => '',
					'options' => array(
						'classic' => __( 'Classic', 'cartflows-pro' ),
						'cards'   => __( 'Cards', 'cartflows-pro' ),
					),
				)
			);

			$elementor->add_control(
				'product_options_images',
				array(
					'label'   => __( 'Show Product Images', 'cartflows-pro' ),
					'type'    => Controls_Manager::SELECT,
					'default' => '',
					'options' => array(
						'yes' => __( 'Yes', 'cartflows-pro' ),
						'no'  => __( 'No', 'cartflows-pro' ),
					),
				)
			);

			$elementor->add_control(
				'product_option_section_title_text',
				array(
					'label'       => __( 'Section Title Text', 'cartflows-pro' ),
					'type'        => Controls_Manager::TEXT,
					'placeholder' => __( 'Your Products', 'cartflows-pro' ),
				)
			);
		} else {

			$elementor->add_control(
				'product_option_disabled',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => sprintf(
						/* translators: %1$1s, %2$2s Link to meta */
						__( 'Please enable "Product Options" from %1$1smeta settings%2$2s to edit options.', 'cartflows-pro' ),
						'<a href="' . Cartflows_Pro_Helper::get_current_page_edit_url( 'product-option' ) . '" target="_blank">',
						'</a>'
					),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
				)
			);
		}
		$elementor->end_controls_section();

	}

	/**
	 * Register product options Style Controls.
	 *
	 * @param array $elementor element data.
	 * @param array $args data.
	 *
	 * @since x.x.x
	 */
	public function register_product_options_style_controls( $elementor, $args ) {

		$checkout_id = get_the_id();
		$is_enabled  = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-enable-product-options' );

		$elementor->start_controls_section(
			'product_options_style_fields',
			array(
				'label' => __( 'Product Options', 'cartflows-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		if ( 'yes' === $is_enabled ) {

			$elementor->add_control(
				'product_text_color',
				array(
					'label'     => __( 'Text Color', 'cartflows-pro' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row' => 'color: {{VALUE}};',
					),
				)
			);

			$elementor->add_control(
				'product_bg_color',
				array(
					'label'     => __( 'Background Color', 'cartflows-pro' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap.wcf-yp-skin-classic .wcf-qty-options' => 'background-color: {{VALUE}};',
						'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap.wcf-yp-skin-cards .wcf-qty-options .wcf-qty-row' => 'background-color: {{VALUE}};',
					),
				)
			);

			$elementor->add_control(
				'product_option_border_style',
				array(
					'label'       => __( 'Border Style', 'cartflows-pro' ),
					'type'        => Controls_Manager::SELECT,
					'label_block' => false,
					'default'     => '',
					'options'     => array(
						''       => __( 'Default', 'cartflows-pro' ),
						'solid'  => __( 'Solid', 'cartflows-pro' ),
						'double' => __( 'Double', 'cartflows-pro' ),
						'dotted' => __( 'Dotted', 'cartflows-pro' ),
						'dashed' => __( 'Dashed', 'cartflows-pro' ),
					),
					'selectors'   => array(
						'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options' => 'border-style: {{VALUE}};',
					),
				)
			);

			$elementor->add_control(
				'product_option_border_size',
				array(
					'label'      => __( 'Border Width', 'cartflows-pro' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px' ),
					'selectors'  => array(
						'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$elementor->add_control(
				'product_option_border_color',
				array(
					'label'     => __( 'Border Color', 'cartflows-pro' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options' => 'border-color: {{VALUE}};',

					),
				)
			);

			$elementor->add_control(
				'product_option_border_radius',
				array(
					'label'      => __( 'Rounded Corners', 'cartflows-pro' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', 'em', '%' ),
					'selectors'  => array(
						'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			// Highlight Product CSS options.

			$elementor->add_control(
				'highlight_product',
				array(
					'label'     => __( 'Highlight Product', 'cartflows-pro' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$elementor->add_control(
				'highlight_product_bg_color',
				array(
					'label'     => __( 'Background Color', 'cartflows-pro' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight' => 'background-color: {{VALUE}};',
					),
				)
			);

			$elementor->add_control(
				'highlight_product_text_color',
				array(
					'label'     => __( 'Text Color', 'cartflows-pro' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight' => 'color: {{VALUE}};',
					),
				)
			);

			$elementor->add_control(
				'highlight_product_option_border_style',
				array(
					'label'       => __( 'Border Style', 'cartflows-pro' ),
					'type'        => Controls_Manager::SELECT,
					'label_block' => false,
					'default'     => '',
					'options'     => array(
						''       => __( 'Default', 'cartflows-pro' ),
						'solid'  => __( 'Solid', 'cartflows-pro' ),
						'double' => __( 'Double', 'cartflows-pro' ),
						'dotted' => __( 'Dotted', 'cartflows-pro' ),
						'dashed' => __( 'Dashed', 'cartflows-pro' ),
					),
					'selectors'   => array(
						'.wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight' => 'border-style: {{VALUE}};',
					),
				)
			);

			$elementor->add_control(
				'highlight_product_border_size',
				array(
					'label'      => __( 'Border Width', 'cartflows-pro' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px' ),
					'selectors'  => array(
						'.wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$elementor->add_control(
				'highlight_box_border_color',
				array(
					'label'     => __( 'Border Color', 'cartflows-pro' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight' => 'border-color: {{VALUE}};',

					),
				)
			);

			$elementor->add_control(
				'highlight_product_border_radius',
				array(
					'label'      => __( 'Rounded Corners', 'cartflows-pro' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', 'em', '%' ),
					'selectors'  => array(
						'.wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$elementor->add_control(
				'highlight_flag_text_color',
				array(
					'label'     => __( 'Highlight Flag Text Color', 'cartflows-pro' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight .wcf-highlight-head' => 'color: {{VALUE}};',
					),
				)
			);

			$elementor->add_control(
				'highlight_flag_bg_color',
				array(
					'label'     => __( 'Highlight Flag Background Color', 'cartflows-pro' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight .wcf-highlight-head' => 'background-color: {{VALUE}};',
					),
				)
			);
		} else {
			$elementor->add_control(
				'product_option_style_disabled',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => sprintf(
						/* translators: %1$1s, %2$2s Link to meta */
						__( 'Please enable "Product Options" from %1$1smeta settings%2$2s to apply styles.', 'cartflows-pro' ),
						'<a href="' . Cartflows_Pro_Helper::get_current_page_edit_url( 'product-option' ) . '" target="_blank">',
						'</a>'
					),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
				)
			);
		}

		$elementor->end_controls_section();
	}

	/**
	 * Register order bump section Controls.
	 *
	 * @param array $elementor element data.
	 * @param array $args data.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	/* phpcs:ignore Squiz.PHP.CommentedOutCode.Found
	public function register_order_bump_section_controls( $elementor, $args ) {

	$checkout_id = get_the_id();
	$is_enabled  = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump' );

	$elementor->start_controls_section(
	'section_order_bump_section_fields',
	array(
	'label' => __( 'Order Bump', 'cartflows-pro' ),
	)
	);

	if ( 'yes' === $is_enabled ) {

			$elementor->add_control(
			'order_bump_position',
			array(
			'label'   => __( 'Position', 'cartflows-pro' ),
			'type'    => Controls_Manager::SELECT,
			'default' => '',
			'options' => array(
			'before-checkout' => __( 'Before Checkout', 'cartflows-pro' ),
			'after-customer'  => __( 'After Customer Details', 'cartflows-pro' ),
			'after-order'     => __( 'After Order', 'cartflows-pro' ),
			'after-payment'   => __( 'After Payment', 'cartflows-pro' ),
			),
			)
			);

			$elementor->add_control(
			'order_bump_checkbox_label',
			array(
			'label'       => __( 'Checkbox Label', 'cartflows-pro' ),
			'type'        => Controls_Manager::TEXT,
			'placeholder' => __( 'Yes, I will take it!', 'cartflows-pro' ),
			)
			);

			$elementor->add_control(
			'order_bump_highlight_text',
			array(
			'label'       => __( 'Highlight Text', 'cartflows-pro' ),
			'type'        => Controls_Manager::TEXT,
			'placeholder' => __( 'ONE TIME OFFER', 'cartflows-pro' ),
			)
			);

			$elementor->add_control(
			'order_bump_product_description',
			array(
			'label'       => __( 'Product Description', 'cartflows-pro' ),
			'type'        => Controls_Manager::TEXTAREA,
			'placeholder' => __( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut, quod hic expedita consectetur vitae nulla sint adipisci cupiditate at. Commodi, dolore hic eaque tempora a repudiandae obcaecati deleniti mollitia possimus.', 'cartflows-pro' ),
			)
			);
	$elementor->add_control(
	'order_bump_checkbox_arrow',
	array(
	'label'        => __( 'Enable Arrow', 'cartflows-pro' ),
	'type'         => Controls_Manager::SWITCHER,
	'label_on'     => 'On',
	'label_off'    => 'Off',
	'return_value' => 'yes',
	'default'      => 'no',
	)
	);

	$elementor->add_control(
	'order_bump_checkbox_arrow_animation',
	array(
	'label'        => __( 'Enable Arrow Animation', 'cartflows-pro' ),
	'type'         => Controls_Manager::SWITCHER,
	'label_on'     => 'On',
	'label_off'    => 'Off',
	'return_value' => 'yes',
	'default'      => 'no',
	)
	);
	} else {
	$elementor->add_control(
	'order_bump_disabled',
	array(
	'type'            => Controls_Manager::RAW_HTML,
	'raw'             => sprintf(
	* translators: %1$1s, %2$2s Link to meta */
	// __( 'Please enable "Order Bump" from %1$1smeta settings%2$2s to edit options.', 'cartflows-pro' ),
	// '<a href="' . Cartflows_Pro_Helper::get_current_page_edit_url( 'order-bump' ) . '" target="_blank">',
	// '</a>'
	// ),
	// 'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
	// )
	// );
	// }
	// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
	// $elementor->end_controls_section();
	// }


	/**
	 * Register order bump Style Controls.
	 *
	 * @param array $elementor element data.
	 * @param array $args data.
	 *
	 * @since x.x.x
	 */
	public function register_order_bump_style_controls( $elementor, $args ) {

		$checkout_id = get_the_id();
		$is_enabled  = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump' );

		$elementor->start_controls_section(
			'order_bump_style_fields',
			array(
				'label' => __( 'Order Bump', 'cartflows-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		if ( 'yes' === $is_enabled ) {

			$elementor->add_control(
				'order_bump_skin_fields_text',
				array(
					'label'       => __( 'Design', 'cartflows-pro' ),
					'type'        => Controls_Manager::HEADING,
					'label_block' => true,
				)
			);

			$elementor->add_control(
				'order_bump_skin',
				array(
					'label'     => __( 'Skin', 'cartflows-pro' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => '',
					'options'   => array(
						'style-1' => __( 'Style 1', 'cartflows-pro' ),
						'style-2' => __( 'Style 2', 'cartflows-pro' ),
					),
					'separator' => 'after',
				)
			);

			$elementor->add_control(
				'order_bump_arrow_fields_text',
				array(
					'label'       => __( 'Arrow', 'cartflows-pro' ),
					'type'        => Controls_Manager::HEADING,
					'label_block' => true,
				)
			);

			$elementor->add_control(
				'order_bump_checkbox_arrow',
				array(
					'label'        => __( 'Enable Arrow', 'cartflows-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => 'On',
					'label_off'    => 'Off',
					'return_value' => 'yes',
					'default'      => 'no',
				)
			);

			$elementor->add_control(
				'order_bump_checkbox_arrow_animation',
				array(
					'label'        => __( 'Enable Arrow Animation', 'cartflows-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => 'On',
					'label_off'    => 'Off',
					'return_value' => 'yes',
					'default'      => 'no',
					'separator'    => 'after',
				)
			);

			$elementor->add_control(
				'order_bump_colors_fields_text',
				array(
					'label'       => __( 'Colors', 'cartflows-pro' ),
					'type'        => Controls_Manager::HEADING,
					'label_block' => true,
				)
			);

			$elementor->add_control(
				'order_bump_bg_color',
				array(
					'label'     => __( 'Background Color', 'cartflows-pro' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wcf-el-checkout-form .wcf-bump-order-wrap' => 'background-color: {{VALUE}};',
					),
				)
			);

			$elementor->add_control(
				'order_bump_label_color',
				array(
					'label'     => __( 'Label Color', 'cartflows-pro' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wcf-el-checkout-form .wcf-bump-order-wrap .wcf-bump-order-field-wrap label' => 'color: {{VALUE}};',
					),
				)
			);

			$elementor->add_control(
				'order_bump_label_bg_color',
				array(
					'label'     => __( 'Label Background Color', 'cartflows-pro' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wcf-el-checkout-form .wcf-bump-order-wrap .wcf-bump-order-field-wrap' => 'background-color: {{VALUE}};',
					),
				)
			);

			$elementor->add_control(
				'order_bump_hl_text_color',
				array(
					'label'     => __( 'Highlight Text Color', 'cartflows-pro' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wcf-el-checkout-form .wcf-bump-order-wrap .wcf-bump-order-bump-highlight' => 'color: {{VALUE}};',
					),
				)
			);

			$elementor->add_control(
				'order_bump_desc_color',
				array(
					'label'     => __( 'Description Text Color', 'cartflows-pro' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wcf-el-checkout-form .wcf-bump-order-wrap .wcf-bump-order-desc' => 'color: {{VALUE}};',
					),
					'separator' => 'after',
				)
			);

			$elementor->add_control(
				'order_bump_border_fields_text',
				array(
					'label'       => __( 'Border', 'cartflows-pro' ),
					'type'        => Controls_Manager::HEADING,
					'label_block' => true,
				)
			);

			$elementor->add_control(
				'order_bump_border_style',
				array(
					'label'       => __( 'Border Style', 'cartflows-pro' ),
					'type'        => Controls_Manager::SELECT,
					'label_block' => false,
					'default'     => '',
					'options'     => array(
						''       => __( 'Default', 'cartflows-pro' ),
						'solid'  => __( 'Solid', 'cartflows-pro' ),
						'double' => __( 'Double', 'cartflows-pro' ),
						'dotted' => __( 'Dotted', 'cartflows-pro' ),
						'dashed' => __( 'Dashed', 'cartflows-pro' ),
					),
					'selectors'   => array(
						'{{WRAPPER}} .wcf-el-checkout-form .wcf-bump-order-wrap' => 'border-style: {{VALUE}};',
						'{{WRAPPER}} .wcf-el-checkout-form .wcf-bump-order-style-1 .wcf-bump-order-field-wrap' => 'border-bottom-style: {{VALUE}};',
						'{{WRAPPER}} .wcf-el-checkout-form .wcf-bump-order-style-2 .wcf-bump-order-field-wrap' => 'border-top-style: {{VALUE}};',
					),
				)
			);
			$elementor->add_control(
				'order_bump_border_size',
				array(
					'label'      => __( 'Border Width', 'cartflows-pro' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px' ),
					'selectors'  => array(
						'{{WRAPPER}} .wcf-el-checkout-form .wcf-bump-order-wrap .wcf-bump-order-field-wrap,
						{{WRAPPER}} .wcf-el-checkout-form .wcf-bump-order-wrap' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$elementor->add_control(
				'order_bump_border_color',
				array(
					'label'     => __( 'Border Color', 'cartflows-pro' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wcf-el-checkout-form .wcf-bump-order-wrap .wcf-bump-order-field-wrap,
						{{WRAPPER}} .wcf-el-checkout-form .wcf-bump-order-wrap' => 'border-color: {{VALUE}};',

					),
				)
			);

			$elementor->add_control(
				'order_bump_border_radius',
				array(
					'label'      => __( 'Rounded Corners', 'cartflows-pro' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', 'em', '%' ),
					'selectors'  => array(
						'{{WRAPPER}} .wcf-el-checkout-form .wcf-bump-order-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);
		} else {
			$elementor->add_control(
				'order_bump_style_disabled',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => sprintf(
						/* translators: %1$1s, %2$2s Link to meta */
						__( 'Please enable "Order Bump" from %1$1smeta settings%2$2s to apply styles.', 'cartflows-pro' ),
						'<a href="' . Cartflows_Pro_Helper::get_current_page_edit_url( 'order-bump' ) . '" target="_blank">',
						'</a>'
					),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
				)
			);
		}
		$elementor->end_controls_section();
	}


	/**
	 * Register checkout offer style Controls.
	 *
	 * @param array $elementor element data.
	 * @param array $args data.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	/* phpcs:ignore Squiz.PHP.CommentedOutCode.Found
	public function register_checkout_offer_section_controls( $elementor, $args ) {

	$checkout_id = get_the_id();
	$is_enabled  = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-pre-checkout-offer' );

	$elementor->start_controls_section(
	'section_checkout_offer_section_fields',
	array(
	'label' => __( 'Pre Checkout Offer', 'cartflows-pro' ),
	)
	);

	if ( 'yes' === $is_enabled ) {

	$elementor->add_control(
	'pre_checkout_enable_preview',
	array(
	'label'        => __( 'Enable Preview', 'cartflows-pro' ),
	'type'         => Controls_Manager::SWITCHER,
	'label_on'     => __( 'YES', 'cartflows-pro' ),
	'label_off'    => __( 'NO', 'cartflows-pro' ),
	'return_value' => 'yes',
	'selectors'    => array(
	'body.elementor-editor-active .wcf-pre-checkout-offer-wrapper.wcf-pre-checkout-full-width' => 'visibility: visible; opacity: 1; text-align: center; position: absolute; width: 100%; height: 100%; left: 0; top: 0; padding: 30px;',
	),
	)
	);

			$elementor->add_control(
			'checkout_offer_title_text',
			array(
			'label'       => __( 'Title Text', 'cartflows-pro' ),
			'type'        => Controls_Manager::TEXT,
			'placeholder' => __( '{first_name}, Wait! Your Order Is Almost Complete...', 'cartflows-pro' ),
			)
			);

			$elementor->add_control(
			'checkout_offer_subtitle_text',
			array(
			'label'       => __( 'Sub-title Text', 'cartflows-pro' ),
			'type'        => Controls_Manager::TEXT,
			'placeholder' => __( 'We have a special one time offer just for you.', 'cartflows-pro' ),
			)
			);

			$elementor->add_control(
			'checkout_offer_product_name',
			array(
			'label'       => __( 'Product Title', 'cartflows-pro' ),
			'type'        => Controls_Manager::TEXT,
			'placeholder' => __( 'Product Name', 'cartflows-pro' ),
			)
			);

			$elementor->add_control(
			'checkout_offer_product_desc',
			array(
			'label'       => __( 'Product Description', 'cartflows-pro' ),
			'type'        => Controls_Manager::TEXT,
			'placeholder' => __( 'Write a few words about this awesome product and tell shoppers why they must get it. You may highlight this as "one time offer" and make it irresistible.', 'cartflows-pro' ),
			)
			);

			$elementor->add_control(
			'checkout_offer_accept_button_text',
			array(
			'label'       => __( 'Order Button Text', 'cartflows-pro' ),
			'type'        => Controls_Manager::TEXT,
			'placeholder' => __( 'Yes, Add to My Order!', 'cartflows-pro' ),
			)
			);

			$elementor->add_control(
			'checkout_offer_skip_button_text',
			array(
			'label'       => __( 'Skip Button Text', 'cartflows-pro' ),
			'type'        => Controls_Manager::TEXT,
			'placeholder' => __( 'No, thanks!', 'cartflows-pro' ),
			)
			);
	} else {

	$elementor->add_control(
	'checkout_offer_disabled',
	array(
	'type'            => Controls_Manager::RAW_HTML,
	'raw'             => sprintf(
	* translators: %1$1s, %2$2s Link to meta */
	// __( 'Please enable "Checkout Offer" from %1$1smeta settings%2$2s to edit options.', 'cartflows-pro' ),
	// '<a href="' . Cartflows_Pro_Helper::get_current_page_edit_url( 'checkout-offer' ) . '" target="_blank">',
	// '</a>'
	// ),
	// 'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
	// )
	// );
	// }
	// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
	// $elementor->end_controls_section();
	// }

	/**
	 * Register Pre-checkout offer Style Controls.
	 *
	 * @param array $elementor element data.
	 * @param array $args data.
	 *
	 * @since x.x.x
	 */
	public function register_checkout_offer_style_controls( $elementor, $args ) {

		$checkout_id = get_the_id();
		$is_enabled  = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-pre-checkout-offer' );

		$elementor->start_controls_section(
			'pre_checkout_offer_style_fields',
			array(
				'label' => __( 'Pre Checkout Offer', 'cartflows-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		if ( 'yes' === $is_enabled ) {

			$elementor->add_control(
				'pre_checkout_enable_preview',
				array(
					'label'        => __( 'Enable Preview', 'cartflows-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => __( 'YES', 'cartflows-pro' ),
					'label_off'    => __( 'NO', 'cartflows-pro' ),
					'return_value' => 'yes',
					'selectors'    => array(
						'body.elementor-editor-active .wcf-pre-checkout-offer-wrapper.wcf-pre-checkout-full-width' => 'visibility: visible; opacity: 1; text-align: center; position: absolute; width: 100%; height: 100%; left: 0; top: 0; padding: 30px;',
					),
				)
			);

			$elementor->add_control(
				'pre_checkout_overlay_bg_color',
				array(
					'label'     => __( 'Overlay Background Color', 'cartflows-pro' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'body .wcf-pre-checkout-offer-wrapper.wcf-pre-checkout-full-width' => 'background-color: {{VALUE}};',
					),
				)
			);

			$elementor->add_control(
				'pre_checkout_bg_color',
				array(
					'label'     => __( 'Modal Background Color', 'cartflows-pro' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-modal' => 'background-color: {{VALUE}};',
						'body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content' => 'background-color: {{VALUE}};',
					),
				)
			);

			$elementor->add_control(
				'pre_checkout_title_color',
				array(
					'label'     => __( 'Title Color', 'cartflows-pro' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'body .wcf-pre-checkout-offer-wrapper .wcf-lightbox-content .wcf-content-main-head .wcf-content-modal-title h1,
						body .wcf-pre-checkout-offer-wrapper .wcf-lightbox-content #wcf-pre-checkout-offer-content .wcf-pre-checkout-info .wcf-pre-checkout-offer-product-title h1,
						body .wcf-pre-checkout-offer-wrapper .wcf-content-main-head .wcf-content-modal-title .wcf_first_name' => 'color: {{VALUE}} !important;',
					),
				)
			);

			$elementor->add_control(
				'pre_checkout_sub_title_color',
				array(
					'label'     => __( 'Subtitle Color', 'cartflows-pro' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'body .wcf-pre-checkout-offer-wrapper .wcf-lightbox-content .wcf-content-main-head .wcf-content-modal-sub-title span,
						body .wcf-pre-checkout-offer-wrapper .wcf-content-modal-sub-title span' => 'color: {{VALUE}} !important;',
					),
				)
			);

			$elementor->add_control(
				'pre_checkout_desc_color',
				array(
					'label'     => __( 'Description Color', 'cartflows-pro' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'body .wcf-pre-checkout-offer-wrapper .wcf-pre-checkout-offer-desc span' => 'color: {{VALUE}};',
						'body .wcf-pre-checkout-offer-wrapper .wcf-pre-checkout-offer-price,
						body .wcf-progress-bar-nav,
						body .wcf-pre-checkout-offer-wrapper .wcf-pre-checkout-skip-btn .wcf-pre-checkout-skip' => 'color: {{VALUE}} !important;',
					),
				)
			);
		} else {
			$elementor->add_control(
				'checkout_offer_style_disabled',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => sprintf(
						/* translators: %1$1s, %2$2s Link to meta */
						__( 'Please enable "Checkout Offer" from %1$1smeta settings%2$2s to to apply styles.', 'cartflows-pro' ),
						'<a href="' . Cartflows_Pro_Helper::get_current_page_edit_url( 'checkout-offer' ) . '" target="_blank">',
						'</a>'
					),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
				)
			);
		}

		$elementor->end_controls_section();
	}

	/**
	 * Added dynamic filter.
	 *
	 * @param array $settings settings data.
	 *
	 * @since x.x.x
	 */
	public function dynamic_filters( $settings ) {

		self::$settings = $settings;

		$checkout_id            = get_the_id();
		$enable_product_options = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-enable-product-options' );
		$enable_order_bump      = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump', true );
		$enable_checkout_offer  = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-pre-checkout-offer' );

		if ( 'yes' === $enable_checkout_offer ) {

			self::$checkout_settings = array(
				// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
				// 'title_text'            => self::$settings['checkout_offer_title_text'],
				// 'subtitle_text'         => self::$settings['checkout_offer_subtitle_text'],
				// 'product_name'          => self::$settings['checkout_offer_product_name'],
				// 'product_desc'          => self::$settings['checkout_offer_product_desc'],
				// 'accept_button_text'    => self::$settings['checkout_offer_accept_button_text'],
				// 'skip_button_text'      => self::$settings['checkout_offer_skip_button_text'],
				'enable_checkout_offer' => $enable_checkout_offer,
			);

			add_filter(
				'cartflows_elementor_checkout_settings',
				function ( $data_settings ) {
					$data_settings = self::$checkout_settings;
					return $data_settings;
				},
				10,
				1
			);
		}

		$checkout_fields = array(
			array(
				'filter_slug'  => 'wcf-checkout-layout',
				'setting_name' => 'layout',
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

				$setting_name = $field['setting_name'];

				if ( '' !== self::$settings[ $setting_name ] ) {

					add_filter(
						'cartflows_checkout_meta_' . $field['filter_slug'],
						function ( $value ) use ( $setting_name ) {

							$value = self::$settings[ $setting_name ];

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
			function ( $is_note_enabled ) {

				$is_note_enabled = ( 'yes' === self::$settings['enable_note'] ) ? 'yes' : 'no';
				return $is_note_enabled;
			},
			10,
			1
		);

		if ( 'yes' === self::$settings['enable_note'] && '' !== self::$settings['note_text'] ) {

			add_filter(
				'cartflows_checkout_meta_wcf-checkout-box-note-text',
				function ( $checkout_note_text ) {

					$checkout_note_text = self::$settings['note_text'];
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

					$setting_name = $field['setting_name'];

					if ( '' !== self::$settings[ $setting_name ] ) {
						add_filter(
							'cartflows_checkout_meta_' . $field['filter_slug'],
							function ( $value ) use ( $setting_name ) {

								$value = self::$settings[ $setting_name ];

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
				function () {

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
						if ( ! empty( self::$settings[ $ob_option ] ) ) {
							echo '<input type="hidden" class="input-hidden" name="_wcf_' . $ob_option . '" value="' . esc_attr( self::$settings[ $ob_option ] ) . '">';
						}
					}
				},
				99
			);
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

					$setting_name = $field['setting_name'];

					if ( isset( self::$settings[ $setting_name ] ) && ( '' !== self::$settings[ $setting_name ] ) ) {
						add_filter(
							'cartflows_checkout_meta_' . $field['filter_slug'],
							function ( $value ) use ( $setting_name ) {

								$value = self::$settings[ $setting_name ];

								return $value;
							},
							10,
							1
						);
					}
				}
			}
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

					$setting_name = $field['setting_name'];

					if ( '' !== self::$settings[ $setting_name ] ) {
						add_filter(
							'cartflows_checkout_meta_' . $field['filter_slug'],
							function ( $value ) use ( $setting_name ) {

								$value = self::$settings[ $setting_name ];

								return $value;
							},
							10,
							1
						);
					}
				}
			}
		}
	}
}

/**
 * Initiate the class.
 */
Cartflows_Pro_Checkout_Form_Extend::get_instance();
