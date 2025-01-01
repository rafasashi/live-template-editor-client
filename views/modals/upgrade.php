<?php
	
	$ltple = LTPLE_Client::instance();
	
    $layer_type = $ltple->gallery->get_current_type();
    
    if( !empty($layer_type->ranges) ){
        
        foreach( $layer_type->ranges as $range ){
            
            $checkout_modal = $ltple->checkout->get_modal($range['slug']);
            
            echo $checkout_modal['content'];
        }
    }