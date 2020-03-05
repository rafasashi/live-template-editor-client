<?php

	$output = ( !empty($_GET['output']) ? '&output='. sanitize_text_field($_GET['output']) : '' );
	
	$layer_type = $this->layer->get_layer_type($this->layer->id);
	
	if( !empty($_SESSION['message']) ){
		
		echo $_SESSION['message'];
		
		$_SESSION['message'] = '';
	}
	elseif( ( !$this->user->is_editor || !isset($_GET['edit']) ) && !isset($_GET['quick']) && ( $this->layer->type == 'cb-default-layer' || $this->layer->is_media ) ){
		
		$user_plan = $this->plan->get_user_plan_info($this->user->ID);
		
		$total_storage = isset($user_plan['info']['total_storage'][$layer_type->name]) ? $user_plan['info']['total_storage'][$layer_type->name] : 0;
		
		$plan_usage = $this->plan->get_user_plan_usage( $this->user->ID );
		
		$download_button = '';
		
		if( $this->layer->layerOutput == 'canvas' ){
			
			$download_button = '<a href="'.add_query_arg('quick','',$this->urls->current).'" class="btn btn-lg btn-primary" style="margin: 15px 15px 0px 15px;">Start a quick collage</a>';
		}
		elseif( $this->layer->layerOutput == 'image' ){
			
			$download_button = '<a href="'.add_query_arg('quick','',$this->urls->current).'" class="btn btn-lg btn-primary" style="margin: 15px 15px 0px 15px;">Edit image ( without saving )</a>';
		}
		elseif( $this->layer->layerOutput == 'inline-css' || $this->layer->layerOutput == 'external-css' ){
			
			$download_button = '<a href="'.add_query_arg('quick','',$this->urls->current).'" class="btn btn-lg btn-primary" style="margin: 15px 15px 0px 15px;">Get the code ( without hosting )</a>';				
		}		
		
		echo '<div style="min-height:500px;">';
			
			echo'<div class="col-xs-12 col-sm-12 col-lg-6" style="padding:20px;">';
				
				echo '<h2>Start a new project <a href="' . $this->urls->profile . '?tab=billing-info"><span class="pull-right label label-default" style="font-size:18px;"> ' . ( !empty($plan_usage[$layer_type->name]) ? $plan_usage[$layer_type->name] : 0 ) . ' / ' . $total_storage . ' </span></a></h2>';

				echo'<hr>';
				
				if( !$this->layer->is_media && $this->user->remaining_days > 0 ){
					
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
						
						if( !empty($download_button) ){
						
							echo'<div style="font-size:18px;width:100%;display:inline-block;padding:35px 20px 20px 20px;">OR</div>';
						}
					}
					else{
						
						echo'<div class="alert alert-warning">';
							
							echo'You can\'t save more projects from the <b>' . $layer_type->name . '</b> gallery with the current plan. Delete an old project or upgrade to increase your storage space.';
					
						echo'</div>';				
					}
				}
				
				echo $download_button;

			echo'</div>';
			
			if( $projects = $this->layer->get_user_projects($this->user->ID,$layer_type) ){

				echo'<div class="col-xs-12 col-sm-12 col-lg-6" style="padding:20px;">';
					
					echo'<h2>Load a similar project <a href="' . $this->urls->editor . '?list=' . $layer_type->storage . '"><span class="pull-right label" style="font-size:14px;color:#cacaca;padding:10px;">see all</span></a></h2>';
					
					echo'<hr>';
					
					foreach( $projects as $project ){
						
						echo'<div style="margin: 5px 0;display: inline-block;width: 100%;">';
						
							echo'<div class="col-xs-6">';
								
								echo $project->post_title;
						
							echo'</div>';
						
							echo'<div class="col-xs-6 text-right">';
							
								echo $this->layer->get_action_buttons($project,$layer_type);
								
							echo'</div>';
							
						echo'</div>';
					}
						
				echo'</div>';	
			}
			
		echo'</div>';	
	}
	elseif( $this->layer->type == 'user-layer' && !$this->user->plan["info"]["total_price_amount"] > 0 ){
		
		echo'<div class="col-xs-12 col-sm-12 col-lg-8" style="padding:20px;min-height:500px;">';
			
			echo '<div class="alert alert-warning">You need a paid plan to edit this template...</div>';

		echo'</div>';
	}
	elseif( $this->layer->is_editable($layer_type->output) ){

		// ouput demo message
		
		if( $this->user->loggedin && $this->user->plan["info"]["total_price_amount"] == 0 ){
			
			//$this->get_demo_message();
		}			
		
		// get iframe url
		
		$iframe_url = $this->urls->editor . '?uri=' . $this->layer->id . '&lk=' . md5( 'layer' . $this->layer->id . $this->_time ) . '&_=' . $this->_time;

		// output editor iframe
		
		echo'<div class="loadingIframe" style="width: 100%;position: relative;background-position: 50% center;background-repeat: no-repeat;background-image:url(\''. $this->server->url .'/c/p/live-template-editor-server/assets/loader.gif\');height:64px;"></div>';
		
		echo'<iframe id="editorIframe" src="' . $iframe_url . '" style="margin-top: -65px;position: relative;width: 100%;top: 0;bottom: 0;border:0;height: 1300px;overflow: hidden;"></iframe>';
	
		//editor settings
			
		echo'<script id="LiveTplEditorSettings">' .PHP_EOL;
			
			if( $this->layer->layerOutput == 'image' ){
				
				if( $this->layer->layerImageTpl->post_type == 'attachment' ){
					
					$attachment_url = wp_get_attachment_url($this->layer->layerImageTpl->ID );
				}
				else{
					
					$attachment_url = trim(strip_tags(apply_filters('the_content',$this->layer->layerImageTpl->post_content)));		
				}
				
				echo ' var layerImageTpl = "' . $this->layer->layerImgProxy . urlencode($attachment_url) . '";' . PHP_EOL;
			}
			else{
				
				echo ' var layerContent = "' . base64_encode($this->layer->output_layer()) . '";' . PHP_EOL;
		
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
	}
	else{
		
		echo'<div class="col-xs-12 col-sm-12 col-lg-8" style="padding:20px;min-height:500px;">';
			
			echo '<div class="alert alert-warning">This template is not editable...</div>';

		echo'</div>';		
	}