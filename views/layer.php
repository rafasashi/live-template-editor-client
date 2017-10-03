<?php 

	$ltple = LTPLE_Client::instance();

	//get page def
	
	$pageDef = $ltple->layer->pageDef;
	
	//get layer static css url
	
	$layerStaticCssUrl = $ltple->layer->layerStaticCssUrl;
	
	//get layer static js url
	
	$layerStaticJsUrl = $ltple->layer->layerStaticJsUrl;
	
	//get default static css url
	
	$defaultStaticCssUrl = $ltple->layer->defaultStaticCssUrl;
	
	//get default static js url
	
	$defaultStaticJsUrl = $ltple->layer->defaultStaticJsUrl;
	
	//get default static path
	
	$defaultStaticPath = $ltple->layer->defaultStaticPath;
	
	//get output config
	
	$layerOutput = $ltple->layer->layerOutput;
	
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
	
	//get layer image proxy
	
	$layerImgProxy = $ltple->request->proto . $_SERVER['HTTP_HOST'].'/image-proxy.php?'.time().'&url=';
	
	//get layer margin
	
	$layerMargin = $ltple->layer->layerMargin;
	
	//get layer Min Width
	
	$layerMinWidth = $ltple->layer->layerMinWidth;
	
	// get layer content
	
	$layerHead 			= '';
	$layerContent 		= '';
	
	if( $layerOutput == 'hosted-page' ){
		
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

			// get head
			
			$layerHead = $dom->saveHtml( $xpath->query('/html/head')->item(0) );
			$layerHead = preg_replace('~<(?:!DOCTYPE|/?(?:head))[^>]*>\s*~i', '', $layerHead);
			
			//var_dump(htmlentities($layerHead));exit;
			
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
			
			$layerContent = LTPLE_Client_Layer::sanitize_content($layerContent);
		}
	}
	else{	
		
		//get layer content
		
		if( isset($_POST['importHtml']) ){

			$layerContent = $_POST['importHtml'];
		}
		else{
			
			$layerContent = $ltple->layer->layerContent;
		}
		
		$layerContent = LTPLE_Client_Layer::sanitize_content($layerContent);
	}
	
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
	
	// normalize canvas content
	
	if( $layerOutput == 'canvas' ){
		
		$layerContent = str_replace(array($layerImgProxy),array(''),$layerContent);		
		
		// replace image sources
		
		$layerContent = str_replace(array('src =','src= "'),array('src=','src="'),$layerContent);
		$layerContent = str_replace(array($layerImgProxy,'src="'),array('','src="'.$layerImgProxy),$layerContent);			
		
		// replace background images

		$regex = '`(background(?:-image)?: ?url\((["|\']?))([^"|\'\)]+)(["|\']?\))`';
		$layerContent = preg_replace($regex, "$1$layerImgProxy$3$4", $layerContent);					
	
		if(!empty($layerCss)){

			$layerCss = str_replace(array($layerImgProxy),array(''),$layerCss);
		
			// replace background images

			$regex = '`(background(?:-image)?: ?url\((["|\']?))([^"|\'\)]+)(["|\']?\))`';
			$layerCss = preg_replace($regex, "$1$layerImgProxy$3$4", $layerCss);		
		}	
	}
	
	// get google fonts
	
	$googleFonts = [];
	
	if( !empty($layerCss) ){
		
		$regex = '`https\:\/\/fonts\.googleapis\.com\/css\?family=([A-Za-z\|]+)`';
		$fonts = preg_match($regex, $layerCss,$match);
		
		if(isset($match[1])){
			
			$googleFonts = explode('|',$match[1]);
		}
	}
	
	// get layer

	$layer = '<!DOCTYPE html>';
	
	$layer .= '<head>';
	
		$layer .= '<!-- Le HTML5 shim, for IE6-8 support of HTML elements -->';
		$layer .= '<!--[if lt IE 9]>';
		$layer .= '<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>';
		$layer .= '<![endif]-->';	

		$layer .= '<meta charset="UTF-8">';
		$layer .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
		
		$layer .= '<link rel="profile" href="http://gmpg.org/xfn/11">';
		
		$layer .= '<link rel="dns-prefetch" href="//fonts.googleapis.com">';
		$layer .= '<link rel="dns-prefetch" href="//s.w.org">';
	
		if( !empty($layerCssLibraries) ){
			
			foreach($layerCssLibraries as $term){
				
				$css_url = get_option( 'css_url_' . $term->slug);

				if( !empty($css_url) ){
					
					$layer .= '<link href="'.$css_url.'" rel="stylesheet" type="text/css" />';
				}
				
				$css_content = get_option( 'css_content_' . $term->slug);
				
				if( !empty($css_content) ){
				
					$layer .= $css_content;
				}
			}
		}
		
		$layer .= PHP_EOL;
	
		if( !empty($layerHead) ){
			
			$layer .= $layerHead;
		}
		
		if(!empty($layerMeta['link'])){
			
			foreach($layerMeta['link'] as $url){
				
				$layer .= '<link href="'.$url.'" rel="stylesheet" type="text/css" />';
			}
		}			
		
		// font library
		
		if( !empty($googleFonts) ){
		
			$layer .= '<link href="https://fonts.googleapis.com/css?family='.implode('|',$googleFonts).'" rel="stylesheet" />';
		}
		
		if( $layerOutput == 'hosted-page' ){		
			
			// output css files
			
			if( !empty($defaultCss) ){
			
				$layer .= '<link href="' . $defaultStaticCssUrl . '" rel="stylesheet" />';
			}
			
			if( $ltple->layer->type == 'user-layer' && $layerCss != $defaultCss ){
				
				$layer .= '<link href="' . $layerStaticCssUrl . '" rel="stylesheet" />';
			}
			
			// output custom meta tags
			
			if( !empty($layerSettings) ){
				
				foreach( $layerSettings as $key => $content ){
					
					if( !empty($content) ){
					
						if( $key == 'meta_title' ){
							
							$title = ucfirst($content);
							
							$layer .= '<title>'.$title.'</title>'.PHP_EOL;
							$layer .= '<meta name="subject" content="'.$title.'" />'.PHP_EOL;
							$layer .= '<meta property="og:title" content="'.$title.'" />'.PHP_EOL;
							$layer .= '<meta name="twitter:title" content="'.$title.'" />'.PHP_EOL;		
						}
						elseif( $key == 'meta_keywords' ){

							$content = implode(',',explode(PHP_EOL,$content));
						
							$layer .= '<meta name="keywords" content="'.$content.'" />'.PHP_EOL;
							
						}
						elseif( $key == 'meta_description' ){
							
							$layer .= '<meta name="description" content="'.$content.'" />'.PHP_EOL;
							$layer .= '<meta name="abstract" content="'.$content.'" />' . PHP_EOL;
							$layer .= '<meta name="summary" content="'.$content.'" />' . PHP_EOL;
							$layer .= '<meta property="og:description" content="'.$content.'" />' . PHP_EOL;
							$layer .= '<meta name="twitter:description" content="'.$content.'" />'.PHP_EOL;
						}
						elseif( $key == 'link_author' ){
							
							$layer .= '<link rel="author" href="'.$content.'" />'.PHP_EOL;
							$layer .= '<link rel="publisher" href="'.$content.'" />'.PHP_EOL;
						}
						elseif( $key == 'meta_image' ){
							
							$layer .= '<meta property="og:image" content="'.$content.'" />'.PHP_EOL;
							$layer .= '<meta name="twitter:image" content="'.$content.'" />'.PHP_EOL;
							
						}
						elseif( $key == 'meta_facebook-id' ){
							
							$layer .= '<meta property="fb:admins" content="'.$content.'"/>'.PHP_EOL;
							
						}				
						else{
							
							list($markup,$name) = explode('_',$key);
							
							if( $markup == 'meta' ){
								
								$layer .= '<meta name="'.$name.'" content="'.$content.'" />'.PHP_EOL;
							}
							elseif( $markup == 'link' ){
								
								$layer .= '<link rel="'.$name.'" href="'.$content.'" />'.PHP_EOL;
							}
						}
					}
				}
			}
			
			if( empty($layerSettings['meta_title']) ){
				
				// output default title
				
				$title = ucfirst($ltple->layer->title);
				
				$layer .= '<title>'.$title.'</title>'.PHP_EOL;
				$layer .= '<meta name="subject" content="'.$title.'" />'.PHP_EOL;
				$layer .= '<meta property="og:title" content="'.$title.'" />'.PHP_EOL;
				$layer .= '<meta name="twitter:title" content="'.$title.'" />'.PHP_EOL;					
			}			
			
			// output default meta tags
			
			$ggl_webmaster_id = get_option( $ltple->_base . 'embedded_ggl_webmaster_id' );
			
			if( !empty($ggl_webmaster_id) ){
			
				$layer .= '<meta name="google-site-verification" content="'.$ggl_webmaster_id.'" />'.PHP_EOL;
			}
			
			$author_name = get_the_author_meta('display_name', $post->post_author );
			$author_mail = get_the_author_meta('user_email', $post->post_author );
			
			if( empty($layerSettings['meta_author']) ){
				
				$layer .= '<meta name="author" content="'.$author_name.', '.$author_mail.'" />' . PHP_EOL;
				$layer .= '<meta name="creator" content="'.$author_name.', '.$author_mail.'" />' . PHP_EOL;
				$layer .= '<meta name="owner" content="' . $author_name . '" />' . PHP_EOL;
				$layer .= '<meta name="reply-to" content="'.$author_mail.'" />' . PHP_EOL;					
			}
			
			$locale = get_locale();
			
			if( empty($layerSettings['meta_language']) ){
				
				$layer .= '<meta name="language" content="' . $locale . '" />'.PHP_EOL;
			}
			
			$robots = 'index,follow';
			
			if( empty($layerSettings['meta_robots']) ){
				
				$layer .= '<meta name="robots" content="'.$robots.'" />' . PHP_EOL;
			}
			
			$revised = $post->post_date;
			
			if( empty($layerSettings['meta_revised']) ){
			
				$layer .= '<meta name="revised" content="' . $revised . '" />' . PHP_EOL;
			}
			
			$content = ucfirst($ltple->layer->title);
			
			if( empty($layerSettings['meta_description']) ){
				
				$layer .= '<meta name="description" content="'.$content.'" />'.PHP_EOL;
				$layer .= '<meta name="abstract" content="'.$content.'" />' . PHP_EOL;
				$layer .= '<meta name="summary" content="'.$content.'" />' . PHP_EOL;
				$layer .= '<meta property="og:description" content="'.$content.'" />' . PHP_EOL;
				$layer .= '<meta name="twitter:description" content="'.$content.'" />'.PHP_EOL;
			}
			
			$layer .= '<meta name="classification" content="Business" />' . PHP_EOL;
			//$layer .= '<meta name="classification" content="products, product classifications, company classification, company type, industry" />' . PHP_EOL;
			
			$service_name = get_bloginfo( 'name' );
			
			$layer .= '<meta name="copyright" content="'.$service_name.'" />'.PHP_EOL;
			$layer .= '<meta name="designer" content="'.$service_name.' team" />' . PHP_EOL;
			
			if( !empty($layerEmbedded) ){
			
				$url = $layerEmbedded;
				
				$layer .= '<meta name="url" content="'.$url.'" />' . PHP_EOL;
				//$layer .= '<meta name="canonical" content="'.$url.'" />' . PHP_EOL;
				$layer .= '<meta name="original-source" content="'.$url.'" />' . PHP_EOL;
				$layer .= '<link rel="original-source" href="'.$url.'" />' . PHP_EOL;
				$layer .= '<meta property="og:url" content="'.$url.'" />' . PHP_EOL;
				$layer .= '<meta name="twitter:url" content="'.$url.'" />' . PHP_EOL;
			}
			
			$layer .= '<meta name=viewport content="width=device-width, initial-scale=1">' . PHP_EOL;
			
			$layer .= '<meta name="rating" content="General" />' . PHP_EOL;
			$layer .= '<meta name="directory" content="submission" />' . PHP_EOL;
			$layer .= '<meta name="coverage" content="Worldwide" />' . PHP_EOL;
			$layer .= '<meta name="distribution" content="Global" />' . PHP_EOL;
			$layer .= '<meta name="target" content="all" />' . PHP_EOL;
			$layer .= '<meta name="medium" content="blog" />' . PHP_EOL;
			$layer .= '<meta property="og:type" content="article" />' . PHP_EOL;
			$layer .= '<meta name="twitter:card" content="summary" />' . PHP_EOL;
			
			/*
			$layer .= '<meta name="geo.position" content="latitude; longitude" />' . PHP_EOL;
			$layer .= '<meta name="geo.placename" content="Place Name" />' . PHP_EOL;
			$layer .= '<meta name="geo.region" content="Country Subdivision Code" />' . PHP_EOL;
			*/
		}
		
		$ggl_analytics_id = get_option( $ltple->_base . 'embedded_ggl_analytics_id' );
						
		if( !empty($ggl_analytics_id) ){
		
			?>
			<script> 
			
				<!-- Google Analytics Code -->
			
				(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

				ga('create', '<?php $layer .= $ggl_analytics_id; ?>', 'auto');
				ga('send', 'pageview');
				
				<!-- End Google Analytics Code -->
				
			</script>

			<?php					
		}			
		
	$layer .= '</head>';

	$layer .= '<body style="padding:0;margin:0;display:flex !important;width:100%;">';
		
		//include style-sheet
		
		$layer .= '<style id="LiveTplEditorStyleSheet">'.PHP_EOL;
		
		if( $ltple->layer->layerOutput != 'hosted-page' && $layerCss!='' ){

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
							
							if( $layerOutput == 'external-css' ){
								
								$layer .= '<div class="col-xs-3">';
								
									$layer .='<label>CSS</label>';
									
								$layer .= '</div>';
								
								$layer .= '<div class="col-xs-9">';
								
									$layer .= '<div class="form-group">';
									
										$layer .= '<textarea class="form-control" name="importCss" style="min-height:100px;"></textarea>';
										
									$layer .= '</div>';
									
								$layer .= '</div>';									
							}

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
		
			if( $ltple->layer->layerOutput != 'hosted-page' && $layerJs != '' ){

				$layer .= $layerJs .PHP_EOL;				
			}				
			
		$layer .='</script>' .PHP_EOL;
		
		if( $ltple->layer->layerOutput == 'hosted-page' ){
			
			if( $ltple->layer->type == 'user-layer' && !empty($layerJs) ){

				$layer .= '<script src="'.$layerStaticJsUrl.'"></script>' .PHP_EOL;
			}
			elseif( !empty($defaultJs) ){
				
				$layer .= '<script src="'.$defaultStaticJsUrl.'"></script>' .PHP_EOL;
			}
		}
		
	$layer .='</body>' .PHP_EOL;
	
	// callback layer object
	
	do_action( 'ltple_layer_loaded', array($layer) );
	