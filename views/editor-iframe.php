<?php
	
	$ltple = LTPLE_Client::instance();
	
	// get iframe url
	
	$iframe_url = $ltple->urls->edit . '?uri=' . $ltple->layer->id . '&lk=' . md5( 'layer' . $ltple->layer->id . $ltple->_time ) . '&_=' . $ltple->_time;

	// output editor iframe
	
	echo'<div class="loadingIframe" style="width: 100%;position: relative;background-position: 50% center;background-repeat: no-repeat;background-image:url(\''. $ltple->server->url .'/c/p/live-template-editor-server/assets/loader.gif\');height:64px;"></div>';
	
	echo'<iframe id="editorIframe" src="' . $iframe_url . '" style="margin-top: -65px;position: relative;width: 100%;top: 0;bottom: 0;border:0;height: 1300px;overflow: hidden;"></iframe>';

	//editor settings
		
	echo'<script id="LiveTplEditorSettings">' .PHP_EOL;
		
		if( $ltple->layer->layerOutput == 'image' ){
			
			if( $ltple->layer->layerImageTpl->post_type == 'attachment' ){
				
				$attachment_url = wp_get_attachment_url($ltple->layer->layerImageTpl->ID );
			}
			else{
				
				$attachment_url = trim(strip_tags(apply_filters('the_content',$ltple->layer->layerImageTpl->post_content)));		
			}
			
			echo ' var layerImageTpl = "' . $ltple->layer->layerImgProxy . urlencode($attachment_url) . '";' . PHP_EOL;
		}
		else{
			
			echo ' var layerContent = "' . base64_encode($ltple->layer->output_layer()) . '";' . PHP_EOL;
	
			if( $ltple->layer->layerOutput != '' ){
				
				echo ' var layerOutput = "' . $ltple->layer->layerOutput . '";' . PHP_EOL;
			}
			
			echo ' var layerSettings = ' . json_encode($ltple->layer->layerSettings) . ';' .PHP_EOL;
			
			//include image proxy
			
			if( $ltple->layer->layerImgProxy != '' ){
			
				echo ' var imgProxy = " ' . $ltple->layer->layerImgProxy . '";' . PHP_EOL;				
			}
			
			//include quick edit
			 
			if( isset($_GET['quick']) ){
				
				echo ' var quickEdit = true;' .PHP_EOL;
			}
			elseif( !$ltple->user->plan['info']['total_price_amount'] > 0 ){
				
				echo ' var quickEdit = true;' .PHP_EOL;
			}
			else{
				
				echo ' var quickEdit = false;' .PHP_EOL;
			}
			
			//include page def
			
			if( $ltple->layer->pageDef != '' ){
				
				echo ' var pageDef = ' . $ltple->layer->pageDef . ';' .PHP_EOL;
			}
			else{
				
				echo ' var pageDef = {};' .PHP_EOL;
			}
			
			//include line break setting

			if( !is_array( $ltple->layer->layerOptions ) ){
				
				echo ' var disableReturn 	= true;' .PHP_EOL;
				echo ' var autoWrapText 	= false;' .PHP_EOL;
			}
			else{
				
				if( !in_array('line-break',$ltple->layer->layerOptions) ){
					
					echo ' var disableReturn = true;' .PHP_EOL;
				}
				else{
					
					echo ' var disableReturn = false;' .PHP_EOL;
				}
				
				if(in_array('wrap-text',$ltple->layer->layerOptions)){
					
					echo ' var autoWrapText = true;' .PHP_EOL;
				}
				else{ 
					
					echo ' var autoWrapText = false;' .PHP_EOL;
				}
			}
			
			//include icon settings
			
			$enableIcons = 'false';
			
			if( in_array_field( 'font-awesome-4-7-0', 'slug', $ltple->layer->layerCssLibraries ) ){
				
				$enableIcons = 'true';
			}
			
			echo ' var enableIcons = '.$enableIcons.';' .PHP_EOL;
			
			//include forms
			
			if( $ltple->layer->layerForm == 'importer' ){
				
				echo ' var layerForm = "' . $ltple->layer->layerForm . '";';
			}
		}
		
	echo'</script>' . PHP_EOL;