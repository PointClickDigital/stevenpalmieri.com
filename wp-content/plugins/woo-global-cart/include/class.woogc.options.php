<?php

    class WooGC_options_interface
        {
         
            var $WooGC;
         
            function __construct()
                {
                    
                    if(!is_admin())
                        return;
                    
                    global $WooGC;
                    $this->WooGC    =   $WooGC;
                    
                    $this->licence          =   $WooGC->licence;
                    
                    if (isset($_GET['page']) && $_GET['page'] == 'woogc-options')
                        {
                            add_action( 'init', array($this, 'options_update'), 1 );
                        }

                    add_action( 'network_admin_menu', array($this, 'network_admin_menu') );
                    if(!$this->licence->licence_key_verify())
                        {
                            add_action('admin_notices', array($this, 'admin_no_key_notices'));
                            add_action('network_admin_notices', array($this, 'admin_no_key_notices'));
                        }
                    
                }

            
            function network_admin_menu()
                {
                    $parent_slug    =   'settings.php';
                        
                    $hookID   = add_submenu_page($parent_slug, 'WooCommerce Global Cart', 'WooCommerce Global Cart', 'manage_options', 'woogc-options', array($this, 'options_interface'));

                    add_action('load-' .                $hookID ,   array($this, 'admin_notices'));
                    add_action('admin_print_styles-' .  $hookID ,   array($this, 'admin_print_styles'));
                    add_action('admin_print_scripts-' . $hookID ,   array($this, 'admin_print_scripts'));
                }
                
                
            function admin_print_scripts()
                {
                    $WC_url     =   plugins_url() . '/woocommerce';
                    
                    wp_register_script( 'jquery-tiptip', $WC_url . '/assets/js/jquery-tiptip/jquery.tipTip.js', array( 'jquery' ), NULL, true );
                    wp_enqueue_script(  'jquery-tiptip');
                    
                    wp_register_script( 'woogc-options',  WOOGC_URL . '/js/woogc-options.js', array( 'jquery' ), NULL, true );
                    wp_enqueue_script(  'woogc-options');
                    
                }
                
                
            function admin_print_styles()
                {
                    wp_register_style(  'woogc-options', WOOGC_URL . '/css/woogc-options.css');
                    wp_enqueue_style(   'woogc-options');
                }    
            
                              
            function options_interface()
                {
                    
                    if(!$this->licence->licence_key_verify())
                        {
                            $this->licence_form();
                            return;
                        }
                        
                    if($this->licence->licence_key_verify())
                        {
                            $this->licence_deactivate_form();
                        }
                    
                    $options    =   $this->WooGC->functions->get_options();
                        
                    ?>
                        <div class="wrap"> 
                            <h3><?php _e( "General Settings", 'woo-global-cart' ) ?></h3>
                                             
                            <form id="form_data" class="options checkout_type_<?php echo $options['cart_checkout_type'] ?>"  name="form" method="post">   
                                <table class="form-table">
                                    <tbody>
                                    
                                        <tr id="cart_checkout_type" valign="top">
                                            <th scope="row">
                                                <select name="cart_checkout_type">
                                                    <option value="single_checkout" <?php selected('single_checkout', $options['cart_checkout_type']); ?>><?php _e( "Single Checkout", 'woo-global-cart' ) ?></option>
                                                    <option value="each_store" <?php selected('each_store', $options['cart_checkout_type']); ?>><?php _e( "Each Store", 'woo-global-cart' ) ?></option>
                                                </select>
                                            </th>
                                            <td>
                                                <label><?php _e( "Checkout Type", 'woo-global-cart' ) ?> </label>
                                                <p class="help"><?php _e( "Checkout can occur for all cart items, independently from where each product is coming from. In this case the payment will be collected in full amount on the check-out site. Selecting Each Store checkout type, the system create individual check-out sessions in shops from where the cart products are belong. Each shop will retrieve the payment separately for their own products", 'woo-global-cart' ) ?></p>
                                            </td>
                                        </tr>
                                    
                                        <tr id="cart_checkout_location" class="hide _show_on_single_checkout" valign="top">
                                            <th scope="row">
                                                <select name="cart_checkout_location">
                                                    <option value="" <?php selected('', $options['cart_checkout_location']); ?>><?php _e( "Any Site", 'woo-global-cart' ) ?></option>
                                                    <?php
                                                    
                                                        $sites  =   $this->WooGC->functions->get_gc_sites( TRUE );
                                                        foreach($sites  as  $site)
                                                            {
                                                                $blog_details = get_blog_details($site->blog_id);
                                                                
                                                                ?><option value="<?php echo $site->blog_id ?>" <?php selected($site->blog_id, $options['cart_checkout_location']); ?>><?php echo $blog_details->blogname ?></option><?php
                                                            }
                                                    
                                                    ?>
                                                </select>
                                            </th>
                                            <td>
                                                <label><?php _e( "Cart Checkout location", 'woo-global-cart' ) ?></label>
                                                <p class="help"><?php _e( "The option is being used when Checkout Type being set as Single Checkout. <br />When checkout a user will be redirected to a specific site to complete the order or he can proceed to any site. ", 'woo-global-cart' ) ?></p>
                                            </td>
                                        </tr>
                                        
                                        <tr id="cart_checkout_split_orders" class="hide _show_on_single_checkout" valign="top">
                                            <th scope="row">
                                                <select name="cart_checkout_split_orders">
                                                    <option value="no" <?php selected('no', $options['cart_checkout_split_orders']); ?>><?php _e( "No", 'woo-global-cart' ) ?></option>
                                                    <option value="yes" <?php selected('yes', $options['cart_checkout_split_orders']); ?>><?php _e( "Yes", 'woo-global-cart' ) ?></option>
                                                </select>
                                            </th>
                                            <td>
                                                <label><?php _e( "Split Order", 'woo-global-cart' ) ?></label>
                                                <p class="help"><?php _e( "The option is being used when Checkout Type being set as Single Checkout. <br />When the option is active, the core creates individual orders within all shops which include a product in the main order. The split order includes only the products for the specific shop. ", 'woo-global-cart' ) ?></p>
                                            </td>
                                        </tr>
                                        
                                        
                                        <tr  valign="top">
                                            <th scope="row">
                                               
                                            </th>
                                            <td>
                                               
                                            </td>
                                        </tr>
                                        
                                        
                                        <tr id="use_sequential_order_numbers" valign="top">
                                            <th scope="row">
                                                <select name="use_sequential_order_numbers">
                                                    <option value="no" <?php selected('no', $options['use_sequential_order_numbers']); ?>><?php _e( "No", 'woo-global-cart' ) ?></option>
                                                    <option value="yes" <?php selected('yes', $options['use_sequential_order_numbers']); ?>><?php _e( "Yes", 'woo-global-cart' ) ?></option>
                                                </select>
                                            </th>
                                            <td>
                                                <label><?php _e( "Use Sequential Order Numbers", 'woo-global-cart' ) ?></label>
                                                <p class="help"><?php _e( "Sequential Order Numbers is a way to maintain consecutive ids for orders across network, independently from shop where order has been placed. This is recommended to always use when Checkout location is set for a specific shop.", 'woo-global-cart' ) ?></p>                                                
                                            </td>
                                        </tr>
                                        
                                        <tr id="show_product_attributes" valign="top">
                                            <th scope="row">
                                                <select name="show_product_attributes">
                                                    <option value="no" <?php selected('no', $options['show_product_attributes']); ?>><?php _e( "No", 'woo-global-cart' ) ?></option>
                                                    <option value="yes" <?php selected('yes', $options['show_product_attributes']); ?>><?php _e( "Yes", 'woo-global-cart' ) ?></option>
                                                </select>
                                            </th>
                                            <td>
                                                <label><?php _e( "Filter to Show Product Attributres", 'woo-global-cart' ) ?></label>
                                                <p class="help"><?php _e( "Output the list of product attributes, on cart page, if not being already outputed on the product title.", 'woo-global-cart' ) ?></p>
                                            </td>
                                        </tr>
                                        
                                        <tr id="login_on_sites" valign="top">
                                            <th scope="row">
                                                <select name="login_on_sites">
                                                    <option value="" <?php selected('', $options['login_on_sites']); ?>><?php _e( "All Sites", 'woo-global-cart' ) ?></option>
                                                    <option value="woocommerce" <?php selected('woocommerce', $options['login_on_sites']); ?>><?php _e( "Only WooCommerce active", 'woo-global-cart' ) ?></option>
                                                </select>
                                            </th>
                                            <td>
                                                <label><?php _e( "On login, authenticate other sites", 'woo-global-cart' ) ?></label>
                                                <p class="help"><?php _e( "On login on a WooCommerce active site, the plugin automatically authenticate to all other WooCommerce sites (and blogs, if set) to maintain data and cart synchronization.", 'woo-global-cart' ) ?></p>
                                            </td>
                                        </tr>
                                        
                                        
                                        <tr id="login_only_specific_roles_status" valign="top">
                                            <th scope="row">
                                
                                                <div id="login_only_specific_roles">
                                                    <?php
                                                    
                                                        $roles = get_editable_roles();
                                                        $disabled   =   array ( 
                                                                                'administrator',
                                                                                'shop_manager'
                                                                                );
                                                        $skip                   =   array();
                                                        foreach( $roles  as  $role_name  =>  $role_data )
                                                            {
                                                                
                                                                if ( in_array ( $role_name, $disabled) )
                                                                    continue;
                                                                
                                                                ?>
                                                                    <p><label>
                                                                       <?php echo $role_data['name'] ?>  <input name="login_only_specific_roles[]" type="checkbox" <?php if ( in_array ( $role_name, $disabled) ) { ?> disabled="disabled" <?php } ?>  value="<?php echo $role_name; ?>" <?php if( count($options['login_only_specific_roles']) > 0 && in_array($role_name, $options['login_only_specific_roles'])  &&  ! in_array ( $role_name, $disabled)) { ?>checked="checked"<?php } ?>>
                                                                    </label></p>
                                                                <?php        
                                                            }
                                                        
                                                    ?>
                                                </div>
                                            </th>
                                            <td>
                                                <label><?php _e( "On login, authenticate other sites only for users of this roles.", 'woo-global-cart' ) ?></label>
                                                <p class="help"><?php _e( "Users can use different roles across the network, all will be checked along this setting. <br />On login on a site in the netowrk, the plugin automatically authenticate the user on all other WooCommerce sites (and blogs, if set).", 'woo-global-cart' ) ?></p>
                                                <br>
                                                <p class="help"><b><?php _e( "Only users of specific roles will be logged-in!", 'woo-global-cart' ) ?></b></p>
                                                <p class="help"><?php _e( "This is recommended to be set for Customer role. Administrator and Shop manager are disabled to avoid any security risks regarding SSO procedure.", 'woo-global-cart' ) ?></p>
                                                <br>
                                                <p class="help important"><b><?php _e( "When testing Global Cart and SSO, use role types selected in the list.", 'woo-global-cart' ) ?></b></p>
                                            </td>
                                        </tr>
                                    
                               
                                    </tbody>
                                </table>
                                
                                
                                
                                <?php do_action('woogc/options/options_html');  ?>
                                               
                                <p class="submit">
                                    <input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Settings', 'woo-global-cart') ?>">
                                </p>
                            
                                <?php wp_nonce_field('woogc_form_submit','woogc_form_nonce'); ?>
                                <input type="hidden" name="woogc_form_submit" value="true" />
                                
                            </form>
                        </div>                                  
                    <?php
                }
            
            function options_update()
                {
                    
                    if (isset($_POST['woogc_licence_form_submit']))
                        {
                            $this->licence_form_submit();
                            return;
                        }
                        
                    if (isset($_POST['woogc_form_submit']))
                        {
                            //check nonce
                            if ( ! wp_verify_nonce($_POST['woogc_form_nonce'],'woogc_form_submit') ) 
                                return;
                            
                            $options    =   $this->WooGC->functions->get_options();
                            
                            global $woogc_interface_messages;

                            $options['cart_checkout_type']                          =   $_POST['cart_checkout_type'];
                            $options['cart_checkout_location']                      =   $_POST['cart_checkout_location'];
                            $options['cart_checkout_split_orders']                  =   $_POST['cart_checkout_split_orders'];
                                                        
                            $options['login_on_sites']                              =   $_POST['login_on_sites'];
                            $options['use_sequential_order_numbers']                =   $_POST['use_sequential_order_numbers'];
                            $options['show_product_attributes']                     =   $_POST['show_product_attributes'];
                            
                            $options['login_only_specific_roles']                   =   isset($_POST['login_only_specific_roles'])          ?   $_POST['login_only_specific_roles']         :   array();
                                                        
                            $options    =   apply_filters('woogc/options/options_save', $options);
                            
                            if($options['use_sequential_order_numbers'] ==  'yes')
                                {
                                    include_once( WOOGC_PATH . '/include/class.woogc.sequential-order-numbers.php');
                                    
                                    WooGC_Sequential_Order_Numbers::network_update_order_numbers();
                                }
                            
                            $this->WooGC->functions->update_options($options);  
                            
                            $woogc_interface_messages[] = array(    'type'  =>   'updated',
                                                                    'text'  =>  __('Settings Saved', 'woo-global-cart'));
              
                        }
            
                }
                  
            function admin_notices()
                {
                    global $woogc_interface_messages;
            
                    if(!is_array($woogc_interface_messages))
                        return;
                              
                    if(count($woogc_interface_messages) > 0)
                        {
                            foreach ($woogc_interface_messages  as  $message)
                                {
                                    echo "<div class='". $message['type'] ." fade'><p>". $message['text']  ."</p></div>";
                                }
                        }

                }
                  
                        
            
            function admin_no_key_notices()
                {
                    if ( !current_user_can('manage_options'))
                        return;
                    
                    $screen = get_current_screen();
                        
                    if(is_multisite())
                        {
                            if(isset($screen->id) && $screen->id    ==  'settings_page_woogc-options-network')
                                return;
                            ?><div class="error fade"><p><?php _e( "WooCommerce Global Cart plugin is inactive, please enter your", 'woo-global-cart' ) ?> <a href="<?php echo network_admin_url() ?>settings.php?page=woogc-options"><?php _e( "Licence Key", 'woo-global-cart' ) ?></a></p></div><?php
                        }
                }
            
            function licence_form_submit()
                {
                    global $woogc_interface_messages; 
                    
                    //check for de-activation
                    if (isset($_POST['woogc_licence_form_submit']) && isset($_POST['woogc_licence_deactivate']) && wp_verify_nonce($_POST['woogc_license_nonce'],'woogc_licence'))
                        {
                            
                            $licence_data = get_site_option('woogc_licence');                        
                            $licence_key = $licence_data['key'];

                            //build the request query
                            $args = array(
                                                'woo_sl_action'         => 'deactivate',
                                                'licence_key'           => $licence_key,
                                                'product_unique_id'     => WOOGC_PRODUCT_ID,
                                                'domain'                => WOOGC_INSTANCE
                                            );
                            $request_uri    = WOOGC_UPDATE_API_URL . '?' . http_build_query( $args , '', '&');
                            $data           = wp_remote_get( $request_uri );
                            
                            if(is_wp_error( $data ) || $data['response']['code'] != 200)
                                {
                                    $woogc_interface_messages[] = array(
                                                                            'type'  =>  'error',
                                                                            'text'  =>  __('There was a problem connecting to ', 'woo-global-cart') . WOOGC_UPDATE_API_URL);
                                    return;  
                                }
                                
                            $response_block = json_decode($data['body']);
                            $response_block = $response_block[count($response_block) - 1];
                            $response = $response_block->message;
                            
                            if(isset($response_block->status))
                                {
                                    if($response_block->status == 'success' && $response_block->status_code == 's201')
                                        {
                                            //the license is active and the software is active
                                            $woogc_interface_messages[] = array(
                                                                                    'type'  =>  'updated',
                                                                                    'text'  =>  $response_block->message);
                                            
                                            $licence_data = get_site_option('woogc_licence');
                                            
                                            //save the license
                                            $licence_data['key']          = '';
                                            $licence_data['last_check']   = time();
                                            
                                            update_site_option('woogc_licence', $licence_data);
                                        }
                                        
                                    else //if message code is e104  force de-activation
                                            if ($response_block->status_code == 'e002' || $response_block->status_code == 'e104'    || $response_block->status_code == 'e110' )
                                                {
                                                    $licence_data = get_site_option('woogc_licence');
                                            
                                                    //save the license
                                                    $licence_data['key']          = '';
                                                    $licence_data['last_check']   = time();
                                                    
                                                    update_site_option('woogc_licence', $licence_data);
                                                }
                                        else
                                        {
                                            $woogc_interface_messages[] =   array(  
                                                                                    'type'  =>  'error',
                                                                                    'text'  =>  __('There was a problem deactivating the licence: ', 'woo-global-cart') . $response_block->message);
                                     
                                            return;
                                        }   
                                }
                                else
                                {
                                    $woogc_interface_messages[] =   array(  
                                                                                    'type'  =>  'error',
                                                                                    'text'  => __('There was a problem with the data block received from ' . WOOGC_UPDATE_API_URL, 'woo-global-cart'));
                                    return;
                                }
                                
                            //redirect
                            $current_url    =   $this->WooGC->functions->current_url();
                            
                            wp_redirect($current_url);
                            
                            die();
                            
                        }   
                    
                    
                    
                    if (isset($_POST['woogc_licence_form_submit']) && wp_verify_nonce($_POST['woogc_license_nonce'],'woogc_licence'))
                        {
                            
                            $licence_key = isset($_POST['licence_key'])? sanitize_key(trim($_POST['licence_key'])) : '';

                            if($licence_key == '')
                                {
                                    $woogc_interface_messages[] =   array(  
                                                                                    'type'  =>  'error',
                                                                                    'text'  =>  __("Licence Key can't be empty", 'woo-global-cart'));
                                    return;
                                }
                                
                            //build the request query
                            $args = array(
                                                'woo_sl_action'         => 'activate',
                                                'licence_key'           => $licence_key,
                                                'product_unique_id'     => WOOGC_PRODUCT_ID,
                                                'domain'                => WOOGC_INSTANCE
                                            );
                            $request_uri    = WOOGC_UPDATE_API_URL . '?' . http_build_query( $args , '', '&');
                            $data           = wp_remote_get( $request_uri );
                            
                            if(is_wp_error( $data ) || $data['response']['code'] != 200)
                                {
                                    $woogc_interface_messages[] =   array(  
                                                                                    'type'  =>  'error',
                                                                                    'text'  =>  __('There was a problem connecting to ', 'woo-global-cart') . WOOGC_UPDATE_API_URL);
                                    return;  
                                }
                                
                            $response_block = json_decode($data['body']);
                            //retrieve the last message within the $response_block
                            $response_block = $response_block[count($response_block) - 1];
                            $response = $response_block->message;
                            
                            if(isset($response_block->status))
                                {
                                    if( $response_block->status == 'success' && ( $response_block->status_code == 's100' || $response_block->status_code == 's101' ) )
                                        {
                                            //the license is active and the software is active
                                            $woogc_interface_messages[] =   array(  
                                                                                    'type'  =>  'error',
                                                                                    'text'  =>  $response_block->message);
                                            
                                            $licence_data = get_site_option('woogc_licence');
                                            
                                            //save the license
                                            $licence_data['key']          = $licence_key;
                                            $licence_data['last_check']   = time();
                                            
                                            update_site_option('woogc_licence', $licence_data);

                                        }
                                        else
                                        {
                                            $woogc_interface_messages[] =   array(  
                                                                                    'type'  =>  'error',
                                                                                    'text'  =>  __('There was a problem activating the licence: ', 'woo-global-cart') . $response_block->message);
                                            return;
                                        }   
                                }
                                else
                                {
                                    $woogc_interface_messages[] =   array(  
                                                                                    'type'  =>  'error',
                                                                                    'text'  =>  __('There was a problem with the data block received from ' . WOOGC_UPDATE_API_URL, 'woo-global-cart'));
                                    return;
                                }
                                
                            //redirect
                            $current_url    =   $this->WooGC->functions->current_url();
                            
                            wp_redirect($current_url);
                            
                            die();
                        }   
                    
                }
                
            function licence_form()
                {
                    ?>
                        <div class="wrap"> 
                            <h2><?php _e( "WooCommerce Global Cart", 'woo-global-cart' ) ?></h2>
                            
                            <h3><?php _e( "Licence", 'woo-global-cart' ) ?></h3>
                            <form id="form_data" name="form" method="post">
                                <div class="postbox">
                                    
                                        <?php wp_nonce_field('woogc_licence','woogc_license_nonce'); ?>
                                        <input type="hidden" name="woogc_licence_form_submit" value="true" />
                                           
                                        

                                        <div class="section section-text ">
                                            <h4 class="heading"><?php _e( "License Key", 'woo-global-cart' ) ?></h4>
                                            <div class="option">
                                                <div class="controls">
                                                    <input type="text" value="" name="licence_key" class="text-input">
                                                </div>
                                                <div class="explain"><?php _e( "Enter the Licence Key you received when purchased this product. If you lost the key, you can always retrieve it from", 'woo-global-cart' ) ?> <a href="https://wooglobalcart.com/my-account/" target="_blank"><?php _e( "My Account", 'woo-global-cart' ) ?></a>
                                                </div>
                                            </div> 
                                        </div>
                                </div>
                                
                                <p><i><small>* <?php _e( "Rememebr, once activated, a new login session is required. The cookies and cache data is recommended to be cleared and a browser restart might also be required", 'woo-global-cart' ) ?>. <?php _e( "More details at", 'woo-global-cart' ) ?> <a href="https://wooglobalcart.com/documentation/plugin-installation/" target="_blank"><?php _e( "Plugin Instalation", 'woo-global-cart' ) ?></a>.</small></i></p>
                                
                                <p class="submit">
                                    <input type="submit" name="Submit" class="button-primary" value="<?php _e('Save', 'woo-global-cart') ?>">
                                </p>
                            </form> 
                        </div> 
                    <?php  
     
                }
            
            function licence_deactivate_form()
                {
                    
                    $licence_data = get_site_option('woogc_licence');
                    
                    ?>
                        <div class="wrap"> 
                            <h2><?php _e( "WooCommerce Global Cart", 'woo-global-cart' ) ?></h2>
                            <div id="form_data">
                                <h3><?php _e( "Licence", 'woo-global-cart' ) ?></h3>
                                <div class="postbox">
                                    <form id="form_data" name="form" method="post">    
                                        <?php wp_nonce_field('woogc_licence','woogc_license_nonce'); ?>
                                        <input type="hidden" name="woogc_licence_form_submit" value="true" />
                                        <input type="hidden" name="woogc_licence_deactivate" value="true" />

                                        <div class="section section-text ">
                                            <h4 class="heading"><?php _e( "License Key", 'woo-global-cart' ) ?></h4>
                                            <div class="option">
                                                <div class="controls">
                                                    <?php  
                                                        if($this->licence->is_local_instance())
                                                        {
                                                            ?>
                                                            <p>Local instance, no key applied.</p>
                                                            <?php   
                                                            }
                                                        else {
                                                            ?>
                                                            <p><b><?php echo substr($licence_data['key'], 0, 20) ?>-xxxxxxxx-xxxxxxxx</b> &nbsp;&nbsp;&nbsp;<a class="button-secondary" title="Deactivate" href="javascript: void(0)" onclick="jQuery(this).closest('form').submit();">Deactivate</a></p>
                                                    <?php } ?>
                                                </div>
                                                <div class="explain"><?php _e( "You can generate more keys from", 'woo-global-cart' ) ?> <a href="https://wooglobalcart.com/my-account/" target="_blank">My Account</a> 
                                                </div>
                                            </div> 
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div> 
                    <?php  
            
                }
                
            function licence_multisite_require_nottice()
                {
                    ?>
                        <div class="wrap"> 
                            <h2 class="subtitle"><?php _e( "WooCommerce Global Cart License", 'woo-global-cart' ) ?><br />&nbsp;</h2>
                            <div id="form_data">
                                <div class="postbox">
                                    <div class="section section-text ">
                                        <h4 class="heading"><?php _e( "License Key Required", 'woo-global-cart' ) ?>!</h4>
                                        <div class="option">
                                            <div class="explain"><?php _e( "Enter the Licence Key you received when purchased this product. If you lost the key, you can always retrieve it from", 'woo-global-cart' ) ?> <a href="https://wooglobalcart.com/my-account/" target="_blank"><?php _e( "My Account", 'woo-global-cart' ) ?></a>
                                            </div>
                                        </div> 
                                    </div>
                                </div>
                            </div>
                        </div> 
                    <?php
                
                }    

                
        }

    
    new WooGC_options_interface();                               

?>