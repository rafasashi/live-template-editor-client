<?php
	
$ltple = LTPLE_Client::instance();

	include_once( $ltple->views . '/profile/header.php' );
	
	echo '<div id="ltple-content" style="min-height:100vh;">';
	
	if( $ltple->profile->is_public() || $ltple->profile->is_self() ){
		
		if( !$ltple->inWidget ){
			
			if( $ltple->profile->tab == 'about' ){
				
				echo $ltple->profile->render_about_page();
			}
			else{
				
				echo $ltple->profile->render_page_content();
			}
			
			/*
			if( !$ltple->user->loggedin ){

				// login modal
				
				include( $ltple->views  . '/modals/login.php');
			}
			*/
		}
	}
	else{
		
		echo '<div class="alert alert-warning" style="padding-top:50px;">';
		
			echo 'This profile is not accessible...';
			
		echo '</div>';
	}
	
	echo '</div>';
	
	include_once( $ltple->views . '/profile/footer.php' );