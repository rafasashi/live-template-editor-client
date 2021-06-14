<?php 

	// get current tab
	
	$currentTab = $_REQUEST['list'];
	
	$output = ( $this->parent->inWidget ? 'widget' : '' ); 
	
	// ------------- output panel --------------------
	
	echo'<div id="panel" class="wrapper">';
		
		echo '<div id="sidebar">';
				
			echo '<div class="gallery_type_title gallery_head">Dashboard</div>';

			echo '<ul class="nav nav-tabs tabs-left">';
				
				echo apply_filters('ltple_list_sidebar','',$currentTab);
				
			echo '</ul>';
			
		echo '</div>';
		
		echo'<div id="content" class="library-content" style="border-left: 1px solid #ddd;background:#fbfbfb;min-height:10vh;">';
			
			echo'<div class="tab-content">';

				if( $post_type = get_post_type_object( $currentTab ) ){
					
					echo'<ul class="nav nav-pills" role="tablist">';
							
						if( $this->parent->inWidget ){
							
							echo'<li>';
							
								echo $this->parent->get_collapse_button();
								
							echo'</li>';
						}
							
						if( $currentTab == 'user-page' || $currentTab == 'user-menu' ){
							
							echo'<li role="presentation"'.( $currentTab == 'user-page' ? ' class="active"' : '' ).'><a href="' . $this->parent->urls->profile . '?list=user-page" role="tab">Pages</a></li>';
						
							echo'<li role="presentation"'.( $currentTab == 'user-menu' ? ' class="active"' : '' ).'><a href="' . $this->parent->urls->profile . '?list=user-menu" role="tab">Menus</a></li>';							
						}
						else{ 
							
							echo'<li role="presentation" class="active"><a href="' . $this->parent->urls->current . '" role="tab">' . $post_type->label . '</a></li>';
						}
						
						if( $currentTab == 'user-app' ){
							
							echo '<li role="presentation"><a href="' . apply_filters( 'ltple_list_'.$currentTab.'_new_url', $this->parent->urls->gallery . '?layer[default_storage]=' . $currentTab, $currentTab, $output ) . '" class="btn btn-success btn-sm" style="margin:7px;padding:5px 10px !important;">+ New</a></li>';						
						}
						else{
							
							echo '<li role="presentation">';
								
								$gallery_url = add_query_arg( array(
								
									'output' 	=> 'widget',
									
								),$this->parent->urls->gallery . '?layer[default_storage]=' . $currentTab);
								
								$modal_id='modal_'.md5($gallery_url);
								
								echo'<a href="#new" style="margin:7px 3px;padding:5px 10px !important;" type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#'.$modal_id.'">'.PHP_EOL;
									
									echo'+ New'.PHP_EOL;
								
								echo'</a>'.PHP_EOL;

								echo'<div class="modal fade" id="'.$modal_id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'.PHP_EOL;
									
									echo'<div class="modal-dialog modal-full" role="document">'.PHP_EOL;
										
										echo'<div class="modal-content">'.PHP_EOL;
										
											echo'<div class="modal-header">'.PHP_EOL;
												
												echo'<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
												
												echo'<h4 class="modal-title text-left" id="myModalLabel">New Project</h4>'.PHP_EOL;
											
											echo'</div>'.PHP_EOL;
										  
											echo '<iframe data-src="'.$gallery_url.'" style="display:block;position:relative;width:100%;top:0;bottom: 0;border:0;height:calc( 100vh - 50px );"></iframe>';
										  
										echo'</div>'.PHP_EOL;
										
									echo'</div>'.PHP_EOL;
									
								echo'</div>'.PHP_EOL;

							echo '</li>';
						}
						
						do_action('ltple_dashboard_list_actions',$post_type);
						
					echo'</ul>';

					// get table fields
					
					echo'<div>';
						
						$fields = array(
							
							array(

								'field' 	=> 'preview',
								'sortable' 	=> 'false',
								'content' 	=> '',
							),
							array(

								'field' 		=> 'name',
								'sortable' 		=> 'true',
								'content' 		=> 'Name',
								'filter-control'=> 'input',
							),
							array(

								'field' 		=> 'type',
								'sortable' 		=> 'true',
								'content' 		=> 'Category',
								'filter-control'=> 'select',
							)								
						);

						$fields = apply_filters('ltple_table_fields',$fields,$post_type);
						
						if( $this->parent->layer->is_public($post_type) && $this->parent->layer->is_hosted($post_type) ){
							
							$fields[] = array(

								'field' 		=> 'status',
								'sortable' 		=> 'true',
								'content' 		=> 'Status',
								'filter-control'=> 'select',
							);
						}

						$fields[] = array(

							'field' 	=> 'action',
							'sortable' 	=> 'false',
							'content' 	=> '',
						);	
					
						// get table of results

						$this->parent->api->get_table(
						
							$this->parent->urls->api . 'ltple-list/v1/'.$currentTab.'?' . http_build_query($_POST, '', '&amp;'), 
							apply_filters('ltple_list_'.$currentTab.'_fields',$fields), 
							$trash		= false,
							$export		= false,
							$search		= true,
							$toggle		= false,
							$columns	= false,
							$header		= true,
							$pagination	= true,
							$form		= false,
							$toolbar 	= 'toolbar',
							$card		= false,
							$itemHeight	= 235,
							$fixedHeight= false
						);

					echo'</div>';
				}
				else{
					
					echo 'This template type doesn\'t exist...';
				}

			echo'</div>';
			
		echo'</div>	';

	echo'</div>';
