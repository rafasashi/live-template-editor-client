<?php $ltple = LTPLE_Client::instance();  ?>
<!DOCTYPE>
<html>

    <head>
	
		<style>
		
			img {
				
				width:100%;
				height:auto;
			}
		
		</style>	
			
	</head>
    
	<body style="padding:0;margin:0;display:inline-block;width:100%;text-align:center;">
		
		<?php 
		
		if ( have_posts() ) : while ( have_posts() ) : the_post();
			
			$post_id = get_the_ID();
			
			if( $image = $ltple->layer->get_preview_image_url($post_id,'full')){

				echo '<img src="' . $image . '">';
			}
			elseif( $image = get_the_post_thumbnail($post_id,'full')){
				
				echo $image;
			}
			else{
				
				echo '<img src="' . $ltple->layer->get_thumbnail_url($post_id,'full') . '">';
			}

		endwhile; endif;
	?>
	
    </body>
</html>