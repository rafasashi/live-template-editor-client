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
	
	$cssLibraries = get_post_meta( $layer_id, 'cssLibraries', true );
	
	if(empty($cssLibraries)){
		
		$cssLibraries = [];
	}
	
	//get js libraries
	
	$jsLibraries = get_post_meta( $layer_id, 'jsLibraries', true );
	
	if(empty($jsLibraries)){
		
		$jsLibraries = [];
	}
	
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
					
					if( $u[0] == '/' ){
						
						$link->setAttribute( $attr,  $parse['scheme'].'://'.$parse['host']. $u );
					}
					elseif( $u[0].$u[1] == './'){
						
						$link->setAttribute( $attr,  dirname($source) . substr($u, 2) );
					}
					elseif($u[0].$u[1].$u[2] == '../'){
						
						$link->setAttribute( $attr,  dirname(dirname($source)) . substr($u, 2) );
					}
					else{
						
						$link->setAttribute( $attr,  $parse['scheme'].'://'.$parse['host']. '/' . $u );
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

				if( is_array($cssLibraries) ){
					
					if( in_array('bootstrap-3',$cssLibraries)|| !empty($layerForm) ){
						
						echo '<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>';
					}

					if( in_array('fontawesome-4',$cssLibraries)){
						
						echo '<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>';
					}
					
					if( in_array('elementor-1.2.3',$cssLibraries) ){

						// elementor
						
						echo '<link href="' . plugins_url('elementor/assets/css/animations.min.css?ver=1.0.1') . '" rel="stylesheet" type="text/css"/>';
						echo '<link href="' . plugins_url('elementor/assets/css/frontend.min.css?ver=1.0.1') . '" rel="stylesheet" type="text/css"/>';
						
						//echo '<link href="' . plugins_url('elementor/assets/css/global.css?ver=1.0.1') . '" rel="stylesheet" type="text/css"/>';
						echo '<style>.elementor-widget-heading .elementor-heading-title{color:#6ec1e4;font-family:Roboto,sans-serif;font-weight:600}.elementor-widget-image .widget-image-caption{color:#7a7a7a;font-family:Roboto,sans-serif;font-weight:400}.elementor-widget-text-editor{color:#7a7a7a;font-family:Roboto,sans-serif;font-weight:400}.elementor-widget-button .elementor-button{font-family:Roboto,sans-serif;font-weight:500;background-color:#61ce70}.elementor-widget-divider .elementor-divider-separator{border-top-color:#7a7a7a}.elementor-widget-image-box .elementor-image-box-content .elementor-image-box-title{color:#6ec1e4;font-family:Roboto,sans-serif;font-weight:600}.elementor-widget-image-box .elementor-image-box-content .elementor-image-box-description{color:#7a7a7a;font-family:Roboto,sans-serif;font-weight:400}.elementor-widget-icon.elementor-view-stacked .elementor-icon{background-color:#6ec1e4}.elementor-widget-icon.elementor-view-framed .elementor-icon,.elementor-widget-icon.elementor-view-default .elementor-icon{color:#6ec1e4;border-color:#6ec1e4}.elementor-widget-icon-box.elementor-view-stacked .elementor-icon{background-color:#6ec1e4}.elementor-widget-icon-box.elementor-view-framed .elementor-icon,.elementor-widget-icon-box.elementor-view-default .elementor-icon{color:#6ec1e4;border-color:#6ec1e4}.elementor-widget-icon-box .elementor-icon-box-content .elementor-icon-box-title{color:#6ec1e4;font-family:Roboto,sans-serif;font-weight:600}.elementor-widget-icon-box .elementor-icon-box-content .elementor-icon-box-description{color:#7a7a7a;font-family:Roboto,sans-serif;font-weight:400}.elementor-widget-image-gallery .gallery-item .gallery-caption{font-family:Roboto,sans-serif;font-weight:500}.elementor-widget-image-carousel .elementor-image-carousel-caption{font-family:Roboto,sans-serif;font-weight:500}.elementor-widget-icon-list .elementor-icon-list-icon i{color:#6ec1e4}.elementor-widget-icon-list .elementor-icon-list-text{color:#54595f;font-family:Roboto,sans-serif;font-weight:400}.elementor-widget-counter .elementor-counter-number-wrapper{color:#6ec1e4;font-family:Roboto,sans-serif;font-weight:600}.elementor-widget-counter .elementor-counter-title{color:#54595f;font-family:Roboto\ Slab,sans-serif;font-weight:400}.elementor-widget-progress .elementor-progress-wrapper .elementor-progress-bar{background-color:#6ec1e4}.elementor-widget-progress .elementor-title{color:#6ec1e4;font-family:Roboto,sans-serif;font-weight:400}.elementor-widget-testimonial .elementor-testimonial-content{color:#7a7a7a;font-family:Roboto,sans-serif;font-weight:400}.elementor-widget-testimonial .elementor-testimonial-name{color:#6ec1e4;font-family:Roboto,sans-serif;font-weight:600}.elementor-widget-testimonial .elementor-testimonial-job{color:#54595f;font-family:Roboto\ Slab,sans-serif;font-weight:400}.elementor-widget-tabs .elementor-tab-title{color:#6ec1e4;font-family:Roboto,sans-serif;font-weight:600}.elementor-widget-tabs .elementor-tab-title.active{color:#61ce70}.elementor-widget-tabs .elementor-tab-content{color:#7a7a7a;font-family:Roboto,sans-serif;font-weight:400}.elementor-widget-accordion .elementor-accordion .elementor-accordion-title{color:#6ec1e4;font-family:Roboto,sans-serif;font-weight:600}.elementor-widget-accordion .elementor-accordion .elementor-accordion-title.active{color:#61ce70}.elementor-widget-accordion .elementor-accordion .elementor-accordion-content{color:#7a7a7a;font-family:Roboto,sans-serif;font-weight:400}.elementor-widget-toggle .elementor-toggle .elementor-toggle-title{color:#6ec1e4;font-family:Roboto,sans-serif;font-weight:600}.elementor-widget-toggle .elementor-toggle .elementor-toggle-title.active{color:#61ce70}.elementor-widget-toggle .elementor-toggle .elementor-toggle-content{color:#7a7a7a;font-family:Roboto,sans-serif;font-weight:400}.elementor-widget-alert .elementor-alert-title{font-family:Roboto,sans-serif;font-weight:600}.elementor-widget-alert .elementor-alert-description{font-family:Roboto,sans-serif;font-weight:400}</style>';
					}
					
					if( in_array('animate',$cssLibraries)){
						
						echo '<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css" rel="stylesheet" type="text/css"/>';
					}
					
					if( in_array('slick',$jsLibraries) || in_array('elementor-1.2.3',$cssLibraries) ){
						
						echo '<link href="http://cdn.jsdelivr.net/jquery.slick/1.6.0/slick.css" rel="stylesheet" type="text/css"/>';
						//echo '<link href="http://cdn.jsdelivr.net/jquery.slick/1.6.0/slick-theme.css" rel="stylesheet" type="text/css"/>';
					
						echo '<style>.slick-slide{height:auto !important;}</style>';
					}
				}				
			}
			
			// font library
			
			echo '<link href="https://fonts.googleapis.com/css?family='.implode('|',$googleFonts).'" rel="stylesheet">';
			
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

			if(	is_array($jsLibraries) ){
			
				if( in_array('jquery',$jsLibraries) || !empty($layerForm)){
					
					echo '<script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>' .PHP_EOL;
				}
				
				if( in_array('bootstrap-3',$jsLibraries) || !empty($layerForm)){
					
					echo '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>' .PHP_EOL;
				
					?>	
					
					<script>
					
					;(function($){
						
						$(document).ready(function(){						
										
							$('.modal').appendTo("body");
							
							$('[data-slide-to]').on('click',function(e){

								e.preventDefault();
							
								if( typeof $(this).attr('data-target') !== typeof undefined ){
									
									var carouselId 	= $(this).attr('data-target');
								}
								else{
									
									var carouselId 	= $(this).attr("href");
								}
								
								var slideTo 	= parseInt( $(this).attr('data-slide-to') );
								
								$(carouselId).carousel(slideTo);
								
								return false;
								
							});
							
							$('[data-slide]').on('click',function(e){

								e.preventDefault();
							
								if( typeof $(this).attr('data-target') !== typeof undefined ){
									
									var carouselId 	= $(this).attr('data-target');
								}
								else{
									
									var carouselId 	= $(this).attr("href");
								}

								var slideTo 	= $(this).attr('data-slide');
								
								$(carouselId).carousel(slideTo);
								
								return false;
								
							});
						});
						
					})(jQuery);	
					
					</script>
					
					<?php
				}

				if( in_array('slick',$jsLibraries) || in_array('elementor-1.2.3',$cssLibraries) ){
					
					//echo '<script src="http://cdn.jsdelivr.net/jquery.slick/1.6.0/slick.min.js"></script>' .PHP_EOL;
					echo '<script src="' . plugins_url('elementor/assets/lib/slick/slick.min.js?ver=1.6.0') . '"></script>' .PHP_EOL;			
					
				}
				
				if( in_array('elementor-1.2.3',$cssLibraries) ){
					
					?>
					
					<script type='text/javascript'>//<![CDATA[
						var elementorFrontendConfig={"isEditMode":"","stretchedSectionContainer":"","is_rtl":""};
					//]]></script>
					
					<?php
				
					echo '<script src="' . plugins_url('elementor/assets/lib/waypoints/waypoints.min.js?ver=4.0.2') . '"></script>' .PHP_EOL;
					echo '<script src="' . plugins_url('elementor/assets/lib/jquery-numerator/jquery-numerator.min.js?ver=0.2.1') . '"></script>' .PHP_EOL;
					echo '<script src="' . plugins_url('elementor/assets/js/frontend.min.js?ver=1.2.3') . '"></script>' .PHP_EOL;								
				
					?>	
					
					<script>
					
					;(function($){
						
						$(document).ready(function(){						

							//$('.slick-slider').slick("unslick"); // works
						});
						
					})(jQuery);	
					
					</script>
					
					<?php					
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