<?php 

	//get page def
	
	$pageDef =$this->pageDef;

	//get css libraries

	$layerCssLibraries =$this->layerCssLibraries;

	//get js libraries
	
	$layerJsLibraries =$this->layerJsLibraries;
	
	//get font libraries
	
	$layerFontLibraries =$this->layerFontLibraries;	
	
	//get layer margin
	
	$layerMargin =$this->layerMargin;
	
	//get layer Min Width
	
	$layerMinWidth =$this->layerMinWidth;

	// get layer content
	
	$layerHead 			= '';
	$layerContent 		= '';
	
	$headStyles = array();
	$headLinks = array();	
		
	//get layer content
	
	if( isset($_POST['importHtml']) ){

		$layerContent = $_POST['importHtml'];
	}
	else{
		
		$layerContent =$this->layerContent;
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

	//get style-sheet
	
	$defaultCss 	= '';
	$layerCss 		= '';
	$defaultJs 		= '';
	$layerJs 		= '';
	$layerMeta 		= '';
	
	if( isset($_POST['importCss']) ){

		$layerCss = stripcslashes($_POST['importCss']);
	}
	elseif( empty($_POST) ){
		
		$defaultCss =$this->defaultCss;
		
		$layerCss =$this->layerCss;
		
		$defaultJs =$this->defaultJs;
		
		$layerJs =$this->layerJs;

		$layerMeta =$this->layerMeta;
	}
	
	$layerContent = str_replace('<?xml encoding="UTF-8">','',$layerContent);

	// get google fonts
	
	$googleFonts = [];
	$fontsLibraries = [];
	
	if( !empty($layerCss) ){
		
		$regex = '`\/\/fonts\.googleapis\.com\/css\?family=([0-9A-Za-z\|\,\+\:]+)`';
		$fonts = preg_match($regex, $layerCss,$match);
		
		if(isset($match[1])){
			
			$googleFonts = array_merge( $googleFonts, explode('|',$match[1]));
		}
	}
	
	// get font libraries
	
	if( !empty($layerFontLibraries) ){
		
		foreach($layerFontLibraries as $term){
			
			$font_url = $this->get_meta( $term, 'font_url' );
			
			if( !empty($font_url) ){
				
				$regex = '`\/\/fonts\.googleapis\.com\/css\?family=([0-9A-Za-z\|\,\+\:]+)`';
				$fonts = preg_match($regex, $font_url,$match);

				if(isset($match[1])){
					
					$googleFonts = array_merge( $googleFonts, explode('|',$match[1]));
				}
				else{
					
					$fontsLibraries[] = $font_url;
				}	
			}
		}
	}

	// get head

	$head = '<head>';
	
		$head .= '<!-- Le HTML5 shim, for IE6-8 support of HTML elements -->';
		$head .= '<!--[if lt IE 9]>';
		$head .= '<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>';
		$head .= '<![endif]-->';	

		$head .= '<meta charset="UTF-8">';
		$head .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
		
		$head .= '<link rel="profile" href="//gmpg.org/xfn/11">';
		
		$head .= '<link rel="dns-prefetch" href="//fonts.googleapis.com">';
		$head .= '<link rel="dns-prefetch" href="//s.w.org">';
	
		// font library
		
		if( !empty($googleFonts) ){
		
			$head .= '<link href="//fonts.googleapis.com/css?family='.implode('|',$googleFonts).'" rel="stylesheet" />';
		}
		
		if( !empty($fontsLibraries) ){
		
			foreach( $fontsLibraries as $font ){
		
				$font =$this->sanitize_url( $font );
				
				if( !empty($font) && !in_array($font,$headLinks) ){
		
					$head .= '<link href="' . $font . '" rel="stylesheet" />';
				
					$headLinks[] = $font;
				}
			}
		}	
		
		if( !empty($layerCssLibraries) ){
			
			foreach($layerCssLibraries as $term){
				
				$css_url =$this->sanitize_url( $this->get_meta( $term, 'css_url' ) );
				
				if( !empty($css_url) && !in_array($css_url,$headLinks) ){

					$head .= '<link href="' . $css_url . '" rel="stylesheet" type="text/css" />';
						
					$headLinks[] = $css_url;
				}
				
				$css_content = $this->get_meta( $term, 'css_content' );
				
				if( !empty($css_content) ){
				
					$head .= '<style>' . stripcslashes($css_content) . '</style>';
				}
			}
		}
		
		$head .= PHP_EOL;
	
		if( !empty($layerHead) ){
			
			$head .= $layerHead;
		}
		
		if(!empty($layerMeta['link'])){
			
			foreach($layerMeta['link'] as $url){
				
				$url =$this->sanitize_url( $url );
				
				if( !empty($url) && !in_array($url,$headLinks) ){
				
					$head .= '<link href="' . $url . '" rel="stylesheet" type="text/css" />';
			
					$headLinks[] = $url;
				}
			}
		}	
		
	$head .= '</head>';

	// get layer
	
	$layer  = '<!DOCTYPE html>';
	$layer .= '<html>';
	$layer .= $head;

	$layer .= '<body style="padding:0;margin:0;display:flex !important;width:100%;font-family:sans-serif;">';
		
		//include style-sheets
		
		$layer .= '<style id="LiveTplEditorStyleSheet">'.PHP_EOL;
		
			if( $layerCss!='' ){

				$layer .= $layerCss .PHP_EOL;
			}
			
		$layer .= '</style>'.PHP_EOL;		
		
		//include layer

		$layer .= '<ltple-layer class="editable" style="width:100%;' . ( !empty($layerMargin) ? 'margin:'.$layerMargin.';' : '' ) . '">';
						
			$layer .= $layerContent;
		
		$layer .= '</ltple-layer>' .PHP_EOL;

		if( !empty($layerJsLibraries) ){
			
			foreach($layerJsLibraries as $term){
				
				$js_url = $this->get_meta( $term, 'js_url' );
				
				if( !empty($js_url) ){
					
					$layer .= '<script src="'.$js_url.'"></script>' .PHP_EOL;
				}
				
				$js_content = $this->get_meta( $term, 'js_content' );
				
				if( !empty($js_content) ){
				
					$layer .= $js_content .PHP_EOL;	
				}
			}
		}
		
		if( !empty($layerMeta['script']) ){
			
			foreach($layerMeta['script'] as $url){
				
				$layer .= '<script src="'.$url.'"></script>' .PHP_EOL;
			}
		}
		
		//include layer script
		
		$layer .='<script id="LiveTplEditorScript">' .PHP_EOL;
		
			if( $layerJs != '' ){

				$layer .= $layerJs .PHP_EOL;				
			}				
			
		$layer .='</script>' .PHP_EOL;
		
	$layer .='</body></html>' .PHP_EOL;