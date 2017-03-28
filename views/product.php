<?php 

	if(!empty($this->message)){ 
	
		echo $this->message;
	}
	
	$permalink = get_permalink($this);
	
	$editor_url = $this->parent->urls->editor . '?uri='.str_replace( $this->parent->urls->home . '/','',$permalink);
	
	$product_url = $this->parent->urls->product . '?id=' . $this->ID;
	
	$modal_id='modal_'.md5($permalink);

	echo'<h1><i class="fa fa-shopping-cart" aria-hidden="true"></i> ' . $this->post_title . ' template</h1>';
	
	echo'<div id="layer_detail" class="col-xs-12">';

		echo'<div class="row">';
			
			echo'<div class="col-xs-12 col-sm-6 col-lg-8">';
				
				echo'<div style="max-height:300px;overflow:hidden;border-radius:10px;">';
				
					echo'<img class="img-responsive" src="' . $this->image . '" alt="">';
				
				echo'</div>';
				
			echo'</div>';
			
			echo'<div class="col-xs-12 col-sm-6 col-lg-4">';

				echo'<div class="row bs-callout bs-callout-primary">';
				
					echo'<div class="col-xs-4 text-right" style="padding:15px 0;text-align:center;font-weight:bold;font-size:21px;">';
						
						echo 'from ';
						echo $this->info['total_price_currency'];
						echo $this->info['total_price_amount'];
					
					echo'</div>';
					
					echo'<div class="col-xs-8 text-right" style="padding:5px 0;">';
						
						echo'<button type="button" class="btn btn-warning btn-lg" data-toggle="modal" data-target="#'.$modal_id.'">'.PHP_EOL;
							
							echo'Preview'.PHP_EOL;
						
						echo'</button>'.PHP_EOL;


							echo'<div class="modal fade" id="'.$modal_id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'.PHP_EOL;
								
								echo'<div class="modal-dialog modal-lg" role="document">'.PHP_EOL;
									
									echo'<div class="modal-content">'.PHP_EOL;
									
										echo'<div class="modal-header">'.PHP_EOL;
											
											echo'<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
											
											echo'<h4 class="modal-title text-left" id="myModalLabel">Preview</h4>'.PHP_EOL;
										
										echo'</div>'.PHP_EOL;
									  
										echo'<div class="modal-body">'.PHP_EOL;
											
											if( $this->parent->user->loggedin && $this->parent->plan->user_has_layer( $this->ID ) === true ){
												
												echo '<iframe data-src="'.$permalink.'" style="width: 100%;position:relative;bottom: 0;border:0;height: 350px;overflow: hidden;"></iframe>';											
											}
											else{
												
												echo get_the_post_thumbnail($this->ID, 'recentprojects-thumb');
											}

										echo'</div>'.PHP_EOL;

										echo'<div class="modal-footer">'.PHP_EOL;
										
											if($this->parent->user->loggedin){

												echo'<a class="btn btn-lg btn-success" href="'. $editor_url .'" target="_self" title="Edit layer">Edit</a>';
											}
											else{
												
												echo'<button type="button" class="btn btn-lg btn-success" data-toggle="modal" data-target="#login_first">'.PHP_EOL;
												
													echo'<span class="glyphicon glyphicon-lock" aria-hidden="true"></span> Edit'.PHP_EOL;
											
												echo'</button>'.PHP_EOL;								
											}
											
										echo'</div>'.PHP_EOL;
									  
									echo'</div>'.PHP_EOL;
									
								echo'</div>'.PHP_EOL;
								
							echo'</div>'.PHP_EOL;

							if($this->parent->user->loggedin){
								
								if($this->parent->plan->user_has_layer( $this->ID ) === true){
									
									echo'<a class="btn btn-lg btn-success" href="'. $editor_url .'" target="_self" title="Edit layer">Edit</a>';
								}
								else{
									
									echo'<button type="button" class="btn btn-lg btn-success" data-toggle="modal" data-target="#upgrade_plan">'.PHP_EOL;
								
										echo'<span class="glyphicon glyphicon-lock" aria-hidden="true"></span> Edit'.PHP_EOL;
							
									echo'</button>'.PHP_EOL;
								}
							}
							else{
								
								echo'<button type="button" class="btn btn-lg btn-success" data-toggle="modal" data-target="#login_first">'.PHP_EOL;
								
									echo'<span class="glyphicon glyphicon-lock" aria-hidden="true"></span> Edit'.PHP_EOL;
							
								echo'</button>'.PHP_EOL;								
							}
								
					echo'</div>';
					
				echo'</div>';			
			
				echo'<p style="margin:5px;height:70px;overflow:hidden;">';

					echo '<b>' . $this->post_title . '</b> is a ';
					
					$layer_type = '';
					
					foreach($this->taxonomies['layer-type']['terms'] as $slug => $term ){
						
						if( $term['has_term'] ){
							
							$layer_type = $term['name'];
							
							echo '<b>'.$layer_type.'</b> ';
							break;
						}
					}
				
					echo' template ';
					
					$layer_range = '';
					
					foreach($this->taxonomies['layer-range']['terms'] as $slug => $term ){
						
						if( $term['has_term'] ){
							
							$layer_range = $term['name'];
							
							echo'from the <b>'.$layer_range.' range</b> ';
							break;
						}
					}					
					
					echo'available for live edition via our online tool. ';
									
					echo $this->post_excerpt;
				
				echo'</p>';
				
				echo'<div id="share_product" style="margin:25px 0;font-size:40px;">';
				
					echo'<a href="https://twitter.com/intent/tweet?text=' . urlencode( 'Awesome ' . $this->post_title . ' template! ' . $product_url ) . '" target="_blank" title="share on twitter" style="margin:5px;">';
					
						echo'<i class="fa fa-twitter-square" aria-hidden="true"></i>';
					
					echo'</a>';
					
					echo'<a href="https://www.facebook.com/sharer/sharer.php?u='.urlencode( $product_url ).'&t='.urlencode( 'Awesome ' . $this->post_title . ' template!' ).'" target="_blank" title="share on facebook" style="margin:5px;">';
				
						echo'<i class="fa fa-facebook-square" aria-hidden="true"></i>';
					
					echo'</a>';
					
					echo'<a href="https://plus.google.com/share?url='.urlencode( $product_url ).'" target="_blank" title="share on google plus" style="margin:5px;">';
					
						echo'<i class="fa fa-google-plus-square" aria-hidden="true"></i>';
				
					echo'</a>';
					
					echo'<a href="http://pinterest.com/pin/create/link/?url='.urlencode( $product_url ).'&description='.urlencode( 'Awesome ' . $this->post_title . ' template!' ).'" target="_blank" title="share on pinterest" style="margin:5px;">';
					
						echo'<i class="fa fa-pinterest-square" aria-hidden="true"></i>';
				
					echo'</a>';
					
					echo'<a href="https://www.linkedin.com/cws/share?url='.urlencode( $product_url ).'&title='.urlencode( $this->post_title ).'&summary='.urlencode( $this->post_excerpt ).'" target="_blank" title="share on linkedin" style="margin:5px;">';
					
						echo'<i class="fa fa-linkedin-square" aria-hidden="true"></i>';
				
					echo'</a>';
				
				echo'</div>';
				
			echo'</div>';
			
		echo'</div>';
		

		echo'<hr>';

		
		echo'<div class="row">';
		
			echo'<div class="col-lg-12">';
			
				echo'<div class="well text-center">';
				
					echo'For more information about a tailored template looking like <b>' . $this->post_title . '</b> please contact us directly.';
					
				echo'</div>';
				
			echo'</div>';
			
		echo'</div>';
		
		echo'<div class="row">';
		
			//output more info
		
			echo'<div id="about_tool" class="col-md-4">';

				echo'<div style="border-bottom:1px solid #DDDDDD;background:rgb(252, 252, 252);" class="panel-heading" role="tab" id="heading1">';
					
					echo'<button style="background:none;text-align:left;font-size:18px;width:100%;padding:5px;border:none;" role="button" data-toggle="collapse" data-parent="#about_tool" data-target="#collapse1" aria-expanded="true" aria-controls="collapse1">';
					  
						echo'<i class="fa fa-check-circle" aria-hidden="true"></i> ';
					  
						echo'Live Edition';
					
					echo'</button>';
				
				echo'</div>';
				
				echo'<div id="collapse1" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading1">';
					
					echo '<p style="margin: 0 5px;">';
					
						echo 'Edit <b>' . $this->post_title . '</b> code, duplicate or remove parts, save your custom version and export the result online directly from the editor.';
					
					echo '</p>';
					
				echo'</div>';
			
				echo'<div style="border-bottom:1px solid #DDDDDD;background:rgb(252, 252, 252);" class="panel-heading" role="tab" id="heading2">';
					
					echo'<button style="background:none;text-align:left;font-size:18px;width:100%;padding:5px;border:none;" role="button" data-toggle="collapse" data-parent="#about_tool" data-target="#collapse2" aria-expanded="true" aria-controls="collapse2">';
					  
						echo'<i class="fa fa-check-circle" aria-hidden="true"></i> ';
					  
						echo'Media Library';
					
					echo'</button>';
				
				echo'</div>';
				
				echo'<div id="collapse2" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading2">';
					
					echo '<p style="margin: 0 5px;">';
					
						echo 'Insert your contents into <b>' . $this->post_title . '</b> template directly form the editor, import images to your library, build custom payment links and add them to your list of bookmarks.';

					echo '</p>';
					
				echo'</div>';

				echo'<div style="border-bottom:1px solid #DDDDDD;background:rgb(252, 252, 252);" class="panel-heading" role="tab" id="heading3">';
					
					echo'<button style="background:none;text-align:left;font-size:18px;width:100%;padding:5px;border:none;" role="button" data-toggle="collapse" data-parent="#about_tool" data-target="#collapse3" aria-expanded="true" aria-controls="collapse3">';
					  
						echo'<i class="fa fa-check-circle" aria-hidden="true"></i> ';
					  
						echo'Connected Apps';
					
					echo'</button>';
				
				echo'</div>';
				
				echo'<div id="collapse3" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading3">';
					
					echo '<p style="margin: 0 5px;">';
					
						echo 'Connect third party apps to import or upload your communication material, take advantage of advance features and gain stars';
					
					echo '</p>';
					
				echo'</div>';

			
				echo'<div style="border-bottom:1px solid #DDDDDD;background:rgb(252, 252, 252);" class="panel-heading" role="tab" id="heading4">';
					
					echo'<button style="background:none;text-align:left;font-size:18px;width:100%;padding:5px;border:none;" role="button" data-toggle="collapse" data-parent="#about_tool" data-target="#collapse4" aria-expanded="true" aria-controls="collapse4">';
					  
						echo'<i class="fa fa-check-circle" aria-hidden="true"></i> ';
					  
						echo'Custom Url';
					
					echo'</button>';
				
				echo'</div>';
				
				echo'<div id="collapse4" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading4">';
					
					echo '<p style="margin: 0 5px;">';
					
						echo 'Add and manage dedicated domain names and assign custom urls to your saved templates.';
					
					echo '</p>';
					
				echo'</div>';				
			
			echo'</div>';
			
			//output video tutorial
			
			echo'<div class="col-md-4">';
				
				$video_url = get_option( $this->parent->_base . 'main_video' );
				
				if( !empty($video_url) ){
					
					echo'<iframe src="https://www.youtube.com/embed/'.$this->parent->apps->get_youtube_id($video_url).'" frameborder="0" style="background-color:#000000;width:100%;height:300px;" allowfullscreen></iframe>';
				}
				
			echo'</div>';
			
			//output related templates
			
			echo'<div class="col-md-4">';
			
				if( !empty($layer_range) && !empty($layer_type) ){
			
					$q = get_posts( array(
					
						'post_type' => $this->post_type,
						'numberposts' => -1,
						'tax_query' => array(
							array(
								'taxonomy' => 'layer-range',
								'field' => 'name',
								'terms' => $layer_range,
								'include_children' => false
							)
						)
					));
					
					if( !empty($q) ){

								$i=1;
							
								shuffle($q);
							
								foreach( $q as $post){
									
									if( $post->ID != $this->ID ){
									
										echo '<div class="row">';
											
											echo '<div class="col-xs-3">';
											
												echo '<a class="thumbnail" href="'. $this->parent->urls->product . '?id=' . $post->ID . '">';
											
													echo get_the_post_thumbnail($post->ID, array(150,150));
											
												echo'</a>';
											
											echo'</div>';
											
											echo '<div class="col-xs-9">';
											
												echo '<a href="'. $this->parent->urls->product . '?id=' . $post->ID . '" style="font-weight:bold;">';
												
													echo $post->post_title;
												
												echo '</a>';
												
												echo '<br>';
												
												echo $post->post_excerpt;
											
											echo'</div>';
											
										echo'</div>';
										
										if($i==3){
											
											break;
										}
										else{
											
											++$i;
										}
									}
								}
							

					}
					
					/*
					echo'<pre>';
					var_dump($q);
					exit;
					*/
				}
				
			echo'</div>';
			
		echo'</div>';

	echo'</div>';
	
	if( !$this->parent->user->loggedin ){

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
						
							echo '<a style="display:block;width:100%;" class="btn-lg btn-success" href="' . $this->parent->urls->plans . '" target="_parent" title="View plans">View plans</a>';
						
						echo '</div>';
						
						echo '<div style="display:block;margin:30px;">';
						
							echo '<a style="display:block;width:100%;" class="btn-lg btn-info" href="' . site_url() . '/contact/' . '" target="_parent" title="Contact us">Contact us</a>';
						
						echo '</div>';
						
					echo '</div>'.PHP_EOL;

				echo '</div>'.PHP_EOL;
				
			echo '</div>'.PHP_EOL;
			
		echo '</div>'.PHP_EOL;				
	}