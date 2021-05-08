<?php

ob_start();

?><!DOCTYPE html>
<html>
<head>
 
<style>
p{font-family: arial;
    font-size: 20px;
    text-align: center;
    font-weight: bold;
    padding-top: 50px;
}
#loader-wrapper {
    position: fixed;
    top: 0px;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
}
#loader {
    display: block;
    position: relative;
    left: 50%;
    top: 50%;
    width: 150px;
    height: 150px;
    margin: -150px 0 0 -75px;
    border-radius: 50%;
    border: 4px solid transparent;
    border-top-color: #3498db;

    -webkit-animation: spin 2s linear infinite; /* Chrome, Opera 15+, Safari 5+ */
          animation: spin 2s linear infinite; /* Chrome, Firefox 16+, IE 10+, Opera */
}

    #loader:before {
        content: "";
        position: absolute;
        top: 5px;
        left: 5px;
        right: 5px;
        bottom: 5px;
        border-radius: 50%;
        border: 4px solid transparent;
        border-top-color: #e74c3c;

        -webkit-animation: spin 3s linear infinite; /* Chrome, Opera 15+, Safari 5+ */
          animation: spin 3s linear infinite; /* Chrome, Firefox 16+, IE 10+, Opera */
    }

    #loader:after {
        content: "";
        position: absolute;
        top: 15px;
        left: 15px;
        right: 15px;
        bottom: 15px;
        border-radius: 50%;
        border: 4px solid transparent;
        border-top-color: #f9c922;

        -webkit-animation: spin 1.5s linear infinite; /* Chrome, Opera 15+, Safari 5+ */
          animation: spin 1.5s linear infinite; /* Chrome, Firefox 16+, IE 10+, Opera */
    }

    @-webkit-keyframes spin {
        0%   { 
            -webkit-transform: rotate(0deg);  /* Chrome, Opera 15+, Safari 3.1+ */
            -ms-transform: rotate(0deg);  /* IE 9 */
            transform: rotate(0deg);  /* Firefox 16+, IE 10+, Opera */
        }
        100% {
            -webkit-transform: rotate(360deg);  /* Chrome, Opera 15+, Safari 3.1+ */
            -ms-transform: rotate(360deg);  /* IE 9 */
            transform: rotate(360deg);  /* Firefox 16+, IE 10+, Opera */
        }
    }
    @keyframes spin {
        0%   { 
            -webkit-transform: rotate(0deg);  /* Chrome, Opera 15+, Safari 3.1+ */
            -ms-transform: rotate(0deg);  /* IE 9 */
            transform: rotate(0deg);  /* Firefox 16+, IE 10+, Opera */
        }
        100% {
            -webkit-transform: rotate(360deg);  /* Chrome, Opera 15+, Safari 3.1+ */
            -ms-transform: rotate(360deg);  /* IE 9 */
            transform: rotate(360deg);  /* Firefox 16+, IE 10+, Opera */
        }
    }
