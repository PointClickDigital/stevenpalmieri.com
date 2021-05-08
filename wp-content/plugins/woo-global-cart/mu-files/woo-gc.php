<?php

    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    
    global $blog_id, $WooGC__MU_Module;
    
    define('WOOGC_MULOADER_VERSION',  '1.1');

    //check if multisite is active
    if ( ! defined('MULTISITE')     ||  MULTISITE   === FALSE )
        return;
    
    $current_network = get_network();
    
    $blog_details   =   get_blog_details( $blog_id );
    
    if( !file_exists(WP_PLUGIN_DIR . '/woo-global-cart/include/static-functions.php'))
        return;
        
    require_once(  WP_PLUGIN_DIR . '/woo-global-cart/include/static-functions.php' );
    
    //set ADMIN_COOKIE_PATH 
    if ( !defined('ADMIN_COOKIE_PATH') &&   is_subdomain_install()) 
        {
            define( 'ADMIN_COOKIE_PATH', '/' );            
        }
    
    $WooGC__MU_Module   =   array();
    
    //set COOKIE_DOMAIN 
    if ( !defined('COOKIE_DOMAIN') ) 
        {
            $_domain =   WooGC_get_domain( $blog_details->domain );
            if ( ! filter_var($_domain, FILTER_VALIDATE_IP) )
                $_domain =   '.' . $_domain;
                
            define( 'COOKIE_DOMAIN', $_domain );
        }
        else
        {
            //we expect the cookie to be undefined   
            $WooGC__MU_Module['issues'][]   =   'e01';
        }


?>