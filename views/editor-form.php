<?php 

$ltple = LTPLE_Client::instance();

get_header();

echo'<div id="' . $ltple->layer->layerForm . '-form" class="editor-form" style="height:calc( 100vh - 50px );">';

	if( file_exists( $ltple->views . '/forms/' . $ltple->layer->layerOutput  . '-' . $ltple->layer->layerForm . '.php' ) ){
		
		include( $ltple->views . '/forms/' . $ltple->layer->layerOutput  . '-' . $ltple->layer->layerForm . '.php' );
	}
	else{
		
		echo 'This form doesn\'t exist...';
	}
	
echo'</div>';

get_footer();