<?php 

	//get current layer id

	if( $post->post_type == 'user-layer' ){
	
		$layer_id = intval(get_post_meta( $post->ID, 'defaultLayerId', true ));
	}
	else{
		
		$layer_id = $post->ID;
	}
	
	//get page def
	
	$pageDef = get_post_meta( $layer_id, 'pageDef', true );
	
	//get output config
	
	$layerOutput = get_post_meta( $layer_id, 'layerOutput', true );
	
	//get layer options
	
	$layerOptions = get_post_meta( $layer_id, 'layerOptions', true );
	
	//get layer form
	
	$layerForm = get_post_meta( $layer_id, 'layerForm', true );

	//get css libraries

	$cssLibraries = wp_get_post_terms( $layer_id, 'css-library' );
	
	//get js libraries
	
	$jsLibraries = wp_get_post_terms( $layer_id, 'js-library' );

	//get layer image proxy
	
	$layerImgProxy = 'http://'.$_SERVER['HTTP_HOST'].'/image-proxy.php?url=';
	
	$layerHead 		= '';
	$layerContent 	= '';
	$layerCss 		= '';
	$layerMargin	= '';
	$layerMinWidth	= '';
	
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
		
		$parse = parse_url($source);
		
		$elements = array(
		
			'link' 	=> 'href',
			'a' 	=> 'href',
			'img' 	=> 'src',
			'script'=> 'src',
		);
		
		foreach( $elements as $tagname => $attr){
		
			foreach($dom->getElementsByTagName($tagname) as $link) {
			
				$u = $link->getAttribute($attr);

				if( !empty($u) && $u[0] != '#' && parse_url($u, PHP_URL_SCHEME) == ''){
					
					if( !empty($u[1]) && $u[0].$u[1] == '//'){

						$link->setAttribute( $attr,  $parse['scheme'].'://'.substr($u, 2) );
					}
					elseif( $u[0] == '/' ){
						
						$link->setAttribute( $attr,  $parse['scheme'].'://'.$parse['host']. $u );
					}
					elseif( !empty($u[1]) && $u[0].$u[1] == './'){
						
						$link->setAttribute( $attr,  dirname($source) . substr($u, 2) );
					}
					elseif( !empty($u[1]) && !empty($u[2]) && $u[0].$u[1].$u[2] == '../'){
						
						$link->setAttribute( $attr,  dirname(dirname($source)) . substr($u, 2) );
					}
					elseif( substr($source, -1) == '/' ){
						
						$link->setAttribute( $attr,  $source . $u );
					}
					else{
						
						$link->setAttribute( $attr,  dirname($source) . '/' . $u );
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
		
		$layerMargin = get_post_meta( $layer_id, 'layerMargin', true );
		
		if( empty($layerMargin) ){
			
			$layerMargin = '-120px 0px -20px 0px';
		}
		
		$layerMinWidth = get_post_meta( $layer_id, 'layerMinWidth', true );
		
		if( empty($layerMinWidth) ){
			
			$layerMinWidth = '1000px';
		}		
		
		//get layer content
		
		if( $post->post_type != 'user-layer' && isset($_POST['importHtml']) ){

			$layerContent = $_POST['importHtml'];
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
			
			if( $layerCss == '' && $post->ID != $layer_id){
				
				$layerCss = get_post_meta( $layer_id, 'layerCss', true );
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
		
			if( !empty($layerHead) ){
				
				echo $layerHead;
			}
			else{
				
				echo '<title>'.ucfirst($post->post_title).'</title>';
				
				echo '<!-- Le HTML5 shim, for IE6-8 support of HTML elements -->';
				echo '<!--[if lt IE 9]>';
				echo '<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>';
				echo '<![endif]-->';

				if( !empty($cssLibraries) ){
					
					foreach($cssLibraries as $term){
						
						$css_url = get_option( 'css_url_' . $term->slug);
 
						if( !empty($css_url) ){
							
							echo '<link href="'.$css_url.'" rel="stylesheet" type="text/css"/>';
						}
						
						$css_content = get_option( 'css_content_' . $term->slug);
						
						if( !empty($css_content) ){
						
							echo $css_content;
						}
					}
				}				
			}
			
			// font library
			
			if( !empty($googleFonts) ){
			
				echo '<link href="https://fonts.googleapis.com/css?family='.implode('|',$googleFonts).'" rel="stylesheet">';
			}
			
		echo '</head>';

		echo '<body style="padding:0;margin:0;display:flex !important;width:100%;">';
			
			//include style-sheet
			
			if( $layerCss!='' ){

				echo '<style id="LiveTplEditorStyleSheet">'.PHP_EOL;	
				
					echo $layerCss .PHP_EOL;
					
				echo '</style>'.PHP_EOL;					
			}
			
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

				echo '<layer class="editable" style="min-width:'.$layerMinWidth.';width:100%;margin:'.$layerMargin.';">';
								
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
					
						echo $js_content;
					}
				}
			}			

			echo'<script>' .PHP_EOL;

				//include layer Output
				
				if($layerOutput!=''){
					
					echo ' var layerOutput = "' . $layerOutput . '";' .PHP_EOL;
				}
				
				//include image proxy
				
				if($layerImgProxy!=''){
				
					echo ' var imgProxy = "' . $layerImgProxy . '";' .PHP_EOL;				
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
				
				if( is_array($cssLibraries) ){

					if( in_array('fontawesome-4',$cssLibraries)){
						
						$enableIcons = 'true';
					}
				}
				
				echo ' var enableIcons = '.$enableIcons.';' .PHP_EOL;
				
				//include medium editor
				
				//echo file_get_contents( trailingslashit(dirname(dirname( __FILE__ ))) . 'assets/js/medium-editor.custom.js' ).PHP_EOL;
				
			echo'</script>' .PHP_EOL;
			
		echo'</body>' .PHP_EOL;
		
	echo'</html>' .PHP_EOL;