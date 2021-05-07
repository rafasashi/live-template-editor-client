<?php
	
	$ltple = LTPLE_Client::instance();
	
	echo get_header();

		if( !empty($_GET['output']) && $_GET['output'] == 'widget' ){
			
			echo do_shortcode( '[subscription-plan id="' . $post->ID . '" widget="true"]' );
		}
		else{
		
			echo '<h2 id="plan_title" style="margin-bottom: 0;padding: 30px 30px;font-weight: bold;background: rgba(158, 158, 158, 0.24);box-shadow:inset 0 -1px 10px -6px rgba(0,0,0,0.75);">' . $post->post_title . '</h2>';
							
			if( $plan_thumb = get_the_post_thumbnail_url($post->ID) ){
				
				echo'<div id="plan_thumb">';
					
					echo '<img src="'.$plan_thumb.'" style="width:100%;">';
				
				echo'</div>';
			}
			else{

				echo'<div id="plan_thumb" style="background-size:cover;background-repeat: no-repeat;background-position: center center;width:100%;height:200px;background-image:url(\''.$ltple->assets_url . 'images/plan_background.jpg'.'\');"></div>';
			}
			echo '<div class="container">';
			
				echo'<div id="plan_description" class="entry-content panel-body">';
				
					echo apply_filters( 'the_content', $post->post_content);
				
				echo '</div>';
				
				echo do_shortcode( '[subscription-plan id="' . $post->ID . '" thumb="true"]' );
				
			echo '</div>';
		}

	echo get_footer();