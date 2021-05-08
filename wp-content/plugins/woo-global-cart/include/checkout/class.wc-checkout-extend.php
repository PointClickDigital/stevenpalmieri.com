<?php

    class WOOGC_WC_Checkout extends WC_Checkout 
        {
            
            public static function instance() 
                {
                    self::$instance = new self();
                        
                    return self::$instance;
                }
            
            
            /**
            * Add line items to the order.
            *
            * @param  WC_Order $order
            * @param WC_Cart $cart
            */
            public function create_order_line_items( &$order, $cart ) 
                {
                    foreach ( $cart->get_cart() as $cart_item_key => $values ) 
                        {
                            if( isset($values['blog_id'])    )
                                switch_to_blog($values['blog_id']);
                            
                            
                            /**
                             * Filter hook to get inital item object.
                             * @since 3.1.0
                             */
                            $item                       = apply_filters( 'woocommerce_checkout_create_order_line_item_object', new WC_Order_Item_Product(), $cart_item_key, $values, $order );
                            $product                    = $values['data'];
                            $item->legacy_values        = $values; // @deprecated For legacy actions.
                            $item->legacy_cart_item_key = $cart_item_key; // @deprecated For legacy actions.
                            $item->set_props( array(
                                'quantity'     => $values['quantity'],
                                'variation'    => $values['variation'],
                                'subtotal'     => $values['line_subtotal'],
                                'total'        => $values['line_total'],
                                'subtotal_tax' => $values['line_subtotal_tax'],
                                'total_tax'    => $values['line_tax'],
                                'taxes'        => $values['line_tax_data'],
                            ) );
                            if ( $product ) {
                                $item->set_props( array(
                                    'name'         => $product->get_name(),
                                    'tax_class'    => $product->get_tax_class(),
                                    'product_id'   => $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id(),
                                    'variation_id' => $product->is_type( 'variation' ) ? $product->get_id() : 0,
                                ) );
                            }
                            $item->set_backorder_meta();

                            /**
                             * Action hook to adjust item before save.
                             * @since 3.0.0
                             */
                            do_action( 'woocommerce_checkout_create_order_line_item', $item, $cart_item_key, $values, $order );
                            
                            if( isset($values['blog_id'])    )
                                restore_current_blog();

                            // Add item to order and save.
                            $order->add_item( $item );
                        }
                }
                
            
            
            
            /**
            * Process an order that does require payment.
            *
            * @since 3.0.0
            * @param int    $order_id       Order ID.
            * @param string $payment_method Payment method.
            */
            protected function process_order_payment( $order_id, $payment_method ) 
                {
                    $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

                    if ( ! isset( $available_gateways[ $payment_method ] ) ) {
                        return;
                    }

                    // Store Order ID in session so it can be re-used after payment failure.
                    WC()->session->set( 'order_awaiting_payment', $order_id );

                    // Process Payment.
                    $result = $available_gateways[ $payment_method ]->process_payment( $order_id );

                    // Redirect to success/confirmation/payment page.
                    if ( isset( $result['result'] ) && 'success' === $result['result'] ) {
                        $result = apply_filters( 'woocommerce_payment_successful_result', $result, $order_id );

                        global $WooGC;
                        $options    =   $WooGC->functions->get_options();
                        
                        if( $options['cart_checkout_type']  ==  'each_store' )
                            {
                                //restore the cart
                                WC()->cart->cart_split->restore_cart();   
                                WC()->cart->calculate_totals(); 
                            }
                        
                        if ( ! is_ajax() ) {
                            wp_redirect( $result['redirect'] );
                            exit;
                        }

                        wp_send_json( $result );
                    }
                }
                
                
                
                
            
            /**
             * Process an order that doesn't require payment.
             *
             * @since 3.0.0
             * @param int $order_id Order ID.
             */
            protected function process_order_without_payment( $order_id ) {
                $order = wc_get_order( $order_id );
                $order->payment_complete();
                wc_empty_cart();

                global $WooGC;
                $options    =   $WooGC->functions->get_options();
                
                if( $options['cart_checkout_type']  ==  'each_store' )
                    {
                        //restore the cart
                        WC()->cart->cart_split->restore_cart();   
                        WC()->cart->calculate_totals(); 
                    }
                
                if ( ! is_ajax() ) {
                    wp_safe_redirect(
                        apply_filters( 'woocommerce_checkout_no_payment_needed_redirect', $order->get_checkout_order_received_url(), $order )
                    );
                    exit;
                }

                wp_send_json(
                    array(
                        'result'   => 'success',
                        'redirect' => apply_filters( 'woocommerce_checkout_no_payment_needed_redirect', $order->get_checkout_order_received_url(), $order ),
                    )
                );
            }
            
            
                
            public function process_checkout() 
                {
                    try {
                        $nonce_value = wc_get_var( $_REQUEST['woocommerce-process-checkout-nonce'], wc_get_var( $_REQUEST['_wpnonce'], '' ) ); // @codingStandardsIgnoreLine.

                        if ( empty( $nonce_value ) || ! wp_verify_nonce( $nonce_value, 'woocommerce-process_checkout' ) ) {
                            WC()->session->set( 'refresh_totals', true );
                            throw new Exception( __( 'We were unable to process your order, please try again.', 'woocommerce' ) );
                        }

                        wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );
                        wc_set_time_limit( 0 );

                        do_action( 'woocommerce_before_checkout_process' );

                        if ( WC()->cart->is_empty() ) {
                            /* translators: %s: shop cart url */
                            throw new Exception( sprintf( __( 'Sorry, your session has expired. <a href="%s" class="wc-backward">Return to shop</a>', 'woocommerce' ), esc_url( wc_get_page_permalink( 'shop' ) ) ) );
                        }

                        do_action( 'woocommerce_checkout_process' );

                        $errors      = new WP_Error();
                        $posted_data = $this->get_posted_data();

                        // Update session for customer and totals.
                        $this->update_session( $posted_data );

                        global $WooGC;
                        $options    =   $WooGC->functions->get_options();
                        
                        if( $options['cart_checkout_type']  ==  'each_store' )
                            {
                                WC()->cart->cart_split->set_block();
                            }
                        
                        // Validate posted data and cart items before proceeding.
                        $this->validate_checkout( $posted_data, $errors );

                        foreach ( $errors->get_error_messages() as $message ) {
                            wc_add_notice( $message, 'error' );
                        }

                        
                        
                        if ( empty( $posted_data['woocommerce_checkout_update_totals'] ) && 0 === wc_notice_count( 'error' ) ) 
                            {
                                $this->process_customer( $posted_data );
                                $order_id = $this->create_order( $posted_data );
                                $order    = wc_get_order( $order_id );

                                if ( is_wp_error( $order_id ) ) {
                                    throw new Exception( $order_id->get_error_message() );
                                }

                                if ( ! $order ) {
                                    throw new Exception( __( 'Unable to create order.', 'woocommerce' ) );
                                }
                                
                                do_action( 'woocommerce_checkout_order_processed', $order_id, $posted_data, $order );
                                
                                if ( WC()->cart->needs_payment() ) {
                                    $this->process_order_payment( $order_id, $posted_data['payment_method'] );
                                } else {
                                    $this->process_order_without_payment( $order_id );
                                }
                            }
                        
                        
                        if( $options['cart_checkout_type']  ==  'each_store' )
                            {
                                //restore the cart
                                WC()->cart->cart_split->restore_cart();   
                                WC()->cart->calculate_totals();    
                            }
                        
                            
                    } catch ( Exception $e ) {
                        wc_add_notice( $e->getMessage(), 'error' );
                    }
                    $this->send_ajax_failure_response();
                }   
            
        }


?>