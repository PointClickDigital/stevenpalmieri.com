<?php

    
    class WooGC 
        {
            
            var $functions;
            
            var $licence;
            
            var $user_login_logout_action       =   FALSE;
            var $user_login_sso_hash            =   FALSE;
            
            var $cache  =   array();
            
            function __construct()
                {
                    
                    $this->functions    =   new WooGC_Functions();
                
                    $this->licence      =   new WooGC_licence();
                    
                    $this->functions->check_required_structure();
                
                }    
            
            function init()
                {

                    //Admin
                    if(is_admin())
                        {
                            //options interface
                            include_once(WOOGC_PATH . '/include/class.woogc.options.php');
                        }

                    if(!$this->licence->licence_key_verify())
                        return FALSE;
                    
                    /**
                    * Check for specific features / functionality disable
                    */
                    $_WooGC_Disable_SSO         =   apply_filters( 'woogc/disable_sso',     FALSE);
                    $_WooGC_Disable_GlobalCart  =   apply_filters( 'woogc/disable_global_cart',     FALSE);    
                        
                    // Check if WooCommerce is enabled
                    if ( ! $this->functions->is_plugin_active( 'woocommerce/woocommerce.php') )
                        {
                            if ( ! $_WooGC_Disable_GlobalCart )
                                {
                                    add_action( 'admin_notices',                array( $this, 'WC_disabled_notice' ));
                                    add_action( 'network_admin_notices',        array( $this, 'WC_disabled_notice' ));
                                }
                            
                            //If using All Sites option for "On login, authenticate other sites" load the session manager
                            $options    =   $this->functions->get_options();
                            if ( $options['login_on_sites'] == '' )
                                add_filter('session_token_manager',                 array( $this, 'session_token_manager' ), 999 );
                                
                            return FALSE;
                        }

                    
                    //the Global Cart can't run if SSO is turned off
                    if  ( $_WooGC_Disable_SSO  ===  TRUE )
                        $_WooGC_Disable_GlobalCart  =   TRUE;
                    
                    if (! $this->functions->is_plugin_active( 'woocommerce/woocommerce.php') )
                        {
                            //return;
                            $_WooGC_Disable_GlobalCart  =   TRUE;
                        }
                    
                    if( $_WooGC_Disable_GlobalCart  === FALSE )
                        {
                            //general filters
                            include_once(WOOGC_PATH . '/include/class.woogc.general-filters.php'); 
                        }      
                        
                    if( $_WooGC_Disable_GlobalCart  === FALSE )
                        { 
                            add_action('woocommerce_init',                      array($this, 'woocommerce_init'));
                        }
                    
                    if( is_admin() )
                        {
                            //plugin core updater check
                            include_once(WOOGC_PATH . '/include/class.woogc.updater.php');
                            
                            //include internal update procedures on update
                            include_once(WOOGC_PATH . '/include/class.woogc.on-update.php');
                            
                            //admin notices
                            add_action( 'admin_notices',                array(&$this, 'on__admin_notices'));
                            add_action( 'network_admin_notices',        array(&$this, 'on__admin_notices'));
                            
                        }
    
                    if( $_WooGC_Disable_GlobalCart  === FALSE )
                        {    
                            if(defined('DOING_AJAX'))
                                {
                                    //AJAX calls 
                                    include(WOOGC_PATH . '/include/class.woogc.ajax.php');
                                    new WooGC_AJAX();
                                }
                        }
                        
                    
                    
                    if( $_WooGC_Disable_SSO  === FALSE )
                        {          
                            //add links to set the cookies on other domains
                            add_action( 'wp_footer',                            array( $this, 'on_action_wp_footer') );   
                            add_action( 'admin_footer',                         array( $this, 'on_action_wp_footer') );
                            add_action( 'login_footer',                         array( $this, 'on_action_wp_footer') );
                            
                            //replace default session manager
                            add_filter('session_token_manager',                 array( $this, 'session_token_manager' ), 999 );
                            add_filter('woocommerce_session_handler',           array( $this, 'woocommerce_session_handler' ), 999 );
                            
                            //On register trigger sync
                            add_filter('woocommerce_registration_redirect',     array( $this, 'woocommerce_registration_redirect'), 999);                            
                            
                        }
                    
                    if( $_WooGC_Disable_SSO  === FALSE && $_WooGC_Disable_GlobalCart  === FALSE )
                        {    
                            include_once ( WOOGC_PATH . '/include/class.woogc.compatibility.php');
                        }
                                
                    if( is_admin() &&   ! is_network_admin() )
                        {
                            if ( $_WooGC_Disable_GlobalCart  === FALSE )
                                include_once ( WOOGC_PATH . '/include/admin/class.admin.php');
                                
                            include_once(WOOGC_PATH . '/include/class.woogc.admin-menus.php');
                            new WooGC_admin_menus();
                        }
                        
                    //network stuff
                    if(is_network_admin())
                        {
                            include_once(WOOGC_PATH . '/include/class.woogc.admin-menus.php');
                            new WooGC_admin_menus();
                        }

                    if( $_WooGC_Disable_SSO  === FALSE )
                        {
                            //add_action( 'wp_login',                             array( $this, 'on__wp_login'), 10, 2);
                            //add_action( 'wp_logout',                            array( $this, 'on__wp_logout'), -1);
                            add_filter( 'woocommerce_login_redirect',           array( $this, 'on__woocommerce_login_redirect'), 10, 2);
                            add_filter( 'login_redirect',                       array( $this, 'on__login_redirect'), 10, 3);
                            add_filter( 'logout_redirect',                      array( $this, 'on__logout_redirect'), 10, 3);
                            add_filter( 'wp_redirect',                          array( $this, 'on__wp_redirect'), 999, 2);
                        }
                    
                    if( $_WooGC_Disable_GlobalCart  === FALSE )
                        { 
                            //include dependencies
                            include_once(WOOGC_PATH . '/include/class.woogc.form-handler.php');
                        }
                    
                    add_action( 'plugins_loaded',                       array( $this, 'on_plugins_loaded') );
                    
                    //GC
                    add_action( 'shutdown',                             array( $this, 'on__shutdown'), 1 );
                    
                    if( $_WooGC_Disable_GlobalCart  === FALSE )
                        { 
                            add_action( 'init',                                 array( $this, 'gc_on_action_init') );
                        }
                    
                    if( $_WooGC_Disable_SSO  === FALSE )
                        {              
                            
                            
                            add_action( 'init',                                 array( $this, 'sso_on_action_init') );
                            
                            //add woogc cookie
                            add_action( 'set_auth_cookie',                      array( $this, 'on_action__set_auth_cookie__add_woogc_cookie'), 998, 5 );
                            add_action( 'clear_auth_cookie',                    array( $this, 'on_action__clear_auth_cookie__clear_cookies'));
                            
                            //add a logged in marker for WooGC->user_login_logout_action
                            add_action( 'set_auth_cookie',                      array( $this, 'on_action__set_auth_cookie'), 99, 6);
                            
                            //add a marker for redirect url to outputs sso data to trigger sso sync
                            add_filter( 'woocommerce_payment_successful_result', array( $this, 'woocommerce_payment_successful_result'), 10, 2);
                        }
                            
                            
                    if( $_WooGC_Disable_GlobalCart  === FALSE )
                        {
                            
                            //GC
                            //after item add to cart through AJAX make sure other blogs know about the session id
                            add_action( 'woocommerce_add_to_cart_fragments',    array( $this, 'on_action_woocommerce_add_to_cart_fragments'));
                            
                            //GC
                            //replicate the cart session to other blogs
                            add_action( 'shutdown',                             array( $this, 'on_action_shutdown_save__session_data' ), 999 );
                            
                            //GC
                            add_filter ( 'woocommerce_get_order_item_classname', array( $this, 'woocommerce_get_order_item_classname' ), 999, 3 );
                            
                            //GC
                            //load the cart when REST API
                            add_filter( 'rest_authentication_errors', array( $this, 'maybe_init_cart_session' ), 10, 2 );
                            
                            //shiping
                            include_once ( WOOGC_PATH . '/include/shipping/class.shipping.php');
                            
                            //stock
                            include_once ( WOOGC_PATH . '/include/stock/class.stock.php');
                            
                            //Order
                            include_once ( WOOGC_PATH . '/include/order/class.order.php');
                            
                            //Cart
                            include_once ( WOOGC_PATH . '/include/cart/class.cart.php');
                            
                            //Templates filters
                            if ( ! is_admin() )
                                include_once ( WOOGC_PATH . '/include/template/class.template.php');   
                            
                            //cart split                            
                            $options    =   $this->functions->get_options();   
                            if( $options['cart_checkout_type']  ==  'single_checkout' )
                                include_once ( WOOGC_PATH . '/include/checkout/class.single_checkout.php');
                            if( $options['cart_checkout_type']  ==  'each_store' )
                                include_once ( WOOGC_PATH . '/include/cart-split/class.woogc.cart-split-core.php');
                        }
                        
                }
            
            
            /**
            * On woocommerce_init
            * 
            */
            function woocommerce_init()
                {
                    
                    //replace the default cart with an extended WC_Cart instance
                    include_once ( WOOGC_PATH . '/include/cart/class.wc-cart-extend.php');
                    include_once ( WOOGC_PATH . '/include/session/class-wc-cart-session-extend.php');
                    if(! is_null($GLOBALS['woocommerce']->cart))
                        {
                            $GLOBALS['woocommerce']->cart   =   new WOOGC_WC_Cart( );
                        }

                    add_action( 'woocommerce_checkout_init',            array( 'WOOGC_WC_Checkout', 'instance' ), 999 );

                    //replace the default checkout with an extended WC_Checkout instance
                    include_once ( WOOGC_PATH . '/include/checkout/class.wc-checkout-extend.php');
                    
                    include_once ( WOOGC_PATH . '/include/order/class-wc-order-item-product.php');
                    
                }
            
            function maybe_init_cart_session( $return, $request = false )
                {
                    // Pass through other errors.
                    if ( ! empty( $error ) ) 
                        {
                            return $error;
                        }    
                    
                    if(! is_null($GLOBALS['woocommerce']->cart))
                        {
                            $GLOBALS['woocommerce']->cart   =   new WOOGC_WC_Cart( TRUE );
                        }
                    
                    
                    return $return;
                    
                }
            
                         
            
            function on__woocommerce_login_redirect( $redirect_to, $user )
                {
                    
                    if ( ! is_a($user, 'WP_User') )
                        return $redirect_to;
                        
                    if( strpos($redirect_to, 'loggedin=true')   === FALSE )
                        {
                            //replace any loggedout argument
                            if( strpos($redirect_to, 'loggedout=true')   !== FALSE )
                                {
                                    preg_match('/(loggedout=true)(\&)?/i', $redirect_to, $matchs);
                                    if  (   ! empty ( $matchs[2] ) )
                                        $redirect_to    =   str_replace( 'loggedout=true&', '', $redirect_to);
                                        else
                                        $redirect_to    =   str_replace( 'loggedout=true', '', $redirect_to);
                                }
                            
                            if (strpos($redirect_to, "?")   ===    FALSE)
                                $redirect_to    .=  "?";
                                
                            if (substr($redirect_to, -1) != '?')
                                $redirect_to    .=  "&";
                                
                            $redirect_to    .=  "loggedin=true";
                            
                            if ( ! empty ( $this->user_login_sso_hash ) )
                                $redirect_to    .=  "&login_hash=" . $this->user_login_sso_hash;
                        }
                    
                    return $redirect_to;   
                    
                }
            
            function on__login_redirect( $redirect_to, $requested_redirect_to, $user )
                {
                    if ( ! is_a($user, 'WP_User') )
                        return $redirect_to;
                            
                    if( strpos($redirect_to, 'loggedin=true')   === FALSE )
                        {
                            //replace any loggedout argument
                            if( strpos($redirect_to, 'loggedout=true')   !== FALSE )
                                {
                                    preg_match('/(loggedout=true)(\&)?/i', $redirect_to, $matchs);
                                    if  (   ! empty ( $matchs[2] ) )
                                        $redirect_to    =   str_replace( 'loggedout=true&', '', $redirect_to);
                                        else
                                        $redirect_to    =   str_replace( 'loggedout=true', '', $redirect_to);
                                }
                            
                            if (strpos($redirect_to, "?")   ===    FALSE)
                                $redirect_to    .=  "?";
                                
                            if (substr($redirect_to, -1) != '?')
                                $redirect_to    .=  "&";
                                
                            $redirect_to    .=  "loggedin=true";

                            if ( ! empty ( $this->user_login_sso_hash ) )
                                $redirect_to    .=  "&login_hash=" . $this->user_login_sso_hash;
                        }
                    
                    return $redirect_to;   
                    
                }
            
            
            function on__logout_redirect( $redirect_to, $requested_redirect_to, $user )
                {
                    if ( ! is_a($user, 'WP_User') )
                        return $redirect_to;
                           
                    if( strpos($redirect_to, 'loggedout=true')   === FALSE )
                        {
                            if (strpos($redirect_to, "?")   ===    FALSE)
                                $redirect_to    .=  "?";
                                
                            if (substr($redirect_to, -1) != '?')
                                $redirect_to    .=  "&";
                                
                            $redirect_to    .=  "loggedout=true";
                        }
                    
                    return $redirect_to;
                       
                }
            
            
            function on__wp_redirect( $location, $status )
                {
                    return $location;
                    
                    
                    $query_string  =   $_SERVER['QUERY_STRING'];
                    
                    if(empty($query_string))
                        return $location;
                    
                    parse_str( $query_string, $query_string_data );
                    
                    $append_string  =   '';
                    
                    if(isset($query_string_data['loggedout'])   &&  $query_string_data['loggedout'] ==  'true' )
                        {
                            if ( strpos($location, 'loggedout=true') !== FALSE )
                                return $location;
                                
                            $append_string  =   'loggedout=true'; 
                        }
                        
                    if(isset($query_string_data['loggedin'])   &&  $query_string_data['loggedin'] ==  'true' )
                        {
                            if ( strpos($location, 'loggedin=true') !== FALSE )
                                return $location;
                                
                            $append_string  =   'loggedin=true';
                        }
                        
                    if( !empty($append_string) )
                        {
                            if( strpos($location, '?')   === FALSE )
                                $location   .=  '?' . $append_string;
                                else
                                $location   .=  '&' . $append_string;
                        }  
                    
                    return $location;
                        
                }
                
                
            function create_sso_login_hash( $user_id )
                {
                    
                    if ( empty ( $user_id )     ||  $user_id < 1 )
                        return FALSE;
                    
                    // Create the token
                    $token_data = array(
                                            'time'      =>  time(),
                                            'ip'        =>  $_SERVER['REMOTE_ADDR'],
                                            'ua'        =>  isset($_SERVER['HTTP_USER_AGENT'])  ?   wp_unslash( $_SERVER['HTTP_USER_AGENT'] )   :   ''
                                        );
                    $key = wp_hash( serialize( $token_data ) );
                    
                    update_user_meta( $user_id, 'woogc_sso_login_hash_time_' . $key, time() );   
                    update_user_meta( $user_id, 'woogc_sso_login_hash_' . $key, $token_data);   
                    
                    
                    return $key;
                }
                
                
            function validate_sso_login_hash_data()
                {
                    
                    global $blog_id, $userdata;
                    
                    $sso_login_hash_data    =   $this->get_sso_login_hash_data();    
                    if ( empty ( $sso_login_hash_data ) )
                        return FALSE;
                        
                    $this->clear_sso_login_hash( $this->user_login_sso_hash );
                    
                    if ( $userdata->ID !=  $sso_login_hash_data->user_id )
                        return FALSE;
                        
                    $sso_login_data =   unserialize($sso_login_hash_data->meta_value);
                    
                    //check expiration    
                    if( time() > $sso_login_data['time'] + WOOGC_SSO_EXPIRE )
                        return FALSE;
                               
                    //check ip
                    if( $_SERVER['REMOTE_ADDR']    !=  $sso_login_data['ip'])
                        return FALSE;
                    
                    //check ua
                    if( $_SERVER['HTTP_USER_AGENT']    !=  $sso_login_data['ua'])
                        return FALSE;
                        
                    return TRUE;
                       
                }
                
            function get_sso_login_hash_data( )
                {
                    
                    global $wpdb;
                    
                    $hash   =   $this->user_login_sso_hash;
                    
                    //check any entry to match
                    $query              =   $wpdb->prepare("SELECT user_id, meta_value FROM "  .   $wpdb->usermeta . "
                                                            WHERE   `meta_key`  =   %s", 'woogc_sso_login_hash_' . $hash);
                    $woogc_sso_data    =   $wpdb->get_row( $query );
                    
                    if ( ! is_object( $woogc_sso_data ) )
                        return FALSE;

                    return $woogc_sso_data;
                    
                }
                    
            function clear_sso_login_hash( $hash )
                {
                              
                    global $wpdb;
                    
                    $mysql_query    =   $wpdb->prepare("DELETE FROM "  .   $wpdb->usermeta . "  WHERE `meta_key`  =   %s", 'woogc_sso_login_hash_' . $hash);   
                    $wpdb->get_results( $mysql_query );
                    
                    $mysql_query    =   $wpdb->prepare("DELETE FROM "  .   $wpdb->usermeta . "  WHERE `meta_key`  =   %s", 'woogc_sso_login_hash_time_' . $hash);   
                    $wpdb->get_results( $mysql_query );
                
                }
                
            
            function clear_expired_sso_login_hash()
                {
                    
                    global $wpdb;
                    
                    $mysql_query    =   "SELECT substr(meta_key, 27) as hash FROM "  .   $wpdb->usermeta . "  
                                                WHERE meta_key LIKE 'woogc_sso_login_hash_time_%' AND meta_value    <  ".  (time() - WOOGC_SSO_EXPIRE);
                    $found_hashes               =   $wpdb->get_results( $mysql_query );
                    
                    if  ( count ( $found_hashes  )  <   1 )
                        return;
                    
                    $hashes         =   array();
                    $hashes_time    =   array();
                    foreach ($found_hashes  as $item )
                        {
                            $hashes[]           =   'woogc_sso_login_hash_' . $item->hash;
                            $hashes_time[]      =   'woogc_sso_login_hash_time_' . $item->hash;
                        }
                    
                    
                    $mysql_query    =   "DELETE FROM "  .   $wpdb->usermeta . " 
                                                WHERE meta_key IN ( '" . implode("','", $hashes) ."')
                                                    OR meta_key IN ( '" . implode("','", $hashes_time) ."')";
                    $wpdb->get_results( $mysql_query );
                }
                
                
            
            function woocommerce_registration_redirect( $redirect_url )
                {
                    
                    if(strpos($redirect_url, 'loggedin=true')   === FALSE )
                        {
                            //replace any loggedout argument
                            if( strpos($redirect_url, 'loggedout=true')   !== FALSE )
                                {
                                    preg_match('/(loggedout=true)(\&)?/i', $redirect_url, $matchs);
                                    if  (   ! empty ( $matchs[2] ) )
                                        $redirect_url    =   str_replace( 'loggedout=true&', '', $redirect_url);
                                        else
                                        $redirect_url    =   str_replace( 'loggedout=true', '', $redirect_url);
                                }
                            
                            if(strpos($redirect_url, '?')   === FALSE)   
                                {
                                    $redirect_url   .=  '?loggedin=true';     
                                }
                                else
                                {
                                    if (substr($redirect_url, -1) != '?')
                                        $redirect_url    .=  "&";
                                        
                                    $redirect_url   .=  'loggedin=true';   
                                }
                            
                            global $WooGC;    
                                
                            if ( ! empty ( $WooGC->user_login_sso_hash ) )
                                $redirect_url    .=  "&login_hash=" . $WooGC->user_login_sso_hash;
                        }
                    
                       
                    return $redirect_url;   
                }  
            
            
            function on_plugins_loaded()
                {
                        
                    //turn on buffering
                    ob_start();
                    
                    include_once ( WOOGC_PATH . '/include/class-woogc-download-handler.php');
                    
                    //Relocate default WordPress shutdown hook
                    remove_action(  'shutdown',                   'wp_ob_end_flush_all',                      1    );
                    add_action(     'shutdown',                   'wp_ob_end_flush_all',                      2    );  
                        
                }
            
            
            /**
            * On WordPress shutdown
            * Change any checkout links to plugin option
            * 
            */
            function on__shutdown()
                {
                    global $blog_id, $woocommerce;
                    
                    if(!is_object($woocommerce->cart))
                        return;
                       
                    $options    =   $this->functions->get_options();
                    $blog_details   =   get_blog_details( $blog_id );
         
                    $levels = ob_get_level();
                    
                    if( $levels < 1 )
                        return; 
                    
                    for ( $i = 1; $i < $levels; $i++ )
                        {
                            
                            $flush_level   =   TRUE;
                            if  ( $i == ( $levels - 1 ) ) 
                                $flush_level   =   FALSE;
                              
                            //allow other ob handlers to force a break
                            $continue   =   apply_filters('woogc/on_shutdown/ob_buferring_output', TRUE, ob_get_status() );
                            if (  $continue !== TRUE )
                                return;
                            
                            if  ( $flush_level  === TRUE )
                                {
                                    ob_get_flush();
                                    continue;
                                }
                            
                            $thml   =   ob_get_clean();
                            //ob_end_clean();
                            
                            //replace any checkout links
                            if( $options['cart_checkout_type']  ==  'single_checkout'  &&  !   empty($options['cart_checkout_location'])   &&  $options['cart_checkout_location']  !=  $blog_id)
                                {
                                    $checkout_url   =   wc_get_checkout_url();
                                    $checkout_url   =   str_replace(array('http:', 'https:'), "", $checkout_url);
                                    $checkout_url   =   trailingslashit($checkout_url);
                                    
                                    $thml   =   str_replace( "//"   .   $blog_details->domain .  untrailingslashit($blog_details->path) . "/checkout/", $checkout_url, $thml);
                                
                                }
                                else if ( $options['cart_checkout_type']  ==  'each_store'  &&  isset ( $woocommerce->cart->cart_split ) )
                                        {
                                            $checkout_url   =   $woocommerce->cart->cart_split->get_checkout_url();
                                            $checkout_url   =   str_replace(array('http:', 'https:'), "", $checkout_url);
                                            $checkout_url   =   trailingslashit($checkout_url);
                                            
                                            $thml   =   str_replace( "//"   .   $blog_details->domain .  untrailingslashit($blog_details->path) . "/checkout/", $checkout_url, $thml);
                                        }
                            
                            echo $thml;
                            
                        }
                    
                }
            
            
            /**
            * Trigger on WordPress Init action
            * 
            */
            function gc_on_action_init( )
                {
                    
                    //unregistre certain WooCommerce filters and use custom
                    remove_action( 'wp_loaded',                 array( 'WC_Form_Handler', 'order_again' ), 20 );
                    remove_action( 'wp_loaded',                 array( 'WC_Form_Handler', 'update_cart_action' ), 20 );
                    remove_action( 'woocommerce_payment_complete', 'wc_maybe_reduce_stock_levels' );
                    
                    //register a custom one
                    add_action( 'wp_loaded',                    array( 'WooGC_Form_Handler', 'order_again' ), 20 );
                    add_action( 'wp_loaded',                    array( 'WooGC_Form_Handler', 'update_cart_action' ), 20 );
                    
                    $options    =   $this->functions->get_options();
                    if($options['use_sequential_order_numbers'] ==  'yes')
                        include_once( WOOGC_PATH . '/include/class.woogc.sequential-order-numbers.php');
                    
                }
                
            /**
            * Trigger on WordPress Init action
            * 
            */
            function sso_on_action_init( )
                {
  
                    //create the environemnt file with required constants and variables
                    $this->functions->set_environment_data();
                    
                    $this->set_login_logout_actions();
                    
                    $this->clean_expired_sso();
                    
                    $options    =   $this->functions->get_options();
                    if($options['use_sequential_order_numbers'] ==  'yes')
                        include_once( WOOGC_PATH . '/include/class.woogc.sequential-order-numbers.php');
                    
                }
                
                
            
            /**
            * Clean up expired woogc_sso_data
            * 
            */    
            function clean_expired_sso()
                {
                    
                    global $wpdb;
                    
                    $mysql_query    =   $wpdb->prepare("SELECT umeta_id, meta_value    FROM "  .   $wpdb->usermeta . "
                                                                WHERE   meta_key  LIKE   %s", 'woogc_sso_data_%');
                    
                    $founds =      $wpdb->get_results( $mysql_query );
                    foreach($founds as  $found)
                        {
                            $data = unserialize($found->meta_value);
                            
                            if( time() > $data['time'] + WOOGC_SSO_EXPIRE )
                                {
                                    //expired, remove it
                                    $mysql_query    =   "DELETE FROM "  .   $wpdb->usermeta . "  WHERE umeta_id =   '". $found->umeta_id ."'";   
                                    $wpdb->get_results( $mysql_query );
                                }
                        }   
                    
                    
                }
  
  
            function set_login_logout_actions()
                {
                    
                    if(isset($_GET['loggedout'])    &&  $_GET['loggedout']  ==  'true') 
                        $this->user_login_logout_action =   'logout';
                        
                    if(isset($_GET['loggedin'])    &&  $_GET['loggedin']  ==  'true') 
                        $this->user_login_logout_action =   'login';
                        
                    if(isset($_GET['login_hash'])    &&  ! empty ( $_GET['login_hash']) ) 
                        {
                            $this->user_login_sso_hash      =   $_GET['login_hash'];
                            $this->user_login_logout_action =   'login';
                        }
                        
                    
                }
                                
            function on_action_woocommerce_add_to_cart_fragments( $mini_cart )
                {
                    return $mini_cart;
                    
                    //only when doing AJAX
                    if ( ! defined( 'DOING_AJAX' ) )
                        return $mini_cart;
                    
                    ob_start(); 
                    ?>
                    <script type='text/javascript'> WooGC_Sync.init(); </script>
                    <?php
                    $html   =   ob_get_contents();
                    ob_end_clean();
                    
                    $mini_cart['div.widget_shopping_cart_content'] .=   $html;
                    
                    return $mini_cart;
                       
                }
                
            
            /**
            * Trigger on WordPress wp_footer action
            * Output front side JavaScript code for syncronisation
            * 
            */
            function on_action_wp_footer()
                {
                    
                    global $blog_id, $userdata;
                    
                    $this->clear_expired_sso_login_hash( );
                    
                    $sync_directory     =   WOOGC_URL   .   '/sync';
                    $options            =   $this->functions->get_options();
                    
                    $only_woocommerce_sites =   $options['login_on_sites']    ==  'woocommerce' ?   TRUE    :   FALSE;
                    
                    if ( $only_woocommerce_sites === TRUE    &&  ! $this->functions->is_plugin_active( 'woocommerce/woocommerce.php') )
                        return;
                    
                    
                    $site_home  =   site_url();
                    $site_home  =   str_replace(array('http://', 'https://'), "", $site_home);
                    $site_home  =   trim($site_home, '/');
                      
                    $sync_directory_url     =   str_replace(array('http://', 'https://'), "", $sync_directory);
                    $sync_directory_url     =   str_replace($site_home, "", $sync_directory_url);
                    $sync_directory_url     =   apply_filters( 'woogc/sync_directory_url', $sync_directory_url );
                    
                    ?>
                    
                    
                    <div id="woogc_sync_wrapper" style="display: none"></div>
                    <script type='text/javascript'>
                    /* <![CDATA[ */
                    var WooGC_Sync_Url      =    '<?php echo $sync_directory_url ?>';
                    var WooGC_Sites = [<?php
                                                    
                            $first  =   TRUE;
                                                    
                            $processed_domains  =   array();
                            $processed_sites    =   array();
                            $blog_details   =   get_blog_details( $blog_id );
                            
                            //ignore current domain
                            $processed_domains[]    =   WooGC_get_domain( $blog_details->domain );
                            
                            $sites  =   $this->functions->get_gc_sites( $only_woocommerce_sites );
                            
                            $sites_ids  =   array();
                            foreach($sites  as  $site)
                                {
                                    $sites_ids[]    =   $site->blog_id;   
                                }
                            
                            $allowed_gc_sites   =   apply_filters('woogc/global_cart/sites', $sites_ids);
                            $allowed_sso_sites  =   apply_filters('woogc/sso/sites', $sites_ids);
                            
                            
                            $disabled_gc_sites  =   array();
                            foreach($sites  as  $site)
                                {
                                    
                                    //ignore the current site
                                    if($site->blog_id   ==  $blog_id)
                                        {
                                            if  ( !in_array($blog_id, $allowed_gc_sites)) 
                                                {   
                                                    $disabled_gc_sites[]    =   $site->blog_id ;   
                                                }
                                            continue;
                                        }
                                        
                                    //no need to set for subfolder domains
                                    if($site->path  !=  '/')
                                        continue;
                                        
                                    $domain_root    =   WooGC_get_domain( $site->domain );
                                        
                                    //subdomain check
                                    if(is_subdomain_install())
                                        {
                                            $found  =   FALSE;
                                            
                                            foreach($processed_domains  as  $processed_domain)
                                                {
                                                    if (strpos($domain_root, "." . $processed_domain)    !== FALSE)
                                                        {
                                                            $found  =   TRUE;
                                                            break;   
                                                        }
                                                }
                                                
                                            if ( $found  === TRUE )
                                                continue;
                                        }
                                        
                                    //if domain already processed continue
                                    if(in_array($domain_root, $processed_domains))
                                        continue;
                                    
                                    $processed_domains[]    =   $domain_root;
                                    $processed_sites[]      =   $site->blog_id;
                                    
                                    if  ( in_array($site->blog_id, $allowed_gc_sites)) 
                                        {
                                            if(!$first)
                                                echo ', ';
                                            echo "'//" . $site->domain . "'";

                                            $first  =   FALSE;
                                        }
                                        else
                                        {
                                            $disabled_gc_sites[]    =   $site->blog_id ;   
                                        }
                                }
                        
                        ?>];
                    <?php
                        
                        if ( count ( $disabled_gc_sites )   >   0   &&  in_array($blog_id, $disabled_gc_sites) )
                            {
                                
                    ?>
                    var WooGC_Disable_GC    =   true;
                    <?php            
                                
                            }
                         
                        if( !empty($this->user_login_logout_action))
                            {
                    ?>
                    var WooGC_Action    =   '<?php echo $this->user_login_logout_action ?>';
                    <?php
                    
                        
                    
                        if( $this->user_login_logout_action     ==  'login'     &&  is_object($userdata))
                            {
                    ?>
                    var WooGC_SSO   =   [<?php

                        if ( $this->validate_sso_login_hash_data()   === TRUE  )
                            {
                                $token_data_list    =   array();
                                
                                //create and output the SSO keys for other sites
                                foreach($processed_sites  as  $processed_site)
                                    {
                                        if  ( ! in_array($site->blog_id, $allowed_sso_sites)) 
                                            continue;
                                            
                                        // Create the token
                                        $token_data = array(
                                                                'site'      =>  $processed_site,
                                                                'time'      =>  time(),
                                                                'ip'        =>  $_SERVER['REMOTE_ADDR'],
                                                                'ua'        =>  isset($_SERVER['HTTP_USER_AGENT'])  ?   wp_unslash( $_SERVER['HTTP_USER_AGENT'] )   :   ''
                                                            );
                                        $key = wp_hash( serialize( $token_data ) );
                                        
                                        update_user_meta( $userdata->ID, 'woogc_sso_data_' . $key, $token_data );        
                                        
                                        $token_data_list[]  =   $key;                            
                                    }
                                    
                                echo "'" . implode("', '", $token_data_list) ."'";
                            }
                    
                    ?>];
                    <?php 
                            
                            } 
                            }
                            
                    
                        //output JavaScript variable for POST action to catch on specific methods
                        $WooGC_on_PostVars  =   apply_filters('woogc/sync/on_post_vars', array());
                        if(is_array($WooGC_on_PostVars) &&  count($WooGC_on_PostVars) > 0)
                            {
                                ?>
                                var WooGC_on_PostVars   =   [<?php  
                                    
                                    $first = TRUE;
                                    foreach ($WooGC_on_PostVars as $key =>  $value)
                                        {
                                            if($first === TRUE)
                                                $first  =   FALSE;
                                                else
                                                echo ", ";
                                                
                                            echo '"' . $value . '"';    
                                        }
                                
                                 ?>];
                                <?php
                            }
                            else
                            {
                                ?>
                                var WooGC_on_PostVars   =   [];
                                <?php   
                            }
                            
                    ?>
                    /* ]]> */
                    </script>
                    <script type='text/javascript' src='<?php echo str_replace(array('http:', 'https:'), "", WOOGC_URL) ?>/js/woogc-sync.js?ver=1.0'></script>
    
                    <?php                    
                     
      
                }
                
      
            /**
            * Add the woogc cookie which helds different informations
            * 
            * @param mixed $auth_cookie
            * @param mixed $expire
            * @param mixed $expiration
            * @param mixed $user_id
            * @param mixed $scheme
            */
            function on_action__set_auth_cookie__add_woogc_cookie( $auth_cookie, $expire, $expiration, $user_id, $scheme )
                {
                    
                    //$cookie_parts   =   explode("|", $auth_cookie);
                    
                    //$cookie_data    =   $cookie_parts[0] . '|'  .   $cookie_parts[2] . '|'  . $cookie_parts[3] . '|'  .  $scheme .  '|'  .  $cookie_parts[1];
                                    
                }
                
                
            
            /**
            * On cookie clear action
            * 
            */
            function on_action__clear_auth_cookie__clear_cookies()
                {
                    
                    
                }
                
                
                
            function on_action__set_auth_cookie( $auth_cookie, $expire, $expiration, $user_id, $scheme, $token  )
                {
                    
                    $this->user_login_logout_action =   'login';
                    
                    $last_login                 =   array();
                    $last_login['expiration']   =   $expiration;
                    $last_login['expire']       =   $expire;
                    
                    update_user_meta( $user_id, 'woogc_sso_last_login', $last_login);
                    
                    if ( empty ( $this->user_login_sso_hash ) )
                        $this->user_login_sso_hash      =   $this->create_sso_login_hash( $user_id );

                    
                }
                
                
            function woocommerce_payment_successful_result( $result, $order_id )
                {
                    
                    if  ( $this->user_login_logout_action   === FALSE )
                        return $result;

                    if ( ! empty ( $this->user_login_sso_hash ) )
                        $result['redirect']    .=  "&login_hash=" . $this->user_login_sso_hash;
                        
                        
                    return $result;
                }
           
            
            
            function on_action_shutdown_save__session_data( )
                {
                    
                    if(is_admin()   &&  ( ! defined('DOING_AJAX') ||  (defined('DOING_AJAX') &&  DOING_AJAX  === FALSE )))
                        return;
                    
                    global $wpdb, $userdata, $blog_id, $woocommerce;
                    
                    $session_key    =   '';
                    
                    if( is_object($woocommerce->session))
                        $session_key        =   $woocommerce->session->get_customer_id();
                    
                    if (empty($session_key ))
                        return;
                    
                    //check if there's a session saved
                    //retrieve the current session data
                    $mysql_query    =   $wpdb->prepare( "SELECT * FROM ". $wpdb->prefix . "woocommerce_sessions WHERE session_key = %s", $session_key );
                    $session_data   =   $wpdb->get_row( $mysql_query );

                    //if empty no need to continue
                    if ( !isset($session_data->session_id)   ||  empty($session_data->session_id) )
                        return;
                    
                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                        
                    $sites  =   $this->functions->get_gc_sites( TRUE );
                    foreach($sites  as  $site)
                        {
                            if ( in_array( $site->blog_id, apply_filters( 'woogc/save_session_data/ignore_sites',     array() ) )    === TRUE )
                                continue; 
                                    
                            //no need to update current blog
                            if ( $blog_id    ==  $site->blog_id ) 
                                continue;
                             
                            switch_to_blog( $site->blog_id );
                                    
                            $mysql_query    =   $wpdb->prepare( "SELECT session_id FROM ". $wpdb->prefix . "woocommerce_sessions WHERE session_key = %s", $session_key );
                            $session_id     =   $wpdb->get_var( $mysql_query );
                            
                            if( empty($session_id) )
                                {
                                    //add new entry    
                                    $mysql_query    =   "INSERT INTO ". $wpdb->prefix . "woocommerce_sessions 
                                                            (`session_id`, `session_key`, `session_value`, `session_expiry`) 
                                                            VALUES (NULL, '". $session_key ."', '". esc_sql ( $session_data->session_value ) ."', '". $session_data->session_expiry ."')";
                                    $results        =   $wpdb->get_results( $mysql_query );
                                    
                                }
                                else
                                {
                                    //update the row   
                                    $mysql_query    =   "UPDATE ". $wpdb->prefix . "woocommerce_sessions 
                                                                SET `session_value` =   '". esc_sql( $session_data->session_value ) ."', `session_expiry`    =   '". $session_data->session_expiry ."'
                                                                WHERE session_id = " . $session_id;
                                    $results        =   $wpdb->get_results( $mysql_query );
                                }
                                
                            restore_current_blog();
                            
                        }
                      
                    
                }
            
            
            function session_token_manager()
                {
                    include_once(WOOGC_PATH . '/include/class.woogc.wp-user-meta-session-tokens.php');
                    
                    return 'WooGC_WP_User_Meta_Session_Tokens';   
                    
                }
                
            function woocommerce_session_handler()
                {
                    
                    include_once(WOOGC_PATH . '/include/class.woogc.wc-session-handler.php');
                    
                    return 'WooGC_WC_Session_Handler';    
                    
                }
                
                
            function on__admin_notices()
                {
                    
                    if(! $this->functions->check_mu_files())
                        {
                            echo "<div class='error'><p><strong>WooCommerce Global Cart:</strong> ". __('Unable to copy woo-gc.php to mu-plugins folder. Is this directory writable?', 'woo-global-cart')  ."</p></div>";
                        }
                        
                    //check for MU module starter issues
                    global $WooGC__MU_Module;
                    
                    if  ( ! is_array($WooGC__MU_Module)  )
                        $WooGC__MU_Module   =   array();
                    
                    if(isset($WooGC__MU_Module['issues'])   &&  count( $WooGC__MU_Module['issues'] )   >   0 )
                        {
                            foreach($WooGC__MU_Module['issues'] as  $issue_code)
                                {
                                    switch($issue_code)
                                        {
                                            case 'e01'      :
                                                                echo "<div class='error'><p><strong>WooCommerce Global Cart:</strong> ". __('COOKIE_DOMAIN constant already defined. The SSO and cart cross-domain features possible not fully functional.', 'woo-global-cart')  ."</p></div>";
                                                                break;   
                                            
                                        }
                                }
                        }
                    
                }
                
                
            function WC_disabled_notice()
                {
                    echo "<div class='error'><p><strong>WooCommerce Global Cart:</strong> ". __('WooCommerce plugin is required to be active.', 'woo-global-cart')  ."</p></div>";
                }

                
            function woocommerce_get_order_item_classname( $classname, $item_type, $id  )
                {
                    
                    switch ( $item_type ) 
                        {
                            case 'line_item' :
                            case 'product' :
                                $classname = 'WooGC_WC_Order_Item_Product';
                            break;
                 
                        }
                        
                    return $classname;
                       
                }
                
                       
        }
        
?>