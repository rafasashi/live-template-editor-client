<?php 

	$ltple = LTPLE_Client::instance();

	if( file_exists( $ltple->views . '/layers/' . $ltple->layer->layerOutput  . '.php' ) ){
		
		include_once( $ltple->views . '/layers/' . $ltple->layer->layerOutput  . '.php' );
	}
	else{
		
		do_action( 'ltple_' . $ltple->layer->layerOutput . '_layer', array($layer) );
	}
	
	do_action( 'ltple_layer_loaded', array($layer) );