<?php
	
	$output = ( !empty($_GET['output']) ? '&output='. sanitize_text_field($_GET['output']) : '' );
		
	if( !empty($_SESSION['message']) ){
		
		echo $_SESSION['message'];
		
		$_SESSION['message'] = '';
	}
	elseif( ( !$this->user->is_editor || !isset($_GET['edit']) ) && !isset($_GET['quick']) && $this->layer->type == 'cb-default-layer' && $this->user->plan["info"]["total_price_amount"] > 0 ){
	
		echo'<div class="col-xs-12 col-sm-12 col-lg-8" style="padding:20px;min-height:500px;">';
			
			echo '<h2>Start a new project</h2>';
			
			if( $this->layer->layerOutput == 'canvas' ){
				
				echo'<hr>';
				
				echo'<a href="'.add_query_arg('quick','',$this->urls->current).'" class="btn btn-lg btn-primary" style="margin: 15px 15px 0px 15px;">Start a quick collage</a>';
			}
			elseif( $this->layer->layerOutput == 'image' ){
				
				echo'<hr>';
				
				echo'<a href="'.add_query_arg('quick','',$this->urls->current).'" class="btn btn-lg btn-primary" style="margin: 15px 15px 0px 15px;">Edit image ( without saving )</a>';
			}
			elseif( $this->layer->layerOutput == 'inline-css' || $this->layer->layerOutput == 'external-css' ){
				
				echo'<hr>';
				
				echo'<a href="'.add_query_arg('quick','',$this->urls->current).'" class="btn btn-lg btn-primary" style="margin: 15px 15px 0px 15px;">Get the code ( without hosting )</a>';				
			}
			
			echo'<hr>';
			
			if( $this->plan->remaining_storage_amount($this->layer->id) > 0 ){
				
				// get editor url
				
				$editor_url = $this->urls->editor . '?uri=' . $this->layer->id . $output;			
				
				echo'<form class="col-xs-6" target="_parent" action="' . $editor_url . '" id="savePostForm" method="post">';
					
					echo'<div class="input-group">';					
						
						echo'<input type="text" name="postTitle" id="postTitle" value="" class="form-control input-lg required" placeholder="Project Title">';
						echo'<input type="hidden" name="postContent" id="postContent" value="">';

						/*
						echo'<input type="hidden" name="postCss" id="postCss" value="">';
						echo'<input type="hidden" name="postJs" id="postJs" value="">';
						echo'<input type="hidden" name="postSettings" id="postSettings" value="">';
						*/
						
						wp_nonce_field( 'user_layer_nonce', 'user_layer_nonce_field' );

						echo'<input type="hidden" name="submitted" id="submitted" value="true">';
						
						echo'<span class="input-group-btn">';

							echo'<input type="hidden" name="postAction" id="postAction" value="save">';
								
							echo'<input class="btn btn-lg btn-primary" type="submit" id="saveBtn" style="border-radius: 0 3px 3px 0;padding: 8px 15px;" value="Start" />';
						
						echo'</span>';
						
					echo'</div>';
					
				echo'</form>';
			}
			else{
				
				$layer_type = $this->layer->get_layer_type($this->layer->id);
				
				echo'<div class="alert alert-warning">';
					
					echo'You can\'t save more <b>' . $layer_type->name . '</b> projects with the current plan...';
			
				echo'</div>';				
			}	

		echo'</div>';
	}
	elseif( $this->layer->type == 'user-layer' && !$this->user->plan["info"]["total_price_amount"] > 0 ){
		
		echo'<div class="col-xs-12 col-sm-12 col-lg-8" style="padding:20px;min-height:500px;">';
			
			echo '<div class="alert alert-warning">You need a paid plan to edit this template...</div>';

		echo'</div>';
	}
	else{

		// ouput demo message
		
		if( $this->user->loggedin && $this->user->plan["info"]["total_price_amount"] == 0 ){
			
			//$this->get_demo_message();
		}			
		
		// get iframe url
		
		$iframe_url = $this->urls->editor . '?uri=' . $this->layer->id . '&lk=' . md5( 'layer' . $this->layer->id . $this->_time ) . '&_=' . $this->_time;

		// output editor iframe
		
		echo'<div class="loadingIframe" style="width: 100%;position: relative;background-position: 50% center;background-repeat: no-repeat;background-image:url(\''. $this->server->url .'/c/p/live-template-editor-server/assets/loader.gif\');height:64px;"></div>';
		
		echo'<iframe id="editorIframe" src="' . $iframe_url . '" style="margin-top: -65px;position: relative;width: 100%;top: 0;bottom: 0;border:0;height: 1300px;overflow: hidden;"></iframe>';
	}
	
	//---------- editor settings ---------------
		
	echo'<script id="LiveTplEditorSettings">' .PHP_EOL;
		
		if( $this->layer->layerOutput == 'image' ){
			
			echo ' var layerImageTpl = "' . $this->urls->home . '?t=' . $this->layer->id . '&_=' . time() . '";' . PHP_EOL;
		}
		else{
		
			if( $this->layer->layerOutput != '' ){
				
				echo ' var layerOutput = "' . $this->layer->layerOutput . '";' . PHP_EOL;
			}
			
			echo ' var layerSettings = ' . json_encode($this->layer->layerSettings) . ';' .PHP_EOL;
			
			//include image proxy
			
			if( $this->layer->layerImgProxy != '' ){
			
				echo ' var imgProxy = " ' . $this->layer->layerImgProxy . '";' . PHP_EOL;				
			}
			
			//include quick edit
			 
			if( isset($_GET['quick']) ){
				
				echo ' var quickEdit = true;' .PHP_EOL;
			}
			elseif( !$this->user->plan['info']['total_price_amount'] > 0 ){
				
				echo ' var quickEdit = true;' .PHP_EOL;
			}
			else{
				
				echo ' var quickEdit = false;' .PHP_EOL;
			}
			
			//include page def
			
			if( $this->layer->pageDef != '' ){
				
				echo ' var pageDef = ' . $this->layer->pageDef . ';' .PHP_EOL;
			}
			else{
				
				echo ' var pageDef = {};' .PHP_EOL;
			}
			
			//include line break setting

			if( !is_array( $this->layer->layerOptions ) ){
				
				echo ' var disableReturn 	= true;' .PHP_EOL;
				echo ' var autoWrapText 	= false;' .PHP_EOL;
			}
			else{
				
				if( !in_array('line-break',$this->layer->layerOptions) ){
					
					echo ' var disableReturn = true;' .PHP_EOL;
				}
				else{
					
					echo ' var disableReturn = false;' .PHP_EOL;
				}
				
				if(in_array('wrap-text',$this->layer->layerOptions)){
					
					echo ' var autoWrapText = true;' .PHP_EOL;
				}
				else{ 
					
					echo ' var autoWrapText = false;' .PHP_EOL;
				}
			}
			
			//include icon settings
			
			$enableIcons = 'false';
			
			if( in_array_field( 'font-awesome-4-7-0', 'slug', $this->layer->layerCssLibraries ) ){
				
				$enableIcons = 'true';
			}
			
			echo ' var enableIcons = '.$enableIcons.';' .PHP_EOL;
			
			//include forms
			
			if( $this->layer->layerForm == 'importer' ){
				
				echo ' var layerForm = "' . $this->layer->layerForm . '";';
			}
		}
		
	echo'</script>' . PHP_EOL;