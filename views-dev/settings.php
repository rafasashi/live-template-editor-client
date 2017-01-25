<?php 

	if(isset($_SESSION['message'])){ 
	
		echo $_SESSION['message'];
		
		//reset message
		
		$_SESSION['message'] ='';
	}
?>

<div id="media_library" style="margin-top:15px;background:#FFF;display:inline-block;width:100%;">

	<div class="col-xs-3 col-sm-2">
	
		<ul class="nav nav-tabs tabs-left">
			
			<li class="gallery_type_title">My Profile</li>
			
			<li class="active"><a href="#general-info" data-toggle="tab">General Info</a></li>
			
			<li><a href="#connected-apps" data-toggle="tab">Connected Apps</a></li>
			
			<li><a href="#custom-profile" data-toggle="tab">Custom Profile</a></li>
			
		</ul>
	</div>

	<div class="col-xs-9 col-sm-10" style="border-left: 1px solid #ddd;">
		
		<div class="tab-content">
		  
		<?php

			//---------------------- output General Info --------------------------
			
			echo'<div class="tab-pane active" id="general-info">';
			
				echo'<form action="" method="post" class="tab-content row" style="margin:20px;">';

					echo'<div class="col-xs-12 col-sm-6">';
				
						echo'<h3>General Information</h3>';
						
					echo'</div>';

					echo'<div class="col-xs-12 col-sm-4"></div>';
					
					echo'<div class="clearfix"></div>';
				
					echo'<div class="col-xs-12 col-sm-8">';

						echo'<table class="form-table">';
						
							foreach( $this->profile->fields as $field ){
								
								echo'<tr>';
								
									echo'<th><label for="'.$field['label'].'">'.ucfirst($field['label']).'</label></th>';
									
									echo'<td>';
									
										$this->admin->display_field( $field , $this->user );
									
									echo'</td>';
									
								echo'</tr>';
							}
							
						echo'</table>';
						
					echo'</div>';
					
					echo'<div class="clearfix"></div>';
					
					echo'<div class="col-xs-12 col-sm-6"></div>';
					
					echo'<div class="col-xs-12 col-sm-2 text-right">';
				
						echo'<button class="btn btn-sm btn-warning" style="width:100%;margin-top: 10px;">Save</button>';
						
					echo'</div>';

					echo'<div class="col-xs-12 col-sm-4"></div>';
						
				echo'</form>';
				
			echo'</div>';
			

			//---------------------- output Connected Apps --------------------------
			
			echo'<div class="tab-pane" id="connected-apps">';
			
				echo'<form action="" method="post" class="tab-content row" style="margin:20px;">';

					echo'<div class="col-xs-12 col-sm-6">';
				
						echo'<h3>Connected Apps</h3>';
						
					echo'</div>';

					echo'<div class="col-xs-12 col-sm-4"></div>';
					
					echo'<div class="clearfix"></div>';
				
					echo'<div class="col-xs-12 col-sm-8">';

						echo'<table class="form-table">';
							
							foreach( $this->user->profile->apps as $field ){
								
								echo'<tr>';
								
									echo'<th><label for="'.$field['label'].'">'.ucfirst($field['label']).'</label></th>';
									
									echo'<td>';
									
										$this->admin->display_field( $field , $this->user );
									
									echo'</td>';
									
								echo'</tr>';
							}
							
						echo'</table>';
						
					echo'</div>';
					
					echo'<div class="clearfix"></div>';
					
					echo'<div class="col-xs-12 col-sm-6"></div>';
					
					echo'<div class="col-xs-12 col-sm-2 text-right">';
				
						echo'<button class="btn btn-sm btn-warning" style="width:100%;margin-top: 10px;">Save</button>';
						
					echo'</div>';

					echo'<div class="col-xs-12 col-sm-4"></div>';
						
				echo'</form>';
				
			echo'</div>';
			
			//---------------------- output Custom Profile --------------------------
						
			echo'<div class="tab-pane" id="custom-profile">';
				
				echo'<form action="" method="post" class="tab-content row" style="margin:20px;">';

					echo'<div class="col-xs-12 col-sm-6">';
				
						echo'<h3>Custom Profile</h3>';
						
					echo'</div>';
					
					echo'<div class="col-xs-12 col-sm-4"></div>';
					
					echo'<div class="clearfix"></div>';
				
					echo'<div class="col-xs-12 col-sm-8">';

						echo'<table class="form-table">';
						
							foreach( $this->user->profile->customization as $field ){
								
								echo'<tr>';
								
									echo'<th><label for="'.$field['label'].'">'.ucfirst($field['label']).'</label></th>';
									
									echo'<td>';
									
										$this->admin->display_field( $field , $this->user );
									
									echo'</td>';
									
								echo'</tr>';
							}
							
						echo'</table>';
						
					echo'</div>';
					
					echo'<div class="clearfix"></div>';
					
					echo'<div class="col-xs-12 col-sm-6"></div>';

					echo'<div class="col-xs-12 col-sm-2 text-right">';
				
						echo'<button class="btn btn-sm btn-warning" style="width:100%;margin-top: 10px;">Update</button>';
						
					echo'</div>';

					echo'<div class="col-xs-12 col-sm-4"></div>';
	
				echo'</form>';
				
			echo'</div>';

		?>
		  
		</div>
		
	</div>	

</div>