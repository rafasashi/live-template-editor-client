<?php 

	//get page def
	
	$pageDef = get_post_meta( $post->layer_id, 'pageDef', true );
	
	//get output config
	
	$layerOutput = get_post_meta( $post->layer_id, 'layerOutput', true );
	
	//get layer options
	
	$layerOptions = get_post_meta( $post->layer_id, 'layerOptions', true );
	
	//get layer settings
	
	$layerSettings = get_post_meta( $post->ID, 'layerSettings', true );
	
	//get layer embedded
	
	$layerEmbedded = get_post_meta( $post->ID, 'layerEmbedded', true );	
	
	//get layer form
	
	$layerForm = get_post_meta( $post->layer_id, 'layerForm', true );
	

	
	//get css libraries

	$cssLibraries = wp_get_post_terms( $post->layer_id, 'css-library', array( 'orderby' => 'term_id' ) );
	
	//get js libraries
	
	$jsLibraries = wp_get_post_terms( $post->layer_id, 'js-library', array( 'orderby' => 'term_id' ) );

	//get layer image proxy
	
	$layerImgProxy = 'http://'.$_SERVER['HTTP_HOST'].'/image-proxy.php?url=';
	
	$layerHead 		= '';
	$layerContent 	= '';
	$layerCss 		= '';
	$layerJs 		= '';
	$layerMeta 		= '';
	$layerMargin	= '';
	$layerMinWidth	= '';
	$layerSources	= [];
	
	if( $post->post_type != 'user-layer' && isset($_POST['scrapeUrl']) ){

		$source = urldecode($_POST['scrapeUrl']);
	
		$ch = curl_init($source);
		curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT,10);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$output = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		// parse dom elements
		
		libxml_use_internal_errors( true );
		
		$dom= new DOMDocument();
		$dom->loadHTML('<?xml encoding="UTF-8">' . $output);  
		
		// absolute urls to relative

		$elements = array(
		
			'link' 	=> 'href',
			'a' 	=> 'href',
			'img' 	=> 'src',
			'script'=> 'src',
		);

		foreach( $elements as $tagname => $attr){
		
			foreach($dom->getElementsByTagName($tagname) as $link) {
				
				$u = $link->getAttribute($attr);
				$u = LTPLE_Client::get_absolute_url( $u, $source );
				
				$link->setAttribute( $attr, $u );
				
				if( $tagname == 'link' || $tagname == 'script' ){
					
					if( !empty($u) ){

						$layerSources[$tagname][] = $u;						
					}
					elseif( $tagname == 'link'){
						
						$layerCss .= PHP_EOL . $link->nodeValue;
					}
					elseif( $tagname == 'script' ){

						$layerJs .= PHP_EOL . $link->nodeValue;
					}
				}
			}
		}		
		
		$xpath = new DOMXPath($dom);
		
		// get head
		
		$layerHead = $dom->saveHtml( $xpath->query('/html/head')->item(0) );			
		$layerHead = preg_replace('~<(?:!DOCTYPE|/?(?:head))[^>]*>\s*~i', '', $layerHead);
		
		// get body
		
		$layerContent = $dom->saveHtml( $xpath->query('/html/body')->item(0) );
		$layerContent = preg_replace('~<(?:!DOCTYPE|/?(?:body))[^>]*>\s*~i', '', $layerContent);
	}
	else{
		
		//get layer margin
		
		$layerMargin = get_post_meta( $post->layer_id, 'layerMargin', true );
		
		if( empty($layerMargin) ){
			
			$layerMargin = '-120px 0px -20px 0px';
		}
		
		$layerMinWidth = get_post_meta( $post->layer_id, 'layerMinWidth', true );
		
		if( empty($layerMinWidth) ){
			
			$layerMinWidth = '1000px';
		}		
		
		//get layer content
		
		if( $post->post_type != 'user-layer' && isset($_POST['importHtml']) ){

			$layerContent = $_POST['importHtml'];
		}
		elseif( !empty($post->post_content) ){
			
			$layerContent = $post->post_content;
		}
		elseif( $post->layer_id != $post->ID ){
			
			if( $layer = get_post( $post->layer_id ) ){
			
				$layerContent = $layer->post_content;
			}
		}
		else{

			$layerContent = $post->post_content;
		}
		
		$layerContent = LTPLE_Client_Layer::sanitize_content($layerContent);
		
		//get style-sheet
		
		if( $post->post_type != 'user-layer' && isset($_POST['importCss']) ){

			$layerCss = stripcslashes($_POST['importCss']);
		}
		elseif(empty($_POST)){
			
			$layerCss = get_post_meta( $post->ID, 'layerCss', true );
			
			if( $layerCss == '' && $post->ID != $post->layer_id){
				
				$layerCss = get_post_meta( $post->layer_id, 'layerCss', true );
			}
			
			$layerJs = get_post_meta( $post->ID, 'layerJs', true );
			
			if( $layerJs == '' && $post->ID != $post->layer_id){
				
				$layerJs = get_post_meta( $post->layer_id, 'layerJs', true );
			}
			
			$layerMeta = get_post_meta( $post->ID, 'layerMeta', true );
			
			if( $layerMeta == '' && $post->ID != $post->layer_id){
				
				$layerMeta = get_post_meta( $post->layer_id, 'layerMeta', true );
			}
			
			if(!empty($layerMeta)){
				
				$layerMeta = json_decode($layerMeta,true);
			}
		}

		$layerCss = sanitize_text_field($layerCss);
		
		if($layerOutput=='canvas'){
			
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
	}
	
	// get google fonts
	
	$googleFonts = [];
	
	if(!empty($layerCss)){
		
		$regex = '`https\:\/\/fonts\.googleapis\.com\/css\?family=([A-Za-z\|]+)`';
		$fonts = preg_match($regex, $layerCss,$match);
		
		if(isset($match[1])){
			
			$googleFonts = explode('|',$match[1]);
		}
	}

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
		
			if( !empty($cssLibraries) ){
				
				foreach($cssLibraries as $term){
					
					$css_url = get_option( 'css_url_' . $term->slug);

					if( !empty($css_url) ){
						
						echo '<link href="'.$css_url.'" rel="stylesheet" type="text/css" />';
					}
					
					$css_content = get_option( 'css_content_' . $term->slug);
					
					if( !empty($css_content) ){
					
						echo $css_content;
					}
				}
			}
			
			echo PHP_EOL;
		
			if( !empty($layerHead) ){
				
				echo $layerHead;
			}
			else{
				
				// output custom meta tags
				
				if( !empty($layerSettings) ){
					
					foreach($layerSettings as $key => $content){
						
						if( $key == 'meta_title' ){
							
							$content = ucfirst($content);
							
							echo '<title>'.$content.'</title>'.PHP_EOL;
							echo '<meta name="subject" content="'.$content.'" />'.PHP_EOL;
							echo '<meta property="og:title" content="'.$content.'" />'.PHP_EOL;
							echo '<meta name="twitter:title" content="'.$content.'" />'.PHP_EOL;		
						}
						elseif( $key == 'meta_keywords' ){

							$content = implode(',',explode(PHP_EOL,$content));
						
							echo '<meta name="keywords" content="'.$content.'" />'.PHP_EOL;
							
						}
						elseif( $key == 'meta_description' ){
							
							echo '<meta name="description" content="'.$content.'" />'.PHP_EOL;
							echo '<meta name="abstract" content="'.$content.'" />' . PHP_EOL;
							echo '<meta name="summary" content="'.$content.'" />' . PHP_EOL;
							echo '<meta property="og:description" content="'.$content.'" />' . PHP_EOL;
							echo '<meta name="twitter:description" content="'.$content.'" />'.PHP_EOL;
						}
						elseif( $key == 'link_author' ){
							
							echo '<link rel="author" href="'.$content.'" />'.PHP_EOL;
							echo '<link rel="publisher" href="'.$content.'" />'.PHP_EOL;
						}
						elseif( $key == 'meta_image' ){
							
							echo '<meta property="og:image" content="'.$content.'" />'.PHP_EOL;
							echo '<meta name="twitter:image" content="'.$content.'" />'.PHP_EOL;
							
						}
						elseif( $key == 'meta_facebook-id' ){
							
							echo '<meta property="fb:admins" content="'.$content.'"/>'.PHP_EOL;
							
						}				
						else{
							
							list($markup,$name) = explode('_',$key);
							
							if( $markup == 'meta' ){
								
								echo '<meta name="'.$name.'" content="'.$content.'" />'.PHP_EOL;
							}
							elseif( $markup == 'link' ){
								
								echo '<link rel="'.$name.'" href="'.$content.'" />'.PHP_EOL;
							}
						}
					}
				}
				
				// output default meta tags
				
				$title = ucfirst($post->post_title);
				
				if( empty($layerSettings['meta_title']) ){
					
					echo '<title>'.$title.'</title>'.PHP_EOL;
					echo '<meta name="subject" content="'.$title.'" />'.PHP_EOL;
					echo '<meta property="og:title" content="'.$title.'" />'.PHP_EOL;
					echo '<meta name="twitter:title" content="'.$title.'" />'.PHP_EOL;					
				}
				
				$author_name = get_the_author_meta('display_name', $post->post_author );
				$author_mail = get_the_author_meta('user_email', $post->post_author );
				
				if( empty($layerSettings['meta_author']) ){
					
					echo '<meta name="author" content="'.$author_name.', '.$author_mail.'" />' . PHP_EOL;
					echo '<meta name="creator" content="'.$author_name.', '.$author_mail.'" />' . PHP_EOL;
					echo '<meta name="owner" content="' . $author_name . '" />' . PHP_EOL;
					echo '<meta name="reply-to" content="'.$author_mail.'" />' . PHP_EOL;					
				}
				
				$locale = get_locale();
				
				if( empty($layerSettings['meta_language']) ){
					
					echo '<meta name="language" content="' . $locale . '" />'.PHP_EOL;
				}
				
				$robots = 'index,follow';
				
				if( empty($layerSettings['meta_robots']) ){
					
					echo '<meta name="robots" content="'.$robots.'" />' . PHP_EOL;
				}
				
				$revised = $post->post_date;
				
				if( empty($layerSettings['meta_revised']) ){
				
					echo '<meta name="revised" content="' . $revised . '" />' . PHP_EOL;
				}
				
				$content = ucfirst($post->post_title);
				
				if( empty($layerSettings['meta_description']) ){
					
					echo '<meta name="description" content="'.$content.'" />'.PHP_EOL;
					echo '<meta name="abstract" content="'.$content.'" />' . PHP_EOL;
					echo '<meta name="summary" content="'.$content.'" />' . PHP_EOL;
					echo '<meta property="og:description" content="'.$content.'" />' . PHP_EOL;
					echo '<meta name="twitter:description" content="'.$content.'" />'.PHP_EOL;
				}
				
				echo '<meta name="classification" content="Business" />' . PHP_EOL;
				//echo '<meta name="classification" content="products, product classifications, company classification, company type, industry" />' . PHP_EOL;
				
				$service_name = get_bloginfo( 'name' );
				
				echo '<meta name="copyright" content="'.$service_name.'" />'.PHP_EOL;
				echo '<meta name="designer" content="'.$service_name.' team" />' . PHP_EOL;
				
				if( !empty($layerEmbedded) ){
				
					$url = $layerEmbedded;
					
					echo '<meta name="url" content="'.$url.'" />' . PHP_EOL;
					//echo '<meta name="canonical" content="'.$url.'" />' . PHP_EOL;
					echo '<meta name="original-source" content="'.$url.'" />' . PHP_EOL;
					echo '<link rel="original-source" href="'.$url.'" />' . PHP_EOL;
					echo '<meta property="og:url" content="'.$url.'" />' . PHP_EOL;
					echo '<meta name="twitter:url" content="'.$url.'" />' . PHP_EOL;
				}
				
				echo '<meta name=viewport content="width=device-width, initial-scale=1">' . PHP_EOL;
				
				echo '<meta name="rating" content="General" />' . PHP_EOL;
				echo '<meta name="directory" content="submission" />' . PHP_EOL;
				echo '<meta name="coverage" content="Worldwide" />' . PHP_EOL;
				echo '<meta name="distribution" content="Global" />' . PHP_EOL;
				echo '<meta name="target" content="all" />' . PHP_EOL;
				echo '<meta name="medium" content="blog" />' . PHP_EOL;
				echo '<meta property="og:type" content="article" />' . PHP_EOL;
				echo '<meta name="twitter:card" content="summary" />' . PHP_EOL;
				
				/*
				echo '<meta name="geo.position" content="latitude; longitude" />' . PHP_EOL;
				echo '<meta name="geo.placename" content="Place Name" />' . PHP_EOL;
				echo '<meta name="geo.region" content="Country Subdivision Code" />' . PHP_EOL;
				*/
			}

			if(!empty($layerMeta['link'])){
				
				foreach($layerMeta['link'] as $source){
					
					echo '<link href="'.$source.'" rel="stylesheet" type="text/css" />';
				}
			}			
			
			// font library
			
			if( !empty($googleFonts) ){
			
				echo '<link href="https://fonts.googleapis.com/css?family='.implode('|',$googleFonts).'" rel="stylesheet">';
			}
			
		echo '</head>';

		echo '<body style="padding:0;margin:0;display:flex !important;width:100%;">';
			
			//include style-sheet
			
			echo '<style id="LiveTplEditorStyleSheet">'.PHP_EOL;
			
			if( $layerCss!='' ){

				echo $layerCss .PHP_EOL;
			}
				
			echo '</style>'.PHP_EOL;		
			
			//include layer
			
			if( $post->post_type != 'user-layer' && empty($_POST) && !empty($layerForm) && $layerForm != 'none' ){
				
				echo '<div class="container">';
				
					echo '<div class="panel panel-default" style="margin:50px;">';
					
					echo '<div class="panel-heading">';
					
						if( !empty($layerForm) ){
							
							echo'<h4>'.ucfirst($post->post_title).'</h4>';
						}
						
					echo '</div>';
					
					echo '<div class="panel-body">';
					
						echo '<form target="_self" action="" method="post" style="width:100%;background:#FFFFFF;">';
						
							if( $layerForm == 'importer' ){
						
								echo '<div class="col-xs-3">';
								
									echo'<label>HTML</label>';
									
								echo '</div>';
								
								echo '<div class="col-xs-9">';
								
									echo '<div class="form-group">';
									
										echo '<textarea class="form-control" name="importHtml" style="min-height:100px;"></textarea>';
										
									echo '</div>';
									
								echo '</div>';
								
								if( $layerOutput == 'external-css' ){
									
									echo '<div class="col-xs-3">';
									
										echo'<label>CSS</label>';
										
									echo '</div>';
									
									echo '<div class="col-xs-9">';
									
										echo '<div class="form-group">';
										
											echo '<textarea class="form-control" name="importCss" style="min-height:100px;"></textarea>';
											
										echo '</div>';
										
									echo '</div>';									
								}

								echo '<div class="col-xs-12 text-right">';
									
									echo '<input class="btn btn-primary btn-md" type="submit" value="Import" />';
									
								echo '</div>';
							}
							elseif( $layerForm == 'scraper' ){
						
								echo '<div class="col-xs-3">';
								
									echo'<label>Page Url</label>';
									
								echo '</div>';
								
								echo '<div class="col-xs-9">';
								
									echo '<div class="form-group">';
									
										echo '<input type="text" placeholder="http://" class="form-control" name="scrapeUrl"/>';
										
									echo '</div>';
									
								echo '</div>';

								echo '<div class="col-xs-12 text-right">';
									
									echo '<input class="btn btn-primary btn-md" type="submit" value="Scrape" />';
									
								echo '</div>';
							}							
						
						echo '</form>';
						
					echo '</div>';
					echo '</div>';
				
				echo '</div>';
			} 
			else{

				if( $layerForm == 'scraper' ){
					
					echo'<div id="scrapeLayer" title="Scrape Layer" style="display:none;z-index:10000;">';
						 
						echo '<form target="_self" action="" method="post" style="width:100%;background:#FFFFFF;">';
							
							echo '<div class="col-xs-3">';
							
								echo'<label style="font-size:12px;">Page Url</label>';
								
							echo '</div>';
							
							echo '<div class="col-xs-9">';
							
								echo '<div class="form-group">';
								
									echo '<input type="text" placeholder="http://" class="form-control" name="scrapeUrl" value="'.$_POST['scrapeUrl'].'"/>';
									
								echo '</div>';
								
							echo '</div>';

							echo '<div class="col-xs-12 text-right">';
								
								echo '<input class="btn btn-primary btn-xs" type="submit" value="Scrape" />';
								
							echo '</div>';
							
						echo'</form>';			
						
					echo'</div>';
				}

				//echo '<layer class="editable" style="min-width:'.$layerMinWidth.';width:100%;margin:'.$layerMargin.';">';
				echo '<layer class="editable" style="width:100%;margin:'.$layerMargin.';">';
								
					echo $layerContent;
				
				echo '</layer>' .PHP_EOL;
			}	

			if( !empty($jsLibraries) ){
				
				foreach($jsLibraries as $term){
					
					$js_url = get_option( 'js_url_' . $term->slug);
					
					if( !empty($js_url) ){
						
						echo '<script src="'.$js_url.'"></script>' .PHP_EOL;
					}
					
					$js_content = get_option( 'js_content_' . $term->slug);
					
					if( !empty($js_content) ){
					
						echo $js_content .PHP_EOL;	
					}
				}
			}
			
			if( !empty($layerMeta['script']) ){
				
				foreach($layerMeta['script'] as $source){
					
					echo '<script src="'.$source.'"></script>' .PHP_EOL;
				}
			}
			
			//include layer script
			
			echo'<script id="LiveTplEditorScript">' .PHP_EOL;
			
				if( $layerJs != '' ){

					echo $layerJs .PHP_EOL;				
				}				
				
			echo'</script>' .PHP_EOL;

			//include layer Output
			
			echo'<script>' .PHP_EOL;

				if($layerOutput!=''){
					
					echo ' var layerOutput = "' . $layerOutput . '";' .PHP_EOL;
				}
				
				echo ' var layerSettings = ' . json_encode($layerSettings) . ';' .PHP_EOL;
				
				//include image proxy
				
				if($layerImgProxy!=''){
				
					echo ' var imgProxy = " ' . $layerImgProxy . '";' .PHP_EOL;				
				}
				
				//include page def
				
				if($pageDef!=''){
					
					echo ' var pageDef = ' . $pageDef . ';' .PHP_EOL;
				}
				else{
					
					echo ' var pageDef = {};' .PHP_EOL;
				}
				
				//include  line break setting

				if( !is_array($layerOptions) ){
					
					echo ' var disableReturn 	= true;' .PHP_EOL;
					echo ' var autoWrapText 	= false;' .PHP_EOL;
				}
				else{
					
					if(!in_array('line-break',$layerOptions)){
						
						echo ' var disableReturn = true;' .PHP_EOL;
					}
					else{
						
						echo ' var disableReturn = false;' .PHP_EOL;
					}
					
					if(in_array('wrap-text',$layerOptions)){
						
						echo ' var autoWrapText = true;' .PHP_EOL;
					}
					else{ 
						
						echo ' var autoWrapText = false;' .PHP_EOL;
					}
				}
				
				//include icon settings
				
				$enableIcons = 'false';
				
				if( in_array_field( 'font-awesome-4-7-0', 'slug', $cssLibraries ) ){
					
					$enableIcons = 'true';
				}
				
				echo ' var enableIcons = '.$enableIcons.';' .PHP_EOL;
				
				//include list of external sources
				
				if( !empty($layerSources) ){
					
					echo ' var layerSources = ' . json_encode($layerSources) . ';' .PHP_EOL;
				}
				else{
					
					echo ' var layerSources = {};' .PHP_EOL;
				}
				
				//include medium editor
				
				//echo file_get_contents( trailingslashit(dirname(dirname( __FILE__ ))) . 'assets/js/medium-editor.custom.js' ).PHP_EOL;			
						
			echo'</script>' .PHP_EOL;
			
		echo'</body>' .PHP_EOL;
		
	echo'</html>' .PHP_EOL;