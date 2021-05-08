<?php

    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    
    class WooGC_Cart_Single_Checkout
        {
            
            var $WooGC;
            
            /**
            * Constructor
            * 
            */
            function __construct()
                {
                    global $WooGC;
                    $this->WooGC    =   $WooGC;
                       
                    $options    =   $this->WooGC->functions->get_options();
                     
                    if( $options['cart_checkout_split_orders']  ==  'yes' )
                        {
                            if ( ! defined( 'WOOGC_SINGLE_CHECKOUT_USE_SPLIT_EACH_ITEM_SEPARATE_ORDER' ) )
                                define ( 'WOOGC_SINGLE_CHECKOUT_USE_SPLIT_EACH_ITEM_SEPARATE_ORDER', FALSE );
                            
                            add_action ( 'woocommerce_checkout_order_created' ,                 array ( $this, 'custom_woocommerce_checkout_order_created' ) );
                            
                            add_action ( 'woocommerce_order_status_changed',                     array ( $this, 'custom_woocommerce_order_status_changed' ) ,    99, 3);
                                                
                            add_filter ( 'woocommerce_order_data_store_cpt_get_orders_query',   array ( $this, 'custom_woocommerce_order_data_store_cpt_get_orders_query' ), 99, 3 );
                        }
                    
                    
                    add_filter( 'woocommerce_admin_order_buyer_name',                   array ( $this, 'custom_woocommerce_admin_order_buyer_name'),   99, 2 );    
                        
                }
                      
            
            function custom_woocommerce_checkout_order_created ( $order ) 
                {
                    global $blog_id;
                    
                    //allow date ordering to apply
                    sleep (1);
                    
                    //re-create the order objets to entusre also the changes applied after the initial order creation are included
                    $main_order     =   new wc_order ( $order->get_ID() ); 
                    
                    $default_order  =   clone $main_order;
                    
                    $order_blog_id  =   $blog_id;
                    
                    $order_items    =   $default_order->get_items();
                    $order_status   =   $default_order->get_status();
                    
                    $items_by_shop  =   array();
                    
                    if ( count ( $order_items ) < 1 )
                        return;
                        
                    //mark the order as being splitted
                    update_post_meta( $order->get_id(),  'order_splitted', 'true' );
                        
                    foreach ( $order_items as $order_item )
                        {
                            $_blog_id   =   $order_item->get_meta('blog_id');
                                    
                            $items_by_shop[ $_blog_id ][]   =   $order_item;    
                            
                        }
                    
                    $blog_currency  =   get_woocommerce_currency();
                    $woocommerce_prices_include_tax =   get_option( 'woocommerce_prices_include_tax' );    
                    
                    //Create split orders for the shops in $items_by_shop
                    foreach  ( $items_by_shop   as  $i_blog_id    =>  $shop_order_items )
                        {
                            
                            if ( ! apply_filters( 'woogc/single_checkout/split_order/blog_id', $i_blog_id, TRUE ) )
                                continue;
                                
                            $args   =   array ( 
                                                            'default_order'     =>  $default_order,
                                                            'order_blog_id'     =>  $order_blog_id,
                                                            'order_status'      =>  $order_status,
                                                            
                                                            'blog_currency'     =>  $blog_currency,
                                                            'woocommerce_prices_include_tax'    =>  $woocommerce_prices_include_tax,
                                                            
                                                            'i_blog_id'         =>  $i_blog_id
                                                                );
                            
                            if ( WOOGC_SINGLE_CHECKOUT_USE_SPLIT_EACH_ITEM_SEPARATE_ORDER === TRUE )
                                    {
                                        foreach ( $shop_order_items     as  $shop_order_item )
                                            {
                                                $args['shop_order_items']   =   array( $shop_order_item );    
                                                $this->_custom_create_order_and_add_items ( $args );        
                                            }                                
                                    }
                                else
                                    {
                                        $args['shop_order_items']   =   $shop_order_items;
                                        $this->_custom_create_order_and_add_items ( $args );
                                    }
                            
                          
                            
                        }
                    
                }
        
        
            function _custom_create_order_and_add_items ( $args )
                {
                    extract( $args );    
                    
                    $default_order_data = $default_order->get_data();
                    unset ( $default_order_data['id'] );
                    
                    switch_to_blog( $i_blog_id );
                            
                    $new_order  =   new WC_Order();
                    
                    $fields_prefix = array(
                        'shipping' => true,
                        'billing'  => true,
                    );

                    $shipping_fields = array(
                        'shipping_method' => true,
                        'shipping_total'  => true,
                        'shipping_tax'    => true,
                    );
                    
                    restore_current_blog();
                    $new_order->set_parent_id( $default_order->get_id() );
                    switch_to_blog( $i_blog_id );
                    
                    $new_order->set_status( $order_status );
                    
                    $new_order->update_meta_data( 'checkout_blog_id', $order_blog_id );
                    $new_order->update_meta_data( 'checkout_order_id', $default_order->get_id() );
                    
                    foreach ( $default_order_data as $key => $value ) 
                        {
                            if ( $key   ==  'meta_data' ) 
                                {                                    
                                    foreach ( $value as $meta_key   =>  $meta_data)   
                                        {
                                            $current_meta   =   $meta_data->get_data();
                                            if ( isset ( $current_meta['id'] ) )
                                                unset ( $current_meta['id'] );
                                                
                                            $new_order->add_meta_data( $current_meta['key'], $current_meta['value'], TRUE );  
                                        }  
                                }
                            else if ( is_callable( array( $new_order, "set_{$key}" ) ) ) 
                                {
                                    $new_order->{"set_{$key}"}( $value );
                                    // Store custom fields prefixed with wither shipping_ or billing_. This is for backwards compatibility with 2.6.x.
                                } 
                                elseif ( isset( $fields_prefix[ current( explode( '_', $key ) ) ] ) ) 
                                {
                                    if ( ! isset( $shipping_fields[ $key ] ) ) 
                                        {
                                            foreach ( $value    as  $a_key  =>  $a_value)
                                                {
                                                    if ( is_callable( array( $new_order, "set_" . current( explode( '_', $key ) ) . "_" . $a_key ) ) ) 
                                                        {
                                                            $new_order->{"set_" . current( explode( '_', $key ) ) . "_" . $a_key}( $a_value );
                                                        }   
                                                }
                                        }
                            
                                }
                        }
                    
                    if ( isset ( $default_order_data['billing_email'] ) )
                        $new_order->hold_applied_coupons( $default_order_data['billing_email'] );
                    $new_order->set_created_via( 'checkout' );
                    $new_order->set_customer_id( apply_filters( 'woocommerce_checkout_customer_id', get_current_user_id() ) );
                    $new_order->set_currency( $blog_currency );
                    $new_order->set_prices_include_tax( 'yes' === $woocommerce_prices_include_tax );
                    $new_order->set_customer_ip_address( $default_order->get_customer_ip_address() );
                    $new_order->set_customer_user_agent( $default_order->get_customer_user_agent() );
                    $new_order->set_customer_note( isset( $default_order_data['order_comments'] ) ? $default_order_data['order_comments'] : '' );
                    $new_order->set_payment_method( isset( $available_gateways[ $default_order_data['payment_method'] ] ) ? $available_gateways[ $default_order_data['payment_method'] ] : $default_order_data['payment_method'] );
                    
                    $order_total    =   0;
                    
                    foreach ( $shop_order_items  as  $order_item )
                        {
                            $values =   $order_item->get_data();   
                            
                            $cart_item_key  =   '';
                                 
                            $item                       = apply_filters( 'woocommerce_checkout_create_order_line_item_object', new WC_Order_Item_Product(), $cart_item_key, $values, $new_order );

                            $item->legacy_values        = $values; // @deprecated For legacy actions.
                            $item->legacy_cart_item_key = $cart_item_key; // @deprecated For legacy actions.
                            $item->set_props(
                                array(
                                    'name'         => $values['name'],
                                    'tax_class'    => $values['tax_class'],
                                    'product_id'   => $values['product_id'],
                                    'variation_id' => $values['variation_id'],
                                    'quantity'     => $values['quantity'],
                                    'subtotal'     => $values['subtotal'],
                                    'total'        => $values['total'],
                                    'subtotal_tax' => $values['subtotal_tax'],
                                    'total_tax'    => $values['total_tax'],
                                    'taxes'        => $values['taxes'],
                                )
                            );
                            
                            $order_total    +=  $values['total'];
                            
                            if ( isset ( $values['variation'] ) )
                                $item->set_props( 
                                    array (
                                            'variation'    => $values['variation'] 
                                            )
                                    );
             
                            $meta_data  =   $order_item->get_meta_data();
                            foreach ( $meta_data    as  $meta_item )
                                {
                                    $meta_item_data =   $meta_item->get_data();
                                    $item->add_meta_data( $meta_item_data['key'], $meta_item_data['value'], TRUE );  
                                }
             
                            $item->set_backorder_meta();

                            /**
                             * Action hook to adjust item before save.
                             *
                             * @since 3.0.0
                             */
                            do_action( 'woocommerce_checkout_create_order_line_item', $item, $cart_item_key, $values, $new_order );

                            // Add item to order and save.
                            $new_order->add_item( $item );                            
                            
                        }
                        
                    
                    $new_order->set_total ( $order_total );
                        
                    $order_id = $new_order->save();
                                    
                    restore_current_blog();
                    
                }
        
            
            function custom_woocommerce_order_status_changed( $order_id, $old_status, $new_status )
                {
                    
                    $order  =   new WC_Order( $order_id );
                    
                    //on master order
                    $order_splitted     =   $order->get_meta( 'order_splitted' );
                    if (  ! empty ( $order_splitted ) )
                        {
                            //update the status for child orders
                            $order_items    =   $order->get_items();
                            
                            $items_by_shop  =   array();
                            
                            if ( count ( $order_items ) > 0 )
                                {
                                
                                    foreach ( $order_items as $order_item )
                                        {
                                            $_blog_id   =   $order_item->get_meta('blog_id');
                                                    
                                            $items_by_shop[ $_blog_id ][]   =   $order_item;    
                                            
                                        }
                                        
                                    foreach ( $items_by_shop    as  $shop_id    =>  $items )
                                        {
                                            switch_to_blog( $shop_id );
                                            
                                            $args = array(
                                                               'post_type'      =>  'shop_order',
                                                               'post_status'    =>  'any',
                                                               
                                                               'meta_query' => array(
                                                                   array(
                                                                       'key'        => 'checkout_order_id',
                                                                       'value'      => $order->get_ID(),
                                                                       'compare'    => '=',
                                                                   )
                                                               )
                                                            );
                                            $query = new WP_Query($args);
                                            
                                            if ( $query->post_count > 0 )
                                                {
                                                    foreach ( $query->posts as  $i_post )
                                                        {
                                                            $post_object    =   (array)$i_post;
                                                            $post_object['post_status'] =   'wc-' . $new_status;
                                                            
                                                            wp_update_post( $post_object );
                                                        }
                                                }
                                        
                                            restore_current_blog();
                                            
                                        }    
                                    
                                }
                            
                        }
                    
                    //on splitted order   
                    $checkout_blog_id   =   $order->get_meta( 'checkout_blog_id' );
                    if (  ! empty ( $checkout_blog_id ) )
                        {
                            //If completed status, check all other split orders if the same and mark the main slso as Completed
                            if ( $new_status == 'completed' )
                                {
                                    //check all other orders    
                                    
                                    $checkout_order_id   =   $order->get_meta( 'checkout_order_id' );
                                                                
                                    switch_to_blog( $checkout_blog_id );
                                    
                                    $main_order  =   new WC_Order( $checkout_order_id );
                                    $order_items    =   $main_order->get_items();
                                    
                                    $items_by_shop  =   array();
                                    $order_status_by_shop   =   array();
                                    
                                    if ( count ( $order_items ) > 0 )
                                        {
                                        
                                            foreach ( $order_items as $order_item )
                                                {
                                                    $_blog_id   =   $order_item->get_meta('blog_id');
                                                            
                                                    $items_by_shop[ $_blog_id ][]   =   $order_item;    
                                                    
                                                }
                                                
                                            foreach ( $items_by_shop    as  $shop_id    =>  $items )
                                                {
                                                    switch_to_blog( $shop_id );
                                                    
                                                    $args = array(
                                                                       'post_type'      =>  'shop_order',
                                                                       'post_status'    =>  'any',
                                                                       
                                                                       'meta_query'     => array(
                                                                                                   'relation' => 'AND',
                                                                                                   array(
                                                                                                       'key'        => 'checkout_blog_id',
                                                                                                       'value'      => $checkout_blog_id,
                                                                                                       'compare'    => '=',
                                                                                                   ),
                                                                                                   array(
                                                                                                       'key'        => 'checkout_order_id',
                                                                                                       'value'      => $main_order->get_ID(),
                                                                                                       'compare'    => '=',
                                                                                                   )
                                                                                               )
                                                                    );
                                                    $query = new WP_Query($args);
                                                    
                                                    if ( $query->post_count > 0 )
                                                        {
                                                            foreach ( $query->posts as  $i_post )
                                                                {
                                                                    $post_object    =   (array)$i_post;
                                                            
                                                                    if ( ! isset ( $order_status_by_shop[$shop_id ] ) )
                                                                        $order_status_by_shop[$shop_id ]    =   array();
                                                                    
                                                                    $order_status_by_shop[$shop_id ][]    =   $post_object['post_status'];
                                                                }
                                                            
                                                        }
                                                
                                                    restore_current_blog();
                                                    
                                                }    
                                            
                                        }

                                    $unique_statuses    =   array();
                                    
                                    foreach ( $order_status_by_shop as  $shop_id    =>  $statuses )
                                        {
                                            $unique_statuses   =  array_merge ( $statuses, $unique_statuses );
                                        }
                                        
                                    $unique_statuses    =   array_unique ( $unique_statuses );
                                    
                                    if ( count ( $unique_statuses ) < 2 )
                                        {
                                            $unique_statuses    =   array_values( $unique_statuses );
                                            
                                            //update the status of the master order
                                            $post_object    =   (array)get_post( $checkout_order_id );
                                            
                                            if ( ! in_array ( $post_object['post_status'], array ( 'wp-refunded', 'wp-cancelled') ))
                                                $post_object['post_status'] =   $unique_statuses[0];
                                            
                                            wp_update_post( $post_object );                                    
                                        }
                                    
                                    restore_current_blog();
                                    
                                }
                            
                        }
                }
            
            function custom_woocommerce_admin_order_buyer_name( $buyer, $object )
                {
                    $checkout_blog_id   =   $object->get_meta( 'checkout_blog_id' );
                    $checkout_order_id  =   $object->get_meta( 'checkout_order_id' );
                    
                    if ( empty ( $checkout_blog_id ) )
                        return $buyer;
                    
                    $buyer   =   "WooGC SPLIT: Shop#" . $checkout_blog_id .", Order ID" . $checkout_order_id . " - " . $buyer;
                       
                    return $buyer;   
                }
                
                
            function custom_woocommerce_order_data_store_cpt_get_orders_query( $wp_query_args, $query_vars, $object )
                {
                    if ( ! isset ( $wp_query_args['meta_query'] ) )
                        $wp_query_args['meta_query'] =   array();
                        
                    $wp_query_args['meta_query'][]     =   array(
                                                        array(
                                                            'key'     => 'checkout_blog_id',
                                                            'compare' => 'NOT EXISTS',
                                                        ),
                                                    ); 
                    
                    return $wp_query_args;
                }
                           
        }
        
 
 
    new WooGC_Cart_Single_Checkout();
        
        
?>