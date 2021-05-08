<?php
    
    
    /**
     * Session API: WooGC_WP_User_Meta_Session_Tokens class
     *
     */

    /**
     * Meta-based user sessions token manager.
     *
     */
    class WooGC_WP_User_Meta_Session_Tokens extends WP_Session_Tokens {

        var $sessions_map   =   array();
        
	    /**
	     * Get all sessions of a user.
	     *
	     * @since 4.0.0
	     * @access protected
	     *
	     * @return array Sessions of a user.
	     */
	    protected function get_sessions() {
            
            global $wpdb;
            
            $sessions   =   array();
            
            //retrieve all sessions
            $mysq_query             =   $wpdb->prepare("SELECT umeta_id, meta_value FROM " . $wpdb->usermeta . "
                                                WHERE   user_id =   %d  AND `meta_key`  =   'woogc_session_tokens'", $this->user_id);
            $user_sessions_raw      =   $wpdb->get_results( $mysq_query );
            
            foreach($user_sessions_raw  as  $user_session_raw)
                {
                    $session_data   =   maybe_unserialize($user_session_raw->meta_value);
                    reset($session_data);
                    
                    
                    
                    $this->sessions_map[ key($session_data) ]['umeta_id']       =   $user_session_raw->umeta_id;
                    $this->sessions_map[ key($session_data) ]['session_data']   =   current( $session_data );
                    
                    $key    =   key($session_data);
                    $value  =   current( $session_data );
                                
                    $sessions[ $key ] =   array();
                    $sessions[ $key ] =   $value;
            
                }
                    
		    if ( ! is_array( $sessions ) ) {
			    return array();
		    }

		    $sessions = array_map( array( $this, 'prepare_session' ), $sessions );
		    return array_filter( $sessions, array( $this, 'is_still_valid' ) );
	    }

	    /**
	     * Converts an expiration to an array of session information.
	     *
	     * @param mixed $session Session or expiration.
	     * @return array Session.
	     */
	    protected function prepare_session( $session ) {
		    if ( is_int( $session ) ) {
			    return array( 'expiration' => $session );
		    }

		    return $session;
	    }

	    /**
	     * Retrieve a session by its verifier (token hash).
	     *
	     * @since 4.0.0
	     * @access protected
	     *
	     * @param string $verifier Verifier of the session to retrieve.
	     * @return array|null The session, or null if it does not exist
	     */
	    protected function get_session( $verifier ) {
		    $sessions = $this->get_sessions();

		    if ( isset( $sessions[ $verifier ] ) ) {
			    return $sessions[ $verifier ];
		    }

		    return null;
	    }

	    /**
	     * Update a session by its verifier.
	     *
	     * @since 4.0.0
	     * @access protected
	     *
	     * @param string $verifier Verifier of the session to update.
	     * @param array  $session  Optional. Session. Omitting this argument destroys the session.
	     */
	    protected function update_session( $verifier, $session = null ) {
		    
            $sessions   =   $this->get_sessions();

		    if ( $session ) 
                {
			        
                    $session_data[$verifier]    =   $session;
                    if(isset($this->sessions_map[$verifier]))
                        {
                            // update
                            $umeta_id   =   $this->sessions_map[$verifier]['umeta_id'];
                            
                            global $wpdb;
                            
                            $mysq_query             =   $wpdb->prepare("UPDATE FROM " . $wpdb->usermeta . "
                                                                            SET `meta_value`    =   %s
                                                                            WHERE   umeta_id =   %d", serialize($session), $umeta_id);
                            $results                =   $wpdb->get_results( $mysq_query );
                            
                        }
                        else
                        {
                            // add
                            add_user_meta($this->user_id, 'woogc_session_tokens', $session_data);
                        }
                    
		        } 
            else 
                {
                    
                    if(!isset($this->sessions_map[$verifier]['session_data']))
                        return;
                    
                    $session_data   =   $this->sessions_map[$verifier]['session_data'];
                    
                    // remove session
                    $umeta_id   =   $this->sessions_map[$verifier]['umeta_id'];
                    if($umeta_id    >   0)
                        {
                            global $wpdb;
                            
                            $mysq_query             =   $wpdb->prepare("DELETE FROM " . $wpdb->usermeta . "
                                                                            WHERE   umeta_id =   %d", $umeta_id);
                            $results                =   $wpdb->get_results( $mysq_query );    
                            
                            //remove it from session_map
                            unset($this->sessions_map[ $verifier ]);
                            
                        }
                        
                    // remove other similar sessions
                    foreach($this->sessions_map as  $session_verifier   =>  $data)
                        {
                            if($data['session_data']['expiration']  ==  $session_data['expiration'] 
                                    && $data['session_data']['ip']  ==  $session_data['ip']
                                )
                                {
                                    
                                    $umeta_id               =   $data['umeta_id'];
                                    
                                    $mysq_query             =   $wpdb->prepare("DELETE FROM " . $wpdb->usermeta . "
                                                                                    WHERE   umeta_id =   %d", $umeta_id);
                                    $results                =   $wpdb->get_results( $mysq_query );
                                    
                                    //remove it from session_map
                                    unset($this->sessions_map[ $session_verifier ]);
                                
                                }   
                        }
                    
		        }
                
            //clear expires
            $this->clear_expred_sessions();

	    }

        
        
        protected function clear_expred_sessions()
            {
                
                global $wpdb;
                
                //check for any expired
                foreach($this->sessions_map as  $verifier   =>  $data)
                    {
                        if($data['session_data']['expiration'] < time())
                            {
                                $umeta_id   =   $data['umeta_id'];   
                                
                                $mysq_query             =   $wpdb->prepare("DELETE FROM " . $wpdb->usermeta . "
                                                                                WHERE   umeta_id =   %d", $umeta_id);
                                $results                =   $wpdb->get_results( $mysq_query );    
                                
                            }
                    }
            }
            

	    /**
	     * Destroy all session tokens for a user, except a single session passed.
	     *
	     * @since 4.0.0
	     * @access protected
	     *
	     * @param string $verifier Verifier of the session to keep.
	     */
	    protected function destroy_other_sessions( $verifier ) {
		    
            global $wpdb;
            
            $sessions   =   $this->get_sessions();
            
            foreach($this->sessions_map as  $session_verifier   =>  $data)
                {
                    if ($verifier   ==  $session_verifier)
                        continue;
                          
                    $mysq_query             =   $wpdb->prepare("DELETE FROM " . $wpdb->usermeta . "
                                                                                WHERE   umeta_id =   %d", $data['umeta_id']);
                    $results                =   $wpdb->get_results( $mysq_query );
                }
            
	    }

	    /**
	     * Destroy all session tokens for a user.
	     *
	     * @since 4.0.0
	     * @access protected
	     */
	    protected function destroy_all_sessions() {
		    
            global $wpdb;
            
            $mysq_query             =   $wpdb->prepare("DELETE FROM " . $wpdb->usermeta . "
                                                            WHERE   user_id =   %d AND `meta_key`  =   'woogc_session_tokens'", $this->user_id);
            $results                =   $wpdb->get_results( $mysq_query );
            
	    }

	    /**
	     * Destroy all session tokens for all users.
	     *
	     * @since 4.0.0
	     * @access public
	     * @static
	     */
	    public static function drop_sessions() {
		    
            global $wpdb;
            
            $mysq_query             =   $wpdb->prepare("DELETE FROM " . $wpdb->usermeta . "
                                                            WHERE   `meta_key`  =   'woogc_session_tokens'");
            $results                =   $wpdb->get_results( $mysq_query );
            
	    }
    }
    
    
?>