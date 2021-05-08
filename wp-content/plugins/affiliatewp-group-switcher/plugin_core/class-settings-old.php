<?php
class AFFWP_GS_Settings {

	protected $plugin_config;
	public $plugin_settings;

	public function __construct() {
		
		// Important
		$this->plugin_config = unserialize(AFFWP_GS_PLUGIN_CONFIG);
		$this->plugin_settings = $this->get_all_settings();
		
		// New settings tab & settings
		add_filter( 'affwp_settings_tabs', array( $this, 'settings_tab' ) );
		add_filter( 'affwp_settings', array( $this, 'settings' ), 10, 1 );
		
		// Custom settings UI
		add_action( 'admin_init', array( $this, 'save_custom_settings' ), 1 );
		add_action( 'admin_init', array( $this, 'output_custom_settings' ) );

	}
	
	// Check if on the plugins settings page
	public function check_if_settings_page() {
		if( isset($_GET['tab']) && $_GET['tab'] == $this->plugin_config['plugin_prefix'] ) return TRUE;
	}
	
	// Create the settings tab
	public function settings_tab( $tabs ) {
		$tabs[$this->plugin_config['plugin_prefix']] = __( $this->plugin_config['plugin_item_sname'], '' );
		return $tabs;
	}
	
	// Get all settings
	public function get_all_settings() {
		
		$options = affiliate_wp()->settings->get_all();
	
		// Add the standard
		$settings = $this->settings(array());
		foreach($settings[$this->plugin_config['plugin_prefix']] as $key => $value) {
			
			if (class_exists( 'Affiliate_WP' ) ) {
				if( !empty($options[$key]) ) {
					$thesettings[$key] = $options[$key];
				}
			}
		}
		
		// Add the custom settings
		$custom_settings_list = $this->custom_settings_list();
		foreach($custom_settings_list as $key => $data) {
			
			if (class_exists( 'Affiliate_WP' ) ) {
				$thesettings[$this->plugin_config['plugin_prefix'].'_'.$key] = $options[$this->plugin_config['plugin_prefix'].'_'.$key];
			}
			
		}
		
		return $thesettings;
	
	}
	
	// Get a plugin setting
	public function plugin_setting( $key ) {

		return $this->plugin_settings[$this->plugin_config['plugin_prefix'].'_'.$key ];
		
	}
	
	// Set Default Settings
	
	// Set the default settings
	public function set_default_settings() {}
	
	// Generate the form settings
	public function settings( $settings ) {
		
		if( is_admin() && $this->check_if_settings_page() ) {
			$license_msg = $this->license_status_msg();
			$license_msg = ( !empty($license_msg) ) ? $license_msg : '' ;
		} else {
			$license_msg = '';
		}

		$settings2 = array(
			$this->plugin_config['plugin_prefix'] => apply_filters( 'affwp_settings_gs',
				array(
					$this->plugin_config['plugin_prefix'].'_section_licensing' => array(
						'name' => '<strong>' . __( 'License Settings', '' ) . '</strong>',
						'desc' => '',
						'type' => 'header'
					),
					
					$this->plugin_config['plugin_prefix'].'_license_key' => array(
						'name' => __( 'License Key', '' ),
						'desc' => $license_msg . '<p>Activated license required for automatic updates.</p>',
						'type' => 'text',
					),
					
				)
			)
		);
		
		$settings2 = $this->check_integration_settings( $settings2 );

		
		// Merge settings
		$settings = array_merge( $settings, $settings2 );	
		
		return $settings;
	}
	
	
	// Remove integration settings when not required
	public function check_integration_settings( $settings2 ) {
		
		return $settings2;
	}
	
	///////////////////////////////// Custom Settings
	
	// Register the Custom settings as a field
	public function output_custom_settings() {
		
		add_settings_field(
			$this->plugin_config['plugin_prefix'].'AFFWP_MLA_matrix_settings_section',
			__( '', '' ),
			array( $this, 'output_custom_settings_form' ),
			'affwp_settings_'.$this->plugin_config['plugin_prefix'],
			'affwp_settings_'.$this->plugin_config['plugin_prefix']
		);
		
	}
	
	// Generate the Custom settings tables
	public function output_custom_settings_form() {
		
		echo $this->get_custom_settings_table( array( 'id' => '', 'section_title' => 'Group Switching Rules' ) );
		
	}
	
	// Save the Custom fields
	public function save_custom_settings() {
		
		if( isset( $_POST[$this->plugin_config['plugin_prefix']._process_custom_settings] ) && !empty( $_POST[$this->plugin_config['plugin_prefix']._process_custom_settings] ) ) {
			
			$options = array();
		
			$form_fields = $this->custom_settings_list();
			
			foreach($form_fields as $name => $data) {
				
				if( isset( $_POST[$this->plugin_config['plugin_prefix'].'_'.$name] ) ):
				
					$field = $_POST[$this->plugin_config['plugin_prefix'].'_'.$name];
					
					if($data['saniitize']) {
						$sanitize_function = 'sanitize_'.$data['type'];
						$field = $sanitize_function($field);
					}
					
					$options[$this->plugin_config['plugin_prefix'].'_'.$name] = $field;
				
				endif;
				
			}
			
			affiliate_wp()->settings->set( $options, TRUE );
			
		}
		
	}
	
