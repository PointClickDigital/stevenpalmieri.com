<?php
class AFFWP_GS_Settings {

    protected $plugin_config;
    public $plugin_settings;

    public function __construct() {

        // Important
        /*if (defined('AFFWP_GS_PLUGIN_CONFIG')) {
            $this->plugin_config = unserialize(AFFWP_GS_PLUGIN_CONFIG);
        }else{
            $this->plugin_config = array();
        }*/
        $this->plugin_config = affiliate_wp_group_switcher()->plugin_config;

        $this->plugin_settings = $this->get_all_settings();

        // Plugin upgrade tasks
        $this->plugin_upgrade_tasks();

        // New settings tab & settings
        add_filter('affwp_settings_tabs', array($this, 'settings_tab'));
        add_filter('affwp_settings', array($this, 'settings'), 10, 1);

        // Custom settings UI
        add_action('admin_init', array($this, 'save_custom_settings'), 1);
        add_action('admin_init', array($this, 'output_custom_settings'));

        if ( $this->check_if_settings_page() ) {
            add_action('admin_init', array($this, 'deactivate_license'), 1);
        }

    }

    // Plugin upgrade tasks
    public function plugin_upgrade_tasks() {

        $previous_version = get_site_option($this->plugin_config['plugin_prefix'] . '_version');
        $new_version = $this->plugin_config['plugin_version'];

        if ( !empty($previous_version) ) {

            // update lps
            if ( $previous_version < '0.9.5' )  :
                update_site_option($this->plugin_config['plugin_prefix'] . '_lps', 1);
            endif;

            // update db version
            if ( $previous_version != $new_version ) :
                update_site_option($this->plugin_config['plugin_prefix'] . '_version', $this->plugin_config['plugin_version']);
            endif;

        } else {

            update_site_option($this->plugin_config['plugin_prefix'] . '_version', $this->plugin_config['plugin_version']);

        }

    }

    // Check if on the plugins settings page
    public function check_if_settings_page() {
        if ( isset($_GET['tab']) && $_GET['tab'] == $this->plugin_config['plugin_prefix'] ) return TRUE;
    }

    // Create the settings tab
    public function settings_tab($tabs) {
        $tabs[$this->plugin_config['plugin_prefix']] = __($this->plugin_config['plugin_item_sname'], '');
        return $tabs;
    }

    // Get all settings
    public function get_all_settings() {

        $options = affiliate_wp()->settings->get_all();

        $thesettings = array();

        // Add the standard
        $settings = $this->settings(array());
        foreach ($settings[$this->plugin_config['plugin_prefix']] as $key => $value) {

            if ( class_exists('Affiliate_WP') ) {
                if ( !empty($options[$key]) ) {
                    $thesettings[$key] = $options[$key];
                }
            }
        }

        // Add the custom settings
        $custom_settings_list = $this->custom_settings_list();

        if ( class_exists('Affiliate_WP') ) {

            foreach ($custom_settings_list as $key => $data) {
                if ( !empty($options[$this->plugin_config['plugin_prefix'] . '_' . $key]) ) :
                    $thesettings[$this->plugin_config['plugin_prefix'] . '_' . $key] = $options[$this->plugin_config['plugin_prefix'] . '_' . $key];
                endif;
            }

        }

        return $thesettings;

    }

    // Get a plugin setting
    public function plugin_setting($key) {

        if ( !empty($this->plugin_settings[$this->plugin_config['plugin_prefix'] . '_' . $key]) ) :
            return $this->plugin_settings[$this->plugin_config['plugin_prefix'] . '_' . $key];
        endif;

    }

    // Set Default Settings

    // Set the default settings
    public function set_default_settings() {
    }

