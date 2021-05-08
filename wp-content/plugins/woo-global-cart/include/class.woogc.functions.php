<?php

    class WooGC_Functions 
        {
                        
            /**
            * Return Options
            * 
            */
            static public function get_options()
                {
                    
                    $_options   =   get_site_option('woogc_options');
                    
                    $defaults = array (
                                             'version'                          =>  '1.0',
                                             'db_version'                       =>  '1.0',
                                             
                                             'cart_checkout_type'               =>  'single_checkout',
                                             'cart_checkout_location'           =>  '',
                                             'cart_checkout_split_orders'       =>  'no',
                                             'login_on_sites'                   =>  '',
                                             'login_only_specific_roles'        =>  array( 'customer' ),
                                             'use_sequential_order_numbers'     =>  'no',
                                             'show_product_attributes'          =>  'no'
                                             
                                       );
                    
                    $options = wp_parse_args( $_options, $defaults );
                          
                    return $options;  
                    
                }
            
            /**
            * Update Options
            *     
            * @param mixed $options
            */
            static public function update_options($options)
                {
                    
                    update_site_option('woogc_options', $options);
                    
                    
                }
            
            
            /**
            * Create the environemnt file with required constants and variables
            * 
            * @param mixed $force_create
            */
            function set_environment_data( $force_create = FALSE)
                {
                    
                    $_WooGC_environment   =   array();
                    
                    $_WooGC_environment['COOKIEHASH']               =   COOKIEHASH;
                    
                    $_WooGC_environment['AUTH_COOKIE']              =   AUTH_COOKIE;
                    $_WooGC_environment['SECURE_AUTH_COOKIE']       =   SECURE_AUTH_COOKIE;
                    $_WooGC_environment['LOGGED_IN_COOKIE']         =   LOGGED_IN_COOKIE;
                    $_WooGC_environment['ADMIN_COOKIE_PATH']        =   ADMIN_COOKIE_PATH;
                    $_WooGC_environment['PLUGINS_COOKIE_PATH']      =   PLUGINS_COOKIE_PATH;
                    $_WooGC_environment['COOKIEPATH']               =   COOKIEPATH;
                    $_WooGC_environment['SITECOOKIEPATH']           =   SITECOOKIEPATH;
                    
                    $_WooGC_environment['USE_SUBDOMAIN_INSTALL']    =   is_subdomain_install()  ?   TRUE    :   FALSE;
                    
                    $_WooGC_environment['WOOGC_SSO_EXPIRE']         =   WOOGC_SSO_EXPIRE;
                    
                    global $blog_id;
                    
                    $options                =   $this->get_options();
                    
                    $only_woocommerce_sites =   $options['login_on_sites']    ==  'woocommerce' ?   TRUE    :   FALSE;
                    $sites  =   $this->get_gc_sites( $only_woocommerce_sites );
                            
                    $sites_ids  =   array();
                    foreach($sites  as  $site)
                        {
                            $sites_ids[]    =   $site->blog_id;   
                        }
                    
                    $allowed_gc_sites   =   apply_filters('woogc/global_cart/sites', $sites_ids);
                    $allowed_sso_sites  =   apply_filters('woogc/sso/sites', $sites_ids);
                    
                    $disabled_gc_sites  =   array();
                    $processed_domains  =   array();
                    $processed_sites    =   array();
                    foreach($sites  as  $site)
                        {
                                      
                            //no need to set for subfolder domains
                            if($site->path  !=  '/')
                                continue;  
                                
                            $domain_root    =   WooGC_get_domain( $site->domain );
                                        
                            //subdomain check
                            if(is_subdomain_install())
                                {
                                    $found  =   FALSE;
                                    
                                    foreach($processed_domains  as  $processed_domain)
                                        {
                                            if (strpos($domain_root, "." . $processed_domain)    !== FALSE)
                                                {
                                                    $found  =   TRUE;
                                                    break;   
                                                }
                                        }
                                        
                                    if ( $found  === TRUE )
                                        continue;
                                }
                                
                            //if domain already processed continue
                            if(in_array($domain_root, $processed_domains))
                                continue;
                            
                            if  ( ! in_array($site->blog_id, $allowed_sso_sites)) 
                                continue;
                            
                            $processed_sites[]      =   $site->domain;
                        }
                        
                        
                    
                    $_WooGC_environment['WOOGC_SSO_SITES']              =   implode( "," , $processed_sites );   
                  
                    if( $force_create   === FALSE )
                        {
                            $WooGC_environment    =   '';
                            
                            if( file_exists(WOOGC_PATH . 'sync/environment.php' ) )
                                {
                                    require_once( WOOGC_PATH . 'sync/environment.php');
                                }
                            
                            //if nothing has changed exit
                            if ( $WooGC_environment   ==  json_encode($_WooGC_environment) )
                                return;
                        }
                    
                    ob_start();
                    
                    echo "<?php ";
                    echo '$WooGC_environment = \''. json_encode($_WooGC_environment) .'\'';
                    echo " ?>";
                    
                    $file_data = ob_get_contents();
                    ob_end_clean();
                    
                    global $wp_filesystem;

                    if (empty($wp_filesystem)) 
                        {
                            require_once (ABSPATH . '/wp-admin/includes/file.php');
                            WP_Filesystem();
                        }
                        
                    if( ! $wp_filesystem->put_contents( WOOGC_PATH . 'sync/environment.php', $file_data , 0644) ) 
                        {
                            $process_interface_save_errors  =   get_transient( 'wph-process_interface_save_errors' );
                            delete_transient( 'wph-process_interface_save_errors' );
                            
                            //++++++++++++++++++
                            //to log this error
                            $process_interface_save_errors[]    =   __('Unable to create environment static file. Is ', 'woo-global-cart') . WOOGC_PATH . 'sync/ ' . __('writable', 'woo-global-cart') . '?';
                                            
                            set_transient( 'wph-process_interface_save_errors', $process_interface_save_errors, HOUR_IN_SECONDS );
                        }
        
                }
            
                
            /**
            * Return current url
            * 
            */
            function current_url()
                {
                    
                    $current_url    =   'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                    
                    return $current_url;
                    
                }
            
            
            
            /**
            * Return a list of blogs to be used along with the plugin
            * 
            */
            function get_gc_sites( $WooCommerce_Active = FALSE)
                {
                    
                    $args   =   array(
                                        'number'    =>  9999,
                                        'public'    =>  1,
                                        'archived'  =>  0
                                        );
                    $sites  =   get_sites( $args );
                    
                    if($WooCommerce_Active  === FALSE)
                        return $sites;
                        
                    foreach ($sites as  $key    =>  $site)
                        {
                            switch_to_blog($site->blog_id);
                            
                            if (! $this->is_plugin_active( 'woocommerce/woocommerce.php') )
                                {
                                    unset($sites[$key]);
                                }
                                
                            restore_current_blog();
                               
                        }
                        
                    $sites  =   array_values($sites);
                    
                    return $sites;   
                    
                }
            
                       
            
            /**
            * Remove Class Filter Without Access to Class Object
            *
            * In order to use the core WordPress remove_filter() on a filter added with the callback
            * to a class, you either have to have access to that class object, or it has to be a call
            * to a static method.  This method allows you to remove filters with a callback to a class
            * you don't have access to.
            *
            * Works with WordPress 1.2+ (4.7+ support added 9-19-2016)
            * Updated 2-27-2017 to use internal WordPress removal for 4.7+ (to prevent PHP warnings output)
            *
            * @param string $tag         Filter to remove
            * @param string $class_name  Class name for the filter's callback
            * @param string $method_name Method name for the filter's callback
            * @param int    $priority    Priority of the filter (default 10)
            *
            * @return bool Whether the function is removed.
            */
            function remove_class_filter( $tag, $class_name = '', $method_name = '', $priority = 10 ) 
                {
                    
                    global $wp_filter;
                    
                    // Check that filter actually exists first
                    if ( ! isset( $wp_filter[ $tag ] ) ) 
                        return FALSE;
                        
                    /**
                    * If filter config is an object, means we're using WordPress 4.7+ and the config is no longer
                    * a simple array, rather it is an object that implements the ArrayAccess interface.
                    *
                    * To be backwards compatible, we set $callbacks equal to the correct array as a reference (so $wp_filter is updated)
                    *
                    * @see https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/
                    */
                    if ( is_object( $wp_filter[ $tag ] ) && isset( $wp_filter[ $tag ]->callbacks ) ) 
                        {
                            // Create $fob object from filter tag, to use below
                            $fob = $wp_filter[ $tag ];
                            $callbacks = &$wp_filter[ $tag ]->callbacks;
                        } 
                        else 
                        {
                            $callbacks = &$wp_filter[ $tag ];
                        }
                        
                    // Exit if there aren't any callbacks for specified priority
                    if ( ! isset( $callbacks[ $priority ] ) || empty( $callbacks[ $priority ] ) ) 
                        return FALSE;
                        
                    // Loop through each filter for the specified priority, looking for our class & method
                    foreach( (array) $callbacks[ $priority ] as $filter_id => $filter ) 
                        {
                            // Filter should always be an array - array( $this, 'method' ), if not goto next
                            if ( ! isset( $filter[ 'function' ]  ) ) 
                                continue;
                            
                            //remove static    
                            if ( ! is_array( $filter[ 'function' ] ) )
                                {
                                    if( $filter[ 'function' ]   ==  $class_name . '::'  .  $method_name)
                                        {
                                            unset( $callbacks[ $priority ][ $filter_id ] );
                                            return TRUE;   
                                        }
                                    continue;   
                                }
                                
                            // If first value in array is not an object, it can't be a class
                            if ( ! is_object( $filter[ 'function' ][ 0 ] ) &&   empty ( $filter[ 'function' ][ 0 ] ) ) 
                                continue;
                                
                            // Method doesn't match the one we're looking for, goto next
                            if ( $filter[ 'function' ][ 1 ] !== $method_name ) 
                                continue;
                                
                            // Method matched, now let's check the Class
                            if ( is_object( $filter[ 'function' ][ 0 ] ) &&  get_class( $filter[ 'function' ][ 0 ] ) === $class_name ) 
                                {
                                    // WordPress 4.7+ use core remove_filter() since we found the class object
                                    if( isset( $fob ) )
                                        {
                                            // Handles removing filter, reseting callback priority keys mid-iteration, etc.
                                            $fob->remove_filter( $tag, $filter['function'], $priority );
                                        } 
                                    else 
                                        {
                                            // Use legacy removal process (pre 4.7)
                                            unset( $callbacks[ $priority ][ $filter_id ] );
                                            
                                            // and if it was the only filter in that priority, unset that priority
                                            if ( empty( $callbacks[ $priority ] ) ) 
                                                {
                                                    unset( $callbacks[ $priority ] );
                                                }
                                                
                                            // and if the only filter for that tag, set the tag to an empty array
                                            if ( empty( $callbacks ) ) 
                                                {
                                                    $callbacks = array();
                                                }

                                            
                                        }
                                        
                                    return TRUE;
                                    
                                }
                                else
                                {
                                    // Use legacy removal process (pre 4.7)
                                    unset( $callbacks[ $priority ][ $filter_id ] );
                                    
                                    // and if it was the only filter in that priority, unset that priority
                                    if ( empty( $callbacks[ $priority ] ) ) 
                                        {
                                            unset( $callbacks[ $priority ] );
                                        }
                                        
                                    // and if the only filter for that tag, set the tag to an empty array
                                    if ( empty( $callbacks ) ) 
                                        {
                                            $callbacks = array();
                                        }
                                    
                                }
                        }
                        
                    return FALSE;
                    
                }
            
            
            /**
            * Remove Class Action Without Access to Class Object
            *
            * In order to use the core WordPress remove_action() on an action added with the callback
            * to a class, you either have to have access to that class object, or it has to be a call
            * to a static method.  This method allows you to remove actions with a callback to a class
            * you don't have access to.
            *
            * Works with WordPress 1.2+ (4.7+ support added 9-19-2016)
            *
            * @param string $tag         Action to remove
            * @param string $class_name  Class name for the action's callback
            * @param string $method_name Method name for the action's callback
            * @param int    $priority    Priority of the action (default 10)
            *
            * @return bool               Whether the function is removed.
            */
            function remove_class_action( $tag, $class_name = '', $method_name = '', $priority = 10 ) 
                {
                    
                    $this->remove_class_filter( $tag, $class_name, $method_name, $priority );
                    
                }
                
            
            /**
            * Replace a filter / action from anonymous object
            * 
            * @param mixed $tag
            * @param mixed $class
            * @param mixed $method
            */
            function remove_anonymous_object_filter( $tag, $class, $method ) 
                {
                    $filters = false;

                    if ( isset( $GLOBALS['wp_filter'][$tag] ) )
                        $filters = $GLOBALS['wp_filter'][$tag];

                    if ( $filters )
                    foreach ( $filters as $priority => $filter ) 
                        {
                            foreach ( $filter as $identifier => $function ) 
                                {
                                    if ( ! is_array( $function ) )
                                        continue;
                                           
                                    if ( isset ( $function['function'] ) && is_array ( $function['function'] ) && isset ( $function['function'][1] ) && $method == $function['function'][1] ) 
                                        {
                                            remove_filter($tag, array( $function['function'][0], $method ), $priority);
                                        }
                                }
                        }
                }
                
                
            function createInstanceWithoutConstructor($class)
                {
                    
                    $reflector  = new ReflectionClass($class);
                    $properties = $reflector->getProperties();
                    $defaults   = $reflector->getDefaultProperties();
                           
                    $serealized = "O:" . strlen($class) . ":\"$class\":".count($properties) .':{';
                    foreach ($properties as $property)
                        {
                            $name = $property->getName();
                            if($property->isProtected())
                                {
                                    $name = chr(0) . '*' .chr(0) .$name;
                                } 
                            elseif($property->isPrivate())
                                {
                                    $name = chr(0)  . $class.  chr(0).$name;
                                }
                            
                            $serealized .= serialize($name);
                            
                            if(array_key_exists($property->getName(),$defaults) )
                                {
                                    $serealized .= serialize($defaults[$property->getName()]);
                                } 
                            else 
                                {
                                    $serealized .= serialize(null);
                                }
                        }
                        
                    $serealized .="}";
                    
                    return unserialize($serealized);
                    
                }
                
                
                
            function is_plugin_active( $plugin_slug )
                {
                    
                    include_once(ABSPATH.'wp-admin/includes/plugin.php');
                    
                    $found_plugin   =   is_plugin_active($plugin_slug);   
                    
                    if ( $found_plugin &&  ! file_exists( trailingslashit ( WP_PLUGIN_DIR ) . $plugin_slug ) )
                        $found_plugin   =   FALSE;
                    
                    return $found_plugin;
                    
                }
                
            
            
            
            /**
            * Check different requires
            * 
            */
            public function check_required_structure()
                {
                    
                    //check if the mu files exists
                    if( ! $this->check_mu_files())
                        $this->copy_mu_files( );
                        
                    //check if outdated
                    if ( ! defined('WOOGC_MULOADER_VERSION')    ||  version_compare( WOOGC_MULOADER_VERSION, '1.1', '<' ) )
                        $this->copy_mu_files( TRUE );
                }
                
                
                
            
            /**
            * Check if MU files exists
            * 
            */
            public function check_mu_files()
                {
                    
                    if( file_exists(WPMU_PLUGIN_DIR . '/woo-gc.php' ))
                        return TRUE;
                        
                    return FALSE;
                    
                }
            
                
                
            /**
            * Attempt to copy the mu files to mu-plugins folder
            * 
            */
            public function copy_mu_files( $force_overwrite    =   FALSE   )
                {
                    
                    //check if mu-plugins folder exists
                    if(! is_dir( WPMU_PLUGIN_DIR ))
                        {
                            if (! wp_mkdir_p( WPMU_PLUGIN_DIR ) )
                                return;
                        }
                    
                    //check if file actually exists already
                    if( !   $force_overwrite    )
                        {
                            if( file_exists(WPMU_PLUGIN_DIR . '/woo-gc.php' ))
                                return;
                        }
                        
                    //attempt to copy the file
                    @copy( WP_PLUGIN_DIR . '/woo-global-cart/mu-files/woo-gc.php', WPMU_PLUGIN_DIR . '/woo-gc.php' );
                    
                }
                
                
            
            /**
            * Remove MU plugin files
            * 
            */
            public function remove_mu_files()
                {
                    
                    //check if file actually exists already
                    if( !file_exists(WPMU_PLUGIN_DIR . '/woo-gc.php' ))
                        return;
                        
                    //attempt to copy the file
                    @unlink ( WPMU_PLUGIN_DIR . '/woo-gc.php' );    
                    
                }
            
            
            /**
            * Check if filter / action exists for anonymous object
            * 
            * @param mixed $tag
            * @param mixed $class
            * @param mixed $method
            */
            function anonymous_object_filter_exists($tag, $class, $method)
                {
                    if ( !  isset( $GLOBALS['wp_filter'][$tag] ) )
                        return FALSE;
                    
                    $filters = $GLOBALS['wp_filter'][$tag];
                    
                    if ( !  $filters )
                        return FALSE;
                        
                    foreach ( $filters as $priority => $filter ) 
                        {
                            foreach ( $filter as $identifier => $function ) 
                                {
                                    if ( ! is_array( $function ) )
                                        continue;
                                    
                                    if ( ! $function['function'][0] instanceof $class )
                                        continue;
                                    
                                    if ( $method == $function['function'][1] ) 
                                        {
                                            return TRUE;
                                        }
                                }
                        }
                        
                    return FALSE;
                }
                
                
            
            /**
            * Cretae a field collation to unify across database
            * 
            */
            function get_collated_column_name( $field_name, $table_name )
                {
                        
                    global $wpdb, $WooGC;
                    
                    //try a cached
                    if( ! isset($WooGC->cache['database'])   ||  ! isset($WooGC->cache['database']['table_collation']) )
                        {
                            //attempt to get all tables collation
                            $mysql_query    =   "SELECT TABLE_NAME, TABLE_COLLATION FROM INFORMATION_SCHEMA.`TABLES` 
                                                    WHERE TABLE_SCHEMA = '" .  DB_NAME  ."'";
                            $results        =   $wpdb->get_results( $mysql_query );
                            
                            if ( count ( $results ) >   0 )
                                {
                                    $WooGC->cache['database']['table_collation']    =   array();
                                    
                                    foreach ( $results  as  $result )
                                        {
                                            $WooGC->cache['database']['table_collation'][ $result->TABLE_NAME ] =   $result->TABLE_COLLATION;
                                        }
                                    
                                }
                                else
                                    {
                                        //something went wrong
                                        $WooGC->cache['database']['table_collation']    =   FALSE;
                                    }
                        }
                    
                    //try the cache
                    if ( $WooGC->cache['database']['table_collation']   !== FALSE   &&  isset ( $WooGC->cache['database']['table_collation'][$table_name] ))
                        {
                            
                            $table_collation    =   explode( "_", $WooGC->cache['database']['table_collation'][$table_name]);
                            $charset            =   $table_collation[0];
                            
                            $collation          =   explode( "_", $wpdb->collate );
                            $collation[0]       =   $charset;
                            $use_collation      =   implode("_", $collation);
                            
                            return $field_name . " COLLATE " . $use_collation . " AS " . $field_name;
                        }
                        else
                        {
                            //regular approach
                            $db_collation =   $wpdb->collate;
                            
                            if(empty($db_collation))
                                return $field_name;
                                
                            return $field_name . " COLLATE " . $db_collation . " AS " . $field_name; 
                        }                   
                    
                }
                
                
                
            /**
            * Create a Lock functionality using the MySql 
            * 
            * @param mixed $lock_name
            * @param mixed $release_timeout
            * 
            * @return bool False if a lock couldn't be created or if the lock is still valid. True otherwise.
            */
            function create_lock( $lock_name, $release_timeout = null ) 
                {
                    
                    global $wpdb, $blog_id;
                    
                    if ( ! $release_timeout ) {
                        $release_timeout = 10;
                    }
                    $lock_option = $lock_name . '.lock';
                                     
                    // Try to lock.
                    $lock_result = $wpdb->query( $wpdb->prepare( "INSERT INTO `". $wpdb->sitemeta ."` (`site_id`, `meta_key`, `meta_value`) 
                                                                    SELECT %s, %s, %s FROM DUAL
                                                                    WHERE NOT EXISTS (SELECT * FROM `". $wpdb->sitemeta ."` 
                                                                          WHERE `meta_key` = %s AND `meta_value` != '') 
                                                                    LIMIT 1", $blog_id, $lock_option, time(), $lock_option) );
                                        
                    if ( ! $lock_result ) 
                        {
                            $lock_result    =   $this->get_lock( $lock_option );

                            // If a lock couldn't be created, and there isn't a lock, bail.
                            if ( ! $lock_result ) {
                                return false;
                            }

                            // Check to see if the lock is still valid. If it is, bail.
                            if ( $lock_result > ( time() - $release_timeout ) ) {
                                return false;
                            }

                            // There must exist an expired lock, clear it and re-gain it.
                            $this->release_lock( $lock_name );

                            return $this->create_lock( $lock_name, $release_timeout );
                        }

                    // Update the lock, as by this point we've definitely got a lock, just need to fire the actions.
                    $this->update_lock( $lock_option, time() );

                    return true;
                    
                }

            
            /**
            * Retrieve a lock value
            * 
            * @param mixed $lock_name
            * @param mixed $return_full_row
            */
            private function get_lock( $lock_name, $return_full_row =   FALSE )
                {
                    
                    global $wpdb;
                    
                    $mysq_query =   $wpdb->get_row( $wpdb->prepare("SELECT `site_id`, `meta_key`, `meta_value` FROM  `". $wpdb->sitemeta ."`
                                                                    WHERE `meta_key`    =   %s", $lock_name ) );
                    
                    
                    if ( $return_full_row   === TRUE )
                        return $mysq_query;
                        
                    if ( is_object($mysq_query) && isset ( $mysq_query->meta_value ) )
                        return $mysq_query->meta_value;
                        
                    return FALSE;
                    
                }
                
                
            /**
            * Update lock value
            *     
            * @param mixed $lock_name
            * @param mixed $lock_value
            */
            private function update_lock( $lock_name, $lock_value )
                {
                    
                    global $wpdb;
                    
                    $mysq_query =   $wpdb->query( $wpdb->prepare("UPDATE `". $wpdb->sitemeta ."` 
                                                                    SET meta_value = %s
                                                                    WHERE meta_key = %s", $lock_value, $lock_name) );
                    
                    
                    return $mysq_query;
                    
                }
                
            
            /**
            * Releases an upgrader lock.
            *
            * @param string $lock_name The name of this unique lock.
            * @return bool True if the lock was successfully released. False on failure.
            */
            function release_lock( $lock_name ) 
                {
                    
                    global $wpdb;
                    
                    $lock_option = $lock_name . '.lock';
                    
                    $mysq_query =   $wpdb->query( $wpdb->prepare( "DELETE FROM `". $wpdb->sitemeta ."` 
                                                                    WHERE meta_key = %s", $lock_option ) );
                    
                    return $mysq_query;
                    
                }
                               
                
        }


?>