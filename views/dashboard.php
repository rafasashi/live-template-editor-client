<?php 

	if(isset($_SESSION['message'])){ 
	
		//output message
	
		echo $_SESSION['message'];
		
		//reset message
		
		$_SESSION['message'] ='';
	}
	// get current tab
	
	$currentTab = ( !empty($_GET['tab']) ? $_GET['tab'] : 'home' );
	
	// ------------- output panel --------------------
	
	echo'<div id="media_library" class="wrapper">';

		echo '<div id="sidebar">';
		
			echo'<ul class="nav nav-tabs tabs-left">';
				
				echo'<li class="gallery_type_title">Dashboard</li>';
				
				echo'<li'.( $currentTab == 'home' ? ' class="active"' : '' ).'><a href="' . $this->parent->urls->dashboard . '">Home</a></li>';
			
			echo'</ul>';
			
		echo'</div>';

		echo'<div id="content" class="library-content" style="border-left: 1px solid #ddd;background:#fbfbfb;padding-bottom:15px;min-height:700px;">';
			
			if( $currentTab == 'home' ){
				
				$boxes = $this->get_all_boxes();
				
				echo'<div class="tab-content">';
					
					foreach( $boxes as $box ){
						
						echo $this->get_widget_box($box['content'],$box['title']);
					}

				echo'</div>';
			}

		echo'</div>	';

	echo'</div>';
	
	?>