    // Generate the form settings
    public function settings($settings) {

        if ( is_admin() && $this->check_if_settings_page() ) {
            $license_msg = $this->license_status_msg();
            $license_msg = (!empty($license_msg)) ? $license_msg : '';
        } else {
            $license_msg = '';
        }

        $settings2 = array(
            $this->plugin_config['plugin_prefix'] => apply_filters('affwp_settings_gs',
                array(
                    $this->plugin_config['plugin_prefix'] . '_section_licensing' => array(
                        'name' => '<strong>' . __('License Settings', 'affiliatewp-group-switcher') . '</strong>',
                        'desc' => '',
                        'type' => 'header'
                    ),

                    $this->plugin_config['plugin_prefix'] . '_license_key' => array(
                        'name' => __('License Key', 'affiliatewp-group-switcher'),
                        'desc' => $license_msg,
                        'type' => 'text',
                        'disabled' => $this->is_license_valid()
                    ),

                )
            )
        );

        $settings2 = $this->check_integration_settings($settings2);


        // Merge settings
        $settings = array_merge($settings, $settings2);

        return $settings;
    }


    // Remove integration settings when not required
    public function check_integration_settings($settings2) {

        return $settings2;
    }

    ///////////////////////////////// Custom Settings

    // Register the Custom settings as a field
    public function output_custom_settings() {

        add_settings_field(
            $this->plugin_config['plugin_prefix'] . 'AFFWP_MLA_matrix_settings_section',
            '',
            array($this, 'output_custom_settings_form'),
            'affwp_settings_' . $this->plugin_config['plugin_prefix'],
            'affwp_settings_' . $this->plugin_config['plugin_prefix']
        );

    }

    // Generate the Custom settings tables
    public function output_custom_settings_form() {

        echo $this->get_custom_settings_table(array('id' => '', 'section_title' => __('Group Switching Rules', 'affiliatewp-group-switcher')));

    }

    // Save the Custom fields
    public function save_custom_settings() {

        if ( isset($_POST[$this->plugin_config['plugin_prefix'] . '_process_custom_settings']) && !empty($_POST[$this->plugin_config['plugin_prefix'] . '_process_custom_settings']) ) {

            $options = array();

            $form_fields = $this->custom_settings_list();

            foreach ($form_fields as $name => $data) {

                if ( isset($_POST[$this->plugin_config['plugin_prefix'] . '_' . $name]) ):

                    $field = $_POST[$this->plugin_config['plugin_prefix'] . '_' . $name];

                    if ( $data['saniitize'] ) {
                        $sanitize_function = 'sanitize_' . $data['type'];
                        $field = $sanitize_function($field);
                    }

                    $options[$this->plugin_config['plugin_prefix'] . '_' . $name] = $field;

                endif;

            }

            affiliate_wp()->settings->set($options, TRUE);

        }

    }

    public function get_custom_setting_array($setting_key, $keys) {

        $array = $this->plugin_setting($setting_key);

        //print_r($array);

        $key_count = count($keys);

        $value = '';

        if ( $key_count == 2 && isset($array[$keys[1]][$keys[2]]) ) {
            $value = $array[$keys[1]][$keys[2]];
        }

        if ( $key_count == 3 && isset($array[$keys[1]][$keys[2]][$keys[3]] ) ) {
            $value = $array[$keys[1]][$keys[2]][$keys[3]];
        }

        return $value;

    }

    // The raw fancy settings array
    public function custom_settings_list($type = '') {

        $custom_settings_list = array(

            'gs_rules' => array(
                'type' => 'text_field',
                'label' => __('Switcher Rules', 'affiliatewp-group-switcher'),
                'saniitize' => (bool)FALSE,
            ),

        );

        return $custom_settings_list;

    }

