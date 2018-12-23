<?php 

	//get page def
	
	$pageDef = $ltple->layer->pageDef;
	
	//get layer form
	
	$layerForm = $ltple->layer->layerForm;
	
	//get css libraries

	$layerCssLibraries = $ltple->layer->layerCssLibraries;

	//get js libraries
	
	$layerJsLibraries = $ltple->layer->layerJsLibraries;
	
	//get font libraries
	
	$layerFontLibraries = $ltple->layer->layerFontLibraries;	
	
	//get layer image proxy
	
	$layerImgProxy = $ltple->request->proto . $_SERVER['HTTP_HOST'].'/image-proxy.php?'.time().'&url=';
	
	//get layer margin
	
	$layerMargin = $ltple->layer->layerMargin;
	
	//get layer Min Width
	
	$layerMinWidth = $ltple->layer->layerMinWidth;

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
		
		$layerContent = $ltple->layer->layerContent;
	}
	
	$layerContent = $ltple->layer->sanitize_content($layerContent);

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
		
		$defaultCss = $ltple->layer->defaultCss;
		
		$layerCss = $ltple->layer->layerCss;
		
		$defaultJs = $ltple->layer->defaultJs;
		
		$layerJs = $ltple->layer->layerJs;

		$layerMeta = $ltple->layer->layerMeta;
	}
	
	$defaultCss = sanitize_text_field($defaultCss);
	$layerCss 	= sanitize_text_field($layerCss);
	
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
			
			$font_url = get_option( 'font_url_' . $term->slug);
			
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
		
				$font = $ltple->layer->sanitize_url( $font );
				
				if( !empty($font) && !in_array($font,$headLinks) ){
		
					$head .= '<link href="' . $font . '" rel="stylesheet" />';
				
					$headLinks[] = $font;
				}
			}
		}	
		
		if( !empty($layerCssLibraries) ){
			
			foreach($layerCssLibraries as $term){
				
				$css_url = $ltple->layer->sanitize_url( get_option( 'css_url_' . $term->slug) );
				
				if( !empty($css_url) && !in_array($css_url,$headLinks) ){

					$head .= '<link href="' . $css_url . '" rel="stylesheet" type="text/css" />';
						
					$headLinks[] = $css_url;
				}
				
				$css_content = get_option( 'css_content_' . $term->slug);
				
				if( !empty($css_content) ){
				
					$head .= stripcslashes($css_content);
				}
			}
		}
		
		$head .= PHP_EOL;
	
		if( !empty($layerHead) ){
			
			$head .= $layerHead;
		}
		
		if(!empty($layerMeta['link'])){
			
			foreach($layerMeta['link'] as $url){
				
				$url = $ltple->layer->sanitize_url( $url );
				
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

	$layer .= '<body style="background:#fff;padding:0;margin:0;display:flex !important;width:100%;">';
		
		//include style-sheets
		
		$layer .= '<style id="LiveTplEditorStyleSheet">'.PHP_EOL;
		
			if( $layerCss!='' ){

				$layer .= $layerCss .PHP_EOL;
			}
			
		$layer .= '</style>'.PHP_EOL;		
		
		//include layer
		
		if( empty($_POST) && $layerForm == 'importer' && empty($ltple->layer->layerContent) ){
			
			$layer .='<script>' .PHP_EOL;

				$layer .= ' var layerFormActive = true;' .PHP_EOL;
				
			$layer .='</script>' .PHP_EOL;
			
			$layer .= '<div class="container">';
			
				$layer .= '<div class="panel panel-default" style="margin:50px;">';
				
				$layer .= '<div class="panel-heading">';
				
					if( !empty($layerForm) ){
						
						$layer .='<h4>'.ucfirst($ltple->layer->title).'</h4>';
					}
					
				$layer .= '</div>';
				
				$layer .= '<div class="panel-body">';
				
					$layer .= '<form target="_self" action="" method="post" style="width:100%;background:#FFFFFF;">';
					
						if( $layerForm == 'importer' ){
					
							$layer .= '<div class="col-xs-3">';
							
								$layer .='<label>HTML</label>';
								
							$layer .= '</div>';
							
							$layer .= '<div class="col-xs-9">';
							
								$layer .= '<div class="form-group">';
								
									$layer .= '<textarea class="form-control" name="importHtml" style="min-height:100px;"></textarea>';
									
								$layer .= '</div>';
								
							$layer .= '</div>';

							$layer .= '<div class="col-xs-12 text-right">';
								
								$layer .= '<input class="btn btn-primary btn-md" type="submit" value="Import" />';
								
							$layer .= '</div>';
						}							
					
					$layer .= '</form>';
					
				$layer .= '</div>';
				$layer .= '</div>';
			
			$layer .= '</div>';
		} 
		else{

			$layer .= '<layer class="editable" style="width:100%;' . ( !empty($layerMargin) ? 'margin:'.$layerMargin.';' : '' ) . '">';
							
				$layer .= $layerContent;
			
			$layer .= '</layer>' .PHP_EOL;
		}	

		if( !empty($layerJsLibraries) ){
			
			foreach($layerJsLibraries as $term){
				
				$js_url = get_option( 'js_url_' . $term->slug);
				
				if( !empty($js_url) ){
					
					$layer .= '<script src="'.$js_url.'"></script>' .PHP_EOL;
				}
				
				$js_content = get_option( 'js_content_' . $term->slug);
				
				if( !empty($js_content) ){
				
					$layer .= stripcslashes($js_content) .PHP_EOL;	
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
	