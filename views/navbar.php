<div class="row" style="background:rgb(35, 64, 88);padding: 8px 0;margin:0;position: relative;">

	<div class="col-xs-6 col-sm-4" style="z-index:10;">				
		
		<div class="pull-left">

			<a class="btn btn-sm btn-warning" href="<?php echo $_SERVER['SCRIPT_URI'] ?>" role="button" data-html="true" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-title="Gallery of Designs" data-content="The gallery is where you can find beautifull designs to start a project. New things are added every week.">
			
				Gallery
			
			</a>
		
		</div>
		
		<div class="pull-left">

			<a style="margin-left: 6px;" class="btn btn-sm btn-primary" href="<?php echo $this->urls->editor . '?media' ?>" role="button" data-html="true" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-title="Media Library" data-content="The media library allows you to import and manage all your media, a good way to centralize everything.">
			
				Media
			
			</a>
		
		</div>
		
		<div class="pull-left">

			<a style="margin-left: 6px;background-color:#bd3d72;border: 1px solid #9c4167;" class="btn btn-sm btn-primary" href="<?php echo $this->urls->editor . '?app' ?>"  role="button" data-html="true" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-title="Connected Apps" data-content="Connect third party accounts to import your contents, to take advantage of advanced features and to gain stars.">
				
				Apps
				
			</a>
		
		</div>
		
		<div class="pull-left">

			<a style="margin-left: 6px;background-color:#E91E63;border: 1px solid #9c4167;" class="btn btn-sm btn-primary" href="<?php echo $this->urls->editor . '?domain' ?>"  role="button" data-html="true" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-title="Domains and Urls" data-content="Manage your domains, add subdomains and assign urls to your hosted pages.">
				
				Domains
				
			</a>
		
		</div>
		
		<div class="pull-left">
 
			<a class="btn btn-sm popover-btn" href="<?php echo $this->urls->editor . '?rank'; ?>" role="button" data-html="true" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-title="Popularity score" data-content="Your stars determine your rank in our World Ranking, give you visibility and drive traffic.">
  
				<span class="badge"><span class="glyphicon glyphicon-star" aria-hidden="true"></span>  <?php echo $this->user->stars; ?></span>
			
			</a>
			
		</div>

	</div>
	
	<div class="col-xs-6 col-sm-8 text-right">

		<?php if( $this->layer->type == 'default-layer' ){
		
			echo'<form class="pull-left" style="width:250px;display:inline-block;" target="_parent" action="'. $_SERVER['SCRIPT_URI'] . '?uri=default-layer/' . $this->layer->slug . '/' . '" id="savePostForm" method="post">';
				
				echo'<div class="input-group">';
					
					echo'<input type="text" name="postTitle" id="postTitle" value="" class="form-control input-sm required" placeholder="Template Title" ' . ( ( !isset($this->user->plan['info']['total_storage']) || $this->user->layerCount + 1 > $this->user->plan['info']['total_storage']['templates']) ? 'disabled' : '' ) .'>';
					echo'<input type="hidden" name="postContent" id="postContent" value="">';
					echo'<input type="hidden" name="postAction" id="postAction" value="save">';
					
					wp_nonce_field( 'user_layer_nonce', 'user_layer_nonce_field' );
					
					echo'<input type="hidden" name="submitted" id="submitted" value="true">';
					
					echo'<span class="input-group-btn">';
						
						if( $this->user->plan["info"]["total_price_amount"]>0 ){
						
							echo'<button class="btn btn-sm btn-primary" type="button" id="saveBtn" style="border-radius: 0 3px 3px 0;">Save</button>';
					
						}
						else{
						
							echo'<button class="btn btn-sm btn-primary" type="button" data-toggle="popover" data-placement="bottom" title="Pro users only" data-content="You need a paid plan <?php echo PHP_EOL; ?> to unlock this action"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span>  Save</button>';
						}
					
					echo'</span>';
					
				echo'</div>';
			echo'</form>';
		
		}
		elseif( $this->user->has_layer && $this->layer->type == 'user-layer'){
			
			echo'<form style="display:inline-block;" target="_parent" action="' . $_SERVER['SCRIPT_URI'] . '?uri=user-layer/' . $this->layer->slug . '/' . '" id="savePostForm" method="post">';
				
				echo'<input type="hidden" name="postTitle" id="postTitle" value="' . $this->user->layer->post_title . '" class="form-control required" placeholder="Layer Title">';
				echo'<input type="hidden" name="postContent" id="postContent" value="">';
				echo'<input type="hidden" name="postAction" id="postAction" value="save">';
				 
				wp_nonce_field( 'user_layer_nonce', 'user_layer_nonce_field' );
				
				echo'<input type="hidden" name="submitted" id="submitted" value="true">';
				
				echo'<button style="background-color: #3F51B5;border: 1px solid #5869ca;margin-right:5px;" class="btn btn-sm btn-primary" type="button" id="saveBtn">Save</button>';
				
			echo'</form>';
			
			echo '<a class="btn btn-sm btn-danger" href="' . $_SERVER['SCRIPT_URI'] . '?uri=user-layer/' . $this->layer->slug . '/&postAction=delete">Delete</a>';
		}
		
		if( $this->layer->type != '' ){
			
			echo '<a target="_blank" class="btn btn-sm btn-default" href="' . get_post_permalink( $this->layer->id ) . '" style="margin-left: 4px;border-color: #9c6433;color: #fff;background-color: rgb(189, 120, 61);">View</a>';
		
			if( $this->user->is_admin ){
			
				echo'<div style="margin:0 2px;" class="btn-group">';
				
					echo'<button type="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="margin-left:2px;font-size:14px;height:30px;background: rgb(110, 96, 96);border: 1px solid #503f3f;color: #fff;"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span></button>';
										
					echo'<ul class="dropdown-menu dropdown-menu-right" style="width:250px;">';
						
						echo'<li style="position:relative;">';
						
							echo '<a href="#duplicateLayer" data-toggle="dialog" data-target="#duplicateLayer">Duplicate Layer <span class="label label-warning pull-right">admin</span></a>';

							echo'<div id="duplicateLayer" title="Duplicate Layer">';
								
								echo'<form class="" style="width:250px;display:inline-block;" target="_parent" action="" id="duplicatePostForm" method="post">';
									
									echo'<input type="text" name="postTitle" value="" class="form-control input-sm required" placeholder="Template Title" style="margin:7px 0;">';
									echo'<input type="hidden" name="postAction" id="postAction" value="duplicate">';
									echo'<input type="hidden" name="postContent" value="">';
									
									wp_nonce_field( 'user_layer_nonce', 'user_layer_nonce_field' );
									
									echo'<input type="hidden" name="submitted" id="submitted" value="true">';
									
									echo'<div class="ui-helper-clearfix ui-dialog-buttonset">';

										echo'<button class="btn btn-xs btn-primary pull-right" type="submit" id="duplicateBtn" style="border-radius:3px;">Duplicate</button>';
								 
									echo'</div>';
									
								echo'</form>';								
								
							echo'</div>';						
							
						echo'</li>';
						
						echo'<li style="position:relative;">';
							
							echo '<a id="updateBtn" href="#update-layer">Update Layer <span class="label label-warning pull-right">admin</span></a>';

						echo'</li>';
						
						echo'<li style="position:relative;">';
							
							echo '<a target="_blank" href="' . get_edit_post_link( $this->layer->id ) . '"> Edit Layer <span class="label label-warning pull-right">admin</span></a>';

						echo'</li>';
						
					echo'</ul>';
					
				echo'</div>';
			}
		}
		
		if( $this->user->ID > 0 ){
		
			if(!empty($this->user->layers)){ 

				echo'<div style="margin:0 2px;" class="btn-group">';
				
					echo'<button type="button" class="btn btn-sm btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Load <span class="caret"></span></button>';
					
					echo'<ul class="dropdown-menu dropdown-menu-right" style="width:250px;">';
						
							foreach($this->user->layers as $i => $layer) {
								
								echo'<li style="position:relative;">';
									
									echo '<a href="' . $_SERVER['SCRIPT_URI'] . '?uri=user-layer/' . $layer->post_name . '/'. '">' . ( $i + 1 ) . ' - ' . ucfirst($layer->post_title) . '</a>';
									echo '<a class="btn-xs btn-danger" href="' . $_SERVER['SCRIPT_URI'] . '?uri=user-layer/' . $layer->post_name . '/&postAction=delete" style="padding: 0px 5px;position: absolute;top: 11px;right: 11px;font-weight: bold;">x</a>';
								
								echo'</li>';						
							}
					echo'</ul>';
					
				echo'</div>';
			}
			elseif( $this->user->plan["info"]["total_price_amount"] ==0 ){ 
				
				echo '<button type="button" class="btn btn-sm btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-lock" aria-hidden="true" data-toggle="popover" data-placement="bottom" title="Pro users only" data-content="You need a paid plan ' . PHP_EOL . 'to unlock this action"></span> Load <span class="caret"></span></button>';
			}
		}
		
		echo'<div style="margin:0 2px;" class="btn-group">';
		
			echo'<button type="button" style="margin:0 2px;" class="btn btn-sm btn-info"  data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">My Profile <span class="caret"></span></button>';
								
			echo'<ul class="dropdown-menu dropdown-menu-right" style="width:250px;">';
				
				echo'<li style="position:relative;">';
					
					echo '<a target="_blank" href="'. $this->urls->editor .'?pr='.$this->user->ID . '"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> View Profile</a>';

				echo'</li>';					
				
				echo'<li style="position:relative;">';
					
					echo '<a href="'. $this->urls->editor .'?my-profile"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> Edit Settings</a>';

				echo'</li>';				
				
				echo'<li style="position:relative;">';
					
					echo '<a href="'. wp_logout_url( $_SERVER['SCRIPT_URI'] ) .'"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> Logout</a>';

				echo'</li>';	
				
			echo'</ul>';
			
		echo'</div>';		
		
		?>

	</div>
	
</div>

<?php 

if( $this->user->plan["info"]["total_price_amount"] == 0 ){ 

	echo'<div class="row" style="background-color: #65c5e8;font-size: 18px;color: #fff;padding: 20px;">';
		
		echo'<div class="col-xs-1 text-right">';
		
			echo'<span style="font-size:40px;" class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> ';
		
		echo'</div>';
		
		echo'<div class="col-xs-9">';

			echo'You are using a Demo version of ' . strtoupper(get_bloginfo( 'name' )) . '. Many features are missing such as: </br>';
			echo'Save & Load templates, Generate Meme images, Insert images from the Media Library, Copy CSS...';
		
		echo'</div>';
		
		echo'<div class="col-xs-2 text-right">';
		
			echo'<a class="btn btn-success btn-lg" href="' . $this->urls->plans . '"><span class="glyphicon glyphicon-hand-right" aria-hidden="true"></span> Upgrade now</a>';
		
		echo'</div>';
		
	echo'</div>';

} 
?>