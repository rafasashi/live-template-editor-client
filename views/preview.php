<?php $ltple = LTPLE_Client::instance();  ?>
<!DOCTYPE>
<html>

    <head></head>
    
	<body style="padding:0;margin:0;display:inline-block;width:100%;text-align:center;">
		
		<?php 
		
		if ( have_posts() ) : while ( have_posts() ) : the_post();
			
			if( $image = get_the_post_thumbnail( get_the_ID(), 'recentprojects-thumb') ){
				
				echo $image;
			}
			else{
				
				echo '<img src="' . $ltple->layer->get_thumbnail_url($ltple->user->layer) . '">';
			}

		endwhile; endif;
	?>
	
    </body>
</html>