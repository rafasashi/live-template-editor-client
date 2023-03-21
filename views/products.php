<?php 

	if(!empty($this->message)){ 
	
		echo $this->message;
	}
	
	echo'<div class="panel-header">';
	
		echo'<h1 class="page-title"><i class="fa fa-shopping-cart" aria-hidden="true"></i> Newly added Products</h1>';
	
	echo'</div>';
	
	echo'<div id="layer_detail" class="col-xs-12">';
		
		echo'<div class="row">';
			
			if( $current_types = $this->parent->gallery->get_current_types() ){
			
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
							
							echo'<div class="col-md-4" style="height:500px;">';

								echo'<h2 style="background: #eee;padding: 10px;font-size: 25px;">';
								
									echo ucfirst($term->name);
								
								echo'</h2>';
							
								foreach( $q as $post){

									echo '<div class="row">';
										
										echo '<div class="col-xs-3">';
										
											echo '<a class="thumbnail" href="' . get_permalink($post) . '">';
						
												// get image thumb
						
												if( !$thumb = get_the_post_thumbnail($post->ID, array(150,150)) ){
													
													$thumb = '<div style="background-image:url('.$this->parent->assets_url . 'images/default_item.png);background-size:cover;background-repeat:no-repeat;background-position:center center;width:75px;height:75px;display:block;"></div>';
												}
													
												echo $thumb;
										
											echo'</a>';
										
										echo'</div>';
										
										echo '<div class="col-xs-9">';
										
											echo '<a href="' . get_permalink($post) . '" style="font-weight:bold;">';
											
												echo $post->post_title;
											
											echo '</a>';
											
											echo '<br>';
											
											echo $post->post_excerpt;
										
										echo'</div>';
										
									echo'</div>';
								}
								
								if( count($q) == 3 ){
							
									echo'<a class="btn btn-xs btn-primary" style="margin:5px;" href="' . $this->parent->urls->gallery . '?gallery=' . $term->slug . '">see more</a>';
								}
								
							echo'</div>';
						}
					}
				}
			}

		echo'</div>';

	echo'</div>';