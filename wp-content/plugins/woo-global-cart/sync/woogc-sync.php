<?php
        
        class WooGC_Sync 
            {
                
                private $query          =   '';
                private $environment    =   array();
                
                private $query_wp_data  =   array();
                private $user_id        =   '';
                private $user_data      =   '';
                
                private $query_wc_data  =   array();
                
                private $session_data   =   array();
                
                private $wc_cookie_max_age   =   '';
                
                private $secure_cookie       =   '';
                
                function __construct()
                    {
                                                
                        $this->query_wp             =   isset($_GET['wp'])      ?  preg_replace("/[^A-Za-z0-9\.\|]/", '', $_GET['wp'])    :   FALSE;
                        $this->query_wc             =   isset($_GET['wc'])      ?  preg_replace("/[^A-Za-z0-9\.\|]/", '', $_GET['wc'])    :   FALSE;
                        
                        $this->sso_hash             =   isset($_GET['sso_hash'])      ?  preg_replace("/[^A-Za-z0-9\.]/", '', $_GET['sso_hash'])    :   FALSE;
                        $this->return_url           =   isset($_GET['return_url'])      ?  $_GET['return_url']    :   FALSE;
                        $this->wooGC_Session_ID     =   isset($_GET['wooGC_Session_ID'])      ?  preg_replace("/[^A-Za-z0-9\.\|]/", '', $_GET['wooGC_Session_ID'])    :   '';
                        $this->wc_cookie_max_age    =   60*60*24*10;
                        
                        $this->secure_cookie             =   isset( $_SERVER['HTTPS'] )  ?   TRUE :   FALSE;
                        //SameSite requires secure cookie!!
                        
                        include_once ( realpath(dirname(__FILE__) . '/..')  . '/include/static-functions.php' );
                        
                        $this->load_environment();
                        $this->set_other_constants();
      
                        $this->run();
                        
                    }
                    
                
                function __destruct()
                    {
                        if ( $this->return_url    === FALSE )    
                            $this->_output_pixel();
                            else
                            {
                                $return_url =   $this->return_url;
                                
                                $url_query  =   '';
                                
                                if ( $this->sso_hash != '' )
                                    $url_query .=  "&sso_hash=". $this->sso_hash;
                                if ( $this->query_wc != '' )
                                    $url_query .=  "&wc=". $this->query_wc;
                                    
                                $url_query  =   ltrim( $url_query, "&" );
                                if ( ! empty  ( $url_query ) )
                                    $return_url .= '?' . $url_query;    
                                
                                if ( ! empty ( $this->wooGC_Session_ID ) )
                                    $this->set_cookie ( "wooGC_Session_ID", urldecode($this->wooGC_Session_ID), $this->wc_cookie_max_age, '/', COOKIE_DOMAIN, '', '', 'Lax' );
                                    else
                                    $this->set_cookie ( 'wooGC_Session_ID', '', -1,   '/',   COOKIE_DOMAIN );
                                    
                                $protocol   =   isset( $_SERVER['HTTPS'] )  ?   'https' :   'http';
                                
                                $return_url =   $protocol . ":" . $return_url;
                                    
                                header("Location: " . $return_url );
                            }
                    }
                    
                
                /**
                * Load environment data
                * 
                */
                private function load_environment()
                    {
                        require_once('environment.php');
                        $this->environment  =   json_decode($WooGC_environment);

                        $this->define_environment_constants();
                    }
                    
                
                private function define_environment_constants()
                    {
                        
                        foreach($this->environment  as  $constant_name  =>  $value)
                            {
                                if(!defined($constant_name))
                                    define($constant_name, $value);
                            }   
                        
                    }
                
                
                
                private function set_other_constants()
                    {
                        
                        $this->environment->COOKIE_DOMAIN    =   $this->environment->USE_SUBDOMAIN_INSTALL  === TRUE    ?   "." . WooGC_get_domain($_SERVER['SERVER_NAME'])  :   $_SERVER['SERVER_NAME'];
                        
                        $this->define_environment_constants();
                    }
                    
                
                
                
                
                
                /**
                * Run the setup
                *     
                */
                private function run()
                    {

                        if( $this->query_wp ===  '')
                            {
                                $this->delete_wp_cookies();
                            }
                        
                        if( ! $this->query_wc )
                            {
                                $this->delete_wc_cookies();
                            }
                        
                        if(empty( $this->query_wp ) &&  ! $this->query_wc)
                            return;
                        
                        if( $this->query_wc !== FALSE )
                            $this->set_wc_cookies();
                        
                        if( ! empty($this->query_wp) )
                            $this->set_wp_cookies();
                    
                    }
                    
                    
                private function delete_wp_cookies()
                    {
                        
                        //clear cookies                       
                        $this->set_cookie( AUTH_COOKIE,         '', -1,   ADMIN_COOKIE_PATH,      COOKIE_DOMAIN, $this->secure_cookie );
                        $this->set_cookie( SECURE_AUTH_COOKIE,  '', -1,   ADMIN_COOKIE_PATH,      COOKIE_DOMAIN, $this->secure_cookie );
                        $this->set_cookie( AUTH_COOKIE,         '', -1,   PLUGINS_COOKIE_PATH,    COOKIE_DOMAIN, $this->secure_cookie );
                        $this->set_cookie( SECURE_AUTH_COOKIE,  '', -1,   PLUGINS_COOKIE_PATH,    COOKIE_DOMAIN, $this->secure_cookie );
                        $this->set_cookie( LOGGED_IN_COOKIE,    '', -1,   COOKIEPATH,             COOKIE_DOMAIN, $this->secure_cookie );
                        $this->set_cookie( LOGGED_IN_COOKIE,    '', -1,   SITECOOKIEPATH,         COOKIE_DOMAIN, $this->secure_cookie );
                                        
                    }
                    
                    
                private function delete_wc_cookies()
                    {
                                                
                        //clear woocommerce cookies
                        $this->set_cookie( 'wp_woocommerce_session_' . COOKIEHASH,  '', -1,   '/',   COOKIE_DOMAIN, $this->secure_cookie );
                        $this->set_cookie( 'woocommerce_cart_hash',                 '', -1,   '/',   COOKIE_DOMAIN, $this->secure_cookie );
                        $this->set_cookie( 'woocommerce_items_in_cart',             '', -1,   '/',   COOKIE_DOMAIN, $this->secure_cookie );
          
                    }
                
                    
                private function set_wp_cookies()
                    {
                        
                        define( 'SHORTINIT', true );
                        require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
                        
                        $woogc_sso_data =   $this->check_wp_login_hash( $this->query_wp );
                        if( $woogc_sso_data === FALSE )
                            return;
                        
                        $this->session_data =   unserialize($woogc_sso_data->meta_value);
                        $this->user_id      =   $woogc_sso_data->user_id;
                        
                        //clean the hash from db as bein used already
                        $this->clear_wp_login_hash( $this->query_wp );
                        
                        //check if user should be loged in only if belong to specific role
                        if( ! $this->use_sso_for_specific_roles( $this->user_id ) )
                            return;                        
                            
                        if( ! $this->validate_sso_data($woogc_sso_data))
                            return;
               
                        add_filter('session_token_manager',             array( $this, 'session_token_manager' ), 999 );
                        
                        $this->wp_set_domain_cookies();
                           
                    }
                    
                    
                private function set_wc_cookies()
                    {
                        
                        $this->parse_wc_query();
                        
                        if( ! $this->check_cookie_expire( $this->query_wc_data['cookie_expiration'] ) )
                            return;
                            
                        $this->wc_set_domain_cookies();
                        
                    }
                    
                          
                private function parse_wc_query()
                    {
                        
                        $data                   =   explode("||", $this->query_wc);
                        
                        $this->query_wc_data       =   array(
                                                        'customer_id'           =>   $data[0],
                                                        'cookie_expiration'     =>   $data[1],
                                                        'cookie_hmac'           =>   $data[3],
                                                        );
                    
                        
                    }
                
                
                private function check_cookie_expire( $expire )
                    {
                        
                        if( $expire < time() )
                            return FALSE;
                            
                        return TRUE;    
                        
                    }
                
                
                
                private function check_wp_login_hash( $hash )
                    {
                        
                        global $wpdb;
                        
                        //check any entry to match
                        $query              =   $wpdb->prepare("SELECT user_id, meta_value FROM "  .   $wpdb->usermeta . "
                                                                WHERE   `meta_key`  =   %s", 'woogc_sso_data_' . $hash);
                        $woogc_sso_data    =   $wpdb->get_row( $query );
                        
                        if ( ! is_object( $woogc_sso_data ) )
                            return FALSE;
  
                        return $woogc_sso_data;
                        
                    }
                
                
                private function clear_wp_login_hash( $hash )
                    {
                                    
                        global $wpdb;
                        
                        $mysql_query    =   $wpdb->prepare("DELETE FROM "  .   $wpdb->usermeta . "  WHERE `meta_key`  =   %s", 'woogc_sso_data_' . $hash);   
                        $wpdb->get_results( $mysql_query );
                    
                    }        
                
                
                
                private function use_sso_for_specific_roles( $user_id )
                    {
        
                        global $wpdb, $blog_id;
                        
                        //check if user contain superamdin privileges and ignore it
                        $query              =   "SELECT meta_value FROM "  .   $wpdb->sitemeta . "
                                                                WHERE   `meta_key`  = 'site_admins'";
                        $site_admins    =   $wpdb->get_var( $query );
                        $meta_value =   maybe_unserialize( $site_admins );
                        if ( is_array ( $meta_value )   &&  count( $meta_value ) > 0  )
                            {
                                $query              =   $wpdb->prepare("SELECT user_login FROM "  .   $wpdb->users . "
                                                                WHERE   `ID`    =   %d", $this->user_id);
                                $user_login    =   $wpdb->get_var( $query );
                                
                                if ( in_array ( $user_login, $meta_value ) )
                                    return FALSE;
                                
                            }
                        
                        
                        $woogc_options  =   $this->get_options();
                                                
                        if( ! isset($woogc_options['login_only_specific_roles']) ||  ! is_array($woogc_options['login_only_specific_roles']))
                            $woogc_options['login_only_specific_roles'] =   array();
                            
                        $disabled   =   array ( 
                                                'administrator',
                                                'shop_manager'
                                                );
                                                
                        if  (  count ( $woogc_options['login_only_specific_roles'] ) > 0 )
                            {
                                foreach  ( $woogc_options['login_only_specific_roles'] as $key  =>  $role )
                                    {
                                        if ( array_search ( $role, $disabled ) !== FALSE )
                                            unset ( $woogc_options['login_only_specific_roles'][$key] );
                                    }
                            }
                        
                        //check if this functionality is turned on
                        $query              =   $wpdb->prepare("SELECT meta_value FROM "  .   $wpdb->usermeta . "
                                                                WHERE   `user_id`    =   %d AND `meta_key`  LIKE '%%" . $wpdb->prefix . "capabilities'", $this->user_id);
                        $user_blogs_roles    =   $wpdb->get_var( $query );
                        
                        if ( empty  ( $user_blogs_roles  ) )
                            return TRUE;
                        
                        $user_roles  =   array();
                        $meta_value =   maybe_unserialize( $user_blogs_roles );
                                
                        if ( ! is_array($meta_value)    ||  count($meta_value) < 1)
                            return TRUE;
                        
                        foreach($meta_value as  $role   =>  $value)
                            {
                                $user_roles[ $role ]   =    TRUE;
                            }
                        
                        $user_roles =   array_keys($user_roles);
                        
                        foreach ( $user_roles   as  $key => $user_role)
                            {
                                if ( in_array( $user_role, $woogc_options['login_only_specific_roles']) )
                                    unset ( $user_roles[$key] );
                            }
                        
                        //if there is any unaccepted roles, return false
                        if ( count( $user_roles ) > 0 ) 
                            return FALSE;
                            
                        return TRUE;
                        
                    }
                    
                
                private function get_options()
                    {
                        
                        global $wpdb;
                        
                        //check if this functionality is turned on
                        $query              =   "SELECT meta_value FROM "  .   $wpdb->sitemeta . " WHERE   `meta_key`  =   'woogc_options'";
                        $woogc_options      =   $wpdb->get_var( $query );
                        
                        $woogc_options      =   maybe_unserialize( $woogc_options );
                    
                        return $woogc_options;
                    
                    }    
                
                private function validate_sso_data($woogc_sso_data)
                    {
                            
                        //check expiration    
                        if( time() > $this->session_data['time'] + WOOGC_SSO_EXPIRE )
                            return FALSE;
                        
                        global $blog_id;
                            
                        //check if correct site    
                        if( $blog_id    !=  $this->session_data['site'])
                            return FALSE;
                        
                        //check ip
                        if( $_SERVER['REMOTE_ADDR']    !=  $this->session_data['ip'])
                            return FALSE;
                        
                        //check ua
                        if( $_SERVER['HTTP_USER_AGENT']    !=  $this->session_data['ua'])
                            return FALSE;
                            
                        return TRUE;   
                        
                    }
                
                
                
                private function verify_cookie_session()
                    {
                        
                        require( ABSPATH . WPINC . '/class-wp-session-tokens.php' );
                        require( ABSPATH . WPINC . '/formatting.php' );
                        require( ABSPATH . WPINC . '/user.php' );
                        require( ABSPATH . WPINC . '/meta.php' );
                        
                        $manager        = WP_Session_Tokens::get_instance( $this->user_data->ID );
                        $this->session_data   =   $manager->get( $this->query_wp_data['cookie_token'] );
                        
                        if(empty($this->session_data))
                            return FALSE;
                            
                        return TRUE;
                        
                    }
                
                
                private function filter_IP()
                    {
                        
                        if ( empty( $_SERVER['REMOTE_ADDR'] ) )
                            return FALSE;
                        
                        
                        $session['ip'] = $_SERVER['REMOTE_ADDR'];
                                
                        if( $session['ip'] !=   $this->session_data['ip'])
                            return FALSE;
                            
                        return TRUE;   

                    }
                
                private function wp_set_domain_cookies()
                    {
                        include_once (ABSPATH . 'wp-includes/pluggable.php');
                        require_once( ABSPATH . WPINC . '/class-wp-session-tokens.php' );
                        require_once( ABSPATH . WPINC . '/formatting.php' );
                        require_once( ABSPATH . WPINC . '/user.php' );
                        require_once( ABSPATH . WPINC . '/meta.php' );
                        require_once( ABSPATH . WPINC . '/class-wp-user.php' );
                        require_once( ABSPATH . WPINC . '/capabilities.php' );
                        require_once( ABSPATH . WPINC . '/class-wp-roles.php' );
                        require_once( ABSPATH . WPINC . '/class-wp-role.php' );
                        
                        $woogc_sso_last_login   =   get_user_meta($this->user_id, 'woogc_sso_last_login', TRUE);
                                                                                             
                        $manager = WP_Session_Tokens::get_instance( $this->user_id );
                        $token   = $manager->create( $woogc_sso_last_login['expiration'] );
                        
                        $scheme = 'auth';
                        $auth_cookie = wp_generate_auth_cookie( $this->user_id, $woogc_sso_last_login['expiration'], $scheme, $token );
                        
                        //set cookies non-ssl
                        //??  SameSite => Secure !!
                        $this->set_cookie( AUTH_COOKIE, $auth_cookie, $woogc_sso_last_login['expire'], ADMIN_COOKIE_PATH, COOKIE_DOMAIN, $this->secure_cookie, TRUE);
                        
                        $scheme = 'secure_auth';
                        $secure_auth_cookie = wp_generate_auth_cookie( $this->user_id, $woogc_sso_last_login['expiration'], $scheme, $token );
                        
                        //set cookies ssl
                        $this->set_cookie( SECURE_AUTH_COOKIE, $secure_auth_cookie, $woogc_sso_last_login['expire'], ADMIN_COOKIE_PATH, COOKIE_DOMAIN, $this->secure_cookie, TRUE);
                        
                        $secure_logged_in_cookie    =   FALSE;
                        
                        $logged_in_cookie = wp_generate_auth_cookie( $this->user_id, $woogc_sso_last_login['expiration'], 'logged_in', $token );
                        
                        $this->set_cookie( LOGGED_IN_COOKIE, $logged_in_cookie, $woogc_sso_last_login['expire'], COOKIEPATH, COOKIE_DOMAIN, $this->secure_cookie, TRUE);
                        if ( COOKIEPATH != SITECOOKIEPATH )
                            {
                                $this->set_cookie( LOGGED_IN_COOKIE, $logged_in_cookie, $woogc_sso_last_login['expire'], SITECOOKIEPATH, COOKIE_DOMAIN, $this->secure_cookie, TRUE);
                            }
                     
                        
                    }
                    
                private function wc_set_domain_cookies()
                    {                       
                        $this->set_cookie( 'wp_woocommerce_session_' . COOKIEHASH, $this->query_wc, $this->wc_cookie_max_age, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, $this->secure_cookie, FALSE);                 
                    }
                    
  
                private function set_cookie(    $CookieName, $CookieValue = '', $CookieMaxAge = 0, $CookiePath = '', $CookieDomain = '', $CookieSecure = false, $CookieHTTPOnly = false, $CookieSameSite = 'none') 
                    {
                        header( 'Set-Cookie: ' . rawurlencode( $CookieName ) . '=' . rawurlencode( $CookieValue )
                                            . ( empty($CookieMaxAge )   ? '' : '; Max-Age=' . $CookieMaxAge)
                                            . ( empty($CookiePath )     ? '' : '; path=' . $CookiePath)
                                            . ( empty($CookieDomain )   ? '' : '; domain=' . $CookieDomain)
                                            . ( !$CookieSecure          ? '' : '; secure')
                                            . ( !$CookieHTTPOnly        ? '' : '; HttpOnly')
                                            . ( empty($CookieSameSite)  ? '' : '; SameSite=' . $CookieSameSite )
                                            
                                            ,false);
                    }
                
                
                public function session_token_manager()
                    {
                        include_once('../include/class.woogc.wp-user-meta-session-tokens.php');
                        
                        return 'WooGC_WP_User_Meta_Session_Tokens';   
                        
                    }
                
                    
                public function _output_pixel()
                    {
                        
                        header('Content-Type: image/png');
                        
                        echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=');
                    
                    }
       
            }
            
        new WooGC_Sync();

     
?>