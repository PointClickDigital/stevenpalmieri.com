<?php
namespace De_Sina_Extension;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Css_Filter;

/**
 * De_Curtain_Animation_Controls Class for extends controls
 *
 * @since 3.0.1
 */
class De_Curtain_Animation_Controls{
	/**
	 * Instance
	 *
	 * @since 3.1.13
	 * @var De_Curtain_Animation_Controls The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 3.1.13
	 * @return De_Curtain_Animation_Controls An Instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		add_action('elementor/element/common/_section_style/after_section_end', [$this, 'register_controls']);
		add_action('elementor/element/column/section_advanced/before_section_end', [$this, 'column_register_controls']);
		add_action('elementor/element/section/section_advanced/before_section_end', [$this, 'column_register_controls']);

		add_filter( 'elementor/widget/render_content', [$this, 'render_template_content'], 10, 2 );
		add_filter( 'elementor/widget/print_template', [$this, 'update_template_content'], 10, 2 );
	}

	public function render_template_content($template,$widget) {
		// if ( 'image' === $widget->get_name() ) {
		// 	$settings = $widget->get_settings_for_display();
		// 	$template = '<div class="block-revealer__content" style="opacity: 1;">' . $template . '</div><div class="block-revealer__element" style="opacity: 1;"></div>';
		// }

		return $template;
	}

	public function update_template_content($template,$widget) {
		// if ( 'image' === $widget->get_name() ) {
		// 	$template = '<div class="block-revealer__content" style="opacity: 1;">' . $template . '</div><div class="block-revealer__element"></div>';
		// }

		return $template;
	}

	public function register_controls($elems) {
		$elems->start_controls_section(
			'de_curtain_animation_section',
			[
				'label' => __( 'De Curtain Animation', 'detheme-kit' ),
				'tab' => Controls_Manager::TAB_ADVANCED,
			]
		);

		$elems->add_control(
			'de_curtain_animation',
			[
				'label' => '<strong>'.esc_html__( 'De Curtain Animation', 'detheme-kit' ).'</strong>',
				'type' => Controls_Manager::SWITCHER,
				'prefix_class' => 'de_curtain_animation_',
			]
		);

		$elems->add_control(
			'de_curtain_duration',
			[
				'label' => '<strong>'.esc_html__( 'Curtain Duration', 'detheme-kit' ).'</strong>',
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_curtain_duration_',
				'default' => '700',
				'condition' => [ 'de_curtain_animation' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_curtain_color',
			[
				'label' => '<strong>'.esc_html__( 'Curtain Color', 'detheme-kit' ).'</strong>',
				'type' => Controls_Manager::COLOR,
				'prefix_class' => 'de_curtain_color_',
				'condition' => [ 'de_curtain_animation' => 'yes' ],
				// 'selectors' => [
				// 	'{{WRAPPER}} .elementor-widget-container .block-revealer__element' => 'background-color: {{VALUE}};',
				// ],
			]
		);

		$elems->add_control(
			'de_curtain_direction',
			[
				'label' => '<strong>'.esc_html__( 'Curtain Direction', 'detheme-kit' ).'</strong>',
				'type' => Controls_Manager::SELECT,
				'options' => [
					'lr' => esc_html__( 'Left to Right', 'detheme-kit' ),
					'rl' => esc_html__( 'Right to Left', 'detheme-kit' ),
					'tb' => esc_html__( 'Top to Bottom', 'detheme-kit' ),
					'bt' => esc_html__( 'Bottom to Top', 'detheme-kit' ),
				],
				'default' => 'lr',
				'prefix_class' => 'de_curtain_direction_',
				'condition' => [ 'de_curtain_animation' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_curtain_easing',
			[
				'label' => '<strong>'.esc_html__( 'Curtain Easing', 'detheme-kit' ).'</strong>',
				'type' => Controls_Manager::SELECT,
				'options' => [
					// 'linear' => esc_html__( 'linear', 'detheme-kit' ),
					// 'easeOutElastic' => esc_html__( 'easeOutElastic', 'detheme-kit' ),
					// 'easeIn' => esc_html__( 'easeIn', 'detheme-kit' ),
					// 'easeOut' => esc_html__( 'easeOut', 'detheme-kit' ),
					// 'easeInOut' => esc_html__( 'easeInOut', 'detheme-kit' ),

					// 'linear' => esc_html__( 'linear', 'detheme-kit' ),
					// 'ease' => esc_html__( 'ease', 'detheme-kit' ),
					// 'easeIn' => esc_html__( 'easeIn', 'detheme-kit' ),
					// 'easeOut' => esc_html__( 'easeOut', 'detheme-kit' ),
					// 'easeInOut' => esc_html__( 'easeInOut', 'detheme-kit' ),
					// 'swing' => esc_html__( 'swing', 'detheme-kit' ),

					'linear' => esc_html__( 'linear', 'detheme-kit' ),
					// 'swing' => esc_html__( 'swing', 'detheme-kit' ),
					// 'easeIn' => esc_html__( 'easeIn', 'detheme-kit' ),
					// 'easeOut' => esc_html__( 'easeOut', 'detheme-kit' ),
					// 'easeInOut' => esc_html__( 'easeInOut', 'detheme-kit' ),
					'easeInQuad' => esc_html__( 'easeInQuad', 'detheme-kit' ),
					'easeOutQuad' => esc_html__( 'easeOutQuad', 'detheme-kit' ),
					'easeInOutQuad' => esc_html__( 'easeInOutQuad', 'detheme-kit' ),
					'easeInCubic' => esc_html__( 'easeInCubic', 'detheme-kit' ),
					'easeOutCubic' => esc_html__( 'easeOutCubic', 'detheme-kit' ),
					'easeInOutCubic' => esc_html__( 'easeInOutCubic', 'detheme-kit' ),
					'easeInQuart' => esc_html__( 'easeInQuart', 'detheme-kit' ),
					'easeOutQuart' => esc_html__( 'easeOutQuart', 'detheme-kit' ),
					'easeInOutQuart' => esc_html__( 'easeInOutQuart', 'detheme-kit' ),
					'easeInQuint' => esc_html__( 'easeInQuint', 'detheme-kit' ),
					'easeOutQuint' => esc_html__( 'easeOutQuint', 'detheme-kit' ),
					'easeInOutQuint' => esc_html__( 'easeInOutQuint', 'detheme-kit' ),
					'easeInExpo' => esc_html__( 'easeInExpo', 'detheme-kit' ),
					'easeOutExpo' => esc_html__( 'easeOutExpo', 'detheme-kit' ),
					'easeInOutExpo' => esc_html__( 'easeInOutExpo', 'detheme-kit' ),
					'easeInSine' => esc_html__( 'easeInSine', 'detheme-kit' ),
					'easeOutSine' => esc_html__( 'easeOutSine', 'detheme-kit' ),
					'easeInOutSine' => esc_html__( 'easeInOutSine', 'detheme-kit' ),
					'easeInCirc' => esc_html__( 'easeInCirc', 'detheme-kit' ),
					'easeOutCirc' => esc_html__( 'easeOutCirc', 'detheme-kit' ),
					'easeInOutCirc' => esc_html__( 'easeInOutCirc', 'detheme-kit' ),
					'easeInElastic' => esc_html__( 'easeInElastic', 'detheme-kit' ),
					'easeOutElastic' => esc_html__( 'easeOutElastic', 'detheme-kit' ),
					'easeInOutElastic' => esc_html__( 'easeInOutElastic', 'detheme-kit' ),
					'easeInBack' => esc_html__( 'easeInBack', 'detheme-kit' ),
					'easeOutBack' => esc_html__( 'easeOutBack', 'detheme-kit' ),
					'easeInOutBack' => esc_html__( 'easeInOutBack', 'detheme-kit' ),
					'easeInBounce' => esc_html__( 'easeInBounce', 'detheme-kit' ),
					'easeOutBounce' => esc_html__( 'easeOutBounce', 'detheme-kit' ),
					'easeInOutBounce' => esc_html__( 'easeInOutBounce', 'detheme-kit' ),					
				],
				'default' => 'linear',
				'prefix_class' => 'de_curtain_easing_',
				'condition' => [ 'de_curtain_animation' => 'yes' ],
				// 'selectors' => [
				// 	'{{WRAPPER}} .elementor-widget-container .block-revealer__element' => 'animation-name: {{settings.de_curtain_direction.VALUE}};',
				// ],

			]
		);

		$elems->end_controls_section();
	}

	public function column_register_controls($elems) {
	}
}