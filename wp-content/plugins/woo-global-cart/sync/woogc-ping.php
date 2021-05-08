<!DOCTYPE html>
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
            
        }
    
    var cookie_sites                =   read_cookie('wooGC_sites');
    var WooGC_Sites                 =   cookie_sites.split('&');
    var WooGC_Bouncer_Return        =   read_cookie('wooGC_bounced_return');
    var WooGC_Bouncer_Path          =   read_cookie('wooGC_bouncer_path');
    
    function getRandomInt(min, max) 
        {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }
        
        
    function load_domains( domain_hash )
        {
            var WooGC_Url   =   '';
            
            if(typeof(domain_hash) !== "undefined")
                {
                    var current_index =     parseInt(domain_hash) +   1;
                    
                    //returning back as completed
                    if(typeof WooGC_Sites[current_index] === 'undefined')
                        {
                            window.open( WooGC_Bouncer_Return ,"_self");
                            return;    
                        }
                                            
                }
                else
                {
                    var current_index =     0;
                }
                
            var site_url      =   WooGC_Sites[current_index];           
            var url_protocol    =   location.protocol   ==  'https:' ?  1   :   0;
            var url = [ location.host, location.pathname].join('');
            
            WooGC_Url           =   site_url + WooGC_Bouncer_Path + "/woogc-pong.php?woogc_hash="+ current_index +"&rand=" + getRandomInt(1,9999999) + "&usessl=" +  url_protocol + "&bounce=" + url;    
            
            setTimeout( function() {
                window.open( WooGC_Url ,"_self");
            }, 200)
            
        }
        
        
    if(window.location.hash !== ''  &&  window.location.hash.indexOf('woogc_hash') >=  0) 
        {
           
            var hash = location.hash.substr(1);
            var vars = hash.split('&');
            for (var i = 0; i < vars.length; i++) 
                {
                    var pair = vars[i].split('=');
                    if (decodeURIComponent(pair[0]) == 'woogc_hash') 
                        {
                            load_domains( decodeURIComponent(pair[1]) );
                            break;
                        }
                }   
        }
        else
        load_domains( );
        
    
 </script>

    </head>
    
    <body>
        <p>Please Wait while Synchronizing...</p>
       
        <div id="loader-wrapper"><div id="loader"></div></div>
    </body>
</html>