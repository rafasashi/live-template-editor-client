<?php
	
	$ltple = LTPLE_Client::instance();
	
	include_once( $ltple->views . '/profile/header.php' );
	
	if( $ltple->profile->is_public()|| $ltple->profile->is_self() ){
		
		if( !$ltple->inWidget ){
			
			if( $ltple->profile->tab == 'about' ){
				
				echo $ltple->profile->get_about_content();
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
	
	include_once( $ltple->views . '/profile/footer.php' );