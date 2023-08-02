<?php 

$ltple = LTPLE_Client::instance();

$currentTab = $ltple->get_current_tab('home');

$output = $ltple->inWidget  ? 'widget' : '';

// ------------- output panel --------------------

echo'<div id="media_library" class="wrapper">';
	
	echo '<div id="sidebar">';
			
		echo '<ul class="nav nav-tabs tabs-left">';
			
			echo apply_filters('ltple_dashboard_sidebar','',$currentTab);
			
		echo '</ul>';
		
	echo '</div>';

	echo'<div id="content" class="library-content" style="padding-bottom:15px;min-height:700px;">';
		
		if( $currentTab == 'home' ){
			
			$boxes = $this->get_all_boxes();
			
			echo'<div id="dashboard" class="tab-content gutter-20" style="padding:10px 5px;display:inline-block;width:100%;">';
				
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
