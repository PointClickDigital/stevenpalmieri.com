<?php   
           
    class WooGC_licence
        {
         
            function __construct()
                {
                    $this->licence_deactivation_check();   
                }
                
            function __destruct()
                {
                    
                }
                
            public function licence_key_verify()
                {

                    $licence_data = get_site_option('woogc_licence');
                    
                    if($this->is_local_instance())
                        return TRUE;
                             
                    if(!isset($licence_data['key']) || $licence_data['key'] == '')
                        return FALSE;
                        
                    return TRUE;
                }
                
            function is_local_instance()
                {
                    return FALSE;
                    
                }
                
                
            function licence_deactivation_check()
                {

                    if(!$this->licence_key_verify() ||  $this->is_local_instance()  === TRUE)
                        return;
                    
                    $licence_data = get_site_option('woogc_licence');
                    
                    if(isset($licence_data['last_check']))
                        {
                            if(time() < ($licence_data['last_check'] + ( 86400 * 2 ) ))
                                {
                                    return;
                                }
                        }
                    
                    $licence_key = $licence_data['key'];
                    $args = array(
                                                'woo_sl_action'         => 'status-check',
                                                'licence_key'           => $licence_key,
                                                'product_unique_id'     => WOOGC_PRODUCT_ID,
                                                'domain'                => WOOGC_INSTANCE
                                            );
                    $request_uri    = WOOGC_UPDATE_API_URL . '?' . http_build_query( $args , '', '&');
                    $data           = wp_remote_get( $request_uri );
                    
                    if(is_wp_error( $data ) || $data['response']['code'] != 200)
                        return;   
                    
                    $response_block = json_decode($data['body']);
                    $response_block = $response_block[count($response_block) - 1];
                    $response = $response_block->message;
                    
                    if(isset($response_block->status))
                        {
                            if($response_block->status == 'success')
                                {
                                    if($response_block->status_code == 's203' || $response_block->status_code == 's204')
                                        {
                                            $licence_data['key']          = '';
                                        }
                                }
                                
                            if($response_block->status == 'error')
                                {
                                    $licence_data['key']          = '';
                                } 
                        }
                    
                    $licence_data['last_check']   = time();    
                    update_site_option('woogc_licence', $licence_data);
                    
                }
            
            
        }
            

        
    
?>