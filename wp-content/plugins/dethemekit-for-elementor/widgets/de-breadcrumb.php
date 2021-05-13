<?php
namespace DethemeKit\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Elementor Hello World
 *
 * Elementor widget for breadcrumb.
 *
 * @since 1.0.0
 */
class De_Breadcrumb extends Widget_Base {

	/**
	 * Retrieve the widget name.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'de-breadcrumb';
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'De Breadcrumb', 'detheme-kit' );
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-product-breadcrumbs';
	}

	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * Note that currently Elementor supports only one category.
	 * When multiple categories passed, Elementor uses the first one.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'dethemekit-elements' ];
	}

	/**
	 * Retrieve the list of scripts the widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return [ 'detheme-kit' ];
	}

	/**
	 * Register the widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _register_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Detheme Kit Breadcrumb', 'detheme-kit' ),
			]
		);

		$this->add_control(
			'selected_icon',
			[
				'label' => __( 'Separator Icon', 'detheme-kit' ),
				'type' => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default' => [
					'value' => 'fas fa-chevron-right',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_responsive_control(
			'size',
			[
				'label' => __( 'Separator Size', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 6,
						'max' => 300,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .breadcrumbs i' => 'font-size: {{SIZE}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'separator_margin',
			[
				'label' => __( 'Separator Margin', 'detheme-kit' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'default' => [
					'unit' => 'px',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'isLinked' => false
				],
				'selectors' => [
					'{{WRAPPER}} .breadcrumbs i' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'separator_padding',
			[
				'label' => __( 'Separator Padding', 'detheme-kit' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'default' => [
					'unit' => 'px',
					'top' => '0',
					'right' => '10',
					'bottom' => '0',
					'left' => '10',
					'isLinked' => false
				],
				'selectors' => [
					'{{WRAPPER}} .breadcrumbs i' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label' => __( 'Alignment', 'detheme-kit' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => __( 'Left', 'detheme-kit' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'detheme-kit' ),
						'icon' => 'eicon-text-align-center',
					],
					'flex-end' => [
						'title' => __( 'Right', 'detheme-kit' ),
						'icon' => 'eicon-text-align-right',
					]
				],
				'default' => 'flex-start',
				'selectors' => [
					'{{WRAPPER}} .uf-breadcrumbs' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Font', 'detheme-kit' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'breadcrumb_color',
			[
				'label' => __( 'Text Color', 'detheme-kit' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#000000',
				'global' => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .breadcrumbs' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'breadcrumb_link_color',
			[
				'label' => __( 'Link Color', 'detheme-kit' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#000000',
				'global' => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .breadcrumbs a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'breadcrumb_link_color_on_hover',
			[
				'label' => __( 'Link Color on hover', 'detheme-kit' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#000000',
				'global' => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => [
					'{{WRAPPER}} .breadcrumbs a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'breadcrumb_icon_color',
			[
				'label' => __( 'Separator Icon Color', 'detheme-kit' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#000000',
				'global' => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .breadcrumbs i' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .breadcrumbs',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'border',
				'selector' => '{{WRAPPER}} .breadcrumbs',
			]
		);

		

		$this->end_controls_section();
	}

	/**
	 * Generate breadcrumbs
	 */
	function get_content_template_breadcrumb() {
		echo '<div class="container">';
		echo '<div class="uf-breadcrumbs">';
		echo '<a href="'.home_url().'" rel="nofollow">Home</a>';
		if (is_category() || is_single()) {
			echo '<i aria-hidden="true" class="{{{ settings.selected_icon.value }}}" />';
			$categories = get_the_category_list(' &bull; ');
			if (is_single()) {
				if (!empty($categories)) {
					echo $categories;
					echo '<i class="{{{ settings.selected_icon.value }}}" />';
				}
				the_title();
			}
		} elseif (is_page()) {
			echo '<i aria-hidden="true" class="{{{ settings.selected_icon.value }}}" />';
			the_title();
		} elseif (is_search()) {
			echo '<i aria-hidden="true" class="{{{ settings.selected_icon.value }}}" />';
			echo "Search Results for... ";
			echo '"<em>';
			echo the_search_query();
			echo '</em>"';
		}
		echo '</div></div>';
	}

	function get_render_breadcrumb() {
		$settings = $this->get_settings_for_display();
		echo '<div class="container">';
		echo '<div class="uf-breadcrumbs">';
		echo '<a href="'.home_url().'" rel="nofollow">Home</a>';
		if (is_category() || is_single()) {
			Icons_Manager::render_icon( $settings['selected_icon'], [ 'aria-hidden' => 'true' ] );
			$categories = get_the_category_list(' &bull; ');
			if (is_single()) {
				if (!empty($categories)) {
					echo $categories;
					Icons_Manager::render_icon( $settings['selected_icon'], [ 'aria-hidden' => 'true' ] );
				}
				the_title();
			}
		} elseif (is_page()) {
			Icons_Manager::render_icon( $settings['selected_icon'], [ 'aria-hidden' => 'true' ] );
			the_title();
		} elseif (is_search()) {
			Icons_Manager::render_icon( $settings['selected_icon'], [ 'aria-hidden' => 'true' ] );
			echo "Search Results for... ";
			echo '"<em>';
			echo the_search_query();
			echo '</em>"';
		}
		echo '</div></div>';
	}
	
	/**
	 * Render the widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function render() {
		if ( ! function_exists( 'calia_breadcrumbs' ) ) { ?>
			<div class="breadcrumbs">
				<?php $this->get_render_breadcrumb(); ?>
			</div>
		<?php 
		} else {
			do_action( 'calia_breadcrumbs' ); 
		}
	}

	
	/**
	 * Render the widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _content_template() {
		if ( ! function_exists( 'calia_breadcrumbs' ) ) { ?>
			<div class="breadcrumbs">
				<?php $this->get_content_template_breadcrumb(); ?>
			</div>
		<?php 
		} else {
			// do_action( 'calia_breadcrumbs' ); 
			$this->get_content_template_breadcrumb();
		}
	}
}
