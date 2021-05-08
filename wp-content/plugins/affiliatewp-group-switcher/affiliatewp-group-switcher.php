<?php
/*
Plugin Name: AffiliateWP - Group Switcher
Plugin URI: http://clickstudio.com.au
Description: Dynamic group switching
Version: 1.1.5
Author: Click Studio
Author URI: http://clickstudio.com.au
License: GPL2
*/

class AFFWP_GS {

	private static $instance = NULL;

	public static function instance() {

		if ( NULL === self::$instance ) {

			self::$instance = new self;
	
			self::$instance->includes();
			self::$instance->setup_objects();
			self::$instance->setup_hooks();
        }

        return self::$instance;	

	}

	public function __construct() {

		$plugin_config = array();
		
		$plugin_config['plugin_file'] = __FILE__;
		$plugin_config['plugin_dir'] = plugin_dir_path( __FILE__ );
		$plugin_config['plugin_lang_dir'] = basename( dirname( __FILE__ ) ) . '/languages';
		
		// Item name must be identical to the name on Easy Digital Downloads server.
		$plugin_config['plugin_item_id'] = '22455';
		$plugin_config['plugin_item_name'] = 'AffiliateWP - Group Switcher';
		$plugin_config['plugin_item_sname'] = 'Group Switcher'; // Used for non critical things. Can be changed
		$plugin_config['plugin_prefix'] = 'AFFWP_GS';
		$plugin_config['plugin_version'] = '1.1.5';
		$plugin_config['plugin_updater_url'] = 'https://www.clickstudio.com.au';	
		$plugin_config['plugin_author'] = 'Click Studio';

		$this->plugin_config = $plugin_config;	
		
		//update_site_option( $plugin_config['plugin_prefix'].'_version', $plugin_config['plugin_version'] );
		
	}
	
	// Includes
	private function includes() {
		require_once $this->plugin_config['plugin_dir'] . 'plugin_core/class-settings.php';
		require_once $this->plugin_config['plugin_dir'] . 'plugin_core/class-base.php';	
		if( is_admin() ) {
		require_once $this->plugin_config['plugin_dir'] . 'includes/class-licenses.php';
		require_once $this->plugin_config['plugin_dir'] . 'includes/class-updater.php';
		}
	}
	
	// Set up objects
	private function setup_objects() {
		
		// AFFWP_GS_PLUGIN_CONFIG
		//define( $this->plugin_config['plugin_prefix'].'_PLUGIN_CONFIG', serialize( $this->plugin_config ) );
		
		// AFFWP_GS_PLUGIN_SETTINGS
		self::$instance->settings = new AFFWP_GS_Settings();
		self::$instance->plugin_settings = self::$instance->settings->plugin_settings;
		//define( $this->plugin_config['plugin_prefix'].'_PLUGIN_SETTINGS', serialize( self::$instance->plugin_settings ) );
		
		self::$instance->base = new AFFWP_GS_Base($this->plugin_config, self::$instance->plugin_settings);
		if( is_admin() ) {
		self::$instance->license = new Click_Studio_Licenses_V1_5($this->plugin_config, self::$instance->plugin_settings);
		self::$instance->updater = new Click_Studio_Updater_V1_4($this->plugin_config, self::$instance->license->get_license_option('license_key'));
		}
	}
	
	// Set up hooks
	private function setup_hooks() {}
	
} // End of class

// Include the public functions
require_once plugin_dir_path( __FILE__ ).'functions.php';


// Dependency check
add_action( 'init', 'affiliate_wp_group_switcher');
function affiliate_wp_group_switcher() {
	
	$activation_config = array(
		'plugin_name' => 'AffiliateWP - Affiliate Groups Switcher',
		'plugin_path' => plugin_dir_path( __FILE__ ),
		'plugin_file' => basename( __FILE__ ),
		'plugin_dependencies' => array(
			'Affiliate_WP' => array(
				'name' => 'AffiliateWP',
				'plugin_folder_file' => 'affiliate-wp/affiliate-wp.php',
				'url' => 'https://affiliatewp.com/ref/613/'
			)
		),
		
	);
	
	require_once 'includes/class-activation.php';
	$activation = new Click_Studio_Activation_V1_1( $activation_config );
	
	// If all dependencies are fine return instance
	if($activation->check_dependencies()) {
		return AFFWP_GS::instance();
		register_activation_hook( __FILE__, 'AFFWP_GS_Activate_Plugin' );
	}
	
}

// Activation hook	
//register_activation_hook( __FILE__, 'AFFWP_GS_Activate_Plugin' );
function AFFWP_GS_Activate_Plugin() {}

// Deactivation hook
register_deactivation_hook( __FILE__, 'AFFWP_GS_Deactivate_Plugin' );
function AFFWP_GS_Deactivate_Plugin() {}

// Functions and actions required on plugin load
//require_once plugin_dir_path( __FILE__ ) . 'functions.php';

?>