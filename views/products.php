<?php 

	if(!empty($this->message)){ 
	
		echo $this->message;
	}

	echo'<h1><i class="fa fa-shopping-cart" aria-hidden="true"></i> Last added Templates</h1>';
	
	echo'<div id="layer_detail" class="col-xs-12">';
		
		echo'<div class="row">';	
			
			$all_types = $this->parent->gallery->get_all_types();
			
			foreach( $all_types as $term ){
				
				if($term->visibility == 'anyone'){
					
					//output related templates
					
					$q = get_posts( array(
					
						'post_type' => 'cb-default-layer',
						'numberposts' => 3,
						'tax_query' => array(
							array(
								'taxonomy' => 'layer-type',
								'field' => 'id',
								'terms' => $term->term_id,
								'include_children' => false
							)
						)
					));			
					
					if( !empty($q) ){
						
						echo'<div class="col-md-4" style="min-height:400px;">';

							echo'<h2 style="background: #eee;padding: 10px;font-size: 25px;color: #8aceec;">';
							
								echo ucfirst($term->name);
							
							echo'</h2>';
						
							foreach( $q as $post){

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
							}
							
							if( count($q) == 3 ){
						
								echo'<a class="btn btn-xs btn-primary" style="margin:5px;" href="' . $this->parent->urls->editor . '?gallery=' . $term->slug . '">see more</a>';
							}
							
						echo'</div>';
					}
				}
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