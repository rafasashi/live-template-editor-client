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
			
			<li class="gallery_type_title">Design Rules</li>
			
			<li><a href="#html-markups" data-toggle="tab">HTML Markups</a></li>
			
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
					
					echo'<div class="col-xs-12 col-sm-2 text-right">';
						
						echo'<a target="_blank" class="label label-warning" style="font-size: 13px;" href="'.$this->urls->editor . '?pr='.$this->user->ID . '">view profile</a>';
						
					echo'</div>';
					
					echo'<div class="col-xs-12 col-sm-2"></div>';
					
					echo'<div class="clearfix"></div>';
				
					echo'<div class="col-xs-12 col-sm-8">';

						echo'<table class="form-table">';
	
							foreach( $this->user->profile->pictures as $field ){
								
								echo'<tr>';
								
									echo'<th><label for="'.$field['label'].'">'.ucfirst($field['label']).'</label></th>';
									
									echo'<td>';
									
										$this->admin->display_field( $field , $this->user );
									
									echo'</td>';
									
								echo'</tr>';
							}
	
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
				
						echo'<h3>Displayed connected accounts</h3>';
						
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
			
			//---------------------- output HTML markups --------------------------
						
			echo'<div class="tab-pane" id="html-markups">';
			
				echo'<h3>List of allowed HTML markups</h3>';
			
				echo'<div class="col-xs-12 wrapper">';
				echo'<div class="col-xs-12 dasheaders">';
				echo'<div class="col-xs-3 hed center">Open tag</div>';
				echo'<div class="col-xs-3 hed center">Closed tag</div>';
				echo'<div class="col-xs-6 hed center">Info</div>';

				echo'<div class="col-xs-3 markup">&lt;a&gt;</div>';
				echo'<div class="col-xs-3 markup">&lt;/a&gt;</div>';
				echo'<div class="col-xs-6 info">Defines a hyperlink, anchor link</div>';

				echo'<div class="col-xs-3 markup">&lt;p&gt;</div>';
				echo'<div class="col-xs-3 markup">&lt;/p&gt;</div>';
				echo'<div class="col-xs-6 info">Defines a paragraph</div>';

				echo'<div class="col-xs-3 markup">&lt;br&gt;</div>';
				echo'<div class="col-xs-3 markup na">n/a</div>';
				echo'<div class="col-xs-6 info">Inserts a single line break</div>';

				echo'<div class="col-xs-3 markup">&lt;hr&gt;</div>';
				echo'<div class="col-xs-3 markup na">n/a</div>';
				echo'<div class="col-xs-6 info">Defines a thematic change in the content - horizontal rule</div>';

				echo'<div class="col-xs-3 markup">&lt;b&gt;</div>';
				echo'<div class="col-xs-3 markup">&lt;/b&gt;</div>';
				echo'<div class="col-xs-6 info">Defines bold text</div>';

				echo'<div class="col-xs-3 markup">&lt;em&gt;</div>';
				echo'<div class="col-xs-3 markup">&lt;/em&gt;</div>';
				echo'<div class="col-xs-6 info">Defines emphasized text</div>';

				echo'<div class="col-xs-3 markup">&lt;i&gt;</div>';
				echo'<div class="col-xs-3 markup">&lt;/i&gt;</div>';
				echo'<div class="col-xs-6 info">Defines a part of text in an alternate voice or mood</div>';

				echo'<div class="col-xs-3 markup">&lt;strong&gt;</div>';
				echo'<div class="col-xs-3 markup">&lt;/strong&gt;</div>';
				echo'<div class="col-xs-6 info">Defines important text - similar output like <strong>b</strong> tag</div>';

				echo'<div class="col-xs-3 markup">&lt;u&gt;</div>';
				echo'<div class="col-xs-3 markup">&lt;/u&gt;</div>';
				echo'<div class="col-xs-6 info">Defines text that should be stylistically different from normal text - underline</div>';

				echo'<div class="col-xs-3 markup">&lt;img/&gt;</div>';
				echo'<div class="col-xs-3 markup"></div>';
				echo'<div class="col-xs-6 info">Defines an image</div>';

				echo'<div class="col-xs-3 markup">&lt;ul&gt;</div>';
				echo'<div class="col-xs-3 markup">&lt;/ul&gt;</div>';
				echo'<div class="col-xs-6 info">Defines an unordered list</div>';

				echo'<div class="col-xs-3 markup">&lt;ol&gt;</div>';
				echo'<div class="col-xs-3 markup">&lt;/ol&gt;</div>';
				echo'<div class="col-xs-6 info">Defines an ordered list</div>';

				echo'<div class="col-xs-3 markup">&lt;li&gt;</div>';
				echo'<div class="col-xs-3 markup">&lt;/li&gt;</div>';
				echo'<div class="col-xs-6 info">Defines a list item</div>';

				echo'<div class="col-xs-3 markup">&lt;div&gt;</div>';
				echo'<div class="col-xs-3 markup">&lt;/div&gt;</div>';
				echo'<div class="col-xs-6 info">Defines a section in a document</div>';

				echo'<div class="col-xs-3 markup">&lt;span&gt;</div>';
				echo'<div class="col-xs-3 markup">&lt;/span&gt;</div>';
				echo'<div class="col-xs-6 info">Defines a section in a document</div>';

				echo'<div class="col-xs-3 markup">&lt;header&gt;</div>';
				echo'<div class="col-xs-3 markup">&lt;/header&gt;</div>';
				echo'<div class="col-xs-6 info html5">Defines a header for a document or section</div>';

				echo'<div class="col-xs-3 markup">&lt;footer&gt;</div>';
				echo'<div class="col-xs-3 markup">&lt;/footer&gt;</div>';
				echo'<div class="col-xs-6 info html5">Defines a footer for a document or section</div>';

				echo'<div class="col-xs-3 markup">&lt;main&gt;</div>';
				echo'<div class="col-xs-3 markup">&lt;/main&gt;</div>';
				echo'<div class="col-xs-6 info html5">Specifies the main content of a document</div>';

				echo'<div class="col-xs-3 markup">&lt;section&gt;</div>';
				echo'<div class="col-xs-3 markup">&lt;/section&gt;</div>';
				echo'<div class="col-xs-6 info html5">Defines a section in a document</div>';

				echo'<div class="col-xs-3 markup">&lt;article&gt;</div>';
				echo'<div class="col-xs-3 markup">&lt;/article&gt;</div>';
				echo'<div class="col-xs-6 info html5">Defines an article</div>';

				echo'<div class="col-xs-3 markup">&lt;aside&gt;</div>';
				echo'<div class="col-xs-3 markup">&lt;/aside&gt;</div>';
				echo'<div class="col-xs-6 info html5">Defines content aside from the page content</div>';


				echo'</div>';
				echo'</div>';				
							
			echo'</div>';

		?>
		  
		</div>
		
	</div>	

</div>