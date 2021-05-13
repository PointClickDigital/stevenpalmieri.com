<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'De_Sticky_Element_Extension' ) ) {

	/**
	 * Define De_Sticky_Element_Extension class
	 */
	class De_Sticky_Element_Extension {

		/**
		 * Sections Data
		 *
		 * @var array
		 */
		public $sections_data = array();

		/**
		 * Columns Data
		 *
		 * @var array
		 */
		public $columns_data = array();

		/**
		 * A reference to an instance of this class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    object
		 */
		private static $instance = null;

		/**
		 * Init Handler
		 */
		public function init() {

			add_action('elementor/element/common/_section_style/before_section_end', [$this, '_register_controls']);

			add_action( 'elementor/element/section/section_advanced/after_section_end', array( $this, 'register_controls' ), 10, 2 );

			add_action( 'elementor/element/column/section_advanced/after_section_end', array( $this, 'after_column_section_layout' ), 10, 2 );

			add_action( 'elementor/frontend/section/before_render',  array( $this, 'column_before_render' ) );
			add_action( 'elementor/frontend/column/before_render',  array( $this, 'column_before_render' ) );
			add_action( 'elementor/frontend/element/before_render', array( $this, 'column_before_render' ) );

			add_action( 'elementor/element/section/section_advanced/after_section_end', array( $this, 'add_section_sticky_controls' ), 10, 2 );

			add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'enqueue_scripts' ), 9 );

		}
		
		/**
		 * After column_layout callback
		 *
		 * @param  object $elems
		 * @param  array $args
		 * @return void
		 */
		public function _register_controls($elems) {
			
			$elems->add_control('dethemekit_carousel_element_child',
				[
					'label' 		=> __( 'De Carousel Child Mode', 'dethemekit-addons-for-elementor' ),
					'description'	=> __( 'Child Mode enables a Thumbnail slide with partial next/previous slides.', 'dethemekit-addons-for-elementor' ),
					'prefix_class' => 'sina-morphing-anim-',
					'type'			=> Elementor\Controls_Manager::SWITCHER,
				]
			);
		}

		/**
		 * After column_layout callback
		 *
		 * @param  object $elems
		 * @param  array $args
		 * @return void
		 */
		public function register_controls($elems) {
			$elems->start_controls_section('dethemekit_carousel_global_settings_advance2',
			[
				'label'         => __( 'De Carousel Extra2' , 'dethemekit-addons-for-elementor' ),
				'tab'   => Elementor\Controls_Manager::TAB_ADVANCED,
			]
			);
			$elems->add_control('dethemekit_carousel_parent',
				[
					'label' 		=> __( 'Parent Mode', 'dethemekit-addons-for-elementor' ),
					'description'	=> __( 'Parent Mode enables a Thumbnail slide with partial next/previous slides.', 'dethemekit-addons-for-elementor' ),
					'type'			=> Elementor\Controls_Manager::SWITCHER,
				]
			);
			$elems->add_control('dethemekit_carousel_childs',
				[
					'label' 		=> __( 'De Carousel Child Mode', 'dethemekit-addons-for-elementor' ),
					'description'	=> __( 'Child Mode enables a Thumbnail slide with partial next/previous slides.', 'dethemekit-addons-for-elementor' ),
					'type'			=> Elementor\Controls_Manager::SWITCHER,
				]
			);
			$elems->end_controls_section();
		}
			

		/**
		 * After column_layout callback
		 *
		 * @param  object $obj
		 * @param  array $args
		 * @return void
		 */
		public function after_column_section_layout( $obj, $args ) {

			$obj->start_controls_section('dethemekit_carousel_global_settings_advance',
			[
				'label'         => __( 'De Carousel Extra' , 'dethemekit-addons-for-elementor' ),
				'tab'   => Elementor\Controls_Manager::TAB_ADVANCED,
			]
			);

			$obj->add_control('dethemekit_carousel_parent',
				[
					'label' 		=> __( 'Parent Mode', 'dethemekit-addons-for-elementor' ),
					'description'	=> __( 'Parent Mode enables a Thumbnail slide with partial next/previous slides.', 'dethemekit-addons-for-elementor' ),
					'type'			=> Elementor\Controls_Manager::SWITCHER,
				]
			);
			$obj->add_control('dethemekit_carousel_childs',
				[
					'label' 		=> __( 'Childs Mode', 'dethemekit-addons-for-elementor' ),
					'description'	=> __( 'Child Mode enables a Thumbnail slide with partial next/previous slides.', 'dethemekit-addons-for-elementor' ),
					'type'			=> Elementor\Controls_Manager::SWITCHER,
				]
			);

			$obj->end_controls_section();

			$obj->start_controls_section(
				'de_sticky_column_sticky_section',
				array(
					'label' => esc_html__( 'De Sticky', 'desticky-for-elementor' ),
					'tab'   => Elementor\Controls_Manager::TAB_ADVANCED,
				)
			);

			$obj->add_control(
				'de_sticky_column_sticky_enable',
				array(
					'label'        => esc_html__( 'Sticky Column', 'desticky-for-elementor' ),
					'type'         => Elementor\Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'desticky-for-elementor' ),
					'label_off'    => esc_html__( 'No', 'desticky-for-elementor' ),
					'return_value' => 'true',
					'default'      => 'false',
				)
			);

			$obj->add_control(
				'de_sticky_column_sticky_top_spacing',
				array(
					'label'   => esc_html__( 'Top Spacing', 'desticky-for-elementor' ),
					'type'    => Elementor\Controls_Manager::NUMBER,
					'default' => 50,
					'min'     => 0,
					'max'     => 500,
					'step'    => 1,
					'condition' => array(
						'de_sticky_column_sticky_enable' => 'true',
					),
				)
			);

			$obj->add_control(
				'de_sticky_column_sticky_bottom_spacing',
				array(
					'label'   => esc_html__( 'Bottom Spacing', 'desticky-for-elementor' ),
					'type'    => Elementor\Controls_Manager::NUMBER,
					'default' => 50,
					'min'     => 0,
					'max'     => 500,
					'step'    => 1,
					'condition' => array(
						'de_sticky_column_sticky_enable' => 'true',
					),
				)
			);

			$obj->add_control(
				'de_sticky_column_sticky_enable_on',
				array(
					'label'    => __( 'Sticky On', 'desticky-for-elementor' ),
					'type'     => Elementor\Controls_Manager::SELECT2,
					'multiple' => true,
					'label_block' => 'true',
					'default' => array(
						'desktop',
						'tablet',
					),
					'options' => array(
						'desktop' => __( 'Desktop', 'desticky-for-elementor' ),
						'tablet'  => __( 'Tablet', 'desticky-for-elementor' ),
						'mobile'  => __( 'Mobile', 'desticky-for-elementor' ),
					),
					'condition' => array(
						'de_sticky_column_sticky_enable' => 'true',
					),
					'render_type' => 'none',
				)
			);

			$obj->end_controls_section();
		}

		/**
		 * Before column render callback.
		 *
		 * @param object $element
		 *
		 * @return void
		 */
		public function column_before_render( $element ) {
			$data     = $element->get_data();
			$type     = isset( $data['elType'] ) ? $data['elType'] : 'column';
			$settings = $data['settings'];

			if ( isset( $settings['dethemekit_carousel_childs'] ) ) {
				$element_settings = array(
					'id'           => $data['id'],
					'child'        => filter_var( $settings['dethemekit_carousel_childs'], FILTER_VALIDATE_BOOLEAN ),
					
				);

				if ( filter_var( $settings['dethemekit_carousel_childs'], FILTER_VALIDATE_BOOLEAN ) ) {

					$element->add_render_attribute( '_wrapper', array(
						'class' => 'de-carousel-slider-childs',
						'data-de-carousel-child-column-settings' => json_encode( $element_settings ),
					) );
				}

				$this->columns_data[ $data['id'] ] = $element_settings;
			}

			if ( 'column' !== $type ) {
				return;
			}

			if ( isset( $settings['de_sticky_column_sticky_enable'] ) ) {
				$column_settings = array(
					'id'            => $data['id'],
					'sticky'        => filter_var( $settings['de_sticky_column_sticky_enable'], FILTER_VALIDATE_BOOLEAN ),
					'topSpacing'    => isset( $settings['de_sticky_column_sticky_top_spacing'] ) ? $settings['de_sticky_column_sticky_top_spacing'] : 50,
					'bottomSpacing' => isset( $settings['de_sticky_column_sticky_bottom_spacing'] ) ? $settings['de_sticky_column_sticky_bottom_spacing'] : 50,
					'stickyOn'      => isset( $settings['de_sticky_column_sticky_enable_on'] ) ? $settings['de_sticky_column_sticky_enable_on'] : array( 'desktop', 'tablet' ),
				);

				if ( filter_var( $settings['de_sticky_column_sticky_enable'], FILTER_VALIDATE_BOOLEAN ) ) {

					$element->add_render_attribute( '_wrapper', array(
						'class' => 'de-sticky-column-sticky',
						'data-de-sticky-column-settings' => json_encode( $column_settings ),
					) );
				}

				$this->columns_data[ $data['id'] ] = $column_settings;
			}
			if ( isset( $settings['dethemekit_carousel_parent'] ) ) {
				$column_settings = array(
					'id'            => $data['id'],
					'parent'        => filter_var( $settings['dethemekit_carousel_parent'], FILTER_VALIDATE_BOOLEAN ),
					
				);

				if ( filter_var( $settings['dethemekit_carousel_parent'], FILTER_VALIDATE_BOOLEAN ) ) {

					$element->add_render_attribute( '_wrapper', array(
						'class' => 'de-carousel-slider-parent',
						'data-de-carousel-parent-column-settings' => json_encode( $column_settings ),
					) );
				}

				$this->columns_data[ $data['id'] ] = $column_settings;
			}

			if ( isset( $settings['dethemekit_carousel_childs'] ) ) {
				$element_settings = array(
					'id'           => $data['id'],
					'child'        => filter_var( $settings['dethemekit_carousel_childs'], FILTER_VALIDATE_BOOLEAN ),
					
				);

				if ( filter_var( $settings['dethemekit_carousel_childs'], FILTER_VALIDATE_BOOLEAN ) ) {

					$element->add_render_attribute( '_wrapper', array(
						'class' => 'de-carousel-slider-childs',
						'data-de-carousel-child-column-settings' => json_encode( $element_settings ),
					) );
				}

				$this->columns_data[ $data['id'] ] = $element_settings;
			}
			
		}

		/**
		 * Add sticky controls to section settings.
		 *
		 * @param object $element Element instance.
		 * @param array  $args    Element arguments.
		 */
		public function add_section_sticky_controls( $element, $args ) {
			$element->start_controls_section(
				'de_sticky_section_sticky_settings',
				array(
					'label' => esc_html__( 'De Sticky', 'desticky-for-elementor' ),
					'tab'   => Elementor\Controls_Manager::TAB_ADVANCED,
				)
			);

			$element->add_control(
				'de_sticky_section_sticky',
				array(
					'label'   => esc_html__( 'Sticky Section', 'desticky-for-elementor' ),
					'type'    => Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'frontend_available' => true,
				)
			);

			$element->add_control(
				'de_sticky_section_sticky_visibility',
				array(
					'label'       => esc_html__( 'Sticky Section Visibility', 'desticky-for-elementor' ),
					'type'        => Elementor\Controls_Manager::SELECT2,
					'multiple'    => true,
					'label_block' => true,
					'default' => array( 'desktop', 'tablet', 'mobile' ),
					'options' => array(
						'desktop' => esc_html__( 'Desktop', 'desticky-for-elementor' ),
						'tablet'  => esc_html__( 'Tablet', 'desticky-for-elementor' ),
						'mobile'  => esc_html__( 'Mobile', 'desticky-for-elementor' ),
					),
					'condition' => array(
						'de_sticky_section_sticky' => 'yes',
					),
					'frontend_available' => true,
				)
			);

			$element->add_control(
				'de_sticky_section_sticky_z_index',
				array(
					'label'       => esc_html__( 'Z-index', 'desticky-for-elementor' ),
					'type'        => Elementor\Controls_Manager::NUMBER,
					'placeholder' => 1100,
					'min'         => 1,
					'max'         => 10000,
					'step'        => 1,
					'selectors'   => array(
						'{{WRAPPER}}.de-sticky-section-sticky--stuck' => 'z-index: {{VALUE}};',
					),
					'condition' => array(
						'de_sticky_section_sticky' => 'yes',
					),
				)
			);

			$element->add_control(
				'de_sticky_section_sticky_max_width',
				array(
					'label' => esc_html__( 'Max Width (px)', 'desticky-for-elementor' ),
					'type'  => Elementor\Controls_Manager::SLIDER,
					'range' => array(
						'px' => array(
							'min' => 500,
							'max' => 2000,
						),
					),
					'selectors'   => array(
						'{{WRAPPER}}.de-sticky-section-sticky--stuck' => 'max-width: {{SIZE}}{{UNIT}};',
					),
					'condition' => array(
						'de_sticky_section_sticky' => 'yes',
					),
				)
			);

			$element->add_responsive_control(
				'de_sticky_section_sticky_style_heading',
				array(
					'label'     => esc_html__( 'Sticky Section Style', 'desticky-for-elementor' ),
					'type'      => Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array(
						'de_sticky_section_sticky' => 'yes',
					),
				)
			);

			$element->add_responsive_control(
				'de_sticky_section_sticky_margin',
				array(
					'label'      => esc_html__( 'Margin', 'desticky-for-elementor' ),
					'type'       => Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%' ),
					'allowed_dimensions' => 'vertical',
					'placeholder' => array(
						'top'    => '',
						'right'  => 'auto',
						'bottom' => '',
						'left'   => 'auto',
					),
					'selectors' => array(
						'{{WRAPPER}}.de-sticky-section-sticky--stuck' => 'margin-top: {{TOP}}{{UNIT}}; margin-bottom: {{BOTTOM}}{{UNIT}};',
					),
					'condition' => array(
						'de_sticky_section_sticky' => 'yes',
					),
				)
			);

			$element->add_responsive_control(
				'de_sticky_section_sticky_padding',
				array(
					'label'      => esc_html__( 'Padding', 'desticky-for-elementor' ),
					'type'       => Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', 'em', '%' ),
					'selectors'  => array(
						'{{WRAPPER}}.de-sticky-section-sticky--stuck' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition' => array(
						'de_sticky_section_sticky' => 'yes',
					),
				)
			);

			$element->add_group_control(
				Elementor\Group_Control_Background::get_type(),
				array(
					'name'      => 'de_sticky_section_sticky_background',
					'selector'  => '{{WRAPPER}}.de-sticky-section-sticky--stuck',
					'condition' => array(
						'de_sticky_section_sticky' => 'yes',
					),
				)
			);

			$element->add_group_control(
				Elementor\Group_Control_Box_Shadow::get_type(),
				array(
					'name'      => 'de_sticky_section_sticky_box_shadow',
					'selector'  => '{{WRAPPER}}.de-sticky-section-sticky--stuck',
					'condition' => array(
						'de_sticky_section_sticky' => 'yes',
					),
				)
			);

			$element->add_control(
				'de_sticky_section_sticky_transition',
				array(
					'label'   => esc_html__( 'Transition Duration', 'desticky-for-elementor' ),
					'type'    => Elementor\Controls_Manager::SLIDER,
					'default' => array(
						'size' => 0.1,
					),
					'range' => array(
						'px' => array(
							'max'  => 3,
							'step' => 0.1,
						),
					),
					'selectors' => array(
						'{{WRAPPER}}.de-sticky-section-sticky--stuck.de-sticky-transition-in, {{WRAPPER}}.de-sticky-section-sticky--stuck.de-sticky-transition-out' => 'transition: margin {{SIZE}}s, padding {{SIZE}}s, background {{SIZE}}s, box-shadow {{SIZE}}s',
					),
					'condition' => array(
						'de_sticky_section_sticky' => 'yes',
					),
				)
			);

			$element->end_controls_section();
		}

		/**
		 * Enqueue scripts
		 *
		 * @return void
		 */
		public function enqueue_scripts() {

			wp_enqueue_script(
				'de-resize-sensor',
				DETHEMEKIT_ADDONS_URL . 'assets/js/lib/ResizeSensor.min.js' ,
				array( 'jquery' ),
				'1.7.0',
				true
			);

			wp_enqueue_script(
				'de-sticky-sidebar',
				DETHEMEKIT_ADDONS_URL . 'assets/js/lib/sticky-sidebar/sticky-sidebar.min.js' ,
				array( 'jquery', 'de-resize-sensor' ),
				'3.3.1',
				true
			);

			wp_enqueue_script(
				'jsticky',
				DETHEMEKIT_ADDONS_URL . 'assets/js/lib/jsticky/jquery.jsticky.js' ,
				array( 'jquery' ),
				'1.1.0',
				true
			);

			de_sticky_assets()->elements_data['columns'] = $this->columns_data;
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @access public
		 * @return object
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}
}

/**
 * Returns instance of De_Sticky_Element_Extension
 *
 * @return object
 */
function de_sticky_element_extension() {
	return De_Sticky_Element_Extension::get_instance();
}