	public function get_custom_setting_array($setting_key, $keys) {
		
		$array = $this->plugin_setting($setting_key);
		
		//print_r($array);
		
		$key_count = count($keys);
		
		if($key_count == 2) {
			$value = $array[$keys[1]][$keys[2]];
		}
		
		if($key_count == 3) {
			$value = $array[$keys[1]][$keys[2]][$keys[3]];
		}
		
		return $value;

	}
	
	// The raw fancy settings array
	public function custom_settings_list($type='') {
			
		$custom_settings_list = array(
		
			'gs_rules' => array(
				'type' => 'text_field',
				'label' => 'Switcher Rules',
				'saniitize' => (bool) FALSE,
			),
			
		);

		return $custom_settings_list;
		
	}
	
		// Generate the settings table
	public function get_custom_settings_table( $args=array() ) {
		
		ob_start();
		?>

<input type="hidden" name="<?php echo $this->plugin_config['plugin_prefix'];?>_process_custom_settings" value="1">
        
<div class='<?php echo $this->plugin_config['plugin_prefix'];?>custom_settings_container_<?php echo $args['id'] ;?>' style="background-color:white;padding:20px;margin-bottom:20px;">
<div style="text-align:left"><h4><?php echo $args['section_title'] ;?></h4></div>

<!-- Rules -->

<table width="750" height="127" cellpadding="4" class="form-table affgs rules">
    <tbody>
    
        <tr>
            <td>
                <table cellpadding="4">
                    <?php 
                    $rules_rows = $this->plugin_setting( 'gs_rules' ) ;
                    $rules_row_count = count($rules_rows);
                    if($rules_row_count <1) $rules_row_count = 1;
                    for ($row_key = 1; $row_key <= $rules_row_count; $row_key++) :
                    ?>
                    <?php 
                       $rule_name = $this->get_custom_setting_array('gs_rules', array( '1' => $row_key,  '2' =>'name') ) ; 
					   $trigger = $this->get_custom_setting_array('gs_rules', array( '1' => $row_key,  '2' =>'trigger') ) ;
					   $switcher_mode = $this->get_custom_setting_array('gs_rules', array( '1' => $row_key,  '2' =>'switcher_mode') ) ;
					   $affiliate_groups = $this->get_custom_setting_array('gs_rules', array( '1' => $row_key,  '2' =>'affiliate_groups') ) ;
                       ?>
                    <tbody class="gs_rules <?php echo ($row_key % 2 == 0) ? 'cs_row_even' : 'cs_row_odd'; ?>">
                    
                       <tr>
                            <!--<td><span class="rowName">Rule <?php echo $row_key; ?></td>-->
                            
                            <td>
                              <select name="<?php echo $this->plugin_config['plugin_prefix'];?>_gs_rules[<?php echo $row_key; ?>][trigger]">
                              	  <option value="" <?php if( $trigger == '' ) { ?>selected="selected"<?php }; ?>>Trigger</option>
                                  <option value="earnings_tier" <?php if( $trigger == 'earnings_tier' ) { ?>selected="selected"<?php }; ?>>Affiliate Earnings Tier</option>
                                  <option value="referrals_tier" <?php if( $trigger == 'referrals_tier' ) { ?>selected="selected"<?php }; ?>>Affiliate Referrals Tier</option>
                                  <?php if (defined('PMPRO_VERSION') ) : ?>
                                  <option value="pmp_level_change" <?php if( $trigger == 'pmp_level_change' ) { ?>selected="selected"<?php }; ?>>PMP Level Change</option>
                              	  <?php endif; ?>
                              </select>
                            </td>

                            <td>
                            
                              <!-- Earnings Tier -->
                              <?php $trigger_value_earnings = $this->get_custom_setting_array('gs_rules', array( '1' => $row_key,  '2' =>'trigger_value_earnings') ) ;?>
                              <div id="earnings_tier" <?php if( $trigger != 'earnings_tier') { ?> style="display:none;"<?php } ?>>
                              	<input type="text" name="<?php echo $this->plugin_config['plugin_prefix'];?>_gs_rules[<?php echo $row_key; ?>][trigger_value_earnings]" value="<?php echo $trigger_value_earnings ;?>">
                              </div>
                              
                              <!-- Referrals Tier -->
                              <?php $trigger_value_referrals = $this->get_custom_setting_array('gs_rules', array( '1' => $row_key,  '2' =>'trigger_value_referrals') ) ;?>
                              <div id="referrals_tier" <?php if( $trigger != 'referrals_tier') { ?> style="display:none;"<?php } ?>>
                              	<input type="text" name="<?php echo $this->plugin_config['plugin_prefix'];?>_gs_rules[<?php echo $row_key; ?>][trigger_value_referrals]" value="<?php echo $trigger_value_referrals ;?>">
                              </div>
                              
                              <!-- PMP Level Change -->
                              <?php if (defined('PMPRO_VERSION') ) : ?>
                              <?php $trigger_value = $this->get_custom_setting_array('gs_rules', array( '1' => $row_key,  '2' =>'trigger_value_pmp_level') ) ;?>
                              <div id="pmp_level_change" <?php if( $trigger != 'pmp_level_change') { ?> style="display:none;"<?php } ?>>
                              <select name="<?php echo $this->plugin_config['plugin_prefix'];?>_gs_rules[<?php echo $row_key; ?>][trigger_value_pmp_level]">
                                  <?php 
								  $pmp_levels = pmpro_getAllLevels(); 
								  foreach( $pmp_levels as $level ) :
								  ?>
                                  <option value="<?php echo $level->id ;?>" <?php if( $trigger_value == $level->id ) { ?>selected="selected"<?php }; ?>><?php echo $level->name ;?></option>
                              	  <?php endforeach; ?>
                              </select>
                              <div>
                              <?php endif; ?>
                              
                            </td>
                            
                            <td class="removeBtnPlace">
							<?php if($row_key >1) : ?>
                            <input class="removeRow" type="button" value="X"> 
                            <?php endif ;?>
                            </td>
                            
                        </tr>
                        
                        <tr>
                        <td>Affiliate Group Changes</td>
                        
                        <!-- Group Changes -->
                        	<td>
                              <select name="<?php echo $this->plugin_config['plugin_prefix'];?>_gs_rules[<?php echo $row_key; ?>][switcher_mode]">
                              	  <option value="">Switching Mode</option>
                                  <option value="reset" <?php if( $switcher_mode == 'reset' ) { ?>selected="selected"<?php }; ?>>Reset</option>
                                  <option value="add" <?php if( $switcher_mode == 'add' ) { ?>selected="selected"<?php }; ?>>Add</option>
                                  <option value="remove" <?php if( $switcher_mode == 'remove' ) { ?>selected="selected"<?php }; ?>>Remove</option>
                              </select>
                            </td>
                            
                            <td>
                              <select name="<?php echo $this->plugin_config['plugin_prefix'];?>_gs_rules[<?php echo $row_key; ?>][affiliate_groups]">
                                  <?php 
								  $aff_groups = get_active_affiliate_groups();
								  foreach( $aff_groups as $group_id => $group_vars ) :
								  ?>
                                  <option value="<?php echo $group_id; ?>" <?php if( $affiliate_groups == $group_id ) { ?>selected="selected"<?php }; ?>><?php echo $group_vars['name']; ?></option>
                                  <?php endforeach; ?>
                              </select>
                            </td>
                        </tr>
                        
                    </tbody>
                    <?php endfor; ?>
                    
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <input type="button" value="Add New Rule" class="addRow" data-clone-class=".gs_rules" data-parent-class=".form-table">
            </td>
        </tr>

    </tbody>
</table>
<?php 
//$rules_rows = $this->plugin_setting( 'gs_rules' ) ; 
//print_r($rules_rows);
?>
<!-- END Rules -->


</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script>
    $(document).ready(function () {

        $("select[name*=AFFWP_GS_gs_rules]").each(showDivOnOption).change(showDivOnOption)

        function showDivOnOption() {
            var option = $(this).find(":selected").val();
            var parent = $(this).closest("tbody.gs_rules");
            parent.find("div[id]").hide();
            parent.find("#" + option).show();
        }
    });
</script>
        
        <?php
    	$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
	}
	
	
	////////////////////////////////////////////////
	

