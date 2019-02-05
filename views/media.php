<?php

	if(isset($_SESSION['message'])){ 
	
		//output message
	
		echo $_SESSION['message'];
		
		//reset message
		
		$_SESSION['message'] ='';
	}	
	
	$output='default';
	$target='_self';

	if( $this->parent->inWidget ){
		
		$output		= 'widget';
		$target		= '_blank';
	}

	if( $this->type == 'image-library' ){
		
		// get current tab
		
		$tab = ( !empty($_REQUEST['tab']) ? $_REQUEST['tab'] : 'backgrounds' );

		$default_images = $this->parent->media->get_default_images($tab);
	}
	elseif( $this->type == 'user-images' ){

		// get current tab
		
		$tab = ( !empty($_REQUEST['tab']) ? $_REQUEST['tab'] : 'upload' );
		
		$image_providers = $this->parent->media->get_user_images($this->parent->user->ID,$tab);
	}
	elseif( $this->type == 'user-payment-urls' ){
		
		//get user bookmarks
		
		$this->parent->media->get_user_bookmarks($this->parent->user->ID);
	}
	
	// output library
		
	echo'<div id="media_library">';

		echo'<div class="col-xs-3 col-sm-2" style="padding:0;">';
		
			echo'<ul class="nav nav-tabs tabs-left">';
				
				echo'<li class="gallery_type_title">Images</li>';
				
				echo'<li'.( $this->type == 'image-library' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->media . 'image-library/?output='.$output.'">Image Library</a></li>';
				echo'<li'.( $this->type == 'user-images' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->media . 'user-images/?output='.$output.'">My Images</a></li>';
				//echo'<li'.( $this->type == 'edited-images' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->media . 'edited-images/?output='.$output.'" data-html="true" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-title="Edited Images" data-content="All the images uploaded during the edition process (cropped, resized...). Hosted images will be removed upon template deletion or plan cancelation." data-original-title="" title="">Edited Images <span class="label label-info pull-right hidden-xs hidden-sm hidden-md">hosted</span></a></li>';
				
				echo'<li class="gallery_type_title">Bookmarks</li>';
				echo'<li'.( $this->type == 'user-payment-urls' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->media . 'user-payment-urls/?output='.$output.'">Payment Urls</a></li>';
					
			echo'</ul>';
		echo'</div>';

		echo'<div class="col-xs-9 col-sm-10 library-content" style="border-left: 1px solid #ddd;background:#fff;padding-bottom:15px;padding-top:15px;min-height:700px;">';

			echo'<div class="tab-content">';
			
				if( $this->type == 'image-library' ){
			  
					//output default images
					
					echo'<div id="image-library">';
					
						echo'<ul class="nav nav-pills" role="tablist">';
						
						$active=' class="active"';
						
						foreach( $default_images as $image_type => $items ){
							
							if( $image_type != '' ){
								
								if( $image_type == $tab ){
									
									$active = ' class="active"';
								}
								else{
									
									$active = '';
								}							
								
								echo'<li role="presentation"'.$active.'><a href="'. add_query_arg('tab',$image_type,$this->parent->urls->current) . '">'.strtoupper(str_replace(array('-','_'),' ',$image_type)).'</a></li>';
							}
						}
						
						echo'</ul>';

						//output Tab panes
						  
						echo'<div class="tab-content row" style="margin-top:20px;">';
							
							if( !empty($default_images[$tab]) ){
							
								$items = $default_images[$tab];
								
								echo'<div role="tabpanel" class="tab-pane active" id="'.$tab.'">';
									
									foreach($items as $item){

										echo $item;
									}

								echo'</div>';
							}
							
						echo'</div>';
						
					echo'</div>';
				}
				elseif( $this->type == 'user-images' ){

					//output user images	
					
					echo '<div id="user-images">';

						if( !empty($_GET['app']) && !empty($this->parent->apps->{$_GET['app']}->message) ){
							
							echo $this->parent->apps->{$_GET['app']}->message;
						}
						else{	
					
							echo'<ul class="nav nav-pills" role="tablist">';
							
							//get app list
							
							$apps = $this->parent->media->get_app_list();

							//output list
							
							foreach($apps as $app){
								
								if( in_array('images',$app->types) ){
								
									if( $app->slug == $tab ){
										
										$active=' class="active"';
									}
									else{
										
										$active='';
									}
								
									echo'<li role="presentation"'.$active.'><a href="' . add_query_arg('tab',$app->slug,$this->parent->urls->current) . '">' . ( $app->pro === true && $this->parent->user->plan["info"]["total_price_amount"] == 0 ? '<span class="glyphicon glyphicon-lock" aria-hidden="true" data-toggle="popover" data-placement="bottom" title="" data-content="You need a paid plan to unlock this action" data-original-title="Pro users only"></span> ':'') . strtoupper($app->name) . '</a></li>';
								}
							}
							
							echo'</ul>';

							//output Tab panes
							  
							echo'<div class="tab-content row" style="margin-top:20px;">';

								foreach( $apps as $app ){
									
									if( in_array('images',$app->types) && $app->slug == $tab ){
									
										echo'<div role="tabpanel" class="tab-pane active" id="'.$app->slug.'">';

											if( $app->slug == 'upload' ){

												echo'<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">';
												
													echo'<div class="panel panel-default" style="background:#efefef;">';
														
														echo '<div class="panel-heading"><b>Upload image</b></div>';
														
														if( $app->pro === true && $this->parent->user->plan["info"]["total_price_amount"] == 0 ){
															
															echo '<div class="alert alert-warning">';
															
																echo 'You need a paid plan to <b>upload images</b>';
																
															echo '</div>';
														}
														else{
															
															$media_url = $this->parent->urls->media . 'user-images/';
														
															if( isset($_GET['output']) && $_GET['output'] == 'widget' ){
																		
																$media_url .= '?output=widget';
															}														
									
															echo '<form style="padding:10px;" target="_self" action="'.$media_url.'" id="saveImageForm" method="post" enctype="multipart/form-data">';
																
																echo '<label>Image File</label>';
																
																echo '<input style="font-size:15px;padding:5px;margin:10px 0;" type="file" name="imgFile" id="imgFile" class="form-control required" />';
																
																echo '<input type="hidden" name="imgAction" id="imgAction" value="upload" />';
																
																wp_nonce_field( 'user_image_nonce', 'user_image_nonce_field' );
																
																echo '<input type="hidden" name="submitted" id="submitted" value="true" />';

																echo '<button class="btn btn-primary" type="button">Upload</button>';

															echo '</form>';
														}
														
													echo'</div>';//add-image-wrapper												
					
													
												echo '</div>';	
												
												if(isset($image_providers[$app->slug])){
													
													foreach($image_providers[$app->slug] as $item){

														echo $item;
													}								
												}												
											}
											elseif( $app->slug == 'canvas' ){
												
												if( !$this->parent->inWidget ){
													
													echo'<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">';
													
														echo'<div class="panel panel-default" style="background:#efefef;">';
															
															echo '<div class="panel-heading"><b>Start a canvas</b></div>';
															
															if( $app->pro === true && $this->parent->user->plan["info"]["total_price_amount"] == 0 ){
																
																echo '<div class="alert alert-warning">';
																
																	echo 'You need a paid plan to <b>start a canvas</b>';
																	
																echo '</div>';
															}
															else{
																
																$media_url = add_query_arg('layer[output]','canvas',$this->parent->urls->editor);
																	
																if( isset($_GET['output']) && $_GET['output'] == 'widget' ){
																	
																	$media_url = add_query_arg('output','widget',$media_url);
																}														
																
																echo '<a href="'.$media_url.'" style="width: 100%;display: inline-block;text-align: center;padding: 40px 20px;font-size: 40px;font-weight: bold;color: #888;">';
																	
																	echo '<div style="font-size: 80px;padding: 10px 0px;">+</div>';
																	
																	echo '<br>';
																	
																	echo '<div style="padding: 5px 0px;">New</div>';
																	
																echo '</a>';
															}
															
														echo'</div>';//add-image-wrapper												
						
														
													echo '</div>';
												}
												
												if(isset($image_providers[$app->slug])){
													
													foreach($image_providers[$app->slug] as $item){

														echo $item;
													}								
												}												
											}
											else{
											
												// add images based on provider
												
												echo'<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">';
												echo'<div class="panel panel-default" style="background:#efefef;">';
													
													if( !empty($app->term_id) ){
														
														echo '<div class="panel-heading"><b>Import image urls</b></div>';
														
														foreach( $this->parent->user->apps as $user_app ){

															if(strpos($user_app->post_name ,$app->slug. '-')===0){
																
																echo '<a href="'.$this->parent->apps->getAppUrl($app->slug,'importImg','user-images').'&output='.$output.'&id=' . $user_app->ID .'" style="width:100%;text-align:left;" class="btn btn-md btn-info"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span> '.ucfirst($user_app->post_title).'</a>';
															}
														}
														
														echo '<a target="'.$target.'" href="'.$this->parent->apps->getAppUrl($app->slug,'connect','user-images').'" style="width:100%;text-align:left;" class="btn btn-md btn-default add_account"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add '.$app->name.' account</a>';
													}
													else{
														
														echo '<div class="panel-heading"><b>Import image url</b></div>';
															
														$save_url = '';
									
														echo '<form style="padding:10px;" target="_self" action="' . $save_url . '" id="saveImageForm" method="post">';
															
															echo '<div style="padding-bottom:10px;display:block;">';

																echo'<label>Title</label>';
																
																echo '<input type="text" name="imgTitle" id="imgTitle" value="" class="form-control required" placeholder="my image" />';
																									
															echo '</div>';

															echo '<div style="padding-bottom:10px;display:block;">';

																echo'<label>Image url</label>';
																
																echo '<input type="text" name="imgUrl" id="imgUrl" value="" class="form-control required" placeholder="http://" />';
																
																echo '<input type="hidden" name="imgAction" id="imgAction" value="save" />';
																
																wp_nonce_field( 'user_image_nonce', 'user_image_nonce_field' );

																echo '<input type="hidden" name="submitted" id="submitted" value="true" />';
															
															echo '</div>';
															
															echo '<div style="display:block;">';
									
																echo '<button class="btn btn-primary" type="button">Import</button>';

															echo '</div>';
															
														echo '</form>';
													}
													
												echo'</div>';//add-image-wrapper
												echo'</div>';//add-image
											
												if(isset($image_providers[$app->slug])){
													
													foreach($image_providers[$app->slug] as $item){

														echo $item;
													}								
												}
											}
											
										echo'</div>';
										
										break;
									}
								}
								
							echo'</div>';
						}
					
					echo'</div>';//user-images
				}
				elseif( $this->type == 'user-payment-urls' ){
				
					//output user-payment-urls
					
					echo '<div id="user-payment-urls">';
					
						if( !empty($_GET['app']) && !empty($this->parent->apps->{$_GET['app']}->message) ){
							
							echo $this->parent->apps->{$_GET['app']}->message;
						}
						else{			
						
							echo'<ul class="nav nav-pills" role="tablist">';
							
							//get app list

							$apps = $this->parent->apps->list;
							
							//output list
							
							$active=' class="active"';
							
							foreach($apps as $app){
								
								if( in_array('payment',$app->types) ){
								
									echo'<li role="presentation"'.$active.'><a href="#' . $app->slug . '" aria-controls="' . $app->slug . '" role="tab" data-toggle="tab">'.strtoupper($app->name).'</a></li>';

									$active='';
								}
							}
							
							echo'</ul>';

							//output Tab panes
							  
							echo'<div class="tab-content row" style="margin-top:20px;">';

								$active	 = ' active';

								foreach( $apps as $app ){
									
									if( in_array('payment',$app->types) ){
									
										echo'<div role="tabpanel" class="tab-pane'.$active.'" id="'.$app->slug.'">';

											// add payment based on provider
											
											echo'<div class="col-xs-12 col-sm-4 col-lg-3">';
											echo'<div class="panel panel-default" style="background:#efefef;">';

												echo '<div class="panel-heading"><b>Add '.ucfirst($app->name).' link</b></div>';
													
												//get app ids
												
												$options =[];
												
												foreach( $this->parent->user->apps as $user_app ){

													if(strpos($user_app->post_name ,$app->slug. '-')===0){

														$options[$user_app->ID] = $user_app->post_title;
													}
												}

												if(!empty($options)){

													echo '<form style="padding:10px;" target="_self" action="'.$this->parent->urls->media . 'user-payment-urls/'.'#' . $app->slug . '" class="saveBookmarkForm" method="post">';
														
														echo '<div style="padding-bottom:10px;display:block;">';
															
															echo'<label>Title</label>';
															
															echo '<input type="text" name="bookmarkTitle" id="bookmarkTitle" value="" class="form-control required" placeholder="Product 1" />';
																										
															echo'<label>Account</label>';
					
															echo $this->parent->admin->display_field(array(
								
																'id' 			=> 'id',
																'description' 	=> '',
																'type'			=> 'select',
																'options'		=> $options,
															));
															
															//get parameters
															
															$parameters = get_option('parameters_'.$app->slug);
															
															if( isset($parameters['key']) ){

																foreach($parameters['key'] as $i => $key){
																	
																	if( $parameters['input'][$i] == 'parameter' ){
																		
																		$value = $parameters['value'][$i];

																		if( is_numeric($value) ){
																			
																			echo'<label>'.ucfirst($key).'</label>';
																			
																			echo $this->parent->admin->display_field(array(
												
																				'id' 			=> $key,
																				'description' 	=> '',
																				'type'			=> 'number',
																				'placeholder'	=> $value,
																			));
																		}
																		elseif( empty($value) ){
																			
																			echo'<label>'.ucfirst($key).'</label>';
																			
																			echo $this->parent->admin->display_field(array(
												
																				'id' 			=> $key,
																				'description' 	=> '',
																				'type'			=> 'text',
																				'placeholder'	=> $key,
																			));															
																		}
																		else{
																			
																			$values = explode('|',$value);
																			
																			if(isset($values[1])){
																				
																				$options =[];
																				
																				foreach($values as $v){
																					
																					$options[$v] = ucfirst($v);
																				}
																				
																				echo'<label>'.ucfirst($key).'</label>';
																			
																				echo $this->parent->admin->display_field(array(
													
																					'id' 			=> $key,
																					'description' 	=> '',
																					'type'			=> 'select',
																					'options'		=> $options,
																					'placeholder'	=> '',
																				));									
																			}
																			else{

																				echo $this->parent->admin->display_field(array(
													
																					'id' 			=> $key,
																					'type'			=> 'hidden',
																					'value'			=> $value,
																					'description'	=> '',
																				));																	
																			}
																		}
																	}
																	elseif( $parameters['input'][$i] == 'filename' ){
																		
																		$value = $parameters['value'][$i];

																		if( is_numeric($value) ){
																			
																			echo'<label>'.ucfirst($key).'</label>';
																			
																			echo $this->parent->admin->display_field(array(
												
																				'id' 			=> $key,
																				'description' 	=> '',
																				'type'			=> 'number',
																				'placeholder'	=> $value,
																			));
																		}								
																	}
																}
															}
															
															echo '<input type="hidden" name="app" value="' . $app->slug . '" />';
															echo '<input type="hidden" name="action" value="addBookmark" />';

															wp_nonce_field( 'user_bookmark_nonce', 'user_bookmark_nonce_field' );

															echo '<input type="hidden" name="submitted" id="submitted" value="true" />';
															
															echo '<div style="display:block;">';
									
																echo '<button class="btn btn-primary btn-sm" type="button">Add link</button>';

															echo '</div>';												
													
														echo '</div>';
													
													echo '</form>';
												}
												
												echo '<a target="_self" href="'.$this->parent->apps->getAppUrl($app->slug,'connect','user-payment-urls') .'&output='.$output. '#' . $app->slug . '" style="width:100%;text-align:left;" class="btn btn-md btn-default add_account"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add '.$app->name.' account</a>';
												
											echo'</div>';//add-bookmark-wrapper
											echo'</div>';//add-bookmark
											
											echo'<div class="col-xs-12 col-sm-8 col-lg-9">';
												
												echo '<table class="table table-striped panel-default">';
												echo '<tbody>';								
												
													if(!empty($bookmarks[$app->slug])){

														foreach($bookmarks[$app->slug] as $item){
															
															echo '<tr>';
																echo '<td>';
																
																	echo $item;
																	
																echo '</td>';
															echo '</tr>';
														}									
													}
													else{

														echo '<tr>';
															echo '<td>';
															
																echo 'No Payment Links found...';
																
															echo '</td>';
														echo '</tr>';
													}
												
												echo '</tbody>';
												echo '</table>';

											echo'</div>';//add-bookmark
											
										echo'</div>';
										
										$active='';
									}
								}
								
							echo'</div>';
						}
						
					echo'</div>';//user-payment-urls
					
				}

			echo'</div>';
			
		echo'</div>';	

	echo'</div>';

?>

<script>

	;(function($){
		
		$(document).ready(function(){

			// submit forms
			
			$( "button" ).click(function() {
				
				this.closest( "form" ).submit();
			});
			
			// set tooltips & popovers
			
			$('[data-toggle="tooltip"]').tooltip();
			$('[data-toggle="popover"]').popover();
		});
		
	})(jQuery);

</script>