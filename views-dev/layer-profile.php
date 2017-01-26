<?php 
	
	if( $displayedUser = get_user_by( 'ID', intval($_GET['pr'])) ){

		$template_id = floatval( get_user_meta( $displayedUser->ID, 'ltple_profile_template', true ) );
		 
		if( $template_id > 0 ){
			
			$post = get_post( $template_id );
			
			include('layer.php');
		}
		elseif( $template_id == -2 ){
			
			// get profile_html
			
			$profile_html = get_user_meta( $displayedUser->ID, 'ltple_profile_html', true );
			
			// get profile_title
		
			$profile_title = get_user_meta( $displayedUser->ID, 'ltple_profile_title', true );
			
			// get profile_css
			
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
			$post->post_title 	= $profile_title;
			$post->post_content = $profile_content;
			
			include('layer.php');
		}
	}
	else{
		
		echo 'This profile doesn\'t exists...';
		exit;
	}