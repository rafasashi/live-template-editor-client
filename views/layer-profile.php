<?php 
	
	if( $displayedUser = get_user_by( 'ID', intval($_GET['pr'])) ){

		if( $post = get_post( intval( get_user_meta( $displayedUser->ID, 'ltple_profile_template', true ) )) ){
			
			include('layer.php');
		}
		else{
			
			$profile_html = get_user_meta( $displayedUser->ID, 'ltple_profile_html', true );
			
			if( !empty($profile_html) ){
			
				//get profile_css
			
				$profile_css = get_user_meta( $displayedUser->ID, 'ltple_profile_css', true );
				
				// get profile_content
				
				$profile_content = '';
				
				if( !empty($profile_css) ){
					
					$profile_content.= '<style>';
					
						$profile_content.= $profile_css;
					
					$profile_content.= '</style>';
				}
				
				$profile_content.= '<div style="min-width:1000px;width:100%;margin:250px auto auto auto;">';
					$profile_content.= '<div><div><div>';
					
						$profile_content.= $profile_html;
					
					$profile_content.= '</div></div></div>';
				$profile_content.= '</div>';
				
				$post = new stdClass();
				
				$post->ID 			= 0;
				$post->post_type 	= 'custom-layer';
				$post->post_content = $profile_content;
				
				include('layer.php');
			}
			else{
				
				echo 'Error loading profile...';
				exit;
			}
		}
	}
	else{
		
		echo 'This profile doesn\'t exists...';
		exit;
	}