<?php


    /**
    * Plugin Name:     WooCommerce Multilingual
    * Version:         4.6.7
    */
    
    
    class WooGC_wcml
        {
           
            function __construct() 
                {
                    
                    add_filter('init',                                      array( $this, 'load'), 9999 );
                    
                    add_filter( 'woocommerce_cart_item_name',               array( $this, '_start_woocommerce_cart_item_name'), -1, 3 );
                    add_filter( 'woocommerce_cart_item_name',               array( $this, '_stop_woocommerce_cart_item_name'), 9999, 3 );
                                  
                }
                
                
            function load()
                {
                    global $sitepress;
                    
                    if  (  $sitepress   === NULL  )
                        return;
                    
                    include_once ( WOOGC_PATH . '/compatibility/woocommerce-multilingual/inc/class-wcml-orders.php');   
                    
                    global $woocommerce_wpml;
                    
                    new WOOGC_WCML_Orders( $woocommerce_wpml, $sitepress );
                    
                }
                
                
            function _start_woocommerce_cart_item_name( $title, $cart_item, $cart_item_key  )   
                {
                    switch_to_blog( $cart_item['blog_id'] );
                        
                    return $title;    
                }
            
            function _stop_woocommerce_cart_item_name( $title, $cart_item, $cart_item_key  )   
                {
                    restore_current_blog();
                    
                    return $title;    
                }
     
        }

    new WooGC_wcml();



?>