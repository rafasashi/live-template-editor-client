<?php 
	
	if( !isset($_GET['preview']) ){
		
		if( $attachment_url = wp_get_attachment_url( $ltple->layer->layerImageTpl->ID ) ){
			
			// CORS Allow from any origin
			
			if (isset($_SERVER['HTTP_ORIGIN'])) {
				
				// Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
				// you want to allow, and if so:
				
				header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
				header('Access-Control-Allow-Credentials: true');
				header('Access-Control-Max-Age: 86400');    // cache for 1 day
			}

			header('Content-type: ' . $ltple->layer->layerImageTpl->post_mime_type);
			readfile($attachment_url);		
		}
		
		flush();
		exit;
		die;		
	}
	else{
		
		$img_url = $ltple->layer->get_thumbnail_url($ltple->layer->layerImageTpl);
		
		$layer = '<img src="'.$img_url.'" />';
	}