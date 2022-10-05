<?php
	 
	$output 	='default';
	$target 	= '_self';
	
	if( $this->parent->inWidget ){
		
		$output		= 'widget';
		$target		= '_blank';
	}
	
	$modal 		= !empty($this->parent->modalId) ?  $this->parent->modalId : '';
	$section	= !empty($_GET['section']) ? $_GET['section'] : '';
	
	// get query arguments
	
	$query_args = array();
	
	if( !empty($output)) 	$query_args[] = 'output=' . $output;
	if( !empty($modal)) 	$query_args[] = 'modal=' 	. $modal;
	if( !empty($section)) 	$query_args[] = 'section=' . $section;
	
	$query_args = !empty($query_args) ? '?'.implode('&amp;',$query_args) : '';

	// get current tab

	if( $this->type == 'image-library' ){
		
		$tab = ( !empty($_REQUEST['tab']) ? $_REQUEST['tab'] : 'backgrounds' );
	}
	elseif( $this->type == 'user-images' ){

		$tab = ( !empty($_REQUEST['tab']) ? $_REQUEST['tab'] : 'upload' );
	}
	elseif( $this->type == 'external-images' ){

		$tab = 'url';
		
		if( !empty($_REQUEST['tab']) ){
			
			$tab = $_REQUEST['tab'];
		}
		elseif( !empty($_REQUEST['app']) ){
			
			$tab = $_REQUEST['app'];
		}
	}
	elseif( $this->type == 'user-payment-urls' ){
		
		//get user bookmarks
		
		$bookmarks = $this->parent->media->get_user_bookmarks($this->parent->user->ID);
	}
	
	// output library
	
	echo'<div id="media_library" class="wrapper">';

		echo '<div id="sidebar">';
						
			echo'<div class="gallery_type_title gallery_head">Media Library</div>';
				
			echo'<ul id="gallery_sidebar" class="nav nav-tabs tabs-left">';

				if( empty($section) || $section == 'images' || $section == $this->type ){
					
					$counts = $this->get_image_counts();
					
					echo'<li class="gallery_type_title">Images</li>';
					
					if( empty($section) || $section == 'images' || $section == 'user-images' )
						
						echo'<li'.( $this->type == 'user-images' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->media . 'user-images/'.$query_args.'">Uploaded Images</a></li>';
					
					if( empty($section) || $section == 'images' || $section == 'external-images' )
					
						echo'<li'.( $this->type == 'external-images' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->media . 'external-images/'.$query_args.'">External Images</a></li>';
					
					if( !empty($counts['default-image']) ){
					
						if( empty($section) || $section == 'images' || $section == 'image-library' )
					
							echo'<li'.( $this->type == 'image-library' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->media . 'image-library/'.$query_args.'">Default Images</a></li>';
					}
				}
				
				if( empty($section) || $section == 'bookmarks' ){
					
					if( $this->parent->apps->get_list('payment') ){
					
						echo'<li class="gallery_type_title">Bookmarks</li>';
						
						echo'<li'.( $this->type == 'user-payment-urls' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->media . 'user-payment-urls/'.$query_args.'">Payment Urls</a></li>';
					}
				}
				
			echo'</ul>';
			
		echo'</div>';

		echo'<div id="content" class="library-content" style="border-left: 1px solid #ddd;background:#fbfbfb;min-height:calc( 100vh - ' . ( $this->parent->inWidget ?  0 : 190 ) . 'px);">';

			echo'<div class="tab-content">';

				if( $this->type == 'user-images' ){

					//output user images	
					
					echo '<div id="user-images">';

						echo'<ul class="nav nav-pills" role="tablist">';
				
							if( $this->parent->inWidget ){
								
								echo'<li>';
								
									echo $this->parent->get_collapse_button();
									
								echo'</li>';
							}
				
							//get app list
							
							$apps = [];

							$item = new stdClass();
							$item->name 	= 'My Images';
							$item->slug 	= 'upload';
							$item->types 	= ['images'];
							$item->pro 		= true;
							
							$apps[] = $item;
							
							//output list
							
							foreach($apps as $app){
								
								if( in_array('images',$app->types) ){
								
									if( $app->slug == $tab ){
										
										$active=' class="active"';
									}
									else{
										
										$active='';
									}
								
									echo'<li role="presentation"'.$active.'><a href="' . add_query_arg('tab',$app->slug,$this->parent->urls->current) . '">' . ( $this->parent->user->plan["info"]["total_price_amount"] == 0 ? '<span class="glyphicon glyphicon-lock" aria-hidden="true" data-toggle="popover" data-placement="bottom" title="" data-content="You need a paid plan to unlock this action" data-original-title="Pro users only"></span> ':'') . strtoupper($app->name) . '</a></li>';
								}
							}
						
							echo '<li role="presentation">';
								
								echo '<button data-toggle="dialog" data-target="#uploadImage" class="btn btn-success btn-sm" style="margin:7px;padding:5px 10px !important;">+ Upload</button>';
								
								echo '<div style="display:none;" id="uploadImage" title="Upload a new Image">';
									
									if( !$this->parent->user->plan['info']['total_price_amount'] > 0 ){
										
										echo '<div class="alert alert-warning">';
										
											echo 'You need a paid plan to <b>upload images</b>';
											
										echo '</div>';
									}
									elseif( !$this->parent->user->remaining_days > 0 ){
										
										echo '<div class="alert alert-warning">';
										
											echo 'You need to renew your plan to <b>upload images</b>';
											
										echo '</div>';										
									}
									else{
										
										$media_url = $this->parent->urls->media . 'user-images/' . $query_args;

										echo '<form style="padding:10px;" target="_self" action="'.$media_url.'" id="saveImageForm" method="post" enctype="multipart/form-data">';
											
											echo '<label>Image File</label>';
											
											echo '<input style="font-size:15px;padding:5px;margin:10px 0;" type="file" name="imgFile" id="imgFile" class="form-control required" />';
											
											echo '<input type="hidden" name="imgAction" id="imgAction" value="upload" />';
											
											wp_nonce_field( 'user_image_nonce', 'user_image_nonce_field' );
											
											echo '<input type="hidden" name="submitted" id="submitted" value="true" />';

											echo '<button id="uploadBtn" class="btn btn-primary" type="button">Upload</button>';

										echo '</form>';
									}									
									
								echo '</div>';						
							
							echo '</li>';

						echo'</ul>';
						
						//output Tab panes
							 
						$this->get_image_table($this->type);
					
					echo'</div>';//user-images
				}			
				elseif( $this->type == 'external-images' ){

					//output user images	
					
					echo '<div id="external-images">';

						if( !empty($_GET['app']) && !empty($this->parent->apps->{$_GET['app']}->message) ){
							
							echo $this->parent->apps->{$_GET['app']}->message;
						}

						echo'<ul class="nav nav-pills" role="tablist">';
							
							if( $this->parent->inWidget ){
								
								echo'<li>';
								
									echo $this->parent->get_collapse_button();
									
								echo'</li>';
							}
							
							echo'<li role="presentation" class="active"><a href="' . $this->parent->urls->current . '">External URLs</a></li>';
							
							echo '<li role="presentation">';
								
								echo '<button data-toggle="dialog" data-target="#addImageUrl" class="btn btn-success btn-sm" style="margin:7px 0px 7px 7px;padding:5px 10px !important;">+ Import</button>';
								
								echo '<div style="display:none;max-width:250px;" id="addImageUrl" title="Add Image URL">';
									
									if( !$this->parent->user->plan['info']['total_price_amount'] > 0 ){
										
										echo '<div class="alert alert-warning">';
										
											echo 'You need a paid plan to <b>add an image</b>';
											
										echo '</div>';
									}
									elseif( !$this->parent->user->remaining_days > 0 ){
										
										echo '<div class="alert alert-warning">';
										
											echo 'You need to renew your plan to <b>add an image</b>';
											
										echo '</div>';										
									}
									else{
										
										$save_url = '';
							
										echo '<form style="padding:10px;" target="_self" action="' . $save_url . '" id="saveImageForm" method="post">';
											
											echo '<div style="padding-bottom:10px;display:block;">';

												echo'<label>From an image url</label>';
												
												echo '<div class="input-group">';
												
													echo '<input type="text" name="imgUrl" id="imgUrl" value="" class="form-control required" placeholder="http://" />';
													
													echo '<div class="input-group-btn">';
													
														echo '<button class="btn btn-primary btn-sm" style="height:34px;" type="button">Import</button>';
														
														echo '<input type="hidden" name="imgAction" id="imgAction" value="save" />';
														
														wp_nonce_field( 'user_image_nonce', 'user_image_nonce_field' );

														echo '<input type="hidden" name="submitted" id="submitted" value="true" />';
														
														echo '<input type="hidden" name="output" value="' . $output . '" />';
														
														echo '<input type="hidden" name="modal" value="' . $this->parent->modalId . '" />';
														
													echo '</div>';
													
												echo '</div>';
												
											echo '</div>';

										echo '</form>';
			
										//get app list
									
										$apps = $this->parent->media->get_external_providers();

										//get options
										
										$options = array( -1 => 'none' );
										
										foreach($apps as $app){
											
											if( in_array('images',$app->types) ){
											
												foreach( $this->parent->user->apps as $user_app ){

													if(strpos($user_app->post_name ,$app->slug . '-')===0){
														
														$options[$user_app->ID] = ucfirst($user_app->post_title);
													}
												}
											}
										}
										
										echo '<form style="padding:10px;" target="_self" id="importAppImages" action="' . $save_url . '" method="get">';
											
											echo '<div style="padding-bottom:10px;display:block;">';

												echo'<label>From a connected accounts</label>';
												
												echo '<div class="input-group">';
													
													echo $this->parent->admin->display_field( array(
											
														'type'				=> 'select',
														'id'				=> 'id',
														'options' 			=> $options,
														'required' 			=> true,
														'description'		=> '',
														'style'				=> '',
														
													),false,false);
													
													echo '<div class="input-group-btn">';
														
														echo '<button class="btn btn-primary btn-sm" style="height:34px;" type="button">Import</button>';
														
														echo '<input type="hidden" name="app" value="autoDetect" />';
														
														echo '<input type="hidden" name="action" value="importImg" />';
														
														echo '<input type="hidden" name="ref" value="' . urlencode(str_replace($this->parent->request->proto,'',$this->parent->urls->current)) .'" />';
														
														echo '<input type="hidden" name="submitted" id="submitted" value="true" />';
														
														echo '<input type="hidden" name="output" value="'.$output.'" />';
														
													echo '</div>';
													
												echo '</div>';
												
											echo '</div>';
											
											
										echo '</form>';
									}									
									
								echo '</div>';						
							
							echo '</li>';
							
							if( !$this->parent->inWidget && !empty($this->parent->apps->list) ){
								
								echo '<li role="presentation">';
									
									echo '<button data-toggle="dialog" data-target="#connectAccount" class="btn btn-default btn-sm" style="margin:7px 0px 7px 7px;padding:5px 10px !important;">+ Connect</button>';
									
									echo '<div style="display:none;max-width:250px;" id="connectAccount" title="Connect new account">';
										
										//get options
										
										$options = array();
										
										foreach( $this->parent->apps->list as $app ){
											
											if( in_array('images',$app->types) ){
											
												$options[$app->slug] = $app->name;
											}
										}
										
										echo '<form style="padding:10px;" target="_self" id="connectNewApp" action="' . $this->parent->urls->apps . '" method="get">';
											
											echo '<div style="padding-bottom:10px;display:block;">';

												echo'<label>Select a provider</label>';
												
												echo '<div class="input-group">';
													
													echo $this->parent->admin->display_field( array(
											
														'type'				=> 'select',
														'id'				=> 'app',
														'options' 			=> $options,
														'required' 			=> true,
														'description'		=> '',
														'style'				=> '',
														
													),false,false);
													
													echo '<div class="input-group-btn">';
														
														echo '<button class="btn btn-primary btn-sm" style="height:34px;" type="button">Connect</button>';
														
														echo '<input type="hidden" name="action" value="connect" />';
														
														echo '<input type="hidden" name="ref" value="' . urlencode(str_replace($this->parent->request->proto,'',$this->parent->urls->current)) .'" />';
														
														echo '<input type="hidden" name="output" value="'.$output.'" />';
														
													echo '</div>';
													
												echo '</div>';
												
											echo '</div>';
											
											
										echo '</form>';								
										
									echo '</div>';						
								
								echo '</li>';
							}								

						echo'</ul>';

						//output Tab panes
						 
						$this->get_image_table($this->type); 
						
					echo'</div>'; //external-images
				}
				elseif( $this->type == 'image-library' ){
					
					//output default images
					
					echo'<div id="image-library">';
					
						echo'<ul class="nav nav-pills" role="tablist">';
							
							if( $this->parent->inWidget ){
								
								echo'<li>';
								
									echo $this->parent->get_collapse_button();
									
								echo'</li>';
							}
							
							echo'<li role="presentation" class="active"><a href="' . $this->parent->urls->current . '">Default Images</a></li>';
							
							$filter = false;
							
							if( !empty($_GET['filter']) ){
								
								parse_str($_GET['filter'],$filter);
							}
							
							$type = !empty($filter['image-type']) ? $filter['image-type'] : '';

							echo'<li style="padding:3px 6px;">';
								
								echo'<form id="formFilters">';
									
									$options = array( '' => 'All' );
									
									if( $image_types = get_terms(array(
								
										'taxonomy' 		=> 'image-type',
										'hide_empty' 	=> true,				
									)) ){
									
										foreach( $image_types as $image_type ){
											
											$options[$image_type->slug] = $image_type->name;
										}
									}
									
									echo $this->parent->admin->display_field( array(
							
										'type'				=> 'select',
										'id'				=> 'image-type',
										'options' 			=> $options,
										'data'				=> $type,
										'description'		=> '',
										'style'				=> '',

									),false,false);
									
								echo'</form>';
								
							echo'</li>';

						echo'</ul>';

						//output Tab panes
							 
						$this->get_image_table($this->type);
						
					echo'</div>';
				}
				elseif( $this->type == 'user-payment-urls' ){
				
					//output user-payment-urls
					
					echo '<div id="user-payment-urls">';
					
						if( !empty($_GET['app']) && !empty($this->parent->apps->{$_GET['app']}->message) ){
							
							echo $this->parent->apps->{$_GET['app']}->message;
						}
						else{			
						
							echo'<ul class="nav nav-pills" role="tablist">';
							
							if( $this->parent->inWidget ){
								
								echo'<li>';
								
									echo $this->parent->get_collapse_button();
									
								echo'</li>';
							}
							
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
							  
							echo'<div class="tab-content" style="margin-top:20px;">';

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

													if(strpos($user_app->post_name ,$app->slug . '-')===0){

														$options[$user_app->ID] = $user_app->post_title;
													}
												}

												if(!empty($options)){

													echo '<form style="padding:10px;" target="_self" action="' . $this->parent->urls->current . '#' . $app->slug . '" class="saveBookmarkForm" method="post">';
														
														echo '<div style="padding-bottom:10px;display:block;">';
															
															echo'<label>Title</label>';
															
															echo '<input type="text" name="bookmarkTitle" id="bookmarkTitle" value="" class="form-control required" placeholder="Product 1" />';
																										
															echo'<label>Account</label>';
					
															echo $this->parent->admin->display_field(array(
								
																'id' 			=> 'id',
																'description' 	=> '',
																'type'			=> 'select',
																'options'		=> $options,
															),false,false);
															
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
																			),false,false);
																		}
																		elseif( empty($value) ){
																			
																			echo'<label>'.ucfirst($key).'</label>';
																			
																			echo $this->parent->admin->display_field(array(
												
																				'id' 			=> $key,
																				'description' 	=> '',
																				'type'			=> 'text',
																				'placeholder'	=> $key,
																			),false,false);															
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
																				),false,false);									
																			}
																			else{
																				
																				echo $this->parent->admin->display_field(array(
													
																					'id' 			=> $key,
																					'type'			=> 'hidden',
																					'data'			=> $value,
																					'description'	=> '',
																					
																				),false,false);
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
																			
																			),false,false);
																		}								
																	}
																}
															}
															
															echo '<input type="hidden" name="app" value="' . $app->slug . '" />';
															echo '<input type="hidden" name="action" value="addBookmark" />';

															wp_nonce_field( 'user_bookmark_nonce', 'user_bookmark_nonce_field' );

															echo '<input type="hidden" name="submitted" id="submitted" value="true" />';
															
															echo '<div style="display:block;margin-top:5px;">';
																
																echo '<input class="btn btn-primary btn-sm" type="submit" value="Add link" />';
															
															echo '</div>';
															
														echo '</div>';
													
													echo '</form>';
												}
												
												echo '<a target="_self" href="'.$this->parent->apps->getAppUrl($app->slug,'connect','user-payment-urls') .'&output='.$output . $modal . $section . '#' . $app->slug . '" style="width:100%;text-align:left;" class="btn btn-md btn-default add_account"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add '.$app->name.' account</a>';
												
											echo'</div>';//add-bookmark-wrapper
											echo'</div>';//add-bookmark
											
											echo'<div class="col-xs-12 col-sm-8 col-lg-9">';
												
												echo '<table class="table-striped panel-default">';
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
	