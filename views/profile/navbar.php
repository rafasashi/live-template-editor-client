<?php
	
	$ltple = LTPLE_Client::instance();
		
	if( $ltple->inWidget ) return;
	
?>

<nav class="formheadersearch" role="navigation" style="background-color:transparent;z-index:998;">

	<?php

		echo'<div id="navbar-features" class="pull-left" style="padding:12px 0;">';	
		
			do_action('ltple_menu_buttons');	
		
		echo'</div>';
		
		// avatar
		
		do_action('ltple_avatar_menu');

		$picture = $ltple->image->get_avatar_url( $ltple->user->ID );
		
		$picture = add_query_arg('_',time(),$picture);
		
		echo'<button style="margin-right:5px;float:right;background:transparent;border:none;width:49px;height:50px;display:inline-block;" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img style="padding:10px;" class="img-circle" src="'.$picture.'" height="50" width="50" /></button>';
		
		// account settings
		
		echo'<ul class="dropdown-menu dropdown-menu-right" style="width:250px;margin-top:-3px;">';
			
			if( $ltple->user->ID > 0 ){

				echo'<li style="position:relative;display:table;width:100%;background:#112331;box-shadow: inset 0 0 10px #060c10;">';
					
					echo'<div style="
						float: left;
						width: 30%;
						padding: 12px;
					">';
					
						echo'<img style="border: 2px solid #3b4954;" class="img-circle" src="'.$picture.'">';
					
					echo'</div>';
					
					echo'<div style="
						width: 70%;
						float: left;
						padding: 8px 6px 2px 6px;
					">';
							
						echo'<div style="color:#eee;font-weight:bold;max-width:100%;overflow:hidden;">';
						
							echo $ltple->user->nickname;

						echo'</div>';
						
						echo'<a href="'. $ltple->urls->primary . '/profile/' . $ltple->user->ID . '/" style="display:block;width:100%;">';
							
							echo'<span class="glyphicon glyphicon-user" aria-hidden="true"></span> ';
							
							echo'View Profile';
						
						echo'</a>';

					echo'</div>';
					
				echo'</li>';	
					
				if( $ltple->user->ID == $ltple->profile->id ){

					do_action('ltple_view_my_profile_settings');														
					
					do_action('ltple_view_my_profile');
				}
				
				echo'<li style="position:relative;background:#182f42;">';
					
					$redirect_to = $ltple->profile->id > 0 ? $ltple->urls->current : $ltple->urls->editor;
					
					echo '<a href="'. wp_logout_url( $redirect_to ) .'"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> Logout</a>';

				echo'</li>';					
			}
			else{
				
				$login_url = home_url('/login/');

				echo'<li style="position:relative;background:#182f42;">';
					
					echo '<a href="'. esc_url( $login_url ) .'"><span class="glyphicon glyphicon-log-in" aria-hidden="true"></span> Login</a>';

				echo'</li>';
				
				echo'<li style="position:relative;background:#182f42;">';
					
					echo '<a href="'. esc_url( add_query_arg('action','register',$login_url) ) .'"><span class="glyphicon glyphicon-hand-right" aria-hidden="true"></span> Register</a>';

				echo'</li>';
			}
			
		echo'</ul>';
	?>
	
</nav>