<?php 

$ltple = LTPLE_Client::instance();

$currentTab = $ltple->get_current_tab();

$output = ( $ltple->inWidget ? 'widget' : '' ); 

// ------------- output panel --------------------

echo'<div id="panel" class="wrapper">';
	
	include('sidebar.php');
	
	echo'<div id="content" class="library-content" style="min-height:10vh;">';
		
		echo'<div class="tab-content">';
			
			if( $post_type = get_post_type_object( $currentTab ) ){
				
				echo'<ul class="nav nav-pills nav-resizable" role="tablist">';
						
					if( $ltple->inWidget ){
						
						echo'<li>';
						
							echo $ltple->get_collapse_button();
							
						echo'</li>';
					}
						
					echo apply_filters('ltple_list_'.$currentTab.'_menu','<li role="presentation" class="active"><a href="' . $ltple->urls->current . '" role="tab">' . $post_type->label . '</a></li>',$currentTab,$post_type);
					
					$modal_url = false;
					
					if( $currentTab == 'user-app' ){
						
						$modal_url = apply_filters( 'ltple_list_'.$currentTab.'_new_url', $ltple->urls->gallery . '?layer[default_storage]=' . $currentTab, $currentTab, 'widget' );					
					
						$modal_title = __('Add Account','live-template-editor-client');
					}
					elseif( apply_filters('ltple_list_'.$currentTab.'_new_modal',true) ){
						
						$modal_url = add_query_arg( array(
						
							'output' 	=> 'widget',
							
						),$ltple->urls->gallery . '?layer[default_storage]=' . $currentTab);
					
						$modal_title = __('New Project','live-template-editor-client');
					}
					
					if( !empty($modal_url) ){
						
						$modal_id='modal_'.md5($modal_url);
							
						echo '<li role="presentation">';

							echo'<a href="#" style="margin:7px 3px;padding:5px 10px !important;" type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#'.$modal_id.'">'.PHP_EOL;
								
								echo'+ New'.PHP_EOL;
							
							echo'</a>'.PHP_EOL;

							echo'<div class="modal fade" id="'.$modal_id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'.PHP_EOL;
								
								echo'<div class="modal-dialog modal-full" role="document">'.PHP_EOL;
									
									echo'<div class="modal-content">'.PHP_EOL;
									
										echo'<div class="modal-header">'.PHP_EOL;
											
											echo'<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
											
											echo'<h4 class="modal-title text-left" id="myModalLabel">' . $modal_title . '</h4>'.PHP_EOL;
										
										echo'</div>'.PHP_EOL;
									  
										echo '<iframe data-src="'.$modal_url.'" style="display:block;position:relative;width:100%;top:0;bottom: 0;border:0;height:calc( 100vh - 50px );"></iframe>';
									  
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
					
					if( $ltple->layer->is_public($post_type) && $ltple->layer->is_hosted($post_type) ){
						
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

					$ltple->api->get_table(
					
						$ltple->urls->api . 'ltple-list/v1/'.$currentTab.'?' . http_build_query($_POST, '', '&amp;'), 
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
