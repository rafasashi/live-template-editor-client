<?php 

	// get current tab
	
	$currentTab = ( !empty($_GET['tab']) ? $_GET['tab'] : 'home' );
	
	// ------------- output panel --------------------

	echo'<div id="media_library" class="wrapper">';

		echo $this->parent->dashboard->get_sidebar($currentTab);

		echo'<div id="content" class="library-content" style="border-left: 1px solid #ddd;background:#fbfbfb;padding-bottom:15px;min-height:700px;">';
			
			if( $currentTab == 'home' ){
				
				$boxes = $this->get_all_boxes();
				
				echo'<div id="dashboard" class="tab-content row gutter-20">';
					
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