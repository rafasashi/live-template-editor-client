<div class="row" style="background: #eee;padding: 10px 0;margin:0;position: relative;">

	<div class="col-xs-6 col-sm-4" style="z-index:0;">				
		
		<div class="pull-left">

			<a class="btn btn-sm btn-warning" href="<?php echo $_SERVER['SCRIPT_URI'] ?>">Gallery</a>
		
		</div>
		
		<div class="pull-left">

			<a style="margin-left: 6px;" class="btn btn-sm btn-primary" href="<?php echo $_SERVER['SCRIPT_URI'].'?media' ?>">Media</a>
		
		</div>
		
		<div class="pull-left">

			<a style="margin-left: 6px;background-color:#bd3d72;border: 1px solid #9c4167;" class="btn btn-sm btn-primary" href="<?php echo $_SERVER['SCRIPT_URI'].'?app' ?>">Apps</a>
		
		</div>	
	
	</div>
	
	<div class="col-xs-6 col-sm-8 text-right">

		<?php if( $this->layer->type == 'default-layer' ){ ?>
		
			<form class="pull-left" style="width:250px;display:inline-block;" target="_parent" action="<?php echo $_SERVER['SCRIPT_URI'] . '?uri=default-layer/' . $this->layer->slug . '/'; ?>" id="savePostForm" method="POST">
				
				<div class="input-group">
					
					<input type="text" name="postTitle" id="postTitle" value="" class="form-control required" placeholder="Layer Title" <?php if( !isset($this->user->plan['info']['total_storage']) || $this->user->layerCount + 1 > $this->user->plan['info']['total_storage']['templates'] ) echo 'disabled'; ?>>
					<input type="hidden" name="postContent" id="postContent" value="">
					<input type="hidden" name="postAction" id="postAction" value="save">
					
					<?php wp_nonce_field( 'user_layer_nonce', 'user_layer_nonce_field' ); ?>
					
					<input type="hidden" name="_wp_http_referer" value="<?php echo '/editor/?uri=default-layer/' . $this->layer->slug . '/&lk=' . md5( 'layer' . $this->layer->uri . $this->_time ) . '&_=' .  $this->_time; ?>">
					<input type="hidden" name="submitted" id="submitted" value="true">
					
					<span class="input-group-btn">
						
						<?php if( $this->user->plan["info"]["total_price_amount"]>0 ){ ?>
						
						<button class="btn btn-sm btn-primary" type="button" id="saveBtn" style="height:34px;border-radius: 0 5px 5px 0;">Save</button>
					
						<?php }else{ ?>
						
						<button class="btn btn-sm btn-primary" type="button" data-toggle="popover" data-placement="bottom" title="Pro users only" data-content="You need a paid plan <?php echo PHP_EOL; ?> to unlock this action"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span>  Save</button>
						
						<?php } ?>
					
					</span>
					
				</div>
			</form>
		
		<?php 
		}
		elseif( $this->user->has_layer && $this->layer->type == 'user-layer'){
			
			?>
			
			<form style="display:inline-block;" target="_parent" action="<?php echo $_SERVER['SCRIPT_URI'] . '?uri=user-layer/' . $this->layer->slug . '/'; ?>" id="savePostForm" method="POST">
				
				<input type="hidden" name="postTitle" id="postTitle" value="<?php echo $this->user->layer->post_title;  ?>" class="form-control required" placeholder="Layer Title">
				<input type="hidden" name="postContent" id="postContent" value="">
				<input type="hidden" name="postAction" id="postAction" value="save">
				
				<?php wp_nonce_field( 'user_layer_nonce', 'user_layer_nonce_field' ); ?>
				
				<input type="hidden" name="_wp_http_referer" value="<?php echo '/editor/?uri=user-layer/' . $this->layer->slug . '/&lk=' . md5( 'layer' . $this->layer->uri . $this->_time ) . '&_=' .  $this->_time; ?>">
				<input type="hidden" name="submitted" id="submitted" value="true">
				
				<button style="background-color: #3F51B5;border: 1px solid #5869ca;" class="btn btn-sm btn-primary" type="button" id="saveBtn">Save</button>
				
			</form>			
			
			<?php
			
			echo '<a class="btn btn-sm btn-danger" href="' . $_SERVER['SCRIPT_URI'] . '?uri=user-layer/' . $this->layer->slug . '/&postAction=delete">Delete</a>';
		}
		
		if( $this->layer->type != '' ){
			
			echo '<a target="_blank" class="btn btn-sm btn-default" href="' . get_post_permalink( $this->layer->id ) . '" style="margin-left: 4px;border-color: #9c6433;color: #fff;background-color: rgb(189, 120, 61);">View</a>';
		
			if( $this->user->is_admin ){
			
				echo'<div style="margin:0 2px;" class="btn-group">';
				
					echo'<button type="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="margin-left:2px;font-size:14px;height:30px;background: rgb(110, 96, 96);border: 1px solid #503f3f;color: #fff;"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span></button>';
										
					echo'<ul class="dropdown-menu dropdown-menu-right" style="width:250px;">';
						
						echo'<li style="position:relative;">';
							
							echo '<a target="_blank" href="' . get_edit_post_link( $this->layer->id ) . '"><span class="label label-warning">admin</span> Edit Layer</a>';

						echo'</li>';	
						
					echo'</ul>';
					
				echo'</div>';
			}
		}
		
		if( $this->user->ID > 0 ){
		
			$q = get_posts(array(
			
				'author'      => $this->user->ID,
				'post_type'   => 'user-layer',
				'post_status' => 'publish',
				'numberposts' => -1
			));
			
			//var_dump( $q );exit;
			
			if(!empty($q)){ 

				echo'<div style="margin:0 2px;" class="btn-group">';
				
					echo'<button type="button" class="btn btn-sm btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Load <span class="caret"></span></button>';
					
					echo'<ul class="dropdown-menu dropdown-menu-right" style="width:250px;">';
						
							foreach($q as $i => $layer) {
								
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
		
		?>
		
		<a style="margin:0 2px;" class="btn btn-sm btn-info" href="<?php echo wp_logout_url( $_SERVER['SCRIPT_URI'] ); ?>">Logout</a>				
	
	</div>
	
</div>

<?php 

if( $this->user->plan["info"]["total_price_amount"] == 0 ){ 

	echo'<div class="row" style="background-color: #65c5e8;font-size: 18px;color: #fff;padding: 20px;">';
		
		echo'<div class="col-xs-1 text-right">';
		
			echo'<span style="font-size:40px;" class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> ';
		
		echo'</div>';
		
		echo'<div class="col-xs-9">';

			echo'You are using a Demo version of ' . strtoupper(get_bloginfo( 'name' )) . '. Many features are missing such as: </br>'.PHP_EOL;
			echo'Save & Load templates, Generate Meme images, Insert images from the Media Library, Copy CSS...';
		
		echo'</div>';
		
		echo'<div class="col-xs-2 text-right">';
		
			echo'<a class="btn btn-success btn-lg" href="' . $this->urls->plans . '"><span class="glyphicon glyphicon-hand-right" aria-hidden="true"></span> Upgrade now</a>';
		
		echo'</div>';
		
	echo'</div>';

} 
?>