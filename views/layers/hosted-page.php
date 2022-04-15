<?php 
	
	// get layer
	
	$layer  = '<!DOCTYPE html>' .PHP_EOL;
	$layer .= '<html class="' . $this->get_layer_classes($this->id) . '">' .PHP_EOL;
	
	$layer .= '<head>' .PHP_EOL;
			
		$layer .= '<!-- Le HTML5 shim, for IE6-8 support of HTML elements -->' .PHP_EOL;
		$layer .= '<!--[if lt IE 9]>' .PHP_EOL;
		$layer .= '<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>' .PHP_EOL;
		$layer .= '<![endif]-->' .PHP_EOL;	

		$layer .= '<meta charset="UTF-8">' .PHP_EOL;
		$layer .= '<meta name="viewport" content="width=device-width, initial-scale=1">' .PHP_EOL;
		
		$layer .= '<link rel="profile" href="//gmpg.org/xfn/11">' .PHP_EOL;
		
		$layer .= '<link rel="dns-prefetch" href="//fonts.googleapis.com">' .PHP_EOL;
		$layer .= '<link rel="dns-prefetch" href="//s.w.org">' .PHP_EOL;
		
		$layer .= $this->layerHeadContent;
		
	$layer .= '<head>' .PHP_EOL;

	$layer .= '<body style="background-color:#fff;padding:0;margin:0;width:100%;font-family:sans-serif;overflow-x:hidden;top:0;bottom:0;right:0;left:0;position:absolute;">' .PHP_EOL;
		
		$layer .= $this->layerBodyContent;
		
	$layer .='</body></html>' .PHP_EOL;