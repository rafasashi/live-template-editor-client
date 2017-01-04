<!DOCTYPE>
<html>

    <head></head>
    
	<body style="padding:0;margin:0;display:inline-block;">
		
		<?php 
		
		if ( have_posts() ) : while ( have_posts() ) : the_post();

			the_content();

		endwhile; endif;
	?>
	
    </body>
</html>