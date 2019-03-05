<?php 

	if(!empty($this->message)){ 
	
		echo $this->message;
	}
	
	if(!empty($_SESSION['message'])){ 
	
		echo $_SESSION['message'];
		
		$_SESSION['message'] = '';
	}
	
	// get layer type
	
	$all_types = $this->gallery->get_all_types();
	
	if( !$layer_type = ( !empty($_GET['gallery']) ? $_GET['gallery'] : false ) ){
		
		foreach($all_types as $term){
						
			if( $term->visibility == 'anyone' || $this->user->is_editor ){
				
				$layer_type = $term->slug;
				
				break; 
			}
		}		
	}
	
	// get layer type name
	
	foreach($all_types as $term){
					
		if( $layer_type == $term->slug ){
			
			$layer_type_name = $term->name;
			
			break; 
		}
	}	
	
	if( $term = get_term_by('slug',$layer_type,'layer-type') ){
		
		//get addon range

		$addon_range = $this->gallery->get_type_addon_range($term);

		//get item ranges
		
		$ranges = $this->gallery->get_type_ranges($layer_type,$addon_range);
		
		//get layer range
		
		$layer_range = ( !empty($_GET['range']) ? $_GET['range'] : key($ranges) );
		
		//get layer range name
		
		$layer_range_name = ( !empty($ranges[$layer_range]['name']) ? $ranges[$layer_range]['name'] : '' );
		
		// get gallery items 
		
		$items = $this->gallery->get_range_items($layer_type,$layer_range,$addon_range);	

		do_action('ltple_gallery_before_output',$layer_type,$layer_range);
		
		// output gallery 
		 
		echo '<div id="layer_gallery" class="wrapper">';

			echo '<div id="sidebar">';
				
				echo '<ul class="nav nav-tabs tabs-left">';
					
					echo '<li class="gallery_type_title" style="border-top: none;">Template library</li>';

						$class='';
						
						foreach( $all_types as $term ){
							
							$gallery_url = add_query_arg($_GET,$this->urls->editor);
							 
							$gallery_url = add_query_arg('gallery',$term->slug,$gallery_url);
							
							$gallery_url = remove_query_arg(array('range','uri'),$gallery_url);
							
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
								
									echo '<a href="' . $gallery_url . '">' . $term->name . ' <span class="badge pull-right hidden-xs" style="margin-top: 4px;padding: 1px 5px;font-size:11px;">' . $layer_count . '</span></a>';
									
								echo '</li>';					
							}
							elseif( $this->user->is_editor ){
								
								echo '<li'.$class.'>';
								
									echo '<a href="' . $gallery_url . '">' . $term->name . ' <span class="badge pull-right hidden-xs" style="margin-top: 4px;padding: 1px 5px;font-size:11px;">' . $layer_count . '</span> <span class="label label-warning pull-right hidden-xs" style="margin-right:8px;padding: 2px 4px;font-size: 10px;"> admin </span></a>';
									
								echo '</li>';						
							}
						}
					
				echo'</ul>';
				
			echo'</div>';

			echo'<div id="content" class="library-content" style="border-left: 1px solid #ddd;background:#fbfbfb;padding-bottom:15px;padding-top:15px;min-height:700px;">';
				
				echo'<div class="tab-content">';
				
					echo'<div class="tab-pane active" id="' . $layer_type . '">';
						
						//output Nav tabs
						
						echo'<ul class="nav nav-pills" role="tablist">';
						
							if(!empty($ranges)){
								
								foreach( $ranges as $range ){
									
									$range_url = add_query_arg($_GET,$this->urls->editor);
									
									$range_url = remove_query_arg(array('uri'),$range_url);
									
									$range_url = add_query_arg( array(
									
										'gallery' 	=> $layer_type,
										'range' 	=> $range['slug'],
										
									), $range_url );
										
									echo'<li role="presentation"' . ( $range['slug'] == $layer_range ? ' class="active"' : '' ) . '><a href="' . $range_url . '" aria-controls="' . $range['slug'] . '" role="tab">'.strtoupper($range['name']).' <span class="badge">'.$range['count'].'</span></a></li>';
								}							
							}
							
							do_action('ltple_gallery_tab',$layer_type,$layer_range);

						echo'</ul>';

						//output Tab panes
						  
						echo'<div class="tab-content" style="margin-top:20px;">';
							
							if(!empty($items)){
								
								$this->plan->options = array($layer_type,$layer_range);
								
								$has_options = $this->plan->user_has_options($this->plan->options);
								
								$plans = $this->plan->get_plans_by_options( $this->plan->options );
	
								echo'<div class="row bs-callout bs-callout-primary" style="background:#fff;">';
									
									echo'<div class="col-xs-12 col-sm-9 col-md-10" style="padding-bottom:5px;">';
									
										echo'<h4>' . ucfirst($layer_type_name) .  ' > ' . ucfirst($layer_range_name) .  '</h4>';
										
										echo'<p>';
										
											if( $has_options === true ){
												
												echo'Edit any template from ' . ucfirst($layer_range_name) .  ' gallery';
											}
											elseif( !empty($plans) ){
												
												echo'You need the <span class="label label-success">'.$plans[0]['title'].'</span> plan'.( count($plans) > 1 ? ' or higher ' : ' ').'to <span class="label label-default">unlock all</span> the templates from this gallery';
											}
											else{
											
												echo'No plan available to unlock this gallery';
											}
										
										echo'</p>';
									
									echo'</div>';
																	
									if( !$has_options && !empty($plans) ){
										
										echo'<div class="col-xs-12 col-sm-3 col-md-2">';
														
											echo'<button type="button" class="btn btn-sm" data-toggle="modal" data-target="'.( $this->user->loggedin  === true ? '#upgrade_plan' : '#login_first').'" style="width:100%;font-size:12px;background:' . $this->settings->mainColor . '99;color:#fff;border:1px solid ' . $this->settings->mainColor . ';">';
											
												echo '<span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span> ' . ( $this->user->plan['info']['total_price_amount'] > 0 ? 'upgrade' : 'start' );
												
												echo '<br>';
												
												echo '<span style="font-size:10px;">from '.$plans[0]['price_tag'].'</span>';
												
											echo'</button>';

										echo'</div>';
									}
									
								echo'</div>';
								
								$active=' active';
							
								foreach($items as $range => $range_items){
									
									echo'<div role="tabpanel" class="tab-pane'.$active.'" id="' . $range . '">';
									
									foreach($range_items as $item){

										echo $item;
									}
									
									echo'</div>';
									
									$active='';
								}
							
								echo'<div class="pagination" style="display: inline-block;width: 100%;padding: 0px 15px;">';
									
									echo paginate_links( array(
										'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
										'total'        => $this->gallery->max_num_pages,
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
							}
							
							do_action('ltple_gallery_items',$layer_type,$layer_range);					
							
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
		
		do_action('ltple_gallery_after_output');
	}
	