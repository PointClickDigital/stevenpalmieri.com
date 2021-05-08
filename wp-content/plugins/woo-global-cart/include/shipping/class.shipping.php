<?php

    class WooGC_Shipping 
        {
            
            function __construct()
                {
                    
                    add_filter('woocommerce_cart_shipping_packages',    array( $this, 'woocommerce_cart_shipping_packages'), 999 );   
                    
                }
                
            
            /**
            * Apply the dimmensions to evey product
            * 
            * @param mixed $packages
            */
            function woocommerce_cart_shipping_packages( $packages )
                {
                    if(is_array($packages)  &&  isset($packages[0]))
                        {
                            foreach($packages[0]['contents']    as  $key    =>  $data)
                                {
                                    $packages[0]['contents'][$key]['data']->weight  =   $packages[0]['contents'][$key]['data']->get_weight();
                                    $packages[0]['contents'][$key]['data']->length  =   $packages[0]['contents'][$key]['data']->get_length();
                                    $packages[0]['contents'][$key]['data']->height  =   $packages[0]['contents'][$key]['data']->get_height();
                                    $packages[0]['contents'][$key]['data']->width   =   $packages[0]['contents'][$key]['data']->get_width();
                                } 
                        }
                    
                    return $packages;
                        
                }
                
        }

    new WooGC_Shipping();

?>