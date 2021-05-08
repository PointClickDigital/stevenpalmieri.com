<?php

    /**
    * Plugin Name:     YITH WooCommerce Minimum Maximum Quantity Premium
    * Version:         1.4.7
    */

    class WooGC_yith_woocommerce_minimum_maximum_quantity_premium
        {
           
            function __construct() 
                {
                    
                    $this->init();
                                  
                }
                
                
            function init()
                {
                    global $WooGC;
                    
                    //unregister the hook from original class
                    $WooGC->functions->remove_class_filter( 'ywmmq_check_exclusion', 'YITH_WC_Min_Max_Qty', 'ywmmq_check_exclusion' );
                      
                    add_filter( 'ywmmq_check_exclusion', array( $this, 'ywmmq_check_exclusion' ), 10, 3 );
                    
                }
                
                
            
            
            /**
             * Check the active exclusions for each product in the cart
             *
             * @param   $value
             * @param   $item_key
             * @param   $product_id
             *
             * @return  bool
             * @since   1.0.0
             *
             * @author  Alberto Ruggiero
             */
            public function ywmmq_check_exclusion( $value, $item_key, $product_id ) 
                {
                    $class_instance =   YITH_WC_Min_Max_Qty::get_instance();
                    
                    $class_instance->contents_to_validate[ $item_key ]['excluded'] = false;

                    global $sitepress;
                    $has_wpml = ! empty( $sitepress ) ? true : false;

                    if ( $has_wpml && apply_filters( 'ywmmq_wpml_use_default_language_settings', false ) ) {
                        $product_id = yit_wpml_object_id( $product_id, 'product', true, wpml_get_default_language() );
                    }

                    $cart_data  =   WC()->cart->get_cart();
                    foreach ( $cart_data     as  $cart_item_key   =>  $cart_item )
                        {
                            if ( $cart_item_key !=  $item_key )
                                continue;
                                
                            switch_to_blog( $cart_item['blog_id'] );
                            break;
                        }
                    
                    $product = wc_get_product( $product_id );

                    if ( $product->get_meta( '_ywmmq_product_exclusion' ) == 'yes' ) {
                        $class_instance->contents_to_validate[ $item_key ]['excluded'] = true;
                        $class_instance->excluded_products                             = true;

                        restore_current_blog();
                        return true;
                    }

                    $product_categories = wp_get_object_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );

                    foreach ( $product_categories as $cat_id ) {

                        $category_exclusion = get_term_meta( $cat_id, '_ywmmq_category_exclusion', true );

                        if ( $category_exclusion == 'yes' ) {
                            $class_instance->contents_to_validate[ $item_key ]['excluded'] = true;
                            $class_instance->excluded_products                             = true;

                            restore_current_blog();
                            return true;
                        }

                    }

                    $product_tag = wp_get_object_terms( $product_id, 'product_tag', array( 'fields' => 'ids' ) );

                    foreach ( $product_tag as $tag_id ) {

                        $tag_exclusion = get_term_meta( $tag_id, '_ywmmq_tag_exclusion', true );

                        if ( $tag_exclusion == 'yes' ) {
                            $class_instance->contents_to_validate[ $item_key ]['excluded'] = true;
                            $class_instance->excluded_products                             = true;

                            restore_current_blog();
                            return true;
                        }

                    }

                    restore_current_blog();
                    return $value;
                }
            
        }

    new WooGC_yith_woocommerce_minimum_maximum_quantity_premium();

?>