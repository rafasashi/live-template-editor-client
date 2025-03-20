<?php 

$ltple = LTPLE_Client::instance();

$layer = LTPLE_Editor::instance()->get_layer();

get_header();

echo'<div id="' . $layer->form . '-form" class="editor-form" style="height:calc( 100vh - 50px );">';

	if( file_exists( $ltple->views . '/forms/' . $layer->output  . '-' . $layer->form . '.php' ) ){
		
		include( $ltple->views . '/forms/' . $layer->output  . '-' . $layer->form . '.php' );
	}
	else{
		
		echo 'This form doesn\'t exist...';
	}
	
echo'</div>';

get_footer();