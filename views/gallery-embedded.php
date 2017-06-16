<?php 
	
	// get embedded url
	
	global $post;
	
	$embedded_url = $this->layer->embedded['scheme'].'://'.$this->layer->embedded['host'].$this->layer->embedded['path'].'wp-admin/post.php?post='.$this->layer->embedded['p'].'&action=edit';

	// get message
	
	if(!empty($this->message)){ 
	
		echo $this->message;
	}
	
	$layer_type = ( !empty($_GET['gallery']) ? $_GET['gallery'] : '' );
	
	if( empty($layer_type) ){

		foreach($this->all->layerType as $term){
						
			if( $term->visibility == 'anyone' || $this->user->is_admin ){
			
				$layer_type = $term->slug;
				break;
			}
		}			
	}
	
	echo '<div id="layer_gallery">';

		echo '<div class="col-xs-3 col-sm-2" style="padding:0;">';
			echo '<ul class="nav nav-tabs tabs-left">';
				
				echo '<li class="gallery_type_title">Template library</li>';

					$class='';
					
					foreach($this->all->layerType as $term){
						
						$gallery_url = $this->urls->current . '&gallery=' . $term->slug;
						
						if( $term->slug == $layer_type ){
							
							$class=' class="active"';
						}
						else{
							
							$class='';
						}

						if($term->visibility == 'anyone'){
							
							echo '<li'.$class.'>';
							
								echo '<a href="' . $gallery_url . '">' . $term->name . '</a>';
								
							echo '</li>';					
						}
						elseif( $this->user->is_admin ){
							
							echo '<li'.$class.'>';
							
								echo '<a href="' . $gallery_url . '">' . $term->name . ' <span class="label label-warning pull-right"> admin </span></a>';
								
							echo '</li>';						
						}
					}
				
			echo'</ul>';
		echo'</div>';

		echo'<div class="col-xs-9 col-sm-10" style="border-left: 1px solid #ddd;background:#fff;padding-bottom:15px;padding-top:15px;min-height:700px;">';
			
			echo'<div class="tab-content">';

				$items =[];
				
				$loop = new WP_Query(array( 
				
					'post_type' 	=> 'cb-default-layer', 
					'posts_per_page'=> -1,
					'tax_query' 	=> array(
						array(
							'taxonomy' 	=> 'layer-type',
							'field' 	=> 'slug',
							'terms' 	=> $layer_type,
							'include_children' => false
						)
					)					
				));
				
				foreach($this->all->layerType as $term){
					
					if( $term->slug == $layer_type ){
				
						while ( $loop->have_posts() ) : $loop->the_post(); 
							
							global $post;
							
							$permalink = get_permalink($post);

							//get editor_url

							$editor_url = $this->urls->editor . '?uri='.$post->ID;
						
							//get post_title
							
							$post_title = the_title('','',false);
							
							if( $term->visibility == 'anyone' || $this->user->is_admin ){
								
								//get layer_range
								
								$layer_range='out of range';
								
								$terms = wp_get_object_terms( $post->ID, 'layer-range' );
								
								if(!empty($terms[0]->slug)){
									
									$layer_range=$terms[0]->slug;
								}	

								//get item
								
								$item='';
								
								$item.='<div class="' . implode( ' ', get_post_class("col-xs-12 col-sm-6 col-md-4",$post->ID) ) . '" id="post-' . $post->ID . '">';
									
									$item.='<div class="panel panel-default" style="border-left:1px solid #DDD;">';
										
										$item.='<div class="panel-heading">';
											
											$item.='<b>' . $post_title . '</b>';
											
										$item.='</div>';

										$item.='<div class="panel-body">';
											
											$item.='<div class="thumb_wrapper" style="background:#ffffff;">';
											
												//$item.= '<a class="entry-thumbnail" href="'. $permalink .'" target="_blank" title="'. $post_title .'">';

												if ( $image_id = get_post_thumbnail_id( $post->ID ) ){
													
													if ($src = wp_get_attachment_image_src( $image_id, 'full' )){

														$item.= '<img class="lazy" data-original="' . $src[0] . '"/>';
													}
												
												}
												//$item.= '</a>';
											
											$item.='</div>'; //thumb_wrapper
											
											$excerpt= strip_tags(get_the_excerpt( $post->ID ),'<span>');
											
											$item.='<div class="post_excerpt" style="overflow:hidden;height:20px;">';
											
												if(!empty($excerpt)){
													
													$item.=$excerpt;
												}
												else{
													
													$item.=$post_title;
												}
												
											$item.='</div>';
											
										$item.='</div>';
										
										$item.='<div class="panel-footer text-right">';

											$modal_id='modal_'.md5($permalink);
											
											$item.='<button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#'.$modal_id.'">'.PHP_EOL;
												
												$item.='Preview'.PHP_EOL;
											
											$item.='</button>'.PHP_EOL;

											$item.='<div class="modal fade" id="'.$modal_id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'.PHP_EOL;
												
												$item.='<div class="modal-dialog modal-lg" role="document">'.PHP_EOL;
													
													$item.='<div class="modal-content">'.PHP_EOL;
													
														$item.='<div class="modal-header">'.PHP_EOL;
															
															$item.='<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
															
															$item.='<h4 class="modal-title text-left" id="myModalLabel">Preview</h4>'.PHP_EOL;
														
														$item.='</div>'.PHP_EOL;
													  
														$item.='<div class="modal-body">'.PHP_EOL;
															
															if( $this->user->loggedin && $this->plan->user_has_layer( $post->ID ) === true ){
																
																$item.= '<iframe data-src="'.$permalink.'" style="width: 100%;position:relative;bottom: 0;border:0;height: 350px;overflow: hidden;"></iframe>';											
															}
															else{
																
																$item.= get_the_post_thumbnail($post->ID, 'recentprojects-thumb');
															}

														$item.='</div>'.PHP_EOL;

														$item.='<div class="modal-footer">'.PHP_EOL;
														
															if($this->user->loggedin){

																//$item.='<a class="btn btn-sm btn-success" href="'. $editor_url .'" target="_self" title="Edit layer">Edit</a>';
															
																$item.='<form target="_top"  method="post" action="'.$embedded_url.'" style="display:inline-block;">';
																
																	$item.='<input type="hidden" name="defaultLayerId" value="' . $post->ID . '" />';
																	
																	$item.='<button class="btn btn-sm btn-success" title="Edit layer">Edit</button>';
																
																$item.='</form>';
															}
															else{
																
																$item.='<button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#login_first">'.PHP_EOL;
																
																	$item.='<span class="glyphicon glyphicon-lock" aria-hidden="true"></span> Edit'.PHP_EOL;
															
																$item.='</button>'.PHP_EOL;								
															}
															
														$item.='</div>'.PHP_EOL;
													  
													$item.='</div>'.PHP_EOL;
													
												$item.='</div>'.PHP_EOL;
												
											$item.='</div>'.PHP_EOL;						
										
											if($this->user->loggedin){
												
												if($this->plan->user_has_layer( $post->ID ) === true){
													
													//$item.='<a class="btn btn-sm btn-success" href="'. $editor_url .'" target="_self" title="Edit layer">Edit</a>';
				
													$item.='<form target="_top" method="post" action="'.$embedded_url.'" style="display:inline-block;">';
													
														$item.='<input type="hidden" name="defaultLayerId" value="' . $post->ID . '" />';

														$item.='<button class="btn btn-sm btn-success" title="Edit layer">Edit</button>';
													
													$item.='</form>';
												}
												else{
													
													$item.='<button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#upgrade_plan">'.PHP_EOL;
												
														$item.='<span class="glyphicon glyphicon-lock" aria-hidden="true"></span> Edit'.PHP_EOL;
											
													$item.='</button>'.PHP_EOL;
												}
											}
											else{
												
												$item.='<button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#login_first">'.PHP_EOL;
												
													$item.='<span class="glyphicon glyphicon-lock" aria-hidden="true"></span> Edit'.PHP_EOL;
											
												$item.='</button>'.PHP_EOL;								
											}
											
										$item.='</div>';
									
									$item.='</div>';
									
								$item.='</div>';
								
								//merge item
								
								$items[$layer_range][]=$item;					
							}
							
						endwhile; wp_reset_query();
					}
				}
			
				echo'<div class="tab-pane active" id="' . $layer_type . '">';
					
					//output Nav tabs
					
					echo'<ul class="nav nav-pills" role="tablist">';

					if(!empty($items)){
						
						$active=' class="active"';
						
						foreach($items as $range => $range_items){
							
							echo'<li role="presentation"'.$active.'><a href="#' . $range . '" aria-controls="' . $range . '" role="tab" data-toggle="tab">'.strtoupper(str_replace(array('-','_'),' ',$range)).'</a></li>';
							
							$active='';
						}							
					}

					echo'</ul>';

					//output Tab panes
					  
					echo'<div class="tab-content row" style="margin-top:20px;">';
						
						if(!empty($items)){
							
							$active=' active';
						
							foreach($items as $range => $range_items){
								
								echo'<div role="tabpanel" class="tab-pane'.$active.'" id="' . $range . '">';
								
								foreach($range_items as $item){

									echo $item;
								}
								
								echo'</div>';
								
								$active='';
							}
						
						}
						
					echo'</div>';
					
					if( !$this->user->loggedin ){

						// login modal
					
						echo '<div class="modal fade" id="login_first" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'.PHP_EOL;
							
							echo '<div class="modal-dialog modal-lg" role="document" style="width:500px !important;">'.PHP_EOL;
								
								echo '<div class="modal-content" style="height:270px !important;">'.PHP_EOL;
									
									echo '<div class="modal-header">'.PHP_EOL;
										
										echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
										
										echo '<h4 class="modal-title text-left" id="myModalLabel">You need to Login first</h4>'.PHP_EOL;
									
									echo '</div>'.PHP_EOL;
								  
									echo '<div class="modal-body text-center">'.PHP_EOL;

										echo '<div style="display:block;margin:30px;">';
										
											echo '<a style="display:block;width:100%;" class="btn-lg btn-success" href="'.wp_login_url( 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] ).'" target="_parent" title="Login">Login</a>';
										
										echo '</div>';
										
										echo '<div style="display:block;margin:30px;">';
										
											echo '<a style="display:block;width:100%;" class="btn-lg btn-info" href="'. wp_login_url() .'?action=register" target="_parent" title="Register">Register</a>';
										
										echo '</div>';
										
									echo '</div>'.PHP_EOL;

								echo '</div>'.PHP_EOL;
								
							echo '</div>'.PHP_EOL;
							
						echo '</div>'.PHP_EOL;
					}
					else{
						
						// upgrade plan modal
					
						echo '<div class="modal fade" id="upgrade_plan" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'.PHP_EOL;
							
							echo '<div class="modal-dialog modal-lg" role="document" style="width:500px !important;">'.PHP_EOL;
								
								echo '<div class="modal-content" style="height:270px !important;">'.PHP_EOL;
									
									echo '<div class="modal-header">'.PHP_EOL;
										
										echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
										
										echo '<h4 class="modal-title text-left" id="myModalLabel">This template is not included in your plan</h4>'.PHP_EOL;
									
									echo '</div>'.PHP_EOL;
								  
									echo '<div class="modal-body text-center">'.PHP_EOL;

										echo '<div style="display:block;margin:30px;">';
										
											echo '<a style="display:block;width:100%;" class="btn-lg btn-success" href="' . $this->urls->plans . '" target="_parent" title="View plans">View plans</a>';
										
										echo '</div>';
										
										echo '<div style="display:block;margin:30px;">';
										
											echo '<a style="display:block;width:100%;" class="btn-lg btn-info" href="' .site_url().'/contact/'. '" target="_parent" title="Contact us">Contact us</a>';
										
										echo '</div>';
										
									echo '</div>'.PHP_EOL;

								echo '</div>'.PHP_EOL;
								
							echo '</div>'.PHP_EOL;
							
						echo '</div>'.PHP_EOL;				
					}
					
				echo '</div>';

			echo'</div>';
			
		echo'</div>	';

	echo'</div>';