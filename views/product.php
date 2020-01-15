<?php 

	if(!empty($this->message)){ 
	
		echo $this->message;
	}
	
	$visibility = get_post_meta( $this->ID, 'layerVisibility', true );
	
	if( $visibility == 'assigned' ){
		
		echo '<div class="alert alert-warning">This template is not publicly accessible...</div>';
	}
	else{
				
		$outputs 		= $this->parent->layer->get_layer_outputs();

		$permalink 		= $this->parent->urls->home . '/preview/' . $this->post_name . '/';
		
		$editor_url 	= $this->parent->urls->editor . '?uri=' . $this->ID;
		
		$product_url 	= $this->parent->urls->product . $this->ID . '/';

		$layer_type 	= $this->parent->layer->get_layer_type($this->ID);

		$layer_range 	= $this->parent->layer->get_layer_range($this->ID);
			
		$modal_id='modal_'.md5($permalink);
		
		$is_html = $this->parent->layer->is_html_output($layer_type->output);
		
		$object = $outputs[$layer_type->output];
		
		if( $is_html ){
			
			$object .= ' template';
		}
		
		// get from value

		$this->parent->plan->options = array();
		
		foreach( $this->taxonomies['layer-range']['terms'] as $term ){
			
			if($term['has_term']){
				
				$this->parent->plan->options[] = $term['slug'];
			}
		}
		
		$has_layer 		= $this->parent->plan->user_has_layer( $this );
		
		$has_preview 	= $this->parent->layer->has_preview( $layer_type->output );
		
		$from_amount = null;
		
		if( !$has_layer ){
		
			$plans = $this->parent->plan->get_plans_by_options($this->parent->plan->options);
		
			$from_amount = isset($plans[0]['info']['total_price_amount']) ? $plans[0]['info']['total_price_amount'] : null;
			$from_currency 	= isset($plans[0]['info']['total_price_currency']) ? $plans[0]['info']['total_price_currency'] : '$';
		}
		
		echo'<div class="panel-header">';
		
			echo'<h1><i class="fa fa-shopping-cart" aria-hidden="true"></i> ' . $this->post_title . '</h1>';
		
		echo'</div>';
		
		echo'<div id="layer_detail" class="col-xs-12 library-content">';

			echo'<div class="row">';
				
				echo'<div class="col-xs-12 col-sm-6 col-lg-8">';
					
					//echo'<div class="thumb_wrapper" style="background:url(' . $this->image . ');height:300px;background-size:cover;background-repeat:no-repeat;background-position:center center;border-radius:10px;"></div>';
					
					//echo'<div style="max-height:300px;overflow:hidden;border-radius:10px;">';
					
						echo'<img style="border-radius:15px;" class="img-responsive" src="' . $this->image . '" alt="">';
					
					//echo'</div>';
					
				echo'</div>';
				
				echo'<div class="col-xs-12 col-sm-6 col-lg-4">';

					echo'<div class="row bs-callout bs-callout-primary">';
					
						echo'<div class="col-xs-4 text-right" style="padding:0;text-align:center;font-weight:bold;font-size:21px;">';
							
							echo'<span class="badge" style="';
							
								echo'font-size:17px;';
								echo'padding: 10px 15px;';
								echo'background:#ffffff;';
								echo'color:' . $this->parent->settings->mainColor . ';';
								echo'border-radius:4px;';
								echo'border: 1px solid ' . $this->parent->settings->mainColor . ';';
								
							echo'">';				
								
								if( !is_null($from_amount) ){
								
									echo 'from ';
							
									echo $from_amount;
									echo $from_currency;
								
								}
								elseif( $has_layer ){
									
									echo 'unlocked';
								}
							
							echo '</span>';
						
						echo'</div>';
						
						echo'<div class="col-xs-8 text-right" style="padding:5px 0;">';
							
							if( $has_preview ){
								
								echo'<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#'.$modal_id.'">'.PHP_EOL;
									
									echo'Preview'.PHP_EOL;
								
								echo'</button>'.PHP_EOL;
								
								echo'<div class="modal fade" id="'.$modal_id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'.PHP_EOL;
									
									echo'<div class="modal-dialog modal-full" role="document">'.PHP_EOL;
										
										echo'<div class="modal-content">'.PHP_EOL;
										
											echo'<div class="modal-header">'.PHP_EOL;
												
												echo'<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
												
												echo'<h4 class="modal-title text-left" id="myModalLabel">Preview</h4>'.PHP_EOL;
											
											echo'</div>'.PHP_EOL;
										  
											echo'<div class="modal-body">'.PHP_EOL;
												
												if( $this->parent->user->loggedin && $has_layer === true ){
													
													echo '<iframe data-src="'.$permalink.'" style="width:100%;position:relative;bottom:0;border:0;height:calc( 100vh - 145px);overflow:hidden;"></iframe>';											
												}
												else{
													
													echo get_the_post_thumbnail($this->ID, 'recentprojects-thumb');
												}

											echo'</div>'.PHP_EOL;

											echo'<div class="modal-footer">'.PHP_EOL;
											
												if( $this->parent->user->loggedin  && $has_layer === true ){

													echo'<a class="btn btn-sm btn-success" href="'. $editor_url .'" target="_self" title="Start editting this '.$object.'">Start</a>';
												}
												
											echo'</div>'.PHP_EOL;
										  
										echo'</div>'.PHP_EOL;
										
									echo'</div>'.PHP_EOL;
									
								echo'</div>'.PHP_EOL;
							}
							
							if( $this->parent->user->loggedin ){
								
								if( $has_layer === true){
									
									echo'<a class="btn btn-sm btn-success" href="'. $editor_url .'" target="_self" title="Start editting this '.$object.'">Start</a>';
								}
								elseif( $this->parent->user->plan['holder'] == $this->parent->user->ID ){
									
									echo $this->get_checkout_button($this,$layer_type->name);
								}
							}
							else{
								
								echo'<button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#login_first">'.PHP_EOL;
								
									echo'<span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span> Buy'.PHP_EOL;
							
								echo'</button>'.PHP_EOL;								
							}
									
						echo'</div>';
						
					echo'</div>';			
				
					echo'<p style="margin:5px;height:70px;overflow:hidden;">';

						echo '<b>' . $this->post_title . '</b> is a ';
						
						/*
						if( !empty($layer_type->name) ){
							
							echo '<b>'.$layer_type->name.'</b> ';
						}
						*/
					
						echo' '.$object.' ';
						
						if( !empty($layer_range) ){
							
							echo'from the <b>'.ucfirst($layer_range->name).' range</b> ';
						}
						
						if( $is_html ){
						
							echo'available via our live HTML editing tool. ';
						}
						
						echo $this->post_excerpt;
					
					echo'</p>';
					
					echo'<div id="share_product" style="margin:25px 0;font-size:40px;">';
					
						echo'<a href="https://twitter.com/intent/tweet?text=' . urlencode( 'Awesome ' . $this->post_title . '! ' . $product_url ) . '" target="_blank" title="share on twitter" style="margin:5px;">';
						
							echo'<i class="fa fa-twitter-square" aria-hidden="true"></i>';
						
						echo'</a>';
						
						echo'<a href="https://www.facebook.com/sharer/sharer.php?u='.urlencode( $product_url ).'&t='.urlencode( 'Awesome ' . $this->post_title . '!' ).'" target="_blank" title="share on facebook" style="margin:5px;">';
					
							echo'<i class="fa fa-facebook-square" aria-hidden="true"></i>';
						
						echo'</a>';
						
						/*
						echo'<a href="https://plus.google.com/share?url='.urlencode( $product_url ).'" target="_blank" title="share on google plus" style="margin:5px;">';
						
							echo'<i class="fa fa-google-plus-square" aria-hidden="true"></i>';
					
						echo'</a>';
						*/
						
						echo'<a href="http://pinterest.com/pin/create/link/?url='.urlencode( $product_url ).'&description='.urlencode( 'Awesome ' . $this->post_title . '!' ).'" target="_blank" title="share on pinterest" style="margin:5px;">';
						
							echo'<i class="fa fa-pinterest-square" aria-hidden="true"></i>';
					
						echo'</a>';
						
						echo'<a href="https://www.linkedin.com/cws/share?url='.urlencode( $product_url ).'&title='.urlencode( $this->post_title ).'&summary='.urlencode( $this->post_excerpt ).'" target="_blank" title="share on linkedin" style="margin:5px;">';
						
							echo'<i class="fa fa-linkedin-square" aria-hidden="true"></i>';
					
						echo'</a>';
						
						echo'<a href="https://www.reddit.com/submit?url='.urlencode( $product_url ).'&title='.urlencode( $this->post_title ) .'" target="_blank" title="share on reddit" style="margin:5px;">';
						
							echo'<i class="fa fa-reddit-square" aria-hidden="true"></i>';
					
						echo'</a>';
					
					echo'</div>';
					
				echo'</div>';
				
			echo'</div>';
			

			echo'<hr>';

			
			echo'<div class="row">';
			
				echo'<div class="col-lg-12">';
				
					//echo do_shortcode('[ltple-client-checkout]');
				
					echo'<div class="well text-center">';
					
						echo'For more information about a tailored '.$object.' like <b>' . $this->post_title . '</b> please contact us directly.';
						
					echo'</div>';
					
				echo'</div>';
				
			echo'</div>';
			
			echo'<div class="row">';
			
				//output more info
				
				do_action('ltple_product_info',$this);
				
				if( $is_html ){
					
					echo'<div class="clearfix"></div>';
					
					echo'<div id="about_tool" class="col-md-4">';

						echo'<div style="border-bottom:1px solid #DDDDDD;background:rgb(252, 252, 252);" class="panel-heading" role="tab" id="heading1">';
							
							echo'<button style="background:none;text-align:left;font-size:18px;width:100%;padding:5px;border:none;" role="button" data-toggle="collapse" data-parent="#about_tool" data-target="#collapse1" aria-expanded="true" aria-controls="collapse1">';
							  
								echo'<i class="fa fa-check-circle" aria-hidden="true"></i> ';
							  
								echo'Live Editing Tool';
							
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
							
								echo 'Insert your contents into <b>' . $this->post_title . '</b> template directly from the editor, import images to your library, build custom payment links and add them to your list of bookmarks.';

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
					
						if( !empty($layer_range) && !empty($layer_type->name) ){
					
							$q = get_posts( array(
							
								'post_type' 	=> $this->post_type,
								'numberposts' 	=> -1,
								'tax_query' 	=> array(
									
									array(
										
										'taxonomy' 			=> 'layer-range',
										'field' 			=> 'name',
										'terms' 			=> $layer_range->name,
										'include_children' 	=> false
									)
								),
								'meta_query' 	=> array(
								
									array(
									
										'key' 		=> 'layerVisibility',
										'value' 	=> 'assigned',
										'compare' 	=> '!='
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
											
												echo '<a class="thumbnail" href="'. $this->parent->urls->product . $post->ID . '/">';
											
													echo get_the_post_thumbnail($post->ID, array(150,150));
											
												echo'</a>';
											
											echo'</div>';
											
											echo '<div class="col-xs-9">';
											
												echo '<a href="'. $this->parent->urls->product . $post->ID . '/" style="font-weight:bold;">';
												
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
						}
						
					echo'</div>';
				}
				
			echo'</div>';

		echo'</div>';
		
		if( !$this->parent->user->loggedin ){

			// login modal
			
			include( $this->parent->views  . '/modals/login.php');
		}
		else{
			
			// upgrade plan modal
			
			include( $this->parent->views  . '/modals/upgrade.php');				
		}
	}