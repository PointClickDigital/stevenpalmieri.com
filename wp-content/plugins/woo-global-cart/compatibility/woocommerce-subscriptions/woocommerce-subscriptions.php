<?php
    
    class WooGC_woocommerce_subscriptions
        {
            
            function __construct()
                {
                    
                    add_action( 'init',                                 array( $this, 'on__init'), -1 );    
                    
                }
                        
            function on__init()
                {
                    
                    global $WooGC;
                    
                    //replace other hoock
                    $WooGC->functions->remove_class_filter ( 'init', 'WC_Subscriptions_Payment_Gateways', 'init_paypal', 5);
                    
                    /*
                    $WooGC->functions->remove_class_filter ( 'init', 'WC_Subscriptions_Payment_Gateways', 'get_available_payment_gateways');
                    $WooGC->functions->remove_class_filter ( 'init', 'WC_Subscriptions_Payment_Gateways', 'init_paypal', 5);
                    $WooGC->functions->remove_class_filter ( 'init', 'WC_Subscriptions_Payment_Gateways', 'init_paypal', 5);
                    $WooGC->functions->remove_class_filter ( 'init', 'WC_Subscriptions_Payment_Gateways', 'init_paypal', 5);
                    
                    add_filter( 'woocommerce_available_payment_gateways', __CLASS__ . '::get_available_payment_gateways' );

                    add_filter( 'woocommerce_no_available_payment_methods_message', __CLASS__ . '::no_available_payment_methods_message' );

                    // Trigger a hook for gateways to charge recurring payments
                    add_action( 'woocommerce_scheduled_subscription_payment', __CLASS__ . '::gateway_scheduled_subscription_payment', 10, 1 );

                    // Create a gateway specific hooks for subscription events
                    add_action( 'woocommerce_subscription_status_updated', __CLASS__ . '::trigger_gateway_status_updated_hook', 10, 2 );
                    */
                    
                    $this->init();
                    
                }
                
            function init()
                {
                    
                    require_once( WP_PLUGIN_DIR . '/woocommerce-subscriptions/includes/gateways/paypal/class-wcs-paypal.php' );
                    
                    //extend few modules
                    include_once ( WOOGC_PATH . '/compatibility/woocommerce-subscriptions/classes/class-woogc-wcs-paypal.php');
                    
                    WooGC_WCS_PayPal::init();   
                    
                }
                
                                                                   
        }


    new WooGC_woocommerce_subscriptions();    
    
?>