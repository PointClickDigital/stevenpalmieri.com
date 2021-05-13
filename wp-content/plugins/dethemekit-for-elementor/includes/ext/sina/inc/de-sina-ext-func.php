<?php
namespace De_Sina_Extension;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \De_Sina_Extension\De_Sina_Extension_Base;
use \De_Sina_Extension\De_Sina_Ext_Controls;

/**
 * De_Sina_Ext_Functions Class For widgets functionality
 *
 * @since 3.0.0
 */
abstract class De_Sina_Ext_Functions extends De_Sina_Extension_Base{
	 /**
	 * Enqueue CSS files
	 *
	 * @since 3.0.0
	 */
	public function widget_styles() {
		wp_enqueue_style( 'sina-morphing-anim', DETHEMEKIT_ADDONS_SINA_EXT_URL .'assets/css/sina-morphing.min.css', [], DETHEMEKIT_ADDONS_VERSION );
		wp_enqueue_style( 'de-curtain-animation-normalize', DETHEMEKIT_ADDONS_SINA_EXT_URL .'assets/css/normalize.css', [], DETHEMEKIT_ADDONS_VERSION );
		wp_enqueue_style( 'de-curtain-animation-demo', DETHEMEKIT_ADDONS_SINA_EXT_URL .'assets/css/demo.css', [], DETHEMEKIT_ADDONS_VERSION );
		wp_enqueue_style( 'de-curtain-animation-revealer', DETHEMEKIT_ADDONS_SINA_EXT_URL .'assets/css/revealer.css', [], DETHEMEKIT_ADDONS_VERSION );
		wp_enqueue_style( 'de-curtain-animation-pater', DETHEMEKIT_ADDONS_SINA_EXT_URL .'assets/css/pater.css', [], DETHEMEKIT_ADDONS_VERSION );
		// wp_enqueue_style( 'de-curtain-animation-css', DETHEMEKIT_ADDONS_SINA_EXT_URL .'assets/css/de-curtain-animation.css', [], DETHEMEKIT_ADDONS_VERSION );
	}

	 /**
	 * Enqueue JS files
	 *
	 * @since 3.0.0
	 */
	public function widget_scripts() {
		wp_enqueue_script( 'de-curtain-animation-anime', DETHEMEKIT_ADDONS_SINA_EXT_URL .'assets/js/anime.min.js');
		wp_enqueue_script( 'de-curtain-animation-scrollmonitor', DETHEMEKIT_ADDONS_SINA_EXT_URL .'assets/js/scrollMonitor.js');
		wp_enqueue_script( 'de-curtain-animation-main', DETHEMEKIT_ADDONS_SINA_EXT_URL .'assets/js/main.js');
		// wp_enqueue_script( 'de-curtain-animation-js', DETHEMEKIT_ADDONS_SINA_EXT_URL .'assets/js/de_curtain_animation.js');
		wp_enqueue_script( 'de-curtain-animation-preview-js', DETHEMEKIT_ADDONS_SINA_EXT_URL .'assets/js/de_curtain_animation.preview.js', [ 'elementor-frontend' ], false, true);
		wp_enqueue_script( 'de-scroll-animation-preview-js', DETHEMEKIT_ADDONS_SINA_EXT_URL .'assets/js/de_scroll_animation.preview.js', [ 'elementor-frontend' ], false, true);
	}

	/**
	 * Initialize the plugin
	 *
	 * @since 3.0.0
	 */
	public function init() {
		// Enqueue Widget Styles
		add_action( 'elementor/frontend/after_register_styles', [ $this, 'widget_styles' ] );

		// Enqueue Widget Scripts
		// add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'widget_scripts' ] );
		add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'widget_scripts' ] );

		$this->files();
		$this->load_actions();

		De_Sina_Ext_Controls::instance();
		De_Curtain_Animation_Controls::instance();
		De_Scroll_Animation_Controls::instance();
	}

	/**
	 * Include helper & hooks files
	 *
	 * @since 3.0.0
	 */
	public function files() {
		require_once( DETHEMEKIT_ADDONS_SINA_EXT_INC .'de-sina-ext-controls-extend.php' );
		require_once( DETHEMEKIT_ADDONS_SINA_EXT_INC .'de-curtain-animation-controls.php' );
		require_once( DETHEMEKIT_ADDONS_SINA_EXT_INC .'de-scroll-animation-controls.php' );
	}
}