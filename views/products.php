<?php 

	$ltple = LTPLE_Client::instance();
	
	get_header();

	if(!empty($ltple->product->message)){ 
	
		echo $ltple->product->message;
	}
	
	echo'<h1 class="px-3"><i class="fa fa-shopping-cart" aria-hidden="true"></i> Newly added Products</h1>';

	echo'<div id="layer_detail" class="container-fluid" style="min-height:calc( 100vh - 167px );">';
		
		echo'<div class="row">';
			
			if( $current_types = $ltple->gallery->get_current_types() ){
			
				foreach( $current_types as $term ){
					
					if($term->visibility == 'anyone'){
						
						//output related templates
						
						$q = get_posts( array(
						
							'post_type' 	=> 'cb-default-layer',
							'numberposts' 	=> 3,
							'meta_query' 	=> array(
							
								array(
									'key' 			=> 'layerVisibility',
									'value' 		=> 'assigned',
									'compare' 		=> '!=',
								)
							),
							'tax_query' 	=> array(
							
								array(
									'taxonomy' 			=> 'layer-type',
									'field' 			=> 'id',
									'terms' 			=> $term->term_id,
									'include_children' 	=> false
								)
							)
						));			
						
						if( !empty($q) ){
							
							echo'<div class="col-md-3" style="min-height:250px;">';

								echo'<h2 class="py-2">';
								
									echo ucfirst($term->name);
								
								echo'</h2>';
							
								foreach( $q as $post){

									//echo '<div class="col-md-3">';
										
										echo '<div class="media mb-1 p-0">';
										
											echo '<a class="thumbnail mb-3 mr-1" href="' . get_permalink($post) . '">';
						
												// get image thumb
						
												if( !$thumb = get_the_post_thumbnail($post->ID, array(50,50)) ){
													
													$thumb = '<div style="background-image:url('.$ltple->assets_url . 'images/default_item.png);background-size:cover;background-repeat:no-repeat;background-position:center center;width:75px;height:75px;display:block;"></div>';
												}
													
												echo $thumb;
										
											echo'</a>';

											echo '<div class="media-body">';
											
												echo '<a href="' . get_permalink($post) . '" style="font-weight:bold;">';
												
													echo $post->post_title;
												
												echo '</a>';
												
												echo '<p style="height:45px;overflow:hidden;">';
												
													echo $post->post_excerpt;
												
												echo'</p>';
												
											echo'</div>';
										
										echo'</div>';
										
									//echo'</div>';
								}
								
								if( count($q) == 3 ){
							
									echo'<a class="btn btn-xs btn-primary" style="margin:5px;" href="' . $ltple->urls->gallery . '?gallery=' . $term->slug . '">see more</a>';
								}
								
							echo'</div>';
						}
					}
				}
			}

		echo'</div>';

	echo'</div>';
	
	get_footer();