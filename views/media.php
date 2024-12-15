<?php

$ltple = LTPLE_Client::instance();

$currentTab = $ltple->get_current_tab();

$output		= $ltple->inWidget ? 'widget' : 'default';
$target		= $ltple->inWidget ? '_blank' : '_self';

$modal 		= !empty($ltple->modalId) ?  $ltple->modalId : '';

$section	= !empty($_GET['section']) ? sanitize_title($_GET['section']) : '';
$sidebar	= !empty($_GET['sidebar']) ? sanitize_title($_GET['sidebar']) : '';

$apps 		= $ltple->media->get_external_providers();

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
	
	$bookmarks = $ltple->media->get_user_bookmarks($ltple->user->ID);
}

// output library

echo'<div id="media_library" class="wrapper">';

	echo '<div id="sidebar" class="'.$sidebar.'">';
		
		echo'<div class="gallery_type_title gallery_head">Directory</div>';
		
		echo'<ul id="gallery_sidebar" class="nav nav-tabs tabs-left">';

			if( empty($section) || $section == 'images' ){
				
				echo'<li class="gallery_type_title">Images</li>';
				
				if( empty($section) || $section == 'images' || $section == 'user-images' )
					
					echo'<li'.( $this->type == 'user-images' ? ' class="active"' : '' ).'><a href="'.$ltple->urls->media . 'user-images/'.$query_args.'"><i class="far fa-file-image"></i> Uploaded Images</a></li>';
				
				foreach( $apps as $app ){
					
					if( $app->slug != 'url' && in_array('images',$app->types) ){
					
						if( empty($section) || $section == 'images' || $section == 'external-images' ){
				
							echo'<li'.( $this->type == 'external-images' ? ' class="active"' : '' ).'><a href="'.$ltple->urls->media . 'external-images/'.$query_args.'"><i class="far fa-file-image"></i> External Images</a></li>';
						}
						
						break;
					}
				}
				
				$counts = $this->get_image_counts();
				
				if( !empty($counts['default-image']) ){
				
					if( empty($section) || $section == 'images' || $section == 'image-library' )
				
						echo'<li'.( $this->type == 'image-library' ? ' class="active"' : '' ).'><a href="'.$ltple->urls->media . 'image-library/'.$query_args.'"><i class="far fa-file-image"></i> Default Images</a></li>';
				}
			}
			
			if( $ltple->apps->has_bookmarks() ){

				if( empty($section) || $section == 'bookmarks' ){
				
					echo'<li class="gallery_type_title">Links</li>';
					
					echo'<li'.( $this->type == 'user-payment-urls' ? ' class="active"' : '' ).'><a href="'.$ltple->urls->media . 'user-payment-urls/'.$query_args.'"><i class="fas fa-credit-card"></i> Payment Links</a></li>';
				}
			}
			
			do_action('ltple_media_gallery_sidebar',$this->type,$section,$query_args);
			
		echo'</ul>';
		
	echo'</div>';

	echo'<div id="content" class="library-content" style="min-height:calc( 100vh - ' . ( $ltple->inWidget ?  0 : 190 ) . 'px);">';

		echo'<div class="tab-content">';
			
			if( $this->type == 'user-images' ){

				//output user images	
				
				echo '<div id="user-images">';

					echo'<ul class="nav nav-pills nav-resizable" role="tablist">';
			
						if( $ltple->inWidget ){
							
							echo'<li>';
							
								echo $ltple->get_collapse_button();
								
							echo'</li>';
						}
			
						echo'<li role="presentation" class="active"><a href="' . add_query_arg('tab','upload',$ltple->urls->current) . '">' . ( $ltple->user->plan["info"]["total_price_amount"] == 0 ? '<span class="glyphicon glyphicon-lock" aria-hidden="true" data-toggle="popover" data-placement="bottom" title="" data-content="You need a paid plan to unlock this action" data-original-title="Pro users only"></span> ':'') . 'My Images</a></li>';
						
						echo '<li role="presentation">';
							
							echo '<button data-toggle="dialog" data-target="#uploadImage" class="btn btn-success btn-sm" style="margin:7px;padding:5px 10px !important;">+ Upload</button>';
							
							echo '<div style="display:none;" id="uploadImage" title="Upload a new Image">';
								
								if( !$ltple->user->plan['info']['total_price_amount'] > 0 ){
									
									echo '<div class="alert alert-warning">';
									
										echo 'You need a paid plan to <b>upload images</b>';
										
									echo '</div>';
								}
								elseif( !$ltple->user->remaining_days > 0 ){
									
									echo '<div class="alert alert-warning">';
									
										echo 'You need to renew your plan to <b>upload images</b>';
										
									echo '</div>';										
								}
								else{
									
									$media_url = $ltple->urls->media . 'user-images/' . $query_args;

									echo '<form style="padding:10px;" target="_self" action="'.$media_url.'" id="saveImageForm" class="dynamic-form" method="post" enctype="multipart/form-data">';
										
										echo '<label>Image File</label>';
										
										echo '<input style="font-size:15px;padding:5px;margin:10px 0;" type="file" name="imgFile" id="imgFile" class="form-control required" />';
										
										echo '<input type="hidden" name="imgAction" id="imgAction" value="upload" />';
										
										wp_nonce_field( 'user_image_nonce', 'user_image_nonce_field' );
										
										echo '<input type="hidden" name="submitted" id="submitted" value="true" />';

										echo '<button id="uploadBtn" class="btn btn-sm btn-primary" type="button">Upload</button>';

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

					if( !empty($_GET['app']) && !empty($ltple->apps->{$_GET['app']}->message) ){
						
						echo $ltple->apps->{$_GET['app']}->message;
					}

					echo'<ul class="nav nav-pills nav-resizable" role="tablist">';
						
						if( $ltple->inWidget ){
							
							echo'<li>';
							
								echo $ltple->get_collapse_button();
								
							echo'</li>';
						}
						
						echo'<li role="presentation" class="active"><a href="' . $ltple->urls->current . '">External URLs</a></li>';
						

						if( !$ltple->inWidget && !empty($ltple->apps->list) ){
							
							//get options
							
							$options = array();
							
							foreach( $ltple->apps->list as $app ){
								
								if( in_array('images',$app->types) ){
								
									$options[$app->slug] = $app->name;
								}
							}
							
							if( !empty($options) ){
								
								echo '<li role="presentation">';
									
									echo '<button data-toggle="dialog" data-target="#connectAccount" class="btn btn-default btn-sm" style="margin:7px 0px 7px 7px;padding:5px 10px !important;">+ Connect</button>';
									
									echo '<div style="display:none;max-width:250px;" id="connectAccount" title="Connect new account">';

										echo '<form style="padding:10px;" target="_self" id="connectNewApp" action="' . $ltple->urls->apps . '" method="get">';
											
											echo '<div style="padding-bottom:10px;display:block;">';

												echo'<label>Select a provider</label>';
												
												echo '<div class="input-group">';
													
													echo $ltple->admin->display_field( array(
											
														'type'				=> 'select',
														'id'				=> 'app',
														'options' 			=> $options,
														'required' 			=> true,
														'description'		=> '',
														'style'				=> '',
														
													),false,false);
													
													echo '<div class="input-group-btn">';
														
														echo '<button class="btn btn-primary btn-sm" style="height:34px;" type="button">Add</button>';
														
														echo '<input type="hidden" name="action" value="connect" />';
														
														echo '<input type="hidden" name="ref" value="' . urlencode(str_replace($ltple->request->proto,'',$ltple->urls->current)) .'" />';
														
														echo '<input type="hidden" name="output" value="'.$output.'" />';
														
													echo '</div>';
													
												echo '</div>';
												
											echo '</div>';
											
											
										echo '</form>';								
										
									echo '</div>';						
								
								echo '</li>';
							}
						}
						
						echo '<li role="presentation">';
							
							echo '<button data-toggle="dialog" data-target="#addImageUrl" class="btn btn-success btn-sm" style="margin:7px 0px 7px 7px;padding:5px 10px !important;">+ Import</button>';
							
							echo '<div style="display:none;max-width:250px;" id="addImageUrl" title="Add Image URL">';
								
								if( !$ltple->user->plan['info']['total_price_amount'] > 0 ){
									
									echo '<div class="alert alert-warning">';
									
										echo 'You need a paid plan to <b>add an image</b>';
										
									echo '</div>';
								}
								elseif( !$ltple->user->remaining_days > 0 ){
									
									echo '<div class="alert alert-warning">';
									
										echo 'You need to renew your plan to <b>add an image</b>';
										
									echo '</div>';										
								}
								else{
									
									//get options
									
									$options = array();
									
									foreach($apps as $app){
										
										if( in_array('images',$app->types) ){
										
											foreach( $ltple->user->apps as $user_app ){

												if(strpos($user_app->post_name ,$app->slug . '-')===0){
													
													$options[$user_app->ID] = ucfirst($user_app->post_title);
												}
											}
										}
									}
									
									if( !empty($options) ){
									
										$save_url = '';
										
										echo '<form style="padding:10px;" target="_self" id="importAppImages" action="' . $save_url . '" method="get">';
											
											echo '<div style="padding-bottom:10px;display:block;">';

												echo'<label>From a connected account</label>';
												
												echo '<div class="input-group">';
													
													echo $ltple->admin->display_field( array(
											
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
														
														echo '<input type="hidden" name="ref" value="' . urlencode(str_replace($ltple->request->proto,'',$ltple->urls->current)) .'" />';
														
														echo '<input type="hidden" name="submitted" id="submitted" value="true" />';
														
														echo '<input type="hidden" name="output" value="'.$output.'" />';
														
													echo '</div>';
													
												echo '</div>';
												
											echo '</div>';
											
											
										echo '</form>';
									}
								}									
								
							echo '</div>';						
						
						echo '</li>';								

					echo'</ul>';

					//output Tab panes
					 
					$this->get_image_table($this->type); 
					
				echo'</div>'; //external-images
			}
			elseif( $this->type == 'image-library' ){
				
				//output default images
				
				echo'<div id="image-library">';
				
					echo'<ul class="nav nav-pills nav-resizable" role="tablist">';
						
						if( $ltple->inWidget ){
							
							echo'<li>';
							
								echo $ltple->get_collapse_button();
								
							echo'</li>';
						}
						
						echo'<li role="presentation" class="active"><a href="' . $ltple->urls->current . '">Default Images</a></li>';
						
						$filter = false;
						
						if( !empty($_GET['filter']) ){
							
							parse_str($_GET['filter'],$filter);
						}
						
						$type = !empty($filter['image-type']) ? $filter['image-type'] : '';
						
						echo'<li style="padding:7px;">';
							
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
								
								echo $ltple->admin->display_field( array(
						
									'type'				=> 'select',
									'id'				=> 'image-type',
									'options' 			=> $options,
									'data'				=> $type,
									'description'		=> '',
									'style'				=> 'height:26px;padding:3px;',

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
					
					if( !empty($_GET['app']) && !empty($ltple->apps->{$_GET['app']}->message) ){
						
						$app = sanitize_title($_GET['app']);
						
						echo $ltple->apps->{$app}->message;
					}
					else{			
					
						echo'<ul class="nav nav-pills nav-resizable" role="tablist">';
							
							if( $ltple->inWidget ){
								
								echo'<li>';
								
									echo $ltple->get_collapse_button();
									
								echo'</li>';
							}
							
							//get app list

							$apps = $ltple->apps->list;
							
							//output list
							
							$active = null;

							foreach($apps as $app){
								
								if( in_array('payment',$app->types) ){
									
									if( empty($currentTab) || $currentTab == $app->slug ){
										
										echo'<li role="presentation" class="active"><a href="'.add_query_arg('tab',$app->slug,$ltple->urls->current).'">'.strtoupper($app->name).'</a></li>';
										
										$active = $app->slug;
										
										break;										
									}
								}
							}
							
							// switch service
							
							echo '<li class="more dropdown" style="margin-left: 8px; margin-bottom: 0px;">';
								
								echo '<button style="padding:3px 5px;margin:8px 0px;height:25px;background:#f2f2f2;border:0;font-size:14px;" class="glyphicon glyphicon-option-vertical dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"></button>';
								
								echo '<ul class="dropdown-menu dropdown-menu-left" style="margin-left:-8px;">';
								
									foreach($apps as $app){
										
										if( in_array('payment',$app->types) && $app->slug != $active ){
										
											echo'<li role="presentation"><a href="'.add_query_arg('tab',$app->slug,$ltple->urls->current).'">'.strtoupper($app->name).'</a></li>';
										}
									}
									
								echo '</ul>';
							
							echo '</li>';

							// app actions

							foreach($apps as $app){
								
								if( $app->slug == $active ){
									
									// add account
									
									echo '<li role="presentation">';
										
										echo '<a target="_self" href="'.$ltple->apps->getAppUrl($app->slug,'connect','user-payment-urls') .'&output='.$output . $modal . $section .'" class="btn btn-default btn-sm" style="margin:7px;padding:5px 10px !important;" title="Add wallet">+ Wallet</a>';
									
									echo '</li>';
									
									//get app ids
									
									$options =[];
									
									foreach( $ltple->user->apps as $user_app ){

										if(strpos($user_app->post_name ,$app->slug . '-')===0){

											$options[$user_app->ID] = $user_app->post_title;
										}
									}
									
									if(!empty($options)){
										
										// add link

										echo '<li role="presentation">';
										
											echo '<button data-toggle="dialog" data-target="#addLink" class="btn btn-success btn-sm" style="margin:7px 0px 7px 1px;padding:5px 10px !important;" title="Add link">+ Link</button>';

											echo '<div style="display:none;" id="addLink" title="Add payment link">';
												
												echo '<form style="padding:10px;" target="_self" action="' . $ltple->urls->current . '#' . $app->slug . '" class="saveBookmarkForm" method="post">';
													
													echo '<div style="padding-bottom:10px;display:block;">';
														
														echo'<label>Title</label>';
														
														echo '<input type="text" name="bookmarkTitle" id="bookmarkTitle" value="" class="form-control required" placeholder="Product 1" />';
																									
														echo'<label>Wallet</label>';
				
														echo $ltple->admin->display_field(array(
							
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
																		
																		echo $ltple->admin->display_field(array(
											
																			'id' 			=> $key,
																			'description' 	=> '',
																			'type'			=> 'number',
																			'placeholder'	=> $value,
																		),false,false);
																	}
																	elseif( empty($value) ){
																		
																		echo'<label>'.ucfirst($key).'</label>';
																		
																		echo $ltple->admin->display_field(array(
											
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
																		
																			echo $ltple->admin->display_field(array(
												
																				'id' 			=> $key,
																				'description' 	=> '',
																				'type'			=> 'select',
																				'options'		=> $options,
																				'placeholder'	=> '',
																			),false,false);									
																		}
																		else{
																			
																			echo $ltple->admin->display_field(array(
												
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
																		
																		echo $ltple->admin->display_field(array(
											
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
														
														echo '<div class="text-right" style="display:block;margin-top:5px;">';
															
															echo '<input class="btn btn-success btn-xs" type="submit" value="Add" />';
														
														echo '</div>';
														
													echo '</div>';
												
												echo '</form>';
												
											echo '</div>';	
											
										echo '</li>';
									}
								}
							}
							
						echo'</ul>';

						//output Tab panes
						  
						echo'<div class="tab-content" style="margin:10px;">';

							foreach( $apps as $app ){
								
								if( in_array('payment',$app->types) && $app->slug == $active ){
								
									echo'<div role="tabpanel" id="'.$app->slug.'">';

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
									
									echo'</div>';
									
									$active='';
								}
							}
							
						echo'</div>';
					}
					
				echo'</div>';//user-payment-urls
			}
			else{
				
				do_action('ltple_media_tab_' . $this->type,$section,$query_args);
			}

		echo'</div>';
		
	echo'</div>';	

echo'</div>';
