<?php

class AFFWP_GS_Common {
	
	protected $plugin_settings;
	protected $plugin_config;

	public function __construct() {
		
		//$this->plugin_config = unserialize(AFFWP_GS_PLUGIN_CONFIG);
		//$this->plugin_settings = unserialize(AFFWP_GS_PLUGIN_SETTINGS);
		$affwp_group_switcher = affiliate_wp_group_switcher();
		$this->plugin_config = $affwp_group_switcher->plugin_config;
		$this->plugin_settings = $affwp_group_switcher->plugin_settings;

	}
	
	// Get a plugin setting
	public function plugin_setting( $key ) {

		return $this->plugin_settings[$this->plugin_config['plugin_prefix'].'_'.$key ];
		
	}
	
	// Check if on the settings page
	public function is_settings_page() {
		
		if( isset($_GET['tab']) && $_GET['tab'] == $this->plugin_config['plugin_prefix'] ) {
			return (bool) TRUE;
		}
	}
	
}

?>