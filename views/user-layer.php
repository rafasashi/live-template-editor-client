<!DOCTYPE>
<html>

    <head>
		
		<style>
		
			<?php echo file_get_contents( trailingslashit(dirname(dirname( __FILE__ ))) . 'assets/css/ltple-layer.css' ).PHP_EOL; ?>
			 
			<?php echo file_get_contents( trailingslashit(dirname(dirname( __FILE__ ))) . 'assets/css/medium-editor.min.css' ).PHP_EOL; ?>
			<?php echo file_get_contents( trailingslashit(dirname(dirname( __FILE__ ))) . 'assets/css/themes/bootstrap.min.css' ).PHP_EOL; ?>
		
		</style>
		
	</head>
    
	<body style="padding:0;margin:0;display:inline-block;width:1000px;">
		
		<?php 
		
		if ( have_posts() ) : while ( have_posts() ) : the_post();
			
			//get default layer id
			
			$layer_id=intval(get_post_meta( get_the_ID(), 'defaultLayerId', true ));
			
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
			
			if( !is_array($layerOptions) || !in_array('line-break',$layerOptions)){
				
				$disable_return = 'data-disable-return="true" data-disable-double-return="true" ';
			}		
			
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

			echo '<script>';

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
				
		endwhile; endif;
	?>
	
    </body>
</html>