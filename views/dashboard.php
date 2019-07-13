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

		echo $this->parent->dashboard->get_sidebar($currentTab);

		echo'<div id="content" class="library-content" style="border-left: 1px solid #ddd;background:#fbfbfb;padding-bottom:15px;min-height:700px;">';
			
			if( $currentTab == 'home' ){
				
				$boxes = $this->get_all_boxes();
				
				echo'<div id="dashboard" class="tab-content">';
					
					foreach( $boxes as $box ){
						
						echo $this->get_widget_box($box['content'],$box['title']);
					}

				echo'</div>';
			}

		echo'</div>	';

	echo'</div>';
	
	?>