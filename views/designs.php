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
		
		$all_types = $this->gallery->get_all_types();
		
		foreach($all_types as $term){
						
			if( $term->visibility == 'anyone' || $this->user->is_editor ){
			
				$layer_type = $term->slug;
				break; 
			}
		}		
	}
	
	if( $term = get_term_by('slug',$layer_type,'layer-type') ){
		
		//get addon range

		$addon_range = ( !empty($this->user->user_email) ? $this->gallery->get_type_addon_range($term) : null );
		
		//get item ranges
		
		$ranges = $this->gallery->get_type_ranges($layer_type,$addon_range);

		$layer_range = ( !empty($_GET['range']) ? $_GET['range'] : key($ranges) );
		
		// get gallery items 
		
		$items = $this->gallery->get_range_items($layer_type,$layer_range,$addon_range);	

		
		// output gallery 
		 
		echo '<div id="layer_gallery">';

			echo '<div class="col-xs-3 col-sm-2" style="padding:0;">';
				
				echo '<ul class="nav nav-tabs tabs-left">';
					
					echo '<li class="gallery_type_title" style="border-top: none;">Template library</li>';

						$class='';
						
						$all_types = $this->gallery->get_all_types();
						
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

			echo'<div class="col-xs-9 col-sm-10 library-content" style="border-left: 1px solid #ddd;background:#fff;padding-bottom:15px;padding-top:15px;min-height:700px;">';
				
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
							
							// addons tab
							
							// marketplace tab

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
	}