</style>
 <script type="text/javascript">
            
    
    var canReturn        =   <?php  
    $wooGC_sites    =   isset ( $_COOKIE['wooGC_sites'] ) ?     $_COOKIE['wooGC_sites'] :   '';
    $SSO_Sites      =   array ( );
    
    if ( empty ( $wooGC_sites ) )
        echo 'true';
        else
        {
            $SSO_Sites  =   explode ( "&", $_COOKIE['wooGC_sites'] );
                        
            //reindex
            $SSO_Sites  =   array_values ( array_filter ( $SSO_Sites ) );
            
            if ( count ( $SSO_Sites ) < 1 )
                echo 'true';
                else
                echo 'false';
        }
    ?>;
    
    if ( canReturn )
        {
            var WooGC_Bouncer_Return        =   read_cookie('WooGC_return_url');    
            
            if ( WooGC_Bouncer_Return !== false )
                {
                    WooGC_Bouncer_Return    =   decodeURIComponent ( WooGC_Bouncer_Return );
                        
                    window.open( WooGC_Bouncer_Return ,"_self");
                }
        }
        else
        {
            <?php
                
                $sso_hash           =   isset ( $_GET['sso_hash'] ) ?   preg_replace("/[^A-Za-z0-9\.]/", '', $_GET['sso_hash'])  :   '';
                $wc                 =   isset ( $_GET['wc'] ) ?         preg_replace("/[^A-Za-z0-9\.\|]/", '', $_GET['wc'])  :   '';

                if (  ( $sso_hash !=  '' ||   $wc !=  ''  ) &&    count  ( $SSO_Sites ) > 0 )
                    {
                        
                        $protocol   =   isset( $_SERVER['HTTPS'] )  ?   'https' :   'http';
                        
                        $current_url    =   $protocol . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                        $current_url_parsed =   parse_url ( $current_url );
                        $current_url_path   =   explode ( "/",  $current_url_parsed['path'] );
                        unset ( $current_url_path[ count ( $current_url_path ) - 1 ] );
                        
                        $return_url     =   "//" .  $current_url_parsed['host'] . $current_url_parsed['path'];
                        
                        $url_query  =   '';
                        
                        if ( $sso_hash != '' )
                            {
                                $sso_hash_parts =   explode ( ".", $sso_hash );
                                $current_ssh    =   $sso_hash_parts[0];
                                if ( $current_ssh == '0' )
                                    $current_ssh    =   '';
                                
                                $to_forward_hash    =   $sso_hash_parts;
                                
                                unset ( $to_forward_hash[0] );
                                
                                if ( count ( $to_forward_hash ) > 0 )
                                    $sso_hash   =   implode(".", $to_forward_hash );
                                    else
                                    $sso_hash   =   '';
                                    
                                $url_query  .=  '&wp=' . $current_ssh . '&sso_hash=' . $sso_hash;
                            }
                            
                        if ( $wc != '' )
                            {
                                $url_query  .=  '&wc=' . $wc;
                            }
                        
                        //add wooGC session data
                        $wooGC_Session_ID   =   isset ( $_COOKIE['wooGC_Session_ID'] ) ?    preg_replace("/[^A-Za-z0-9\.\|]/", '', $_COOKIE['wooGC_Session_ID'])  :   '';
                        $url_query  .=  '&wooGC_Session_ID=' . urldecode( $wooGC_Session_ID );
                            
                        $url_query  .=  '&return_url=' . urldecode( $return_url );
                        $url_query  =   ltrim( $url_query, "&" );
                        
                        $woogc_sync_url =   '';
                        $woogc_sync_url =   $protocol   .   ':' .  $SSO_Sites[ 0 ] . implode('/', $current_url_path ) . '/woogc-sync.php?' . $url_query ;
                        
                        //remove processed site
                        unset ( $SSO_Sites[ 0 ] );
                        
                        set_cookie ("wooGC_sites", implode("&", $SSO_Sites ), '', '/', '', '', '', 'Lax');
                                                         
                        ?>
                            var WooGC_Url       =   '<?php  echo $woogc_sync_url; ?>';
                            
                            setTimeout( function() {
                                window.open( WooGC_Url ,"_self");
                            }, 100)  
                        <?php
                    }
                
                
            ?>
               
        }
          
    function read_cookie( cookie_name )    
        {
            var CookiesPairs = document.cookie.split(';');
            for(var i = 0; i < CookiesPairs.length; i++) 
                {
                    var name    = CookiesPairs[i].substring(0, CookiesPairs[i].indexOf('='));
                    var value   = CookiesPairs[i].substring(CookiesPairs[i].indexOf('=')+1);
                    
                    name        =   name.trim();
                    value       =   value.trim();
                        
                    if(name == cookie_name)
                        {
                            return value;
                        }
                }   
            
            return false;
        }
    
 </script>

    </head>
    
    <body>
        <p>Please Wait while Synchronizing...</p>
       
        <div id="loader-wrapper"><div id="loader"></div></div>
    </body>
</html><?php


    function set_cookie(    $CookieName, $CookieValue = '', $CookieMaxAge = 0, $CookiePath = '', $CookieDomain = '', $CookieSecure = false, $CookieHTTPOnly = false, $CookieSameSite = 'none') 
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

        
    ob_end_flush();
        
?>