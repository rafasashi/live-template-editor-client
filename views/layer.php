<?php 

	$ltple = LTPLE_Client::instance();
	
	$layer = LTPLE_Editor::instance()->get_layer();
	
	echo $ltple->layer->render_output($layer);