    // Generate the settings table
    public function get_custom_settings_table($args = array()) {

        ob_start();
        ?>

        <input type="hidden" name="<?php echo $this->plugin_config['plugin_prefix']; ?>_process_custom_settings"
               value="1">

        <div class='<?php echo $this->plugin_config['plugin_prefix']; ?>custom_settings_container_<?php echo $args['id']; ?>'
             style="background-color:white;padding:20px;margin-bottom:20px;">
            <div style="text-align:left"><h4><?php echo $args['section_title']; ?></h4></div>

            <!-- Rules -->

            <table width="100%" height="127" cellpadding="4" class="form-table affgs rules">
                <tbody>

                <tr>
                    <td>
                        <table width="100%" cellpadding="4" cellspacing="0">
                            <?php
                            $rules_rows = $this->plugin_setting('gs_rules');
                            $rules_row_count = count($rules_rows);
                            if ( $rules_row_count < 1 ) $rules_row_count = 1;
                            for ($row_key = 1; $row_key <= $rules_row_count; $row_key++) :
                                ?>
                                <?php
                                $rule_name = $this->get_custom_setting_array('gs_rules', array('1' => $row_key, '2' => 'name'));
                                $trigger = $this->get_custom_setting_array('gs_rules', array('1' => $row_key, '2' => 'trigger'));
                                $switcher_mode = $this->get_custom_setting_array('gs_rules', array('1' => $row_key, '2' => 'switcher_mode'));
                                $affiliate_groups = $this->get_custom_setting_array('gs_rules', array('1' => $row_key, '2' => 'affiliate_groups'));
                                ?>
                                <tbody class="gs_rules <?php echo ($row_key % 2 == 0) ? 'cs_row_even' : 'cs_row_odd'; ?>">

                                <tr>
                                    <!--<td><span class="rowName">Rule <?php echo $row_key; ?></td>-->

                                    <td style="width:20%;">
                                        <select name="<?php echo $this->plugin_config['plugin_prefix']; ?>_gs_rules[<?php echo $row_key; ?>][trigger]">
                                            <option value=""
                                                    <?php if ($trigger == '') { ?>selected="selected"<?php }; ?>><?php __('Trigger', 'affiliatewp-group-switcher'); ?></option>

                                            <!--<option disabled></option>-->
                                            <option disabled>- Existing Affiliates -</option>
                                            <option value="earnings_tier"
                                                    <?php if ($trigger == 'earnings_tier') { ?>selected="selected"<?php }; ?>><?php _e('Affiliate Earnings Tier', 'affiliatewp-group-switcher'); ?></option>
                                            <option value="referrals_tier"
                                                    <?php if ($trigger == 'referrals_tier') { ?>selected="selected"<?php }; ?>><?php _e('Affiliate Referrals Tier', 'affiliatewp-group-switcher'); ?></option>

                                            <option disabled></option>
                                            <option disabled>- Existing and/or Auto Registering Affiliates -</option>
                                            <!-- WooCommerce Product Purchased -->
                                            <?php if ( class_exists('woocommerce') ) : ?>
                                                <option value="woo_product_purchased"
                                                        <?php if ($trigger == 'woo_product_purchased') { ?>selected="selected"<?php }; ?>><?php _e('WooCommerce Product Purchased', 'affiliatewp-group-switcher'); ?></option>
                                            <?php endif; ?>

                                            <!-- EDD Product Purchased -->
                                            <?php if ( class_exists('Easy_Digital_Downloads') ) : ?>
                                                <option value="edd_product_purchased"
                                                        <?php if ($trigger == 'edd_product_purchased') { ?>selected="selected"<?php }; ?>><?php _e('EDD Download Purchased', 'affiliatewp-group-switcher'); ?></option>
                                            <?php endif; ?>

                                            <!-- WooCommerce Memberships Plan Change -->
                                            <?php if ( class_exists('WC_Memberships') ) : ?>
                                                <option value="wcm_membership_status"
                                                        <?php if ($trigger == 'wcm_membership_status') { ?>selected="selected"<?php }; ?>><?php _e('WC Membership Status Change', 'affiliatewp-group-switcher'); ?></option>
                                            <?php endif; ?>

                                            <!-- PMP Level Change -->
                                            <?php if ( defined('PMPRO_VERSION') ) : ?>
                                                <option value="pmp_level_change"
                                                        <?php if ($trigger == 'pmp_level_change') { ?>selected="selected"<?php }; ?>><?php _e('PMP Level Change', 'affiliatewp-group-switcher'); ?></option>
                                            <?php endif; ?>

                                            <option disabled></option>
                                            <option disabled>- Registering Affiliates -</option>
                                            <!-- WooCommerce Memberships - Is Member -->
                                            <?php if ( class_exists('WC_Memberships') ) : ?>
                                                <option value="wcm_membership_is_member"
                                                        <?php if ($trigger == 'wcm_membership_is_member') { ?>selected="selected"<?php }; ?>><?php _e('WC Membership - Is Active Member', 'affiliatewp-group-switcher'); ?></option>
                                            <?php endif; ?>

                                            <option disabled></option>
                                        </select>
                                    </td>

                                    <td>

                                        <!-- Earnings Tier -->
                                        <?php $trigger_value_earnings = $this->get_custom_setting_array('gs_rules', array('1' => $row_key, '2' => 'trigger_value_earnings')); ?>
                                        <div id="earnings_tier" <?php if ( $trigger != 'earnings_tier' ) { ?> style="display:none;"<?php } ?>>
                                            <input type="text"
                                                   placeholder="<?php _e('Paid Earnings eg: 500', 'affiliatewp-group-switcher'); ?>"
                                                   name="<?php echo $this->plugin_config['plugin_prefix']; ?>_gs_rules[<?php echo $row_key; ?>][trigger_value_earnings]"
                                                   value="<?php echo $trigger_value_earnings; ?>">
                                        </div>

                                        <!-- Referrals Tier -->
                                        <?php $trigger_value_referrals = $this->get_custom_setting_array('gs_rules', array('1' => $row_key, '2' => 'trigger_value_referrals')); ?>
                                        <div id="referrals_tier" <?php if ( $trigger != 'referrals_tier' ) { ?> style="display:none;"<?php } ?>>
                                            <input type="text"
                                                   placeholder="<?php _e('Paid Referrals. eg: 100', 'affiliatewp-group-switcher'); ?>"
                                                   name="<?php echo $this->plugin_config['plugin_prefix']; ?>_gs_rules[<?php echo $row_key; ?>][trigger_value_referrals]"
                                                   value="<?php echo $trigger_value_referrals; ?>">
                                        </div>

                                        <!-- WooCommerce Product Purchased -->
                                        <?php if ( class_exists('woocommerce') ) : ?>
                                            <?php
                                            $trigger_value = $this->get_custom_setting_array('gs_rules', array('1' => $row_key, '2' => 'trigger_value_woo_product'));
                                            ?>
                                            <div id="woo_product_purchased" <?php if ( $trigger != 'woo_product' ) { ?> style="display:none;"<?php } ?>>
                                                <select name="<?php echo $this->plugin_config['plugin_prefix']; ?>_gs_rules[<?php echo $row_key; ?>][trigger_value_woo_product]">
                                                    <?php
                                                    $args = array('limit' => -1);
                                                    $products = wc_get_products($args);
                                                    foreach ($products as $product) :
                                                        $product_id = $product->get_id();
                                                        ?>
                                                        <option value="<?php echo $product_id; ?>"
                                                                <?php if ($trigger_value == $product_id) { ?>selected="selected"<?php }; ?>><?php echo $product->get_name(); ?></option>
                                                        <?php
                                                        if ( $product->has_child() ) :
                                                            $children = $product->get_children();
                                                            foreach ($children as $child_id) :
                                                                $v_product = wc_get_product($child_id);
                                                                ?>
                                                                <option value="<?php echo $child_id; ?>"
                                                                        <?php if ($trigger_value == $child_id) { ?>selected="selected"<?php }; ?>>
                                                                    -- <?php echo $v_product->get_formatted_name(); ?></option>
                                                            <?php
                                                            endforeach;
                                                        endif;
                                                        ?>

                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        <?php endif; ?>

                                        <!-- EDD Product Purchased -->
                                        <?php if ( class_exists('Easy_Digital_Downloads') ) : ?>
                                            <?php
                                            $trigger_value = $this->get_custom_setting_array('gs_rules', array('1' => $row_key, '2' => 'trigger_value_edd_product'));
                                            ?>
                                            <div id="edd_product_purchased" <?php if ( $trigger != 'edd_product' ) { ?> style="display:none;"<?php } ?>>
                                                <select name="<?php echo $this->plugin_config['plugin_prefix']; ?>_gs_rules[<?php echo $row_key; ?>][trigger_value_edd_product]">
                                                    <?php
                                                    $edd_products = $this->get_edd_downlaods();
                                                    //print_r($edd_products);
                                                    foreach ($edd_products as $product) :
                                                        $product_id = $product->ID;
                                                        ?>
                                                        <option value="<?php echo $product_id; ?>"
                                                                <?php if ($trigger_value == $product_id) { ?>selected="selected"<?php }; ?>><?php echo get_the_title($product_id); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        <?php endif; ?>

                                        <!-- WooCommerce Memberships Status Change -->
                                        <?php if (class_exists('WC_Memberships')) : ?>
                                        <div id="wcm_membership_status" <?php if ( $trigger != 'wcm_membership_status' ) { ?> style="display:none;"<?php } ?>>
                                            <?php $wc_plan_value = $this->get_custom_setting_array('gs_rules', array('1' => $row_key, '2' => 'trigger_value_wcm_membership_plan')); ?>
                                            Membership: <select
                                                    name="<?php echo $this->plugin_config['plugin_prefix']; ?>_gs_rules[<?php echo $row_key; ?>][trigger_value_wcm_membership_plan]">
                                                <option value="" disabled>Bulk Options</option>
                                                <option value="any"
                                                        <?php if ($wc_plan_value == 'any') { ?>selected="selected"<?php }; ?>>
                                                    Any
                                                </option>
                                                <option value="" disabled></option>
                                                <option value="" disabled>Memberships</option>
                                                <?php
                                                $wcm_plans = wc_memberships_get_membership_plans();
                                                foreach ($wcm_plans as $plan) :
                                                    ?>
                                                    <option value="<?php echo $plan->id; ?>"
                                                            <?php if ($wc_plan_value == $plan->id) { ?>selected="selected"<?php }; ?>><?php echo $plan->name; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php $wc_plan_status = $this->get_custom_setting_array('gs_rules', array('1' => $row_key, '2' => 'trigger_value_wcm_membership_status')); ?>
                                            <br>New Status: <select
                                                    name="<?php echo $this->plugin_config['plugin_prefix']; ?>_gs_rules[<?php echo $row_key; ?>][trigger_value_wcm_membership_status]">
                                                <option value="" disabled>Bulk Options</option>
                                                <option value="not_expired_or_cancelled"
                                                        <?php if ($wc_plan_value == 'any') { ?>selected="selected"<?php }; ?>>
                                                    Not Expired or Cancelled
                                                </option>
                                                <option value="not_active"
                                                        <?php if ($wc_plan_value == 'any') { ?>selected="selected"<?php }; ?>>
                                                    Not Active
                                                </option>
                                                <option value="expired_or_cancelled"
                                                        <?php if ($wc_plan_value == 'any') { ?>selected="selected"<?php }; ?>>
                                                    Expired or Cancelled
                                                </option>
                                                <option value="" disabled></option>
                                                <option value="" disabled>Specific Statuses</option>
                                                <option value="active"
                                                        <?php if ($wc_plan_status == 'active') { ?>selected="selected"<?php }; ?>>
                                                    Active
                                                </option>
                                                <option value="pending"
                                                        <?php if ($wc_plan_status == 'pending') { ?>selected="selected"<?php }; ?>>
                                                    Pending
                                                </option>
                                                <option value="delayed"
                                                        <?php if ($wc_plan_status == 'delayed') { ?>selected="selected"<?php }; ?>>
                                                    Delayed
                                                </option>
                                                <option value="complimentary"
                                                        <?php if ($wc_plan_status == 'Complimentary') { ?>selected="selected"<?php }; ?>>
                                                    Complimentary
                                                </option>
                                                <option value="on-hold"
                                                        <?php if ($wc_plan_status == 'on-hold') { ?>selected="selected"<?php }; ?>>
                                                    On Hold
                                                </option>
                                                <option value="paused"
                                                        <?php if ($wc_plan_status == 'paused') { ?>selected="selected"<?php }; ?>>
                                                    Paused
                                                </option>
                                                <option value="expired"
                                                        <?php if ($wc_plan_status == 'expired') { ?>selected="selected"<?php }; ?>>
                                                    Expired
                                                </option>
                                                <option value="cancelled"
                                                        <?php if ($wc_plan_status == 'cancelled') { ?>selected="selected"<?php }; ?>>
                                                    Cancelled
                                                </option>

                                            </select>
                                        </div>
                                            <?php endif; ?>

                                            <!-- PMP Level Change -->
                                            <?php if ( defined('PMPRO_VERSION') ) : ?>
                                                <?php
                                                $trigger_value = $this->get_custom_setting_array('gs_rules', array('1' => $row_key, '2' => 'trigger_value_pmp_level'));
                                                $run_after_auto_enrol = $this->get_custom_setting_array('gs_rules', array('1' => $row_key, '2' => 'run_after_auto_enrol'));
                                                ?>
                                                <div id="pmp_level_change" <?php if ( $trigger != 'pmp_level_change' ) { ?> style="display:none;"<?php } ?>>
                                                    <select name="<?php echo $this->plugin_config['plugin_prefix']; ?>_gs_rules[<?php echo $row_key; ?>][trigger_value_pmp_level]">
                                                        <?php
                                                        $pmp_levels = pmpro_getAllLevels();
                                                        foreach ($pmp_levels as $level) :
                                                            ?>
                                                            <option value="<?php echo $level->id; ?>"
                                                                    <?php if ($trigger_value == $level->id) { ?>selected="selected"<?php }; ?>><?php echo $level->name; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <br><br><input
                                                            name="<?php echo $this->plugin_config['plugin_prefix']; ?>_gs_rules[<?php echo $row_key; ?>][run_after_auto_enrol]"
                                                            type="checkbox" value="1"
                                                            <?php if ($run_after_auto_enrol == 1) { ?>checked<?php }; ?>><?php _e('Run this rule after affiliate registration form', 'affiliatewp-group-switcher') . '.'; ?>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Registering Affiliates -->
                                            <!-- WooCommerce Memberships Status Change -->
                                            <?php if (class_exists('WC_Memberships')) : ?>
                                            <div id="wcm_membership_is_member" <?php if ( $trigger != 'wcm_membership_is_member' ) { ?> style="display:none;"<?php } ?>>
                                                    <?php $wc_plan_value = $this->get_custom_setting_array('gs_rules', array('1' => $row_key, '2' => 'trigger_value_wcm_membership_plan')); ?>
                                                    Membership: <select
                                                            name="<?php echo $this->plugin_config['plugin_prefix']; ?>_gs_rules[<?php echo $row_key; ?>][trigger_value_wcm_membership_plan]">
                                                        <!--<option value="" disabled>Bulk Options</option>
                                                        <option value="any"
                                                                <?php /*if ($wc_plan_value == 'any') { */?>selected="selected"<?php /*}; */?>>
                                                            Any
                                                        </option>-->
                                                        <option value="" disabled></option>
                                                        <option value="" disabled>Memberships</option>
                                                        <?php
                                                        $wcm_plans = wc_memberships_get_membership_plans();
                                                        foreach ($wcm_plans as $plan) :
                                                            ?>
                                                            <option value="<?php echo $plan->id; ?>"
                                                                    <?php if ($wc_plan_value == $plan->id) { ?>selected="selected"<?php }; ?>><?php echo $plan->name; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                             </div>
                                             <?php endif; ?>


                                    </td>

                                    <td></td>

                                    <td class="removeBtnPlace">
                                        <?php if ( $row_key > 1 ) : ?>
                                            <input class="removeRow" type="button" value="X">
                                        <?php endif; ?>
                                    </td>

                                </tr>

                                <tr>
                                    <td><?php _e('Affiliate Group Changes', 'affiliatewp-group-switcher'); ?></td>

                                    <!-- Group Changes -->
                                    <td>
                                        <select style="width:100%;"
                                                name="<?php echo $this->plugin_config['plugin_prefix']; ?>_gs_rules[<?php echo $row_key; ?>][switcher_mode]">
                                            <!-- <option value="">Switching Mode</option>-->
                                            <option value="reset"
                                                    <?php if ($switcher_mode == 'reset') { ?>selected="selected"<?php }; ?>><?php _e('Reset to this group', 'affiliatewp-group-switcher'); ?></option>
                                            <option value="add"
                                                    <?php if ($switcher_mode == 'add') { ?>selected="selected"<?php }; ?>><?php _e('Add Group', 'affiliatewp-group-switcher'); ?></option>
                                            <option value="remove"
                                                    <?php if ($switcher_mode == 'remove') { ?>selected="selected"<?php }; ?>><?php _e('Remove Group', 'affiliatewp-group-switcher'); ?></option>
                                            <option value="remove_all"
                                                    <?php if ($switcher_mode == 'remove_all') { ?>selected="selected"<?php }; ?>><?php _e('Remove All Groups', 'affiliatewp-group-switcher'); ?></option>
                                        </select>
                                    </td>

                                    <td>
                                        <select style="width:100%;"
                                                name="<?php echo $this->plugin_config['plugin_prefix']; ?>_gs_rules[<?php echo $row_key; ?>][affiliate_groups]">
                                            <?php
                                            $aff_groups = get_active_affiliate_groups();
                                            foreach ($aff_groups as $group_id => $group_vars) :
                                                ?>
                                                <option value="<?php echo $group_id; ?>"
                                                        <?php if ($affiliate_groups == $group_id) { ?>selected="selected"<?php }; ?>><?php echo $group_vars['name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>

                                    <td></td>

                                </tr>

                                </tbody>
                            <?php endfor; ?>

                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="button" value="Add New Rule" class="addRow" data-clone-class=".gs_rules"
                               data-parent-class=".form-table">
                    </td>
                </tr>

                </tbody>
            </table>
            <?php
            //$rules = $this->plugin_setting( 'gs_rules' ) ;
            //print_r($rules);
            ?>
            <!-- END Rules -->

        </div>

        <?php
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    public function get_edd_downlaods() {

        global $post;

        $args = array(
        'post_type' => 'download',
        'post_per_page' => -1,
        );

        $downloads = get_posts($args);

        wp_reset_postdata();

        return $downloads;

        //$download = new EDD_Download($download_id);

    }
	
	
	/////////// Licensing Methods ///////////////
	
	// Deactive license
	public function deactivate_license() {
		
	  if( 
	  (isset($_GET[$this->plugin_config['plugin_prefix'].'_license_change'])) &&
	  ($_GET[$this->plugin_config['plugin_prefix'].'_license_change'] == 'deactivate') 
	  ){
		  
		  $license = new Click_Studio_Licenses_V1_5($this->plugin_config, $this->plugin_settings);
		  if($license->deactivate_license()) {
			  
			  // Redirect to settings page
			  $location = $_SERVER['HTTP_REFERER'];
			  wp_safe_redirect($location);
		  
		  }
	  
	  }
		
	}
	
	// Get the license message actions and messages. Also activate license keys.
	public function license_status_msg() {

		$license = new Click_Studio_Licenses_V1_5($this->plugin_config, $this->plugin_settings);

		if( isset($_GET['cs_remove_license_data']) && $_GET['cs_remove_license_data'] == true ) {
		
			$license->remove_license_data();
			$license->remove_affwp_settings();
		
		}else{
		
		$license->activate_license();
		
		$license_message = $license->license_status_msg();
		return $license_message;
		
		}
	
	}
	
	// Check license status
	public function is_license_valid() {
		
		if( $this->check_if_settings_page() ) {
		
			$license = new Click_Studio_Licenses_V1_5($this->plugin_config, $this->plugin_settings);
			$status = $license->get_license_option( 'license_status' );
			
			if( !empty($status) && $status == 'valid' ) return true;
		
		}
		
		return false;
		
	}
	
} // End of class
?>