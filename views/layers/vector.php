<?php 

	//get page def
	
	$pageDef =$this->pageDef;
	
	//get css libraries

	$layerCssLibraries = $this->layerCssLibraries;

	//get js libraries
	
	$layerJsLibraries = $this->layerJsLibraries;
	
	//get font libraries
	
	$layerFontLibraries = $this->layerFontLibraries;	
	
	//get layer margin
	
	$layerMargin = $this->layerMargin;
	
	//get layer Min Width
	
	$layerMinWidth = $this->layerMinWidth;

	// get layer content
	
	$layerHead 		= '';
	$layerContent 	= '';
	$layerJson 		= '';
	
	$headStyles = array();
	$headLinks = array();

	//get layer content
	
	if( isset($_POST['importHtml']) ){

		$layerContent = $_POST['importHtml'];
	}
	else{
		
		$layerContent = $this->layerContent;
	}
	
	$layerContent = LTPLE_Editor::sanitize_content($layerContent);

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
	
		
	$defaultCss =$this->defaultCss;
		
	$layerCss =$this->layerCss;
		
	$defaultJs =$this->defaultJs;
		
	$layerJs =$this->layerJs;

	$layerMeta =$this->layerMeta;
	
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
				elseif( $font_url = $this->get_font_parsed_url($term) ){
					
					$fontsLibraries[$font_url] = $this->get_font_family($term);
				}	
			}
		}
	}

	// get head

	$head = '<head>' . PHP_EOL;
	
		$head .= '<!-- Le HTML5 shim, for IE6-8 support of HTML elements -->' . PHP_EOL;
		$head .= '<!--[if lt IE 9]>' . PHP_EOL;
		$head .= '<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>' . PHP_EOL;
		$head .= '<![endif]-->' . PHP_EOL;	

		$head .= '<meta charset="UTF-8">' . PHP_EOL;
		$head .= '<meta name="viewport" content="width=device-width, initial-scale=1">' . PHP_EOL;
		
		$head .= '<link rel="profile" href="//gmpg.org/xfn/11">' . PHP_EOL;
		
		$head .= '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . PHP_EOL;
		$head .= '<link rel="dns-prefetch" href="//s.w.org">' . PHP_EOL;
	
		// font library
		
		
		if( !empty($googleFonts) ){
		
			$head .= '<link href="//fonts.googleapis.com/css?family='.implode('|',$googleFonts).'" rel="stylesheet" />' . PHP_EOL;
		}
		
		if( !empty($fontsLibraries) ){
			
			$head .= '<style id="LiveTplEditorFonts">' . PHP_EOL;
			
			foreach( $fontsLibraries as $font_url => $font_family ){
		
				$font_url =$this->sanitize_url( $font_url );
				
				if( !empty($font_url) && !in_array($font_url,$headLinks) ){
		
					$head .= '@font-face { font-family: ' . $font_family . '; src: url("' . $font_url . '"); }' . PHP_EOL;
				
					$headLinks[] = $font_url;
				}
			}
			
			$head .= '</style>' . PHP_EOL;
		}	
		
		if( !empty($layerCssLibraries) ){
			
			foreach($layerCssLibraries as $term){
				
				$css_url = $this->get_css_parsed_url($term);
				
				$css_url = $this->sanitize_url($css_url);
				
				if( !empty($css_url) && !in_array($css_url,$headLinks) ){

					$head .= '<link href="' . $css_url . '" rel="stylesheet" type="text/css" />' . PHP_EOL;
						
					$headLinks[] = $css_url;
				}
			}
		}
		
		$head .= PHP_EOL;
	
		if( !empty($layerHead) ){
			
			$head .= $layerHead . PHP_EOL;
		}
		
		if(!empty($layerMeta['link'])){
			
			foreach($layerMeta['link'] as $url){
				
				$url = $this->sanitize_url( $url );
				
				if( !empty($url) && !in_array($url,$headLinks) ){
				
					$head .= '<link href="' . $url . '" rel="stylesheet" type="text/css" />' . PHP_EOL;
			
					$headLinks[] = $url;
				}
			}
		}	
		
		//include style-sheets

		$head .= '<style id="LiveTplEditorStyleSheet">'.PHP_EOL;
		
			if( !empty($layerCss) ){

				$head .= $layerCss .PHP_EOL;
			}
			
		$head .= '</style>'.PHP_EOL;
		
	$head .= '</head>' . PHP_EOL;

	// get layer
	
	$layer  = '<!DOCTYPE html>';
	$layer .= '<html class="' . $this->get_layer_classes($this->id) . '">';
	$layer .= $head;
	
	$layer .= '<body style="margin:0;background-repeat: repeat;background-image: url(data:image/jpeg;base64,/9j/4AAQSkZJRgABAQED6APoAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAAQABADAREAAhEBAxEB/8QAFgABAQEAAAAAAAAAAAAAAAAACQAK/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/EABQBAQAAAAAAAAAAAAAAAAAAAAD/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwDfwA/wQEAB/9k=);">';		
		
		// include app html
		
		$layer .= '<canvas id="LiveTplEditorCanvas" width="600" height="500"></canvas>';
		
		// include js
		
		/*
		if( !empty($layerJsLibraries) ){
			
			foreach($layerJsLibraries as $term){
				
				$js_url = $this->get_js_parsed_url( $term);
				
				if( !empty($js_url) ){
					
					$layer .= '<script src="'.$js_url.'"></script>' .PHP_EOL;
				}
			}
		}
		
		if( !empty($layerMeta['script']) ){
			
			foreach($layerMeta['script'] as $url){
				
				$layer .= '<script src="'.$url.'"></script>' .PHP_EOL;
			}
		}
		*/

		//include layer script
		
		$layer .='<script id="LiveTplEditorScript">' .PHP_EOL;
		
			if( $layerJs != '' ){

				$layer .= $layerJs .PHP_EOL;				
			}				
			
		$layer .='</script>' .PHP_EOL;

	$layer .='</body></html>' .PHP_EOL;