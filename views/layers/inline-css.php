<?php 

	//get page def
	
	$pageDef =$this->pageDef;

	//get css libraries

	$layerCssLibraries = isset($this->layerCssLibraries) ? $this->layerCssLibraries : array();

	//get js libraries
	
	$layerJsLibraries = isset($this->layerJsLibraries) ? $this->layerJsLibraries : array();
	
	//get layer margin
	
	$layerMargin = isset($this->layerMargin) ? $this->layerMargin : '';
	
	//get layer Min Width
	
	$layerMinWidth = isset($this->layerMinWidth) ? $this->layerMinWidth : '';

	// get layer content
	
	$layerContent = '';
	
	$headStyles = array();
	$headLinks = array();	
		
	//get layer content
	
	if( isset($_POST['importHtml']) ){

		$layerContent = $_POST['importHtml'];
	}
	else{
		
		$layerContent = isset($this->layerContent) ? $this->layerContent : '';
	}
	
	$layerContent =LTPLE_Editor::sanitize_content($layerContent);
	
	// parse content elements
	
	libxml_use_internal_errors( true );
	
	$dom= new DOMDocument();
	$dom->loadHTML('<?xml encoding="UTF-8">' . $layerContent); 

	$xpath = new DOMXPath($dom);

	// remove pagespeed_url_hash
	
	$links = [];
	
	$nodes = $xpath->query('//img');
	
	foreach ($nodes as $node) {
		
		$node->removeAttribute('pagespeed_url_hash');
	}			
	
	$layerContent = $dom->saveHtml( $xpath->query('/html/body')->item(0) );
	$layerContent = preg_replace('~<(?:!DOCTYPE|/?(?:body))[^>]*>\s*~i', '', $layerContent);
	$layerContent = str_replace('<?xml encoding="UTF-8">','',$layerContent);

	// get head

	$head = '<head>';
	
		$head .= '<!-- Le HTML5 shim, for IE6-8 support of HTML elements -->';
		$head .= '<!--[if lt IE 9]>';
		$head .= '<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>';
		$head .= '<![endif]-->';	

		$head .= '<meta charset="UTF-8">';
		$head .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
		
		$head .= '<link rel="profile" href="//gmpg.org/xfn/11">';
		
		$head .= '<link rel="dns-prefetch" href="//s.w.org">';	

	$head .= '</head>';

	// get layer
	
	$layer  = '<!DOCTYPE html>'.PHP_EOL;
	$layer .= '<html>'.PHP_EOL;
	$layer .= $head.PHP_EOL;
	$layer .= '<body style="padding:0;margin:0;display:flex !important;width:100%;font-family:sans-serif;">'.PHP_EOL;
		
		//include style-sheets
		
		$layer .= '<style id="LiveTplEditorStyleSheet">'.PHP_EOL;
		$layer .= '</style>'.PHP_EOL;		
		
		//include layer

		$layer .= '<ltple-layer class="editable" style="width:100%;' . ( !empty($layerMargin) ? 'margin:'.$layerMargin.';' : '' ) . '">'.PHP_EOL;
						
			$layer .= $layerContent.PHP_EOL;
		
		$layer .= '</ltple-layer>' .PHP_EOL;
		
		//include layer script
		
		$layer .='<script id="LiveTplEditorScript">' .PHP_EOL;
		$layer .='</script>' .PHP_EOL;
		
	$layer .='</body></html>' .PHP_EOL;