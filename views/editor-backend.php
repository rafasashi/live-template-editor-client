<?php
echo '<!DOCTYPE>';
echo '<html>';

	echo '<head>';
	
		echo '<!-- Le HTML5 shim, for IE6-8 support of HTML elements -->';
		echo '<!--[if lt IE 9]>';
		echo '<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>';
		echo '<![endif]-->';		

		echo '<meta charset="UTF-8">';
		echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
		
		echo '<link rel="profile" href="http://gmpg.org/xfn/11">';
		
		echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">';
		echo '<link rel="dns-prefetch" href="//s.w.org">';

		echo '<title>Live Editor</title>';

		wp_head();

	echo '</head>';

	echo '<body style="margin:0px;padding:0px;overflow:hidden;">';
		
		echo '<div id="ltple-wrapper" class="boxedcontent" style="position:absolute;z-index:auto;border:none;">';
		
			echo do_shortcode( '[ltple-client-editor]' );
		
		echo '</div>';
		
		wp_footer();
		
	echo '</body>';
echo '</html>';