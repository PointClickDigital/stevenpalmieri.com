<?php
class AFFWP_GS_Base {

	protected $plugin_settings;
	protected $plugin_config;

	public function __construct() {
		
		//$this->plugin_config = unserialize(AFFWP_GS_PLUGIN_CONFIG);
		//$this->plugin_settings = unserialize(AFFWP_GS_PLUGIN_SETTINGS);
		$affwp_group_switcher = affiliate_wp_group_switcher();
		$this->plugin_config = $affwp_group_switcher->plugin_config;
		$this->plugin_settings = $affwp_group_switcher->plugin_settings;
		
		$lps = get_site_option(  $this->plugin_config['plugin_prefix'].'_'.'lps', '' );
		if( !empty($lps) && $lps != '2' ) :
		
			$this->load_textdomain();
			$this->includes();
			$this->setup_objects();	
		
		endif;
		
		//endif;		
	}
	
	public function load_textdomain() {

		// Set filter for plugin's languages directory
		$lang_dir = $this->plugin_config['plugin_lang_dir'];

		global $wp_version;
		$get_locale = get_locale();
		if ( $wp_version >= 4.7 ) {
			$get_locale = get_user_locale();
		}

		$locale = apply_filters( 'plugin_locale', $get_locale, 'affiliatewp-group-switcher' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'affiliatewp-group-switcher', $locale );

		$mofile_global = WP_LANG_DIR . '/affiliatewp-group-switcher/'. $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/ folder
			load_textdomain( 'affiliatewp-group-switcher', $mofile_global );
		}else {
			// Load the default language files from plugin
			load_plugin_textdomain( 'affiliatewp-group-switcher', false, $lang_dir );
		}
	}
	
	private function includes() {
		
		// Core
		//require_once $this->plugin_config['plugin_dir'] . 'plugin_core/class-template-loader.php';
		require_once $this->plugin_config['plugin_dir'] . 'plugin_core/class-common.php';
		require_once $this->plugin_config['plugin_dir'] . 'plugin_core/class-hooks.php';
		
	}
	
	private function setup_objects() {
		$this->hooks = new AFFWP_GS_Hooks();
	}
		
} // End of class

?>