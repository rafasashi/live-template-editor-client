<?php
	
	if(isset($_SESSION['message'])){ 
	
		//output message
	
		echo $_SESSION['message'];
		
		//reset message
		
		$_SESSION['message'] ='';
	}	
	
	$inWidget = false;
	$output='default';
	$target='_self';

	if( isset($_GET['output']) && $_GET['output'] == 'widget' ){
		
		$inWidget = true;
		$output=$_GET['output'];
		$target='_blank';
	}
	
	$upload_app_id = get_option( $this->_base . 'wpcom_main_account' );

	// get current tab
	
	$currentTab = 'image-library';
	
	if( !empty($_GET['media']) ){
		
		$currentTab = $_GET['media'];
	}
	
	if( $currentTab == 'image-library' ){
		
		//------------------ get default images ------------

		//get image types

		$default_images = [];

		foreach($this->image->types as $term){
			
			$default_images[$term->slug] = [];
		}
			
		$loop = new WP_Query( array( 'post_type' => 'default-image', 'posts_per_page' => -1 ) );
		
		//var_dump($loop);exit;
		
		$home_url = home_url();
		
		while ( $loop->have_posts() ) : $loop->the_post(); 
			
			global $post;
			$image = $post;

			$editor_url = $this->urls->editor . '?uri=' . $image->ID;

			//get permalink
			
			$permalink = get_permalink($image);
			
			//get post_title
			
			$image_title = the_title('','',false);
			
			//get terms
			
			$terms = wp_get_object_terms( $image->ID, 'image-type' );
			//var_dump($terms);exit;
			
			//get image_type
			$image_type='image';
			
			if( !isset($terms->errors) && isset($terms[0]->slug) ){
				
				$image_type=$terms[0]->slug;
			}
			
			//get item
			
			$item='';
			
			$item.='<div class="' . implode( ' ', get_post_class("col-xs-12 col-sm-6 col-md-4 col-lg-3",$image->ID) ) . '" id="post-' . $image->ID . '">';
				
				$item.='<div class="panel panel-default" style="border-left:1px solid #DDD;">';
					
					$item.='<div class="panel-heading">';

						$item.='<b>' . $image_title . '</b>';
						
					$item.='</div>';

					$item.='<div class="panel-body">';
						
						$item.='<div class="thumb_wrapper">';
						
							$item.= '<img class="lazy" data-original="'.$image->post_content.'" />';
						
						$item.='</div>'; //thumb_wrapper
						
						$item.='<div class="text-right">';

							if($inWidget){
								
								if($this->user->plan["info"]["total_price_amount"] > 0 ){
									
									$item.='<a class="btn-sm btn-primary insert_media" href="#" data-src="'.$image->post_content.'">Insert</a>';
								}
								else{ 
									
									$item.='<a href="#" class="btn-sm btn-primary" data-toggle="popover" data-placement="top" title="Pro users only" data-content="You need a paid plan ' . PHP_EOL . 'to unlock this action"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span> Insert</a>';
								}								
							}
							else{

								$item.='<input style="width:100%;padding: 2px;" type="text" value="'. $image->post_content .'" />';
							}
							
						$item.='</div>';
						
					$item.='</div>'; //panel-body

				$item.='</div>';
				
			$item.='</div>';
			
			//merge item
			
			$default_images[$image_type][]=$item;
			
		endwhile; wp_reset_query();		

	}
	elseif( $currentTab == 'user-images' ){
			
		//get user images
		
		$image_providers = [];
		
		if( $this->user->ID  > 0 ){
			
			//get user apps
			
			$loop = new WP_Query( array( 'post_type' => 'user-image', 'posts_per_page' => -1, 'author' => $this->user->ID ) );
			
			//var_dump($loop);exit;
			
			while ( $loop->have_posts() ) : $loop->the_post(); 
				
				global $post;
				$image = $post;

				$editor_url = $this->urls->editor . 'editor/?uri=' . $image->ID;

				//get permalink
				
				$permalink = get_permalink($image);
				
				//get post_title
				
				$image_title = the_title('','',false);
				
				//get terms
				
				$terms = wp_get_object_terms( $image->ID, 'app-type' );
				
				//get image_provider
				
				$image_provider = 'url';
				
				if( !isset($terms->errors) && isset($terms[0]->slug) ){
					
					if( $imageUploaded = get_post_meta($image->ID, 'imageUploaded', true) ){
						
						$image_provider = 'upload';
					}
					else{
						
						$image_provider = $terms[0]->slug;
					}
				}
				
				//get item
				
				$item='';
				
				$item.='<div class="' . implode( ' ', get_post_class("col-xs-12 col-sm-6 col-md-4 col-lg-3",$image->ID) ) . '" id="post-' . $image->ID . '">';
					
					$item.='<div class="panel panel-default" style="border-left:1px solid #DDD;">';
						
						$item.='<div class="panel-heading">';
							
							$item.='<b>' . $image_title . '</b>';
							
							if(!$inWidget){
							
								$item.='<a class="btn-xs btn-danger" href="' . $this->urls->editor . '?media=user-images&output='.$output.'&uri=' . $image->ID . '&imgAction=delete" style="padding: 0px 5px;position: absolute;top: 11px;right: 25px;font-weight: bold;">x</a>';
							}
							
						$item.='</div>';

						$item.='<div class="panel-body">';
							
							$item.='<div class="thumb_wrapper">';
							
								//$item.= '<a class="entry-thumbnail" href="'. $permalink .'" target="_blank" title="'. $image_title .'">';
									
									//$item.= get_the_post_thumbnail($image->ID, 'recentprojects-thumb');
									
									$item.= '<img class="lazy" data-original="'.$image->post_content.'" />';
									
								//$item.= '</a>';
							
							$item.='</div>'; //thumb_wrapper
							
							//$item.= get_the_excerpt( $image->ID );

							$item.='<div class="text-right">';

								if($inWidget){
									
									if($this->user->plan["info"]["total_price_amount"]>0){
										
										$item.='<a class="btn-sm btn-primary insert_media" href="#" data-src="'.$image->post_content.'">Insert</a>';
									}
									else{ 
										
										$item.='<a href="#" class="btn-sm btn-primary" data-toggle="popover" data-placement="top" title="Pro users only" data-content="You need a paid plan ' . PHP_EOL . 'to unlock this action"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span> Insert</a>';
									}
								}
								else{
									
									$item.='<input style="width:100%;padding: 2px;" type="text" value="'. $image->post_content .'" />';
								}
								
							$item.='</div>';							
							
						$item.='</div>'; //panel-body

					$item.='</div>';
					
				$item.='</div>';
				
				//merge item
				
				$image_providers[$image_provider][]=$item;
				
			endwhile; wp_reset_query();					
		}
	}
	elseif( $currentTab == 'user-payment-urls' ){
		
		//get user bookmarks
		
		$bookmarks = [];
		
		if( $this->user->ID  > 0 ){
			
			//get user apps
			
			$loop = new WP_Query( array( 'post_type' => 'user-bookmark', 'posts_per_page' => -1, 'author' => $this->user->ID ) );
			
			//var_dump($loop);exit;
			
			while ( $loop->have_posts() ) : $loop->the_post(); 
				
				global $post;
				$bookmark = $post;

				$editor_url = $this->urls->editor . 'editor/?uri=' . $bookmark->ID;

				//get permalink
				
				$permalink = get_permalink($bookmark);
				
				//get post_title
				
				$bookmark_title = the_title('','',false);
				
				//get terms
				
				$terms = wp_get_object_terms( $bookmark->ID, 'app-type' );
				
				//get bookmark_provider
				
				$bookmark_provider = $terms[0]->slug;

				//get item
				
				$item='';
				
				$item.='<div class="col-xs-2 col-sm-2 col-lg-1">';

					$item.='<img class="lazy" data-original="' . $this->assets_url . '/images/payment.png" />';
						
				$item.='</div>';

				$item.='<div class="col-xs-8 col-sm-8 col-lg-9">';

					$item.='<b>' . $bookmark_title . '</b>';
					$item.='<br>';
					$item.='<input style="width:100%;padding: 2px;" type="text" value="'. $bookmark->post_content .'" />';

				$item.='</div>';
				
				$item.='<div class="col-xs-2 col-sm-2 col-lg-2">';
				
					if($inWidget){

						if($this->user->plan["info"]["total_price_amount"]>0){
							
							$item.='<a style="display:block;margin-top:11px;" class="btn-sm btn-primary insert_media" href="#" data-src="'.$bookmark->post_content.'">Insert</a>';
						}
						else{ 
							
							$item.='<a style="display:block;margin-top:11px;" href="#" class="btn-sm btn-primary" data-toggle="popover" data-placement="top" title="Pro users only" data-content="You need a paid plan ' . PHP_EOL . 'to unlock this action"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span> Insert</a>';
						}
					}
					else{
						
						$item.='<a class="btn-xs btn-danger" href="' . $this->urls->editor . '?media=user-payment-urls&output='.$output.'&id='. $bookmark->ID . '&action=deleteBookmark&app='.$bookmark_provider.'" style="padding: 0px 5px;position: absolute;top: 11px;right: 25px;font-weight: bold;">x</a>';
					}
				
				$item.='</div>';
				
				//merge item
				
				$bookmarks[$bookmark_provider][]=$item;
				
			endwhile; wp_reset_query();					
		}
	}
	
	// output library
		
	echo'<div id="media_library">';

		echo'<div class="col-xs-3 col-sm-2" style="padding:0;">';
		
			echo'<ul class="nav nav-tabs tabs-left">';
				
				echo'<li class="gallery_type_title">Images</li>';
				
				echo'<li'.( $currentTab == 'image-library' ? ' class="active"' : '' ).'><a href="'.$this->urls->editor . '?media=image-library&output='.$output.'">Image Library</a></li>';
				echo'<li'.( $currentTab == 'user-images' ? ' class="active"' : '' ).'><a href="'.$this->urls->editor . '?media=user-images&output='.$output.'">My Images</a></li>';
				
				echo'<li class="gallery_type_title">Bookmarks</li>';
				echo'<li'.( $currentTab == 'user-payment-urls' ? ' class="active"' : '' ).'><a href="'.$this->urls->editor . '?media=user-payment-urls&output='.$output.'">Payment Urls</a></li>';
					
			echo'</ul>';
		echo'</div>';

		echo'<div class="col-xs-9 col-sm-10" style="border-left: 1px solid #ddd;background:#fff;padding-bottom:15px;padding-top:15px;min-height:700px;">';

			echo'<div class="tab-content">';
			
				if( $currentTab == 'image-library' ){
			  
					//output default images
					
					echo'<div id="image-library">';
					
						echo'<ul class="nav nav-pills" role="tablist">';
						
						$active=' class="active"';
						
						foreach($default_images as $image_type => $items){
							
							if($image_type != ''){
								
								echo'<li role="presentation"'.$active.'><a href="#'.$image_type.'" aria-controls="'.$image_type.'" role="tab" data-toggle="tab">'.strtoupper(str_replace(array('-','_'),' ',$image_type)).'</a></li>';
							}

							$active='';
						}
						
						echo'</ul>';

						//output Tab panes
						  
						echo'<div class="tab-content row" style="margin-top:20px;">';
							
							$active=' active';
							
							foreach($default_images as $image_type => $items){
								
								echo'<div role="tabpanel" class="tab-pane'.$active.'" id="'.$image_type.'">';
									
									foreach($items as $item){

										echo $item;
									}

								echo'</div>';
								
								$active='';
							}
							
						echo'</div>';
						
					echo'</div>';
				}
				
				if( $currentTab == 'user-images' ){

					//output user images	
					
					echo '<div id="user-images">';

						if( !empty($_GET['app']) && !empty($this->apps->{$_GET['app']}->message) ){
							
							echo $this->apps->{$_GET['app']}->message;
						}
						else{	
					
							echo'<ul class="nav nav-pills" role="tablist">';
							
							//get app list
							
							$apps = [];

							$item = new stdClass();
							$item->name 	= 'Upload';
							$item->slug 	= 'upload';
							$item->types 	= ['images'];
							
							$apps[] = $item;					

							$item = new stdClass();
							$item->name 	= 'Url';
							$item->slug 	= 'url';
							$item->types 	= ['images'];
							
							$apps[] = $item;

							if( !empty($this->apps->list) ){
								
								$apps = array_merge($apps,$this->apps->list);
							}
							
							
							//output list
							
							$active=' class="active"';
							
							foreach($apps as $app){
								
								if( in_array('images',$app->types) ){
								
									echo'<li role="presentation"'.$active.'><a href="#'.$app->slug.'" aria-controls="'.$app->slug.'" role="tab" data-toggle="tab">'.strtoupper($app->name).'</a></li>';

									$active='';
								}
							}
							
							echo'</ul>';

							//output Tab panes
							  
							echo'<div class="tab-content row" style="margin-top:20px;">';

								$active	 = ' active';

								foreach( $apps as $app ){
									
									if( in_array('images',$app->types) ){
									
										echo'<div role="tabpanel" class="tab-pane'.$active.'" id="'.$app->slug.'">';

											if( $app->slug == 'upload' ){

												echo'<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">';
												
													echo'<div class="panel panel-default" style="background:#efefef;border-left:1px solid #ddd;">';
														
														echo '<div class="panel-heading"><b>Upload image</b></div>';
														
														$media_url = $this->urls->editor . '?media=user-images';
														
														if( isset($_GET['output']) && $_GET['output'] == 'widget' ){
																	
															$media_url .= '&output=widget';
														}														
														
														echo '<form style="padding:10px;" target="_self" action="'.$media_url.'" id="saveImageForm" method="post" enctype="multipart/form-data">';
														
															echo'<input type="hidden" id="imgHost" name="imgHost" value="'.$upload_app_id.'" />';
														
															echo '<label>Image File</label>';
														
															echo '<input style="font-size:15px;padding:5px;margin:10px 0;" type="file" name="imgFile" id="imgFile" class="form-control required" />';
															
															echo '<input type="hidden" name="imgAction" id="imgAction" value="upload" />';
															
															wp_nonce_field( 'user_image_nonce', 'user_image_nonce_field' );
															
															echo '<input type="hidden" name="submitted" id="submitted" value="true" />';

															echo '<button class="btn btn-primary" type="button">Upload</button>';

														echo '</form>';														
														
													echo'</div>';//add-image-wrapper												
					
													
												echo '</div>';	
												
												if(isset($image_providers[$app->slug])){
													
													foreach($image_providers[$app->slug] as $item){

														echo $item;
													}								
												}												
											}
											else{
											
												// add images based on provider
												
												echo'<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">';
												echo'<div class="panel panel-default" style="background:#efefef;border-left:1px solid #ddd;">';
													
													if( !empty($app->term_id) ){
														
														echo '<div class="panel-heading"><b>Import image urls</b></div>';
														
														foreach( $this->user->apps as $user_app ){

															if(strpos($user_app->post_name ,$app->slug. '-')===0){
																
																echo '<a href="'.$this->apps->getAppUrl($app->slug,'importImg','user-images').'&output='.$output.'&id=' . $user_app->ID .'" style="width:100%;text-align:left;" class="btn btn-md btn-info"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span> '.ucfirst($user_app->post_title).'</a>';
															}
														}
														
														echo '<a target="'.$target.'" href="'.$this->apps->getAppUrl($app->slug,'connect','user-images').'" style="width:100%;text-align:left;" class="btn btn-md btn-default add_account"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add '.$app->name.' account</a>';
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
										
										$active='';
									}
								}
								
							echo'</div>';
						}
					
					echo'</div>';//user-images
				}
			
				if( $currentTab == 'user-payment-urls' ){
				
					//output user-payment-urls
					
					echo '<div id="user-payment-urls">';
					
						if( !empty($_GET['app']) && !empty($this->apps->{$_GET['app']}->message) ){
							
							echo $this->apps->{$_GET['app']}->message;
						}
						else{			
						
							echo'<ul class="nav nav-pills" role="tablist">';
							
							//get app list

							$apps = $this->apps->list;
							
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
											echo'<div class="panel panel-default" style="background:#efefef;border-left:1px solid #ddd;">';

												echo '<div class="panel-heading"><b>Add '.ucfirst($app->name).' link</b></div>';
													
												//get app ids
												
												$options =[];
												
												foreach( $this->user->apps as $user_app ){

													if(strpos($user_app->post_name ,$app->slug. '-')===0){

														$options[$user_app->ID] = $user_app->post_title;
													}
												}

												if(!empty($options)){

													echo '<form style="padding:10px;" target="_self" action="'.$this->urls->editor . '?media=user-payment-urls'.'#' . $app->slug . '" class="saveBookmarkForm" method="post">';
														
														echo '<div style="padding-bottom:10px;display:block;">';
															
															echo'<label>Title</label>';
															
															echo '<input type="text" name="bookmarkTitle" id="bookmarkTitle" value="" class="form-control required" placeholder="Product 1" />';
																										
															echo'<label>Account</label>';
					
															echo $this->admin->display_field(array(
								
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
																			
																			echo $this->admin->display_field(array(
												
																				'id' 			=> $key,
																				'description' 	=> '',
																				'type'			=> 'number',
																				'placeholder'	=> $value,
																			));
																		}
																		elseif( empty($value) ){
																			
																			echo'<label>'.ucfirst($key).'</label>';
																			
																			echo $this->admin->display_field(array(
												
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
																			
																				echo $this->admin->display_field(array(
													
																					'id' 			=> $key,
																					'description' 	=> '',
																					'type'			=> 'select',
																					'options'		=> $options,
																					'placeholder'	=> '',
																				));									
																			}
																			else{

																				echo $this->admin->display_field(array(
													
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
																			
																			echo $this->admin->display_field(array(
												
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
												
												echo '<a target="_self" href="'.$this->apps->getAppUrl($app->slug,'connect','user-payment-urls') .'&output='.$output. '#' . $app->slug . '" style="width:100%;text-align:left;" class="btn btn-md btn-default add_account"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add '.$app->name.' account</a>';
												
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