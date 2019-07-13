<?php 

	// get current tab
	
	$currentTab = $_REQUEST['list'];
	
	$output = ''; 
	
	// ------------- output panel --------------------
	
	if(!empty($this->parent->message)){ 
	
		//output message
	
		echo $this->parent->message;
	}
	
	
	if(!empty($_SESSION['message'])){
		
		echo $_SESSION['message'].PHP_EOL;
		
		$_SESSION['message'] = '';
	}
	
	echo'<div id="panel" class="wrapper">';

		echo $this->dashboard->get_sidebar($currentTab);
		
		echo'<div id="content" class="library-content" style="border-left: 1px solid #ddd;background:#fbfbfb;padding-bottom:15px;;min-height:700px;">';
			
			echo'<div class="tab-content">';

				if( $post_type = get_post_type_object( $currentTab ) ){
					
					echo'<ul class="nav nav-pills" role="tablist">';
						
						if( $currentTab == 'user-page' || $currentTab == 'user-menu' ){
							
							echo'<li role="presentation"'.( $currentTab == 'user-page' ? ' class="active"' : '' ).'><a href="' . $this->urls->editor . '?list=user-page" role="tab">Pages</a></li>';
						
							echo'<li role="presentation"'.( $currentTab == 'user-menu' ? ' class="active"' : '' ).'><a href="' . $this->urls->editor . '?list=user-menu" role="tab">Menus</a></li>';							
						}
						else{ 
							
							echo'<li role="presentation" class="active"><a href="' . $this->urls->current . '" role="tab">' . $post_type->label . '</a></li>';
						}
						
						echo '<li role="presentation"><a href="' . apply_filters( 'ltple_list_'.$currentTab.'_new_url', $this->urls->editor . '?layer[default_storage]=' . $currentTab, $currentTab, $output ) . '" class="btn btn-success btn-sm" style="margin:7px;padding:5px 10px !important;">+ New</a></li>';
						
					echo'</ul>';

					// get table fields
					
					echo'<div class="row">';
						
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
								'content' 		=> 'Type',
								'filter-control'=> 'select',
							)								
						);

						$fields = apply_filters('ltple_table_fields',$fields,$post_type);
						
						if( $this->layer->is_hosted($post_type) ){
							
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

						$this->api->get_table(
						
							$this->urls->api . 'ltple-list/v1/'.$currentTab.'?' . http_build_query($_POST, '', '&amp;'), 
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
							$card		= false
						);

					echo'</div>';
				}
				else{
					
					echo 'This template type doesn\'t exist...';
				}

			echo'</div>';
			
		echo'</div>	';

	echo'</div>';
	
	?>
	
	<script>

		;(function($){		
			
			$(document).ready(function(){

			
				
			});
			
		})(jQuery);

	</script>