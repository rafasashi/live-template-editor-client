<?php 

	//get page def
	
	$pageDef =$this->pageDef;
	
	//get default static directory url
	
	$defaultStaticDirUrl =$this->defaultStaticDirUrl;
	
	//get layer static css url

	$layerStaticCssUrl =$this->sanitize_url($this->layerStaticCssUrl );
	
	//get layer static js url
	
	$layerStaticJsUrl =$this->sanitize_url($this->layerStaticJsUrl );
	
	//get default static css url
	
	$defaultStaticCssUrl =$this->sanitize_url($this->defaultStaticCssUrl );
	
	//get default static js url
	
	$defaultStaticJsUrl =$this->sanitize_url($this->defaultStaticJsUrl );
	
	//get default static path
	
	$defaultStaticPath =$this->defaultStaticPath;

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

	if( !empty($defaultStaticPath) && file_exists($defaultStaticPath) ){
		
		$output = file_get_contents($defaultStaticPath);

		// strip html comments
		
		$output = preg_replace('/<!--(.*)-->/Uis', '', $output);
		
		// parse dom elements
		
		libxml_use_internal_errors( true );
		
		$dom= new DOMDocument();
		$dom->loadHTML('<?xml encoding="UTF-8">' . $output); 

		$xpath = new DOMXPath($dom);
		
		// remove nodes
		
		$nodes = $xpath->query('//meta|//title|//base');
		
		foreach ($nodes as $node) {
			
			$node->parentNode->removeChild($node);
		}

		// remove duplicate styles
		
		$nodes = $xpath->query('//style');
		
		foreach ($nodes as $node) {
			
			$nodeValue 	= $node->nodeValue;
			
			if( !empty($nodeValue) ){
			
				if( !in_array($nodeValue,$headStyles) ){
				
					$headStyles[] = $nodeValue;
				}
				else{
				
					$node->parentNode->removeChild($node);
				}
			}
		}		
		
		// remove duplicate links
		
		$nodes = $xpath->query('//link');
		
		foreach ($nodes as $node) {
			
			$nodeValue 	= $node->getAttribute('href');
			
			if( !empty($nodeValue) ){
				
				$link =$this->sanitize_url($nodeValue,$defaultStaticDirUrl);
			
				if( !in_array($link,$headLinks) ){
					
					if( $link != $nodeValue ){
						
						//normalize link
						
						$node->setAttribute('href',$link);
					}
				
					$headLinks[] = $link;
				}
				else{
				
					$node->parentNode->removeChild($node);
				}
			}
		}
		
		// parse relative image urls
		
		$nodes = $xpath->query('//img');
		
		foreach ($nodes as $node) {
			
			$nodeValue 	= $node->getAttribute('src');
			
			if( !empty($nodeValue) ){
				
				//normalize link
				
				$link =$this->sanitize_url($nodeValue,$defaultStaticDirUrl);

				$node->setAttribute('src',$link);
			}
		}

		// get head
		
		$layerHead = $dom->saveHtml( $xpath->query('/html/head')->item(0) );
		$layerHead = preg_replace('~<(?:!DOCTYPE|/?(?:head))[^>]*>\s*~i', '', $layerHead);
		
		// get body
		
		if( !empty($this->parent->layer->layerContent) ){
		
			$layerContent =$this->layerContent;
		}
		else{
			
			$layerContent = $dom->saveHtml( $xpath->query('/html/body')->item(0) );
			$layerContent = preg_replace('~<(?:!DOCTYPE|/?(?:body))[^>]*>\s*~i', '', $layerContent);
		}
	}
	else{
		
		$layerContent =$this->layerContent;
		
		$layerContent =LTPLE_Editor::sanitize_content($layerContent);
	}
	}
	
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
	
		// output css files
		
		if( !empty($defaultStaticCssUrl) ){
			
			$defaultStaticCssUrl =$this->sanitize_url( $defaultStaticCssUrl );
			
			if( !empty($defaultStaticCssUrl) && !in_array($defaultStaticCssUrl,$headLinks) ){
			
				$head .= '<link href="' . $defaultStaticCssUrl . '" rel="stylesheet" />';
			
				$headLinks[] = $defaultStaticCssUrl;
			}
		}
		
		if($this->type == 'user-layer' && $layerCss != $defaultCss ){
			
			$layerStaticCssUrl =$this->sanitize_url( $layerStaticCssUrl );
			
			if( !empty($layerStaticCssUrl) && !in_array($layerStaticCssUrl,$headLinks) ){
			
				$head .= '<link href="' . $layerStaticCssUrl . '" rel="stylesheet" />';
			
				$headLinks[] = $layerStaticCssUrl;
			}
		}
	
		// output default title
		
		$title = ucfirst($this->parent->layer->title);
		
		$head .= '<title>'.$title.'</title>'.PHP_EOL;
		$head .= '<meta name="subject" content="'.$title.'" />'.PHP_EOL;
		$head .= '<meta property="og:title" content="'.$title.'" />'.PHP_EOL;
		$head .= '<meta name="twitter:title" content="'.$title.'" />'.PHP_EOL;							
		
		// output default meta tags
		
		$ggl_webmaster_id = get_option( $this->parent->_base . 'embedded_ggl_webmaster_id' );
		
		if( !empty($ggl_webmaster_id) ){
		
			$head .= '<meta name="google-site-verification" content="'.$ggl_webmaster_id.'" />'.PHP_EOL;
		}
		
		/*
		
		//TODO $post doesnt exist
		
		$author_name = get_the_author_meta('display_name', $post->post_author );
		$author_mail = get_the_author_meta('user_email', $post->post_author );
		
		if( empty($layerSettings['meta_author']) ){
			
			$head .= '<meta name="author" content="'.$author_name.', '.$author_mail.'" />' . PHP_EOL;
			$head .= '<meta name="creator" content="'.$author_name.', '.$author_mail.'" />' . PHP_EOL;
			$head .= '<meta name="owner" content="' . $author_name . '" />' . PHP_EOL;
			$head .= '<meta name="reply-to" content="'.$author_mail.'" />' . PHP_EOL;					
		}
		*/
		
		$locale = get_locale();
		
		if( empty($layerSettings['meta_language']) ){
			
			$head .= '<meta name="language" content="' . $locale . '" />'.PHP_EOL;
		}
		
		$robots = 'index,follow';
		
		if( empty($layerSettings['meta_robots']) ){
			
			$head .= '<meta name="robots" content="'.$robots.'" />' . PHP_EOL;
		}
		/*
		$revised = $post->post_date;
		
		if( empty($layerSettings['meta_revised']) ){
		
			$head .= '<meta name="revised" content="' . $revised . '" />' . PHP_EOL;
		}
		*/
		
		$content = ucfirst($this->parent->layer->title);
		
		if( empty($layerSettings['meta_description']) ){
			
			$head .= '<meta name="description" content="'.$content.'" />'.PHP_EOL;
			$head .= '<meta name="abstract" content="'.$content.'" />' . PHP_EOL;
			$head .= '<meta name="summary" content="'.$content.'" />' . PHP_EOL;
			$head .= '<meta property="og:description" content="'.$content.'" />' . PHP_EOL;
			$head .= '<meta name="twitter:description" content="'.$content.'" />'.PHP_EOL;
		}
		
		$head .= '<meta name="classification" content="Business" />' . PHP_EOL;
		//$head .= '<meta name="classification" content="products, product classifications, company classification, company type, industry" />' . PHP_EOL;
		
		$service_name = get_bloginfo( 'name' );
		
		$head .= '<meta name="copyright" content="'.$service_name.'" />'.PHP_EOL;
		$head .= '<meta name="designer" content="'.$service_name.' team" />' . PHP_EOL;
		
		$head .= '<meta name=viewport content="width=device-width, initial-scale=1">' . PHP_EOL;
		
		$head .= '<meta name="rating" content="General" />' . PHP_EOL;
		$head .= '<meta name="directory" content="submission" />' . PHP_EOL;
		$head .= '<meta name="coverage" content="Worldwide" />' . PHP_EOL;
		$head .= '<meta name="distribution" content="Global" />' . PHP_EOL;
		$head .= '<meta name="target" content="all" />' . PHP_EOL;
		$head .= '<meta name="medium" content="blog" />' . PHP_EOL;
		$head .= '<meta property="og:type" content="article" />' . PHP_EOL;
		$head .= '<meta name="twitter:card" content="summary" />' . PHP_EOL;	
		
	$head .= '</head>';

	// get layer
	
	$layer  = '<!DOCTYPE html>';
	$layer .= '<html>';
	$layer .= $head;

	$layer .= '<body style="padding:0;margin:0;display:flex !important;width:100%;font-family:sans-serif;overflow-x:hidden;">';
		
		//include style-sheets
		
		$layer .= '<style id="LiveTplEditorStyleSheet">'.PHP_EOL;
			
		$layer .= '</style>'.PHP_EOL;		

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
			
		$layer .='</script>' .PHP_EOL;
		
		if($this->type == 'user-layer' && !empty($layerJs) ){

			$layer .= '<script src="'.$layerStaticJsUrl.'"></script>' .PHP_EOL;
		}
		elseif( !empty($defaultJs) ){
			
			$layer .= '<script src="'.$defaultStaticJsUrl.'"></script>' .PHP_EOL;
		}
		
	$layer .='</body></html>' .PHP_EOL;