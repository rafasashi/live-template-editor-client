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
			
		</ul>
	</div>

	<div class="col-xs-9 col-sm-10" style="border-left: 1px solid #ddd;">
		
		<div class="tab-content">
		  
			<?php

			//---------------------- output General Info --------------------------
			
			echo'<div class="tab-pane active" id="general-info">';
			
				//output Tab panes
				
				echo'<div class="tab-content row" style="margin:20px;">';
					
					if( $this->user->loggedin ){

						echo'<div class="col-xs-12 col-sm-8">';
							
							echo'<h3>General Information</h3>';
							
							echo'<form action="" method="post">';
							
								echo'<table class="form-table">';
								
									foreach( $this->user->profile->fields as $field ){
										
										echo'<tr>';
										
											echo'<th><label for="'.$field['label'].'">'.ucfirst($field['label']).'</label></th>';
											
											echo'<td>';
											
												$this->admin->display_field( $field , $this->user );
											
											echo'</td>';
											
										echo'</tr>';
									}
									
								echo'</table>';
								
							echo'</form>';
							
						echo'</div>';
					}
					
				echo'</div>';
				
			echo'</div>';

			?>
		  
		</div>
		
	</div>	

</div>

<script>

	;(function($){
		
		$(document).ready(function(){

			// submit forms
			
			$( "button" ).click(function() {
				
				this.closest( "form" ).submit();
			});
		
		});
		
	})(jQuery);

</script>