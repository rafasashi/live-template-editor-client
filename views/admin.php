<?php ob_clean(); 

	// add head
	/*
	remove_action( 'wp_head', '_wp_render_title_tag', 1 );
	
	add_action( 'wp_head', array( $this, 'get_header') );
	
	// add menu
	
	add_filter( 'wp_nav_menu', array( $this, 'get_menu' ), 10, 2);
	*/
	
?>
<!DOCTYPE html>
<html>

	<head>
	
		<?php 
		
			//wp_head(); 
		
			
		?>
		
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

	</head>
	<body style="background:#fff !important;">
	
		<?php

			global $post;
			
			if( isset($post->post_type) ){

				echo apply_filters( 'the_content', $post->post_content );
			}
			
			//wp_footer(); 
		 
		?>
	
	</body>
</html>

<?php exit; ?>