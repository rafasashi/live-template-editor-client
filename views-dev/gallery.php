<div id="layer_gallery" style="margin-top:20px;">

	<div class="col-xs-3 col-sm-2">
		<ul class="nav nav-tabs tabs-left">
			
			<li class="gallery_type_title">Template library</li>
			
			<?php
				
				$released 				= [];
				$released['demo'] 		= true;
				$released['emails'] 	= true;
				$released['chaturbate'] = true;
				$released['myfreecams'] = true;
				$released['standalone'] = true;
				$released['memes'] 		= true;
				
				$class=' class="active"';
				
				foreach($this->all->layerType as $term){
					
					if(isset($released[$term->slug])){
						
						echo '<li'.$class.'>';
						
							echo '<a href="#' . $term->slug . '" data-toggle="tab">' . $term->name . '</a>';
							
						echo '</li>';	

						$class='';						
					}
					elseif($this->user->is_admin){
						
						echo '<li'.$class.'>';
						
							echo '<a href="#' . $term->slug . '" data-toggle="tab">' . $term->name . ' <span class="btn-xs btn-warning pull-right"> admin </span></a>';
							
						echo '</li>';	

						$class='';							
					}
				}
			?>
			
		</ul>
	</div>

	<div class="col-xs-9 col-sm-10" style="border-left: 1px solid #ddd;">
		
		<div class="tab-content">
			
			<?php

			$items =[];
			
			$loop = new WP_Query( array( 'post_type' => 'cb-default-layer', 'posts_per_page' => -1 ) );
			
			while ( $loop->have_posts() ) : $loop->the_post(); 
				
				global $post;
				
				$editor_url = $this->urls->editor . '?uri='.str_replace(home_url().'/','',get_permalink());

				//get permalink
				
				$permalink = get_permalink($post);
				
				//get post_title
				
				$post_title = the_title('','',false);
				
				//get layer_type
				
				$layer_type='';
				
				$terms = wp_get_object_terms( $post->ID, 'layer-type' );
				
				if(isset($terms[0]->slug)){
					
					$layer_type=$terms[0]->slug;
				}
				
				if( isset($released[$layer_type]) || $this->user->is_admin ){
					
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
								
								$item.='<div class="thumb_wrapper">';
								
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

								//$item.='<a class="btn btn-warning" href="'. $permalink .'" target="_blank" title="'. $post_title .'">Preview</a>';
							
								$modal_id='modal_'.md5($permalink);
								
								$item.='<button type="button" class="btn btn-warning" data-toggle="modal" data-target="#'.$modal_id.'">'.PHP_EOL;
									
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
												
												if( $this->user->loggedin && $this->user_has_layer( $post->ID ) === true ){
													
													$item.= '<iframe data-src="'.$permalink.'" style="width: 100%;position:relative;bottom: 0;border:0;height: 350px;overflow: hidden;"></iframe>';											
												}
												else{
													
													$item.= get_the_post_thumbnail($post->ID, 'recentprojects-thumb');
												}

											$item.='</div>'.PHP_EOL;

											$item.='<div class="modal-footer">'.PHP_EOL;
											
												if($this->user->loggedin){

													$item.='<a class="btn btn-success" href="'. $editor_url .'" target="_self" title="Edit layer">Edit</a>';
												}
												else{
													
													$item.='<button type="button" class="btn btn-success" data-toggle="modal" data-target="#login_first">'.PHP_EOL;
													
														$item.='<span class="glyphicon glyphicon-lock" aria-hidden="true"></span> Edit'.PHP_EOL;
												
													$item.='</button>'.PHP_EOL;								
												}
												
											$item.='</div>'.PHP_EOL;
										  
										$item.='</div>'.PHP_EOL;
										
									$item.='</div>'.PHP_EOL;
									
								$item.='</div>'.PHP_EOL;						
							
								if($this->user->loggedin){
									
									if($this->user_has_layer( $post->ID ) === true){
										
										$item.='<a class="btn btn-success" href="'. $editor_url .'" target="_self" title="Edit layer">Edit</a>';
									}
									else{
										
										$item.='<button type="button" class="btn btn-success" data-toggle="modal" data-target="#upgrade_plan">'.PHP_EOL;
									
											$item.='<span class="glyphicon glyphicon-lock" aria-hidden="true"></span> Edit'.PHP_EOL;
								
										$item.='</button>'.PHP_EOL;
									}
								}
								else{
									
									$item.='<button type="button" class="btn btn-success" data-toggle="modal" data-target="#login_first">'.PHP_EOL;
									
										$item.='<span class="glyphicon glyphicon-lock" aria-hidden="true"></span> Edit'.PHP_EOL;
								
									$item.='</button>'.PHP_EOL;								
								}
								
							$item.='</div>';
						
						$item.='</div>';
						
					$item.='</div>';
					
					//merge item
					
					$items[$layer_type][$layer_range][]=$item;					
				}
				
			endwhile; wp_reset_query();
			
			$active_pane=' active';
			
			foreach($this->all->layerType as $term){
				
				if( isset($released[$term->slug]) || $this->user->is_admin ){
					
					echo'<div class="tab-pane'.$active_pane.'" id="' . $term->slug . '">';
						
						//output Nav tabs
						
						echo'<ul class="nav nav-pills" role="tablist">';

						if(!empty($items[$term->slug])){
							
							$active=' class="active"';
							
							foreach($items[$term->slug] as $range => $range_items){
								
								echo'<li role="presentation"'.$active.'><a href="#' . $term->slug .  '_' . $range . '" aria-controls="'. $term->slug .  '_' . $range.'" role="tab" data-toggle="tab">'.strtoupper(str_replace(array('-','_'),' ',$range)).'</a></li>';
								
								$active='';
							}							
						}

						
						echo'</ul>';

						//output Tab panes
						  
						echo'<div class="tab-content row" style="margin-top:20px;">';
							
							if(!empty($items[$term->slug])){
								
								$active=' active';
							
								foreach($items[$term->slug] as $range => $range_items){
									
									echo'<div role="tabpanel" class="tab-pane'.$active.'" id="' . $term->slug .  '_' . $range . '">';
									
									foreach($range_items as $item){

										echo $item;
									}
									
									echo'</div>';
									
									$active='';
								}
							
							}
							
						echo'</div>';
						
						
						if(!$this->user->loggedin){

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
					
					$active_pane='';
				}
			}
						
			?>						

			<!--<div class="tab-pane" id="saved-layers"> Tab.</div>-->
		  
		</div>
		
	</div>	

</div>