<?php 

	// get current tab
	
	$currentTab = !empty($_GET['tab']) ? $_GET['tab'] : 'home';
	
	$output = $this->parent->inWidget  ? 'widget' : '';
	
	// ------------- output panel --------------------

	echo'<div id="media_library" class="wrapper">';
		
		echo '<div id="sidebar">';
				
			echo '<div class="gallery_type_title gallery_head">Dashboard</div>';

			echo '<ul class="nav nav-tabs tabs-left">';
				
				echo apply_filters('ltple_dashboard_sidebar','',$currentTab);
				
			echo '</ul>';
			
		echo '</div>';

		echo'<div id="content" class="library-content" style="border-left: 1px solid #ddd;background:#fbfbfb;padding-bottom:15px;min-height:700px;">';
			
			if( $currentTab == 'home' ){
				
				$boxes = $this->get_all_boxes();
				
				echo'<div id="dashboard" class="tab-content gutter-20" style="padding-top:15px;">';
					
					foreach( $boxes as $box ){
						
						echo $this->get_widget_box($box);
					}

				echo'</div>';
			}
			else{
				
				do_action( 'ltple_dashboard_' . $currentTab );			
			}

		echo'</div>	';

	echo'</div>';
	
	?>