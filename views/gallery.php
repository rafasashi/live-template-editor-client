<?php 
	
	// get gallery sections
	
	$all_sections = $this->gallery->get_all_sections();
	
	// get gallery types
	
	$current_types = $this->gallery->get_current_types();
	
	// get layer type
	
	if( $layer_type = $this->gallery->get_current_type() ){
		
		// get layer range
		
		$layer_range = $this->gallery->get_current_range();
		
		//get layer range name
		
		$layer_range_name = ( !empty($layer_type->ranges[$layer_range]['name']) ? $layer_type->ranges[$layer_range]['name'] : '' );
		
		do_action('ltple_gallery_before_output',$layer_type->slug,$layer_range);
		
		// output gallery 
		 
		echo '<div id="gallery_wrapper" class="wrapper">';

			echo '<div id="sidebar">';
				
				//if( !$this->inWidget ){
				
					echo '<div class="gallery_type_title gallery_head">';
						
						if( empty($_GET['layer']) ){
							
							echo 'All Categories';
						}
						elseif( is_array($_GET['layer']) && !empty($_GET['layer']['default_storage']) ){
							
							echo $this->layer->get_storage_name(sanitize_title($_GET['layer']['default_storage']));
						}
						else{
							
							echo 'Gallery';
						}
						
						// filters
						
						/*
						echo '<button class="btn btn-xs btn-info pull-right" style="';
							echo 'padding: 3px 7px;';
							echo 'margin: 5px;';
							echo 'background: #fff;';
							echo 'color: #4276a0;';
							echo 'font-size: 9px;';
							echo 'line-height: 16px;';
						echo '">filter</button>';
						*/
						
					echo '</div>';	
				//}
				
				echo '<ul id="gallery_sidebar" class="nav nav-tabs tabs-left">';
										
					foreach( $all_sections as $section => $type_ids ){
					
						echo '<li class="gallery_type_title">'.$section.'</li>';

						//$editors = $this->layer->get_layer_editors();
                        
						$class='';
						
						foreach( $type_ids as $id ){
							
							if( isset($current_types[$id]) ){
								
								$term = $current_types[$id];

                                $storage = $term->storage;
                                
								if( $storage_name = $this->layer->get_storage_name($term->storage) ){
								
									$gallery_url = add_query_arg($_GET,$this->urls->gallery);
									 
									$gallery_url = add_query_arg('gallery',$term->slug,$gallery_url);
									
									$gallery_url = remove_query_arg(array('range','uri'),$gallery_url);
									
									if( $term->slug == $layer_type->slug ){
										
										$class=' class="active" style="border-top: none;"';
										
										$layer_count = 0;
										
										foreach( $layer_type->ranges as $range ){
											
											$layer_count += $range['count'];
										}
									}
									else{
										
										$class='';
										
										$layer_count = $term->count;
									}
									
									if( $layer_count < 1 )
										
										continue;
									
									echo '<li'.$class.'>';
									
										echo '<a style="display:inline-block;width:100%;" href="' . $gallery_url . '">';
											
											echo '<div>';
											
												echo $term->name;
												
												echo ' <span class="badge pull-right hidden-xs" style="margin-top:13px;padding:1px 5px;font-size:10px;">' . $this->gallery->get_badge_count($layer_count) . '</span>';
												
											echo '</div>';
											
											echo '<div>';
												
												$label_style = 'margin-right:8px;padding:2px 0px;font-size:11px;color:' . $this->settings->navbarColor . 'b8;';
												
												echo '<i class="pull-left" style="' . $label_style . '">'.$storage_name.'</i> ';											
																			
												if( $term->visibility == 'admin' ){
													
													$label_style = 'margin-right:8px;padding:3px;font-size:9px;';
													
													echo '<div class="hidden-xs" style="display:inline-block;width:100%;"><span class="label label-warning" style="'.$label_style.'"> admin </span></div>';
												}
											
											echo '</div>';
											
										echo '</a>';
										
									echo '</li>';
								}
							}
						}
					}
					
				echo'</ul>';
				
			echo'</div>';

			echo'<div id="content" class="library-content" style="padding-bottom:15px;padding-top:0px;min-height:calc( 100vh - ' . ( $this->inWidget ?  0 : 190 ) . 'px);">';
				
				echo'<div class="tab-content">';
				
					echo'<div class="tab-pane active" id="' . $layer_type->slug . '">';
						
						//output Nav tabs
                            
                        echo'<ul class="nav nav-pills nav-resizable" role="tablist">';
                        
                            if( $ltple->inWidget ){
                                
                                echo'<li>';
                                
                                    echo $ltple->get_collapse_button();
                                    
                                echo'</li>';
                            }
                            
                            echo '<li>';

                                echo'<form id="tableFilters">';
                                
                                    // switch range
                                        
                                    $ranges = array('all'=>'All');
                                    
                                    if( !empty($layer_type->ranges) ){

                                        $gallery_count = 0;
                                        
                                        foreach( $layer_type->ranges as $range ){
                                            
                                            if( $range['count'] < 1 )
                                                continue;
                                            
                                            if( !$this->user->loggedin && !empty($layer_type->addon) && $layer_type->addon->slug == $range['slug'] )
                                                continue;
                                            
                                            $gallery_count += $range['count'];
                                            
                                            $ranges[$range['slug']] = $range['short'] . ' (' . $range['count'] . ')';
                                        }
                                        
                                        $ranges['all'] .= ' (' . $gallery_count . ')';
                                    }
                                
                                    echo $ltple->admin->display_field( array(

                                        'type'			=> 'select',
                                        'id'			=> 'gallery-range',
                                        'name'			=> 'range',
                                        'options' 		=> $ranges,
                                        'data'			=> $layer_range,

                                    ),false,false);
                                    
                                    do_action('ltple_gallery_tab',$layer_type,$layer_range);
                                
                                echo'</form>';
                            
                            echo '</li>';
                        
                        echo'</ul>';

						//output Tab panes
						
						$this->gallery->get_gallery_table($layer_type,$layer_range);
							
					echo '</div>';

				echo'</div>';
				
			echo'</div>	';

		echo'</div>';
		
		do_action('ltple_gallery_after_output');
	}
	