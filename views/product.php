<?php 
	
	$ltple = LTPLE_Client::instance();
	
	global $post;
	
	$layer = !empty($ltple->product->ID) ? $ltple->product : $post;
	
	$layer = LTPLE_Editor::instance()->get_layer($layer);

	$visibility = $ltple->layer->get_layer_visibility($layer);
	
	$features = $ltple->layer->get_layer_features($layer);
	
	$has_layer = $ltple->plan->user_has_layer($layer);
	
	get_header();
	
	if( $visibility != 'assigned' || $has_layer || $ltple->user->is_admin ){
				
		$start_url 	= apply_filters('ltple_start_url',$ltple->urls->edit . '?uri=' . $layer->ID,$layer);
		
		$product_url = get_permalink($layer->ID);

		$layer_range = $ltple->layer->get_layer_range($layer->ID);
			
		$is_html = $ltple->layer->is_html_output($layer->output);
		
		$is_editable = $layer->is_editable;
				
		$output_name = $ltple->layer->get_output_name($layer->output);
		
		// get from value
	
		$preview_modal = $ltple->layer->get_modal($layer);
		
		$from_amount = null;
		
		if( !$has_layer ){
		
			$ranges = $ltple->product->get_product_ranges($layer);
		
			$plans = $ltple->plan->get_plans_by_options($ranges);
		
			$from_amount = isset($plans[0]['info']['total_price_amount']) ? $plans[0]['info']['total_price_amount'] : null;
			$from_currency 	= isset($plans[0]['info']['total_price_currency']) ? $plans[0]['info']['total_price_currency'] : '$';
		}
		
		if(!empty($ltple->product->message)){ 
		
			echo $ltple->product->message;
		}
		
		echo '<div id="product_detail">';
			
			if( $visibility == 'assigned' ){
				
				echo '<div class="alert alert-warning">This template is not publicly accessible...</div>';
			}
			
			echo '<div id="product_wrap" class="container-fluid px-2 py-0">';
				
				echo '<div class="row">';
				
					echo '<div id="product_gallery" class="mt-4 p-0 col-sm-1 d-none d-md-block">';
						
						if( $ids = $ltple->product->get_product_gallery_ids($layer) ){
							
							foreach( $ids as $i => $image_id ){
							
								echo '<div class="media border p-1 mx-3 mb-2 float-left' . ( $i===0 ? ' active' : '' ) . '" data-index="' . $i . '">';
									
									echo '<img loading="lazy" class="lazy" src="'.$ltple->product->get_product_image_url($image_id,'thumbnail').'" decoding="async"/>';
									
								echo '</div>';
							}
						}
						
					echo '</div>';
					
					echo '<div id="product_preview" class="p-0 col-12 col-sm-6 col-lg-7">';
						
						if( $ids = $ltple->product->get_product_gallery_ids($layer) ){
							
							foreach( $ids as $i => $image_id ){
								
								echo '<div class="m-auto product-image svgLoader text-center">';
									
									echo '<img loading="lazy" class="lazy pl-3 pr-3 img-responsive" src="' . $ltple->product->get_product_image_url($image_id,'medium_large') . '" alt="' . $layer->post_title . '">';
									
                                    if( !$ltple->inWidget ){
                                    
                                        echo '<a class="product-view mt-4" href="' . $ltple->product->get_product_image_url($image_id,'full') . '" title="' . $layer->post_title . '"><i class="fas fa-search-plus"></i></a>';
                                    }
                                    
								echo '</div>';
							}
						}
						
					echo '</div>';
					
					echo '<div id="product_sidebar" class="col-12 col-sm-5 col-lg-4">';

						echo  '<h1 id="product_title">' . $layer->post_title . '</h1>';

						echo'<div class="bs-callout bs-callout-primary">';
						
							echo'<div class="pull-left" style="padding:0;text-align:center;font-weight:bold;font-size:21px;">';
								
								echo'<span class="badge" style="';
								
									echo'font-size:17px;';
									echo'padding: 10px 15px;';
									echo'background:#ffffff;';
									echo'color:' . $ltple->settings->mainColor . ';';
									echo'border-radius:4px;';
									echo'border: 1px solid ' . $ltple->settings->mainColor . ';';
									
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

								if( !empty($preview_modal) ){

									echo '<button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#'.$preview_modal['id'].'">'.PHP_EOL;
										
										echo 'Preview'.PHP_EOL;
									
									echo '</button>'.PHP_EOL;
								
									echo $preview_modal['content'].PHP_EOL;
								}
								
								if( $has_layer === true){
									
									echo'<a class="btn btn-sm btn-success" href="'. $start_url .'" target="_parent" title="Start editing this '.$output_name.'">Start</a>';
								}
								elseif( empty($ltple->user->plan) || $ltple->user->plan['holder'] == $ltple->user->ID ){
									
									echo $ltple->product->get_checkout_button($layer);
								}
										
							echo'</div>';
							
						echo'</div>';			
					
						echo'<p style="margin:5px;height:70px;overflow:hidden;">';

							echo '<b>' . $layer->post_title . '</b> is a ';
						
							echo' '.$output_name.' ';
							
							if( !empty($layer_range) ){
								
								echo'from the <b>'.ucfirst($layer_range->name).' range</b> ';
							}
							
							if( $is_html && $is_editable ){
								
								echo'available online via our '.( $layer->output == 'web-app' ? 'platform' : 'HTML editing tool' ).'. ';
							}
							
							echo $layer->post_excerpt;
							
							edit_post_link( __( 'Edit', 'templatemela' ), '<span class="edit-link"><i class="fa fa-pencil"></i>', '</span>', $layer->ID );
						
						echo'</p>';
						
						if( !empty($features) ){
							
							echo'<div id="product_features" class="mt-2">';
								
								echo'<ul class="list-group list-group-flush">';
								
									foreach( $features as $feature ){
										
										echo'<li class="list-group-item">';
										
											echo '<i class="fa fa-star"></i>' . $feature->name;
										
										echo'</li>';
									}
									
								echo'</ul>';
								
							echo'</div>';
						}
						
						echo'<div id="share_product" style="margin:25px 0;font-size:40px;">';
						
							echo'<a href="https://twitter.com/intent/tweet?text=' . urlencode( 'Awesome ' . $layer->post_title . '! ' . $product_url ) . '" target="_blank" title="share on twitter" style="margin:5px;">';
							
								echo'<i class="fab fa-twitter-square" aria-hidden="true"></i>';
							
							echo'</a>';
							
							echo'<a href="https://www.facebook.com/sharer/sharer.php?u='.urlencode( $product_url ).'&t='.urlencode( 'Awesome ' . $layer->post_title . '!' ).'" target="_blank" title="share on facebook" style="margin:5px;">';
						
								echo'<i class="fab fa-facebook-square" aria-hidden="true"></i>';
							
							echo'</a>';
							
							echo'<a href="http://pinterest.com/pin/create/link/?url='.urlencode( $product_url ).'&description='.urlencode( 'Awesome ' . $layer->post_title . '!' ).'" target="_blank" title="share on pinterest" style="margin:5px;">';
							
								echo'<i class="fab fa-pinterest-square" aria-hidden="true"></i>';
						
							echo'</a>';
							
							echo'<a href="https://www.linkedin.com/cws/share?url='.urlencode( $product_url ).'&title='.urlencode( $layer->post_title ).'&summary='.urlencode( $layer->post_excerpt ).'" target="_blank" title="share on linkedin" style="margin:5px;">';
							
								echo'<i class="fab fa-linkedin" aria-hidden="true"></i>';
						
							echo'</a>';
							
							echo'<a href="https://www.reddit.com/submit?url='.urlencode( $product_url ).'&title='.urlencode( $layer->post_title ) .'" target="_blank" title="share on reddit" style="margin:5px;">';
							
								echo'<i class="fab fa-reddit-square" aria-hidden="true"></i>';
						
							echo'</a>';
						
						echo'</div>';
						
					echo '</div>';
                
                    echo '<div class="container-fluid">';

                        echo'<div class="alert alert-info text-center">';
                        
                            echo'For more information about a tailored '.$output_name.' like <b>' . $layer->post_title . '</b> please contact us directly.';
                            
                        echo'</div>';							
                    
                        //output more info
                        
                        do_action('ltple_product_info',$layer,$layer_range);
                        
                        if( !$ltple->inWidget && $is_html && $is_editable && $layer->output != 'web-app'  ){
                            
                            echo'<div class="row mt-5">';
                                
                                echo'<div id="about_tool" class="col-md-4">';

                                    echo'<div style="border-bottom:1px solid #DDDDDD;background:rgb(252, 252, 252);" id="heading1">';
                                        
                                        echo'<button class="d-block" style="background:none;text-align:left;font-size:15px;font-weight:bold;width:100%;padding:10px;border:none;" role="button" data-toggle="collapse" data-parent="#about_tool" data-target="#collapse1" aria-expanded="true" aria-controls="collapse1">';
                                          
                                            echo'<i class="fa fa-check-circle" aria-hidden="true"></i> ';
                                          
                                            echo'Live Editing Tool';
                                        
                                        echo'</button>';
                                    
                                    echo'</div>';
                                    
                                    echo'<div id="collapse1" class="panel-collapse in show" role="tabpanel" aria-labelledby="heading1">';
                                        
                                        echo '<p style="margin:10px;">';
                                        
                                            echo 'Edit <b>' . $layer->post_title . '</b> code, duplicate or remove parts, save your custom version and export the result online directly from the editor.';
                                        
                                        echo '</p>';
                                        
                                    echo'</div>';
                                
                                    echo'<div style="border-bottom:1px solid #DDDDDD;background:rgb(252, 252, 252);" id="heading2">';
                                        
                                        echo'<button class="d-block" style="background:none;text-align:left;font-size:15px;font-weight:bold;width:100%;padding:10px;border:none;" role="button" data-toggle="collapse" data-parent="#about_tool" data-target="#collapse2" aria-expanded="true" aria-controls="collapse2">';
                                          
                                            echo'<i class="fa fa-check-circle" aria-hidden="true"></i> ';
                                          
                                            echo'Media Library';
                                        
                                        echo'</button>';
                                    
                                    echo'</div>';
                                    
                                    echo'<div id="collapse2" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading2">';
                                        
                                        echo '<p style="margin:10px;">';
                                        
                                            echo 'Insert your contents into <b>' . $layer->post_title . '</b> template directly from the editor, import images to your library, build custom payment links and add them to your list of bookmarks.';

                                        echo '</p>';
                                        
                                    echo'</div>';

                                    echo'<div style="border-bottom:1px solid #DDDDDD;background:rgb(252, 252, 252);" id="heading3">';
                                        
                                        echo'<button class="d-block" style="background:none;text-align:left;font-size:15px;font-weight:bold;width:100%;padding:10px;border:none;" role="button" data-toggle="collapse" data-parent="#about_tool" data-target="#collapse3" aria-expanded="true" aria-controls="collapse3">';
                                          
                                            echo'<i class="fa fa-check-circle" aria-hidden="true"></i> ';
                                          
                                            echo'Connected Apps';
                                        
                                        echo'</button>';
                                    
                                    echo'</div>';
                                    
                                    echo'<div id="collapse3" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading3">';
                                        
                                        echo '<p style="margin:10px;">';
                                        
                                            echo 'Connect third party apps to import or upload your communication material, take advantage of advance features and gain stars';
                                        
                                        echo '</p>';
                                        
                                    echo'</div>';

                                    echo'<div style="border-bottom:1px solid #DDDDDD;background:rgb(252, 252, 252);" id="heading4">';
                                        
                                        echo'<button class="d-block" style="background:none;text-align:left;font-size:15px;font-weight:bold;width:100%;padding:10px;border:none;" role="button" data-toggle="collapse" data-parent="#about_tool" data-target="#collapse4" aria-expanded="true" aria-controls="collapse4">';
                                          
                                            echo'<i class="fa fa-check-circle" aria-hidden="true"></i> ';
                                          
                                            echo'Custom Url';
                                        
                                        echo'</button>';
                                    
                                    echo'</div>';
                                    
                                    echo'<div id="collapse4" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading4">';
                                        
                                        echo '<p style="margin:10px;">';
                                        
                                            echo 'Add and manage dedicated domain names and assign custom urls to your saved templates.';
                                        
                                        echo '</p>';
                                        
                                    echo'</div>';				
                                
                                echo'</div>';
                                
                                //output video tutorial
                                
                                echo'<div class="col-md-4">';
                                    
                                    $video_url = get_option( $ltple->_base . 'main_video' );
                                    
                                    if( !empty($video_url) ){
                                        
                                        echo'<iframe src="https://www.youtube.com/embed/'.$ltple->apps->get_youtube_id($video_url).'" frameborder="0" style="background-color:#000000;width:100%;height:300px;" allowfullscreen></iframe>';
                                    }
                                    
                                echo'</div>';
                                
                                //output related templates
                                
                                echo'<div class="col-md-4">';
                                
                                    if( !empty($layer_range) && !empty($layer_range->name) ){
                                
                                        $q = get_posts( array(
                                        
                                            'post_type' 	=> $layer->post_type,
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
                                                
                                                if( $post->ID != $layer->ID ){
                                                
                                                    echo '<div class="media mb-1 p-0">';
                                                        
                                                        if( $image = $ltple->layer->get_preview_image_url($post->ID,'thumbnail',$ltple->assets_url . 'images/default_item.png') ){
                                                        
                                                            echo '<a class="thumbnail mb-3 mr-1" href="' . get_permalink($post) . '">';
                                                        
                                                                echo '<img src="'.$image.'" style="height:75px;width:75px;"/>';
                                                        
                                                            echo'</a>';
                                                        }
                                                        
                                                        echo '<div class="media-body">';
                                                        
                                                            echo '<a href="' . get_permalink($post) . '" style="font-weight:bold;">';
                                                            
                                                                echo $post->post_title;
                                                            
                                                            echo '</a>';
                                                            
                                                            echo '<p style="height:45px;overflow:hidden;">';
                                                            
                                                                echo $post->post_excerpt;
                                                            
                                                            echo '</p>';
                                                        
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
                                
                            echo'</div>';
                        }
                        
                    echo'</div>';

				echo '</div>';
				
			echo '</div>';

		echo '</div>' . PHP_EOL;
		
		// upgrade plan modal
			
		include( $ltple->views  . '/modals/upgrade.php');
	}
	
	get_footer();