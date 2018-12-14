<?php 

	if(!empty($this->message)){ 
	
		echo $this->message;
	}
	
	if(!empty($_SESSION['message'])){ 
	
		echo $_SESSION['message'];
		
		$_SESSION['message'] = '';
	}
	
	$layer_type = ( !empty($_GET['gallery']) ? $_GET['gallery'] : '' );

	if( empty($layer_type) ){

		foreach($this->all->layerType as $term){
						
			if( $term->visibility == 'anyone' || $this->user->is_editor ){
			
				$layer_type = $term->slug;
				break;
			}
		}			
	}

	//get item ranges
	
	$ranges = [];
	
	$meta_query = [];
	
	if( !$this->user->is_editor ){
		
		$meta_query = array(
		
			'relation' => 'OR',
			array(
				'key' 		=> 'layerUserId',
				'value' 	=> $this->user->ID,
				'type' 		=> 'NUMERIC',
				'compare' 	=> '='
			),			
			array(
				'key' 		=> 'layerUserId',
				'value' 	=> 0,
				'type' 		=> 'NUMERIC',
				'compare' 	=> '='
			),
			array(
				'key' 		=> 'layerUserId',
				'compare' 	=> 'NOT EXISTS'
			),
		);	
	}
	
	$query = new WP_Query(array( 
		'post_type' 		=> 'cb-default-layer', 
		'posts_per_page'	=> -1,
		'fields'		 	=> 'ids',
		'tax_query' 		=> array(
			array(
				'taxonomy' 			=> 'layer-type',
				'field' 			=> 'slug',
				'terms' 			=> $layer_type,
				'include_children' 	=> false
			)
		),
		'meta_query' => $meta_query,
	));

	if( !empty($query->posts) ){
	
		foreach( $query->posts as $post_id ){
			
			if( $layer_ranges = wp_get_post_terms( $post_id, 'layer-range' ) ){
			
				foreach( $layer_ranges as $range ){
					
					if( !isset($ranges[$range->slug]) ){
						
						$ranges[$range->slug]['name'] 	= $range->name;
						$ranges[$range->slug]['slug'] 	= $range->slug;
						$ranges[$range->slug]['count'] 	= 1;
					}
					else{
						
						++$ranges[$range->slug]['count'];
					}
				}
			}
		}
	}
	
	$layer_range = ( !empty($_GET['range']) ? $_GET['range'] : key($ranges) );
	
	// get gallery items 
	
	$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

	$items =[];
	
	$tax_query = array();
	
	if( !empty($layer_range) ){
		
		$tax_query['relation'] = 'AND';
		
		$tax_query[] = array(
		
			'taxonomy' 			=> 'layer-range',
			'field' 			=> 'slug',
			'terms' 			=> $layer_range,
			'include_children' 	=> false
		);
	} 
	
	$tax_query[] = array(
	
		'taxonomy' 			=> 'layer-type',
		'field' 			=> 'slug',
		'terms' 			=> $layer_type,
		'include_children' 	=> false
	);
	$query = new WP_Query(array( 
	
		'post_type' 	=> 'cb-default-layer', 
		'posts_per_page'=> 15,
		'paged' 		=> $paged,
		'tax_query' 	=> $tax_query,
		'meta_query' 	=> $meta_query,
	));
	
	foreach($this->all->layerType as $term){
		
		if( $term->slug == $layer_type ){
			
			while ( $query->have_posts() ) : $query->the_post(); 
				
				global $post;
				
				$permalink = get_permalink($post) . '?preview';

				//get editor_url

				$editor_url = $this->urls->editor . '?uri='.$post->ID;
			
				//get post_title
				
				$post_title = the_title('','',false);
				
				if( $term->visibility == 'anyone' || $this->user->is_editor ){
					
					//get layer_range
					
					$layer_range='out of range';
					
					$terms = wp_get_object_terms( $post->ID, 'layer-range' );
					
					if(!empty($terms[0]->slug)){
						
						$layer_range=$terms[0]->slug;
					}				
					
					//get item
					
					$item='';
					
					$item.='<div class="' . implode( ' ', get_post_class("col-xs-12 col-sm-6 col-md-4",$post->ID) ) . '" id="post-' . $post->ID . '">';
						
						$item.='<div class="panel panel-default">';
							
							$item.='<div class="panel-heading">';
								
								$item.='<b>' . $post_title . '</b>';
								
							$item.='</div>';

							$item.='<div class="panel-body">';
								
								if ( $image_id = get_post_thumbnail_id( $post->ID ) ){
									
									if ($src = wp_get_attachment_image_src( $image_id, 'medium' )){
										
										$item.='<div class="thumb_wrapper" style="background:url(' . $src[0] . ');background-size:cover;background-repeat:no-repeat;">';
											
											//$item.= '<img src="' . $src[0] . '"/>';
										
										$item.='</div>'; //thumb_wrapper
									}
									else{
										$item.='<div class="thumb_wrapper" style="background:#ffffff;"></div>';
									}
								}
								else{
									$item.='<div class="thumb_wrapper" style="background:#ffffff;"></div>';
								}

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

								$item.='<a class="btn btn-sm btn-info" style="margin-right:4px;" href="'. $this->urls->product .'?id=' . $post->ID . '" title="More info about '. $post_title .' template">Info</a>';
							
								//$item.='<a class="btn btn-sm btn-warning" href="'. $permalink .'" target="_blank" title="'. $post_title .'">Preview</a>';
							
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
													
													$item.= '<iframe data-src="'.$permalink.'" style="width: 100%;position:relative;bottom: 0;border:0;height: 450px;overflow: hidden;"></iframe>';											
												}
												else{
													
													$item.= get_the_post_thumbnail($post->ID, 'recentprojects-thumb');
												}

											$item.='</div>'.PHP_EOL;

											$item.='<div class="modal-footer">'.PHP_EOL;
											
												if($this->user->loggedin){

													$item.='<a class="btn btn-sm btn-success" href="'. $editor_url .'" target="_self" title="Edit layer">Edit</a>';
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
										
										$item.='<a class="btn btn-sm btn-success" href="'. $editor_url .'" target="_self" title="Edit layer">Edit</a>';
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

	// output gallery 
	 
	echo '<div id="layer_gallery">';

		echo '<div class="col-xs-3 col-sm-2" style="padding:0;">';
			
			echo '<ul class="nav nav-tabs tabs-left">';
				
				echo '<li class="gallery_type_title" style="border-top: none;">Template library</li>';

					$class='';
					
					foreach( $this->all->layerType as $term ){
						
						$gallery_url = $this->urls->editor . '?gallery=' . $term->slug;

						if( $term->slug == $layer_type ){
							
							$class=' class="active" style="border-top: none;"';
							
							$layer_count = 0;
							
							foreach($ranges as $range){
								
								$layer_count += $range['count'];
							}
						}
						else{
							
							$class='';
							
							$layer_count = $term->count;
						}

						if($term->visibility == 'anyone'){
							
							echo '<li'.$class.'>';
							
								echo '<a href="' . $gallery_url . '">' . $term->name . ' <span class="badge pull-right" style="margin-top: 4px;padding: 1px 5px;font-size:11px;">' . $layer_count . '</span></a>';
								
							echo '</li>';					
						}
						elseif( $this->user->is_editor ){
							
							echo '<li'.$class.'>';
							
								echo '<a href="' . $gallery_url . '">' . $term->name . ' <span class="badge pull-right" style="margin-top: 4px;padding: 1px 5px;font-size:11px;">' . $layer_count . '</span> <span class="label label-warning pull-right" style="margin-right:8px;padding: 2px 4px;font-size: 10px;"> admin </span></a>';
								
							echo '</li>';						
						}
					}
				
			echo'</ul>';
		echo'</div>';

		echo'<div class="col-xs-9 col-sm-10 library-content" style="border-left: 1px solid #ddd;background:#fff;padding-bottom:15px;padding-top:15px;min-height:700px;">';
			
			echo'<div class="tab-content">';
			
				echo'<div class="tab-pane active" id="' . $layer_type . '">';
					
					//output Nav tabs
					
					echo'<ul class="nav nav-pills" role="tablist">';

					if(!empty($items)){

						foreach( $ranges as $range ){
							
							$url = add_query_arg( array(
								'gallery' 	=> $layer_type,
								'range' 	=> $range['slug'],
							), $this->urls->editor );
								
							echo'<li role="presentation"' . ( $range['slug'] == $layer_range ? ' class="active"' : '' ) . '><a href="' . $url . '" aria-controls="' . $range['slug'] . '" role="tab">'.strtoupper($range['name']).' <span class="badge">'.$range['count'].'</span></a></li>';
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
						
						echo'<div class="pagination" style="display: inline-block;width: 100%;padding: 0px 15px;">';

							echo paginate_links( array(
								'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
								'total'        => $query->max_num_pages,
								'current'      => max( 1, get_query_var( 'paged' ) ),
								'format'       => '?paged=%#%',
								'show_all'     => false,
								'type'         => 'plain',
								'end_size'     => 2,
								'mid_size'     => 1,
								'prev_next'    => true,
								'prev_text'    => sprintf( '<i></i> %1$s', __( 'Prev', 'live-template-editor-client' ) ),
								'next_text'    => sprintf( '%1$s <i></i>', __( 'Next', 'live-template-editor-client' ) ),
								'add_args'     => false,
								'add_fragment' => '',
							) );
							
						echo'</div>	';					
						
					echo'</div>';
					
					if( !$this->user->loggedin ){

						// login modal
						
						include( $this->views  . '/modals/login.php');
					}
					else{
						
						// upgrade plan modal
						
						include( $this->views  . '/modals/upgrade.php');				
					}
					
				echo '</div>';

			echo'</div>';
			
		echo'</div>	';

	echo'</div>';