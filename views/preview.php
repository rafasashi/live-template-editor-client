<!DOCTYPE>
<html>

    <head></head>
    
	<body style="padding:0;margin:0;display:inline-block;">
		
		<?php 
		
		if ( have_posts() ) : while ( have_posts() ) : the_post();
			
			echo get_the_post_thumbnail( get_the_ID(), 'recentprojects-thumb');

		endwhile; endif;
	?>
	
    </body>
</html>