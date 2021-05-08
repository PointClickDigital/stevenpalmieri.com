<?php
   
    $_woogc_bounce_back  =   isset( $_GET['bounce'] )       ?  $_GET['bounce']      :   '';
    $_woogc_hash         =   isset( $_GET['woogc_hash'] )   ?  $_GET['woogc_hash']  :   '';
    $_woogc_use_ssl      =   isset( $_GET['usessl'] )       ?  $_GET['usessl']      :   '';

    if( $_woogc_bounce_back == ''    ||  $_woogc_hash    ==  '' ||  $_woogc_use_ssl ==  '' )
        die();
    
    $protocol           =   $_woogc_use_ssl >   0   ?   'https' :   'http';
    $_woogc_bounce_back =   $protocol   .'://'  .   $_woogc_bounce_back;
        
    setcookie("_woogc_bounced", rand(1,9999), 0, '/');
    header("Location: " . $_woogc_bounce_back ."?rand=" . rand(1,9999). "#woogc_hash=" . $_woogc_hash);

?>