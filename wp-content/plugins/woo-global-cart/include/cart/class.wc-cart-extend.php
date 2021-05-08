<?php

    class WOOGC_WC_Cart extends WC_Cart {
        
        public function __construct( $reload_session_data = FALSE ) 
            {
                global $WooGC;
                
                //remove the other hooks for WC_Cart_Session
                $WooGC->functions->remove_class_action('wp_loaded', 'WC_Cart_Session', 'get_cart_from_session');
                $WooGC->functions->remove_class_action('woocommerce_cart_emptied', 'WC_Cart_Session', 'destroy_cart_session');
                $WooGC->functions->remove_class_action('wp', 'WC_Cart_Session', 'maybe_set_cart_cookies', 99);
                $WooGC->functions->remove_class_action('shutdown', 'WC_Cart_Session', 'maybe_set_cart_cookies', 0);
                $WooGC->functions->remove_class_action('woocommerce_add_to_cart', 'WC_Cart_Session', 'maybe_set_cart_cookies');
                $WooGC->functions->remove_class_action('woocommerce_after_calculate_totals', 'WC_Cart_Session', 'set_session');
                $WooGC->functions->remove_class_action('woocommerce_cart_loaded_from_session', 'WC_Cart_Session', 'set_session');
                $WooGC->functions->remove_class_action('woocommerce_removed_coupon', 'WC_Cart_Session', 'set_session');
                $WooGC->functions->remove_class_action('woocommerce_cart_updated', 'WC_Cart_Session', 'persistent_cart_update');
                
                //remove parent class actions
                $WooGC->functions->remove_class_action('woocommerce_add_to_cart', 'WC_Cart', 'calculate_totals', 20);
                $WooGC->functions->remove_class_action('woocommerce_applied_coupon', 'WC_Cart', 'calculate_totals', 20);
                $WooGC->functions->remove_class_action('woocommerce_cart_item_removed', 'WC_Cart', 'calculate_totals', 20);
                $WooGC->functions->remove_class_action('woocommerce_cart_item_restored', 'WC_Cart', 'calculate_totals', 20);
                $WooGC->functions->remove_class_action('woocommerce_check_cart_items', 'WC_Cart', 'check_cart_items', 1);
                $WooGC->functions->remove_class_action('woocommerce_check_cart_items', 'WC_Cart', 'check_cart_coupons', 1);
                $WooGC->functions->remove_class_action('woocommerce_after_checkout_validation', 'WC_Cart', 'check_customer_coupons', 1);
                
                $this->session          = new WOOGC_WC_Cart_Session( $this );
                $this->fees_api         = new WC_Cart_Fees( $this );
                $this->tax_display_cart = get_option( 'woocommerce_tax_display_cart' );

                // Register hooks for the objects.
                $this->session->init( $reload_session_data );

                add_action( 'woocommerce_add_to_cart', array( $this, 'calculate_totals' ), 20, 0 );
                add_action( 'woocommerce_applied_coupon', array( $this, 'calculate_totals' ), 20, 0 );
                add_action( 'woocommerce_cart_item_removed', array( $this, 'calculate_totals' ), 20, 0 );
                add_action( 'woocommerce_cart_item_restored', array( $this, 'calculate_totals' ), 20, 0 );
                add_action( 'woocommerce_check_cart_items', array( $this, 'check_cart_items' ), 1 );
                add_action( 'woocommerce_check_cart_items', array( $this, 'check_cart_coupons' ), 1 );
                add_action( 'woocommerce_after_checkout_validation', array( $this, 'check_customer_coupons' ), 1 );
                
            }
         

    }


?>