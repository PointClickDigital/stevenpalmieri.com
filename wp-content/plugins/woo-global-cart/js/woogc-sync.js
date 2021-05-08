            
        
    var WooGC_Sync  = {
        
        foundLoggedIn   :   false,
        foundLoggedOut  :   false,
        
        wc_cookie_name  :   '',
        wc_cookie       :   '',
        
        queryArgs       :   '',

        init    :   function() {
            
            this.reset();
            
            this.login_logout_check();
            this.wc_cookie_seek();
            
            if( this.device_require_bounce() )
                {
                    this.bounce_prepare_query_args();
                    this.start_bounce();
                }
                else
                {
                    this.prepare_query_args();
                    this.doSync();
                }
            
        },
        
        reset  : function () {
            
            this.foundLoggedIn  =   false;
            this.foundLoggedOut =   false;    
            
            this.wc_cookie_name =   '';
            this.wc_cookie      =   '';
            
            this.queryArgs      =   '';
            
        },
        
        login_logout_check  :   function() {
            
            if (typeof WooGC_Action !== 'undefined') 
                {
                    if(WooGC_Action ==  'login')
                        this.foundLoggedIn  =   true;
                    if(WooGC_Action ==  'logout')
                        this.foundLoggedOut  =   true;
                }  
            
        },
        
        wc_cookie_seek :   function( ) {
            
            //search for cookie
            var CookiesPairs = document.cookie.split(';');
            for(var i = 0; i < CookiesPairs.length; i++) 
                {
                    var name    = CookiesPairs[i].substring(0, CookiesPairs[i].indexOf('='));
                    var value   = CookiesPairs[i].substring(CookiesPairs[i].indexOf('=')+1);
                    
                    name        =   name.trim();
                    value       =   value.trim();
                        
                    if(name.indexOf("wp_woocommerce_session_") > -1)
                        {
                            this.wc_cookie_name =   name;
                            this.wc_cookie      =   value;
                        }
                }
        
        },
        
        
        prepare_query_args  :   function() {
            
            if(this.wc_cookie  ==  '')
                return;
                 
            if(this.wc_cookie   !=  '')
                {
                    if(this.queryArgs   !=  ''  &&  this.queryArgs.slice(-1) !=  '?')
                        this.queryArgs  +=  '&';
                    
                    this.queryArgs  +=  'wc=' + this.wc_cookie;
                    
                }
            
        },
        
        bounce_prepare_query_args  :   function() {
            
            var wooGC_Session_ID =   this.read_cookie('wooGC_Session_ID');     
            if(this.wc_cookie   !=  ''  &&  wooGC_Session_ID    ==  false )
                {
                    if(this.queryArgs   !=  ''  &&  this.queryArgs.slice(-1) !=  '?')
                        this.queryArgs  +=  '&';
                    
                    this.queryArgs  +=  'wc=' + this.wc_cookie;
                }
                
            if(this.wc_cookie   ==  ''  &&  wooGC_Session_ID    !=  false )
                {
                    var parsedUrl = new URL( window.location.href );
                    document.cookie = 'wooGC_Session_ID=;path=/; domain=.' + parsedUrl.hostname + ';expires=Thu, 01 Jan 1970 00:00:01 GMT;';
                }
        },
        
        doSync   :   function() {
            
            var woogc_sync_wrapper  =   document.getElementById('woogc_sync_wrapper');
            
            //clear the existing
            woogc_sync_wrapper.innerHTML    =   '';
            
            if (typeof WooGC_Sites === 'undefined')
                return;
                
            for ( var key in WooGC_Sites ) 
                {
                    var   site_url    =   WooGC_Sites[key] + WooGC_Sync_Url + '/woogc-sync.php';
                    
                    var url_query   =   this.queryArgs;
 
                    if(this.foundLoggedIn   === true    ||  this.foundLoggedOut === true)
                        {
                                
                            if(this.foundLoggedIn   === true)
                                {
                                    if(url_query.slice(-1) !=  '?')
                                        url_query  +=  '&';
                                        
                                    url_query   +=  'wp=' + WooGC_SSO[key];
                                    
                                }
                            if(this.foundLoggedOut === true)
                                {
                                    
                                    if(url_query.slice(-1) !=  '?')
                                        url_query  +=  '&';
                                        
                                    url_query   +=  'wp=';
                                    
                                }
                        }
                    
                    url_query = url_query.replace(/^&/, '');
                    
                    if (url_query   ==  '')
                        continue;
                    
                    woogc_sync_wrapper.innerHTML = woogc_sync_wrapper.innerHTML + '<img src="' +  site_url + '?' + url_query +'" alt="sync" />';
                }
          
        
        },
        
        
        device_require_bounce : function() {
            
            //return true;
            
            var     require_bounce = /iPhone|iPod|Safari/.test(navigator.userAgent) && ! /Chrome/.test(navigator.userAgent) && !window.MSStream;
            return  require_bounce;   
            
        },
        
        bounced :   function() {
            
            var required_cookie_val =   this.read_cookie('_woogc_bounced');
            if (required_cookie_val !==  false)
                return true;
                
            return false;
              
        },
        
        start_bounce    :   function () {
            
            if ( this.foundLoggedIn   === true && typeof( WooGC_SSO ) !== "undefined"  && WooGC_SSO.length > 0 )
                {
                    this.queryArgs    +=   '&sso_hash=' + WooGC_SSO.join('.');
                    
                }
            
            if ( this.foundLoggedOut   === true  )
                {
                    var wooGC_bounced_loggeout =   this.read_cookie('wooGC_bounced_loggeout');
                    
                    if ( wooGC_bounced_loggeout === false )
                        {
                            var sso_hash    =   '';
                            
                            for (i = 0; i < WooGC_Sites.length; i++) {
                                if  ( sso_hash != '' )
                                    sso_hash += '.';
                                    
                                sso_hash    +=  '0';
                            }
                            
                            this.queryArgs    +=   '&sso_hash=' + sso_hash;
                        }
                }
 
            
            if ( this.queryArgs   ==  '' )
                return;
                
            
            var All_Sites   =   '';    
            for (var key in WooGC_Sites) 
                {
                    
                    if( All_Sites != '' )
                        All_Sites   += '&';
                        
                    var   site_url    =   WooGC_Sites[key];
                    All_Sites   += site_url;
                }
            
            //set the cookie with the sites
            document.cookie = "wooGC_sites="+ All_Sites + ";path=/; SameSite=Lax";
                        
            //set the return url
            var Return_Url = window.location.href;
            
            var parser = document.createElement('a');
            parser.href = Return_Url; 
            
            var return_url_parts    =   Return_Url.split("?");
            
            if ( return_url_parts.length > 1 )
                {
                    var urlParams = new URLSearchParams( return_url_parts[1] );
                    urlParams.delete("loggedin");
                    urlParams.delete("login_hash");
                    
                    if ( urlParams.toString() != '' )
                        Return_Url  =   return_url_parts[0] + "?" + urlParams.toString();
                        else
                        Return_Url  =   return_url_parts[0];
                }
            
            document.cookie = "WooGC_return_url=" + Return_Url + "; path=/; SameSite=Lax";
            
            if ( this.foundLoggedOut   === true  )
                document.cookie = "wooGC_bounced_loggeout=true;path=/; SameSite=Lax";
                else
                document.cookie = 'wooGC_bounced_loggeout=;path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
            
            this.queryArgs = this.queryArgs.replace(/^&/, '');
            
            var url_query   =   this.queryArgs;
                
            if ( url_query.search("wc=") > -1 )
                {
                    var cookie_expire   =   60*60*24*10;
                    var date = new Date();
                    var expire_timestamp = date.setDate( date.getDate() + 10 );
                    document.cookie = "wooGC_Session_ID=" + this.generate_session_id( 12 ) + "|" + expire_timestamp +"; Max-age=" + cookie_expire + "; path=/; domain=." + parser.host + ";SameSite=Lax";
                    
                    //update cookie expiration
                    var cookie_value = this.read_cookie ( this.wc_cookie_name );
                    var cookie_secure   =   parser.protocol.toLowerCase() == 'https:' ?   '; Secure'  :   '';
                    document.cookie = this.wc_cookie_name + "=" + cookie_value + "; Max-age=" + cookie_expire + "; path=/; domain=." + parser.host + ";SameSite=Lax" + cookie_secure;
                }
                                        
            setTimeout( function() {
                window.open( '//' + parser.host + WooGC_Sync_Url + '/sync-hub.php?' + url_query ,"_self");
            }, 100)  

        },
        
        bounce_completed_check    :   function() {
            
            //check if bounce completed
            if(window.location.hash !== ''  &&  window.location.hash.indexOf('bounce-completed') >=  0)
                {
                   this.removeHash ();
                   
                   //clear cookies
                   document.cookie = 'wooGC_sites=;path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
                   document.cookie = 'wooGC_bounced_return=;path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
                   document.cookie = 'wooGC_bouncer_path=;path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
                   
                   document.cookie = "_woogc_bounced=" + this.getRandomInt(1,9999) + ";path=/";

                }    
            
        },
        
        removeHash :    function (){ 
            
            var scrollV, scrollH, loc = window.location;
            if ("pushState" in history)
                history.pushState("", document.title, loc.pathname + loc.search);
            else {
                // Prevent scrolling by storing the page's current scroll offset
                scrollV = document.body.scrollTop;
                scrollH = document.body.scrollLeft;

                loc.hash = "";

                // Restore the scroll offset, should be flicker free
                document.body.scrollTop = scrollV;
                document.body.scrollLeft = scrollH;
            }
        },
        
        getRandomInt    :   function (min, max) {
            
            return Math.floor(Math.random() * (max - min + 1)) + min;
        
        },
        
        read_cookie :   function( cookie_name ) {
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
            
        },
        
        generate_session_id : function ( length ) {
           var result           = '';
           var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
           var charactersLength = characters.length;
           for ( var i = 0; i < length; i++ ) 
               {
                  result += characters.charAt( Math.floor( Math.random() * charactersLength ) );
               }
           return result;
        }

  
    }
    
    
    WooGC_Sync.init();

    
    (function() {
        var origOpen = XMLHttpRequest.prototype.send;
        
        XMLHttpRequest.prototype.realSend = XMLHttpRequest.prototype.send; 
        var newSend = function(vData) { 
            
                        
            var XMLHttpRequestPostVars =   ( 0 in arguments ) ? arguments[0] : "";
            
            this.addEventListener('load', function( args ) {
                
                var found = false;
                
                if( typeof (this.responseURL) !== "undefined"   &&  this.responseURL.indexOf("?wc-ajax=") !== -1 )
                    found = true;
                    
                if ( found === false && WooGC_on_PostVars.length    >   0 ) 
                    {
                        for (var i = 0; i < WooGC_on_PostVars.length; i++) 
                            {
                                if( XMLHttpRequestPostVars.indexOf( WooGC_on_PostVars[i] ) !== -1 )
                                    {
                                        found = true;
                                        break;
                                    }
                            }   
                    }
                
                if ( found   === true )
                    WooGC_Sync.init();    
                
            });
            
            
            this.realSend(vData); 
        };
        XMLHttpRequest.prototype.send = newSend;
        
        
    })();

    
    
    
    
    