	/////////// Licensing Methods ///////////////
	
	
	// Only modify this method
	public function remove_license_settings() {
		$options = affiliate_wp()->settings->get_all();
		unset( $options[$this->plugin_config['plugin_prefix'].'_'.'license_key'] );
		update_option( 'affwp_settings', $options );
	}
	
	// Deactive license
	public function deactivate_license() {
	
		if (class_exists('Click_Studio_Licenses_V1_1')) {
			
			if( 
			(isset($_GET[$this->plugin_config['plugin_prefix'].'_license_change'])) &&
			($_GET[$this->plugin_config['plugin_prefix'].'_license_change'] == 'deactivate') 
			){
				
				$license = new Click_Studio_Licenses_V1_1($this->plugin_config, $this->plugin_settings);
				if($license->deactivate_license()) {
					
					$this->remove_license_settings();
					
					// Redirect to settings page
					$location = $_SERVER['HTTP_REFERER'];
					wp_safe_redirect($location);
				
				}
			
			}
		
		}
		
	}
	
	// Get the license message actions and messages. Also activate license keys.
	public function license_status_msg() {
		
		if (class_exists('Click_Studio_Licenses_V1_1')) {
			
			$license = new Click_Studio_Licenses_V1_1($this->plugin_config, $this->plugin_settings);
			$license->activate_license();
			
			$license_message = $license->license_status_msg();
			return $license_message;
			
		}
	
	}
	
} // End of class
?>