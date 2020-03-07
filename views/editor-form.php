<?php 

	$ltple = LTPLE_Client::instance();

	if( file_exists( $ltple->views . '/forms/' . $ltple->layer->layerOutput  . '-' . $ltple->layer->layerForm . '.php' ) ){
		
		include( $ltple->views . '/forms/' . $ltple->layer->layerOutput  . '-' . $ltple->layer->layerForm . '.php' );
	}
	else{
		
		echo 'This form doesn\'t exist...';
	}	