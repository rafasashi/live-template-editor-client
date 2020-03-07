<?php

	echo get_header();

		echo '<div class="container">';
		
			//echo '<div class="row">';
				
				if( !empty($_GET['output']) && $_GET['output'] == 'widget' ){
					
					echo do_shortcode( '[subscription-plan id="' . $post->ID . '" widget="true"]' );
				}
				else{
					
					echo do_shortcode( '[subscription-plan id="' . $post->ID . '" thumb="true"]' );
				}
			//echo '</div>';
		
		echo '</div>';
	

	echo get_footer();