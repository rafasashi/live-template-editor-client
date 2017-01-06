<?php 

if ( have_posts() ) : while ( have_posts() ) : the_post();

	$layer_type	= get_post_type();

	//get current layer id
	 
	if( $layer_type == 'user-layer' ){
	
		$layer_id=intval(get_post_meta( get_the_ID(), 'defaultLayerId', true ));
	}
	else{
		
		$layer_id=get_the_ID();
	}
	
	//get page def
	
	$pageDef = get_post_meta( $layer_id, 'pageDef', true );
	
	//get output config
	
	$layerOutput = get_post_meta( $layer_id, 'layerOutput', true );
	
	//get style-sheet
	
	$layerCss = get_post_meta( $layer_id, 'layerCss', true );
	
	//get layer margin
	
	$layerMargin = get_post_meta( $layer_id, 'layerMargin', true );
	
	if($layerMargin == ''){
		
		$layerMargin = '-120px 0px -20px 0px';
	}
	
	//get layer options
	
	$layerOptions = get_post_meta( $layer_id, 'layerOptions', true );
	
	//get css libraries
	
	$cssLibraries = get_post_meta( $layer_id, 'cssLibraries', true );
	
	//get js libraries
	
	$jsLibraries = get_post_meta( $layer_id, 'jsLibraries', true );
	
	//get layer image proxy
	
	$layerImgProxy = 'http://'.$_SERVER['HTTP_HOST'].'/image-proxy.php?url=';
					
	//get layer content
	
	$layerContent = get_the_content();
	$layerContent = str_replace(array('&quot;','cursor: pointer;'),'',$layerContent);
	
	if($layerOutput=='canvas'){
		
		// replace image sources
		
		$layerContent = str_replace(array('src =','src= "'),array('src=','src="'),$layerContent);
		$layerContent = str_replace(array($layerImgProxy,'src="'),array('','src="'.$layerImgProxy),$layerContent);			
		
		// replace background images
		
		$regex = '/(background(?:-image)?: ?url\((["|\']?))(.+)(["|\']?\))/';
		$layerContent = preg_replace($regex, "$1$layerImgProxy$3$4", $layerContent);					
	
		if(!empty($layerCss)){
			
			// replace background images
			
			$regex = '/(background(?:-image)?: ?url\((["|\']?))(.+)(["|\']?\))/';
			$layerCss = preg_replace($regex, "$1$layerImgProxy$3$4", $layerCss);				
		}
	}

	//get line break setting

	$disable_return = '';
	/*
	if( !is_array($layerOptions) || !in_array('line-break',$layerOptions)){
		
		$disable_return = 'data-disable-return="true" data-disable-double-return="true" ';
	}
	*/

	echo '<!DOCTYPE>';
	echo '<html>';

		echo '<head>';
			
			echo '<style>';
			
				//echo file_get_contents( trailingslashit(dirname(dirname( __FILE__ ))) . 'assets/css/ltple-layer.css' ).PHP_EOL;
				 
				echo file_get_contents( trailingslashit(dirname(dirname( __FILE__ ))) . 'assets/css/medium-editor.min.css' ).PHP_EOL;
				echo file_get_contents( trailingslashit(dirname(dirname( __FILE__ ))) . 'assets/css/themes/bootstrap.min.css' ).PHP_EOL;
			
			echo '</style>';
			
			if( is_array($cssLibraries) ){
				
				if( in_array('bootstrap-3',$cssLibraries)){
					
					echo '<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>';
				}				
			}
			
		echo '</head>';
		
		
		
		echo '<body style="padding:0;margin:0;display:inline-block;width:100%;">';
			
			//include style-sheet
			
			if($layerCss!=''){

				echo '<style id="LiveTplEditorStyleSheet">'.PHP_EOL;	
				
					echo $layerCss .PHP_EOL;
					
				echo '</style>'.PHP_EOL;					
			}
			
			//include layer
			
			echo '<layer '.$disable_return.'class="editable" style="width:100%;margin:'.$layerMargin.';">';
				
				echo $layerContent;

			echo '</layer>';
			
			if(	is_array($jsLibraries) ){
			
				if( in_array('jquery',$jsLibraries)){
					
					echo '<script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>';
				}
				
				if( in_array('bootstrap-3',$jsLibraries)){
					
					echo '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>';
				}
			}

			echo'<script>';
				
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
				
				//include medium editor
				
				//echo file_get_contents( trailingslashit(dirname(dirname( __FILE__ ))) . 'assets/js/medium-editor.custom.js' ).PHP_EOL;
				
			echo'</script>';
			
		echo'</body>';
		
	echo'</html>';		
				
break; 
endwhile; 
endif;