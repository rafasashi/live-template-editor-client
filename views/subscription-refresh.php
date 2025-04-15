<?php

$ltple = LTPLE_Client::instance();

if( !$ltple->inWidget ){

    get_header();
     
        include('navbar.php');

        $iframe_url = add_query_arg( array(
        
            'output' => 'widget',
            
        ),$ltple->urls->current);
        
        echo'<iframe class="full-height" data-src="' . $iframe_url . '" style="width:100%;border:0;min-height:calc(100vh - 130px);overflow:hidden;"></iframe>';

    get_footer();
}
else{
    
   $ltple->users->remote_update_period($ltple->user->ID);

    // redirect url
    
    $refresh_url = add_query_arg( array(
        
        'period_refreshed' => 'true',
        
    ),$ltple->urls->current);
    
    wp_redirect($refresh_url);
    exit;
}
