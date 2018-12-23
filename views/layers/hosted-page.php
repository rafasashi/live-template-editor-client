<?php 

	//get page def
	
	$pageDef = $ltple->layer->pageDef;
	
	//get default static directory url
	
	$defaultStaticDirUrl = $ltple->layer->defaultStaticDirUrl;
	
	//get layer static css url

	$layerStaticCssUrl = $ltple->layer->sanitize_url( $ltple->layer->layerStaticCssUrl );
	
	//get layer static js url
	
	$layerStaticJsUrl = $ltple->layer->sanitize_url( $ltple->layer->layerStaticJsUrl );
	
	//get default static css url
	
	$defaultStaticCssUrl = $ltple->layer->sanitize_url( $ltple->layer->defaultStaticCssUrl );
	
	//get default static js url
	
	$defaultStaticJsUrl = $ltple->layer->sanitize_url( $ltple->layer->defaultStaticJsUrl );
	
	//get default static path
	
	$defaultStaticPath = $ltple->layer->defaultStaticPath;
	
	//get layer options
	
	$layerOptions = $ltple->layer->layerOptions;
	
	//get layer settings
	
	$layerSettings = $ltple->layer->layerSettings;

	//get layer embedded
	
	$layerEmbedded = $ltple->layer->layerEmbedded;
	
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
				
				$link = $ltple->layer->sanitize_url($nodeValue,$defaultStaticDirUrl);
			
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
				
				$link = $ltple->layer->sanitize_url($nodeValue,$defaultStaticDirUrl);

				$node->setAttribute('src',$link);
			}
		}

		// get head
		
		$layerHead = $dom->saveHtml( $xpath->query('/html/head')->item(0) );
		$layerHead = preg_replace('~<(?:!DOCTYPE|/?(?:head))[^>]*>\s*~i', '', $layerHead);
		
		// get body
		
		if( !empty($ltple->layer->layerContent) ){
		
			$layerContent = $ltple->layer->layerContent;
		}
		else{
			
			$layerContent = $dom->saveHtml( $xpath->query('/html/body')->item(0) );
			$layerContent = preg_replace('~<(?:!DOCTYPE|/?(?:body))[^>]*>\s*~i', '', $layerContent);
		}
	}
	else{
		
		$layerContent = $ltple->layer->layerContent;
		
		$layerContent = $ltple->layer->sanitize_content($layerContent);
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
		

		// output css files
		
		if( !empty($defaultStaticCssUrl) ){
			
			$defaultStaticCssUrl = $ltple->layer->sanitize_url( $defaultStaticCssUrl );
			
			if( !empty($defaultStaticCssUrl) && !in_array($defaultStaticCssUrl,$headLinks) ){
			
				$head .= '<link href="' . $defaultStaticCssUrl . '" rel="stylesheet" />';
			
				$headLinks[] = $defaultStaticCssUrl;
			}
		}
		
		if( $ltple->layer->type == 'user-layer' && $layerCss != $defaultCss ){
			
			$layerStaticCssUrl = $ltple->layer->sanitize_url( $layerStaticCssUrl );
			
			if( !empty($layerStaticCssUrl) && !in_array($layerStaticCssUrl,$headLinks) ){
			
				$head .= '<link href="' . $layerStaticCssUrl . '" rel="stylesheet" />';
			
				$headLinks[] = $layerStaticCssUrl;
			}
		}
		
		// output custom meta tags
		
		if( !empty($layerSettings) ){
			
			foreach( $layerSettings as $key => $content ){
				
				if( !empty($content) ){
				
					if( $key == 'meta_title' ){
						
						$title = ucfirst($content);
						
						$head .= '<title>'.$title.'</title>'.PHP_EOL;
						$head .= '<meta name="subject" content="'.$title.'" />'.PHP_EOL;
						$head .= '<meta property="og:title" content="'.$title.'" />'.PHP_EOL;
						$head .= '<meta name="twitter:title" content="'.$title.'" />'.PHP_EOL;		
					}
					elseif( $key == 'meta_keywords' ){

						$content = implode(',',explode(PHP_EOL,$content));
					
						$head .= '<meta name="keywords" content="'.$content.'" />'.PHP_EOL;
						
					}
					elseif( $key == 'meta_description' ){
						
						$head .= '<meta name="description" content="'.$content.'" />'.PHP_EOL;
						$head .= '<meta name="abstract" content="'.$content.'" />' . PHP_EOL;
						$head .= '<meta name="summary" content="'.$content.'" />' . PHP_EOL;
						$head .= '<meta property="og:description" content="'.$content.'" />' . PHP_EOL;
						$head .= '<meta name="twitter:description" content="'.$content.'" />'.PHP_EOL;
					}
					elseif( $key == 'link_author' ){
						
						$head .= '<link rel="author" href="' . $ltple->layer->sanitize_url( $content ) . '" />'.PHP_EOL;
						$head .= '<link rel="publisher" href="' . $ltple->layer->sanitize_url( $content ) . '" />'.PHP_EOL;
					}
					elseif( $key == 'meta_image' ){
						
						$head .= '<meta property="og:image" content="'.$content.'" />'.PHP_EOL;
						$head .= '<meta name="twitter:image" content="'.$content.'" />'.PHP_EOL;
						
					}
					elseif( $key == 'meta_facebook-id' ){
						
						$head .= '<meta property="fb:admins" content="'.$content.'"/>'.PHP_EOL;
						
					}				
					else{
						
						list($markup,$name) = explode('_',$key);
						
						if( $markup == 'meta' ){
							
							$head .= '<meta name="'.$name.'" content="'.$content.'" />'.PHP_EOL;
						}
						elseif( $markup == 'link' ){
							
							$head .= '<link rel="'.$name.'" href="' . $ltple->layer->sanitize_url( $content ) . '" />'.PHP_EOL;
						}
					}
				}
			}
		}
		
		if( empty($layerSettings['meta_title']) ){
			
			// output default title
			
			$title = ucfirst($ltple->layer->title);
			
			$head .= '<title>'.$title.'</title>'.PHP_EOL;
			$head .= '<meta name="subject" content="'.$title.'" />'.PHP_EOL;
			$head .= '<meta property="og:title" content="'.$title.'" />'.PHP_EOL;
			$head .= '<meta name="twitter:title" content="'.$title.'" />'.PHP_EOL;					
		}			
		
		// output default meta tags
		
		$ggl_webmaster_id = get_option( $ltple->_base . 'embedded_ggl_webmaster_id' );
		
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
		
		$content = ucfirst($ltple->layer->title);
		
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
		
		if( !empty($layerEmbedded) ){
		
			$url = $ltple->layer->sanitize_url( $layerEmbedded );
			
			$head .= '<meta name="url" content="'.$url.'" />' . PHP_EOL;
			//$head .= '<meta name="canonical" content="'.$url.'" />' . PHP_EOL;
			$head .= '<meta name="original-source" content="'.$url.'" />' . PHP_EOL;
			$head .= '<link rel="original-source" href="'.$url.'" />' . PHP_EOL;
			$head .= '<meta property="og:url" content="'.$url.'" />' . PHP_EOL;
			$head .= '<meta name="twitter:url" content="'.$url.'" />' . PHP_EOL;
		}
		
		$head .= '<meta name=viewport content="width=device-width, initial-scale=1">' . PHP_EOL;
		
		$head .= '<meta name="rating" content="General" />' . PHP_EOL;
		$head .= '<meta name="directory" content="submission" />' . PHP_EOL;
		$head .= '<meta name="coverage" content="Worldwide" />' . PHP_EOL;
		$head .= '<meta name="distribution" content="Global" />' . PHP_EOL;
		$head .= '<meta name="target" content="all" />' . PHP_EOL;
		$head .= '<meta name="medium" content="blog" />' . PHP_EOL;
		$head .= '<meta property="og:type" content="article" />' . PHP_EOL;
		$head .= '<meta name="twitter:card" content="summary" />' . PHP_EOL;
		
		/*
		$head .= '<meta name="geo.position" content="latitude; longitude" />' . PHP_EOL;
		$head .= '<meta name="geo.placename" content="Place Name" />' . PHP_EOL;
		$head .= '<meta name="geo.region" content="Country Subdivision Code" />' . PHP_EOL;
		*/

		/*
		$ggl_analytics_id = get_option( $ltple->_base . 'embedded_ggl_analytics_id' );
						
		if( !empty($ggl_analytics_id) ){
		
			?>
			<script> 
			
				<!-- Google Analytics Code -->
			
				(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

				ga('create', '<?php echo $ggl_analytics_id; ?>', 'auto');
				ga('send', 'pageview');
				
				<!-- End Google Analytics Code -->
				
			</script>

			<?php					
		}
		*/		
		
	$head .= '</head>';

	// get layer
	
	$layer  = '<!DOCTYPE html>';
	$layer .= '<html>';
	$layer .= $head;

	$layer .= '<body style="background:#fff;padding:0;margin:0;display:flex !important;width:100%;">';
		
		//include style-sheets
		
		if( $defaultCss!='' ){
			
			$layer .= '<style id="LiveTplEditorDefaultStyleSheet">'.PHP_EOL;
				
				$layer .= $defaultCss .PHP_EOL;
			
			$layer .= '</style>'.PHP_EOL;
		}
		
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
		
		if( $ltple->layer->type == 'user-layer' && !empty($layerJs) ){

			$layer .= '<script src="'.$layerStaticJsUrl.'"></script>' .PHP_EOL;
		}
		elseif( !empty($defaultJs) ){
			
			$layer .= '<script src="'.$defaultStaticJsUrl.'"></script>' .PHP_EOL;
		}
		
	$layer .='</body></html>' .PHP_EOL;
	