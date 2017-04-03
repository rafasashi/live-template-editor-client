<?php 

	if(!empty($this->message)){ 
	
		echo $this->message;
	}

	echo'<h1><i class="fa fa-shopping-cart" aria-hidden="true"></i> Last added Templates</h1>';
	
	echo'<div id="layer_detail" class="col-xs-12">';
		
		echo'<div class="row">';	
		
			foreach($this->parent->layer->types as $term){
				
				if(isset($this->parent->layer->released[$term->slug])){
					
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
						
								echo'<a class="btn btn-xs btn-primary" style="margin:5px;" href="' . $this->parent->urls->editor . '#' . $term->slug . '">see more</a>';
							}
							
						echo'</div>';
					}
				}
			}

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