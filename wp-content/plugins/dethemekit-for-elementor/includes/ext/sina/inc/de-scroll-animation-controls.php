<?php
namespace De_Sina_Extension;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Css_Filter;

/**
 * De_Scroll_Animation_Controls Class for extends controls
 *
 * @since 3.0.1
 */
class De_Scroll_Animation_Controls{
	/**
	 * Instance
	 *
	 * @since 3.1.13
	 * @var De_Scroll_Animation_Controls The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 3.1.13
	 * @return De_Scroll_Animation_Controls An Instance of the class.
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
			'de_scroll_animation_section',
			[
				'label' => __( 'De Scroll Animation', 'detheme-kit' ),
				'tab' => Controls_Manager::TAB_ADVANCED,
			]
		);

		$elems->add_control(
			'de_scroll_animation',
			[
				'label' => esc_html__( 'De Scroll Animation', 'detheme-kit' ),
				'type' => Controls_Manager::SWITCHER,
				'prefix_class' => 'de_scroll_animation_',
			]
		);

		$elems->add_control(
			'de_scroll_animation_preview',
			[
				'label' => esc_html__( 'Run Animation on Preview', 'detheme-kit' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'prefix_class' => 'de_scroll_animation_preview_',
				'condition' => [ 'de_scroll_animation' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_scroll_transforms',
			[
				'label' => esc_html__( 'Transforms', 'detheme-kit' ),
				'type' => Controls_Manager::SWITCHER,
				'prefix_class' => 'de_scroll_transforms_',
				'condition' => [ 'de_scroll_animation' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_scroll_translateX_popover_toggle',
			[
				'label' => esc_html__( '- Translate X', 'detheme-kit' ),
				'type' => Controls_Manager::POPOVER_TOGGLE,
				'return_value' => 'checked',
				'default' => 'unchecked',
				'prefix_class' => 'de_scroll_translateX_popover_',
				'condition' => [ 'de_scroll_animation' => 'yes', 'de_scroll_transforms' => 'yes' ],
			]
		);

		$elems->start_popover();

		$elems->add_control(
			'de_scroll_translateX_distance',
			[
				'label' => esc_html__( 'Distance (px)', 'detheme-kit' ),
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_scroll_translateX_distance_',
				'default' => '500',
				'condition' => [ 'de_scroll_translateX_popover_toggle' => 'checked' ],
			]
		);

		$elems->end_popover();

        $elems->add_control(
			'de_scroll_translateY_popover_toggle',
			[
				'label' => esc_html__( '- Translate Y', 'detheme-kit' ),
				'type' => Controls_Manager::POPOVER_TOGGLE,
				'return_value' => 'checked',
				'default' => 'unchecked',
				'prefix_class' => 'de_scroll_translateY_popover_',
				'condition' => [ 'de_scroll_animation' => 'yes', 'de_scroll_transforms' => 'yes' ],
			]
		);

		$elems->start_popover();

		$elems->add_control(
			'de_scroll_translateY_distance',
			[
				'label' => esc_html__( 'Distance (px)', 'detheme-kit' ),
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_scroll_translateY_distance_',
				'default' => '500',
				'condition' => [ 'de_scroll_translateY_popover_toggle' => 'checked' ],
			]
		);

		$elems->end_popover();


        $elems->add_control(
			'de_scroll_translateZ_popover_toggle',
			[
				'label' => esc_html__( '- Translate Z', 'detheme-kit' ),
				'type' => Controls_Manager::POPOVER_TOGGLE,
				'return_value' => 'checked',
				'default' => 'unchecked',
				'prefix_class' => 'de_scroll_translateZ_popover_',
				'condition' => [ 'de_scroll_animation' => 'yes', 'de_scroll_transforms' => 'yes' ],
			]
		);

		$elems->start_popover();

		$elems->add_control(
			'de_scroll_translateZ_distance',
			[
				'label' => esc_html__( 'Distance (px)', 'detheme-kit' ),
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_scroll_translateZ_distance_',
				'default' => '10',
				'condition' => [ 'de_scroll_translateZ_popover_toggle' => 'checked' ],
			]
		);

		$elems->add_control(
			'de_scroll_translateZ_perspective',
			[
				'label' => esc_html__( 'Perspective (px)', 'detheme-kit' ),
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_scroll_translateZ_perspective_',
				'default' => '100',
				// 'selectors' => [ '{{WRAPPER}}' => 'perspective: {{VALUE}}px;'],
				'condition' => [ 'de_scroll_translateZ_popover_toggle' => 'checked' ],
			]
		);

		$elems->end_popover();

        $elems->add_control(
			'de_scroll_rotate_popover_toggle',
			[
				'label' => esc_html__( '- Rotate', 'detheme-kit' ),
				'type' => Controls_Manager::POPOVER_TOGGLE,
				'return_value' => 'checked',
				'default' => 'unchecked',
				'prefix_class' => 'de_scroll_rotate_popover_',
				'condition' => [ 'de_scroll_animation' => 'yes', 'de_scroll_transforms' => 'yes' ],
			]
		);

		$elems->start_popover();

		$elems->add_control(
			'de_scroll_rotate_distance',
			[
				'label' => esc_html__( 'Degree', 'detheme-kit' ),
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_scroll_rotate_distance_',
				'default' => '90',
				'condition' => [ 'de_scroll_rotate_popover_toggle' => 'checked' ],
			]
		);

		$elems->end_popover();

        $elems->add_control(
			'de_scroll_rotateX_popover_toggle',
			[
				'label' => esc_html__( '- Rotate X', 'detheme-kit' ),
				'type' => Controls_Manager::POPOVER_TOGGLE,
				'return_value' => 'checked',
				'default' => 'unchecked',
				'prefix_class' => 'de_scroll_rotateX_popover_',
				'condition' => [ 'de_scroll_animation' => 'yes', 'de_scroll_transforms' => 'yes' ],
			]
		);

		$elems->start_popover();

		$elems->add_control(
			'de_scroll_rotateX_distance',
			[
				'label' => esc_html__( 'Degree', 'detheme-kit' ),
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_scroll_rotateX_distance_',
				'default' => '90',
				'condition' => [ 'de_scroll_rotateX_popover_toggle' => 'checked' ],
			]
		);

		$elems->end_popover();

        $elems->add_control(
			'de_scroll_rotateY_popover_toggle',
			[
				'label' => esc_html__( '- Rotate Y', 'detheme-kit' ),
				'type' => Controls_Manager::POPOVER_TOGGLE,
				'return_value' => 'checked',
				'default' => 'unchecked',
				'prefix_class' => 'de_scroll_rotateY_popover_',
				'condition' => [ 'de_scroll_animation' => 'yes', 'de_scroll_transforms' => 'yes' ],
			]
		);

		$elems->start_popover();

		$elems->add_control(
			'de_scroll_rotateY_distance',
			[
				'label' => esc_html__( 'Degree', 'detheme-kit' ),
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_scroll_rotateY_distance_',
				'default' => '90',
				'condition' => [ 'de_scroll_rotateY_popover_toggle' => 'checked' ],
			]
		);

		$elems->end_popover();

        $elems->add_control(
			'de_scroll_rotateZ_popover_toggle',
			[
				'label' => esc_html__( '- Rotate Z', 'detheme-kit' ),
				'type' => Controls_Manager::POPOVER_TOGGLE,
				'return_value' => 'checked',
				'default' => 'unchecked',
				'prefix_class' => 'de_scroll_rotateZ_popover_',
				'condition' => [ 'de_scroll_animation' => 'yes', 'de_scroll_transforms' => 'yes' ],
			]
		);

		$elems->start_popover();

		$elems->add_control(
			'de_scroll_rotateZ_distance',
			[
				'label' => esc_html__( 'Degree', 'detheme-kit' ),
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_scroll_rotateZ_distance_',
				'default' => '90',
				'condition' => [ 'de_scroll_rotateZ_popover_toggle' => 'checked' ],
			]
		);

		$elems->end_popover();

        $elems->add_control(
			'de_scroll_scale_popover_toggle',
			[
				'label' => esc_html__( '- Scale', 'detheme-kit' ),
				'type' => Controls_Manager::POPOVER_TOGGLE,
				'return_value' => 'checked',
				'default' => 'unchecked',
				'prefix_class' => 'de_scroll_scale_popover_',
				'condition' => [ 'de_scroll_animation' => 'yes', 'de_scroll_transforms' => 'yes' ],
			]
		);

		$elems->start_popover();

		$elems->add_control(
			'de_scroll_scale_distance',
			[
				'label' => esc_html__( 'Scale', 'detheme-kit' ),
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_scroll_scale_distance_',
				'default' => '1.5',
				'condition' => [ 'de_scroll_scale_popover_toggle' => 'checked' ],
			]
		);

		$elems->end_popover();

        $elems->add_control(
			'de_scroll_scaleX_popover_toggle',
			[
				'label' => esc_html__( '- Scale X', 'detheme-kit' ),
				'type' => Controls_Manager::POPOVER_TOGGLE,
				'return_value' => 'checked',
				'default' => 'unchecked',
				'prefix_class' => 'de_scroll_scaleX_popover_',
				'condition' => [ 'de_scroll_animation' => 'yes', 'de_scroll_transforms' => 'yes' ],
			]
		);

		$elems->start_popover();

		$elems->add_control(
			'de_scroll_scaleX_distance',
			[
				'label' => esc_html__( 'Scale', 'detheme-kit' ),
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_scroll_scaleX_distance_',
				'default' => '1.5',
				'condition' => [ 'de_scroll_scaleX_popover_toggle' => 'checked' ],
			]
		);

		$elems->end_popover();

        $elems->add_control(
			'de_scroll_scaleY_popover_toggle',
			[
				'label' => esc_html__( '- Scale Y', 'detheme-kit' ),
				'type' => Controls_Manager::POPOVER_TOGGLE,
				'return_value' => 'checked',
				'default' => 'unchecked',
				'prefix_class' => 'de_scroll_scaleY_popover_',
				'condition' => [ 'de_scroll_animation' => 'yes', 'de_scroll_transforms' => 'yes' ],
			]
		);

		$elems->start_popover();

		$elems->add_control(
			'de_scroll_scaleY_distance',
			[
				'label' => esc_html__( 'Scale', 'detheme-kit' ),
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_scroll_scaleY_distance_',
				'default' => '1.5',
				'condition' => [ 'de_scroll_scaleY_popover_toggle' => 'checked' ],
			]
		);

		$elems->end_popover();

        $elems->add_control(
			'de_scroll_scaleZ_popover_toggle',
			[
				'label' => esc_html__( '- Scale Z', 'detheme-kit' ),
				'type' => Controls_Manager::POPOVER_TOGGLE,
				'return_value' => 'checked',
				'default' => 'unchecked',
				'prefix_class' => 'de_scroll_scaleZ_popover_',
				'condition' => [ 'de_scroll_animation' => 'yes', 'de_scroll_transforms' => 'yes' ],
			]
		);

		$elems->start_popover();

		$elems->add_control(
			'de_scroll_scaleZ_distance',
			[
				'label' => esc_html__( 'Scale', 'detheme-kit' ),
				'description' => esc_html__( 'TranslateZ transform is needed to be activated.', 'detheme-kit' ),
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_scroll_scaleZ_distance_',
				'default' => '1.5',
				'condition' => [ 'de_scroll_scaleZ_popover_toggle' => 'checked' ],
			]
		);

		$elems->end_popover();

        $elems->add_control(
			'de_scroll_skew_popover_toggle',
			[
				'label' => esc_html__( '- Skew', 'detheme-kit' ),
				'type' => Controls_Manager::POPOVER_TOGGLE,
				'return_value' => 'checked',
				'default' => 'unchecked',
				'prefix_class' => 'de_scroll_skew_popover_',
				'condition' => [ 'de_scroll_animation' => 'yes', 'de_scroll_transforms' => 'yes' ],
			]
		);

		$elems->start_popover();

		$elems->add_control(
			'de_scroll_skew_distance',
			[
				'label' => esc_html__( 'Degree', 'detheme-kit' ),
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_scroll_skew_distance_',
				'default' => '180',
				'condition' => [ 'de_scroll_skew_popover_toggle' => 'checked' ],
			]
		);

		$elems->end_popover();

        $elems->add_control(
			'de_scroll_skewX_popover_toggle',
			[
				'label' => esc_html__( '- Skew X', 'detheme-kit' ),
				'type' => Controls_Manager::POPOVER_TOGGLE,
				'return_value' => 'checked',
				'default' => 'unchecked',
				'prefix_class' => 'de_scroll_skewX_popover_',
				'condition' => [ 'de_scroll_animation' => 'yes', 'de_scroll_transforms' => 'yes' ],
			]
		);

		$elems->start_popover();

		$elems->add_control(
			'de_scroll_skewX_distance',
			[
				'label' => esc_html__( 'Degree', 'detheme-kit' ),
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_scroll_skewX_distance_',
				'default' => '180',
				'condition' => [ 'de_scroll_skewX_popover_toggle' => 'checked' ],
			]
		);

		$elems->end_popover();

        $elems->add_control(
			'de_scroll_skewY_popover_toggle',
			[
				'label' => esc_html__( '- Skew Y', 'detheme-kit' ),
				'type' => Controls_Manager::POPOVER_TOGGLE,
				'return_value' => 'checked',
				'default' => 'unchecked',
				'prefix_class' => 'de_scroll_skewY_popover_',
				'condition' => [ 'de_scroll_animation' => 'yes', 'de_scroll_transforms' => 'yes' ],
			]
		);

		$elems->start_popover();

		$elems->add_control(
			'de_scroll_skewY_distance',
			[
				'label' => esc_html__( 'Degree', 'detheme-kit' ),
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_scroll_skewY_distance_',
				'default' => '180',
				'condition' => [ 'de_scroll_skewY_popover_toggle' => 'checked' ],
			]
		);

		$elems->end_popover();

		$elems->add_control(
			'de_scroll_duration',
			[
				'label' => esc_html__( 'Duration (ms)', 'detheme-kit' ),
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_scroll_duration_',
				'default' => '700',
				'condition' => [ 'de_scroll_animation' => 'yes' ],
				'separator' => 'before',
			]
		);

		$elems->add_control(
			'de_scroll_easing',
			[
				'label' => esc_html__( 'Easing', 'detheme-kit' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'linear' => esc_html__( 'linear', 'detheme-kit' ),
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
				'prefix_class' => 'de_scroll_easing_',
				'condition' => [ 'de_scroll_animation' => 'yes' ],
			]
		);

		$elems->end_controls_section();
	}

	public function column_register_controls($elems) {
	}
}