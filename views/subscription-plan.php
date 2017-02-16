<?php

	echo get_header();
		
		echo '<div class="container">';
		
			//echo '<div class="row">';
		
				echo do_shortcode( '[subscription-plan id="' . $post->ID . '"]' );
			
			//echo '</div>';
		
		echo '</div>';
	

	echo get_footer();