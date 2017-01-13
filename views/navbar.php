<div class="row" style="background: #eee;padding: 10px 0;margin:0;position: relative;">

	<div class="col-xs-6 col-sm-4" style="z-index:0;">				
		
		<div class="pull-left">

			<a class="btn btn-warning" href="<?php echo $_SERVER['SCRIPT_URI'] ?>">Gallery</a>
		
		</div>
		
		<div class="pull-left">

			<a style="margin-left: 6px;" class="btn btn-primary" href="<?php echo $_SERVER['SCRIPT_URI'].'?media' ?>">Media</a>
		
		</div>
		
		<div class="pull-left">

			<a style="margin-left: 6px;background-color:#bd3d72;border: 1px solid #9c4167;" class="btn btn-primary" href="<?php echo $_SERVER['SCRIPT_URI'].'?app' ?>">Apps</a>
		
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
						
						<button class="btn btn-primary" type="button" id="saveBtn">Save</button>
					
						<?php }else{ ?>
						
						<button class="btn btn-primary" type="button" data-toggle="popover" data-placement="bottom" title="Pro users only" data-content="You need a paid plan <?php echo PHP_EOL; ?> to unlock this action"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span>  Save</button>
						
						<?php } ?>
					
					</span>
					
				</div>
			</form>
		
		<?php }
		elseif( $this->user->has_layer && $this->layer->type=='user-layer'){
			
			?>
			
			<form style="display:inline-block;" target="_parent" action="<?php echo $_SERVER['SCRIPT_URI'] . '?uri=user-layer/' . $this->layer->slug . '/'; ?>" id="savePostForm" method="POST">
				
				<input type="hidden" name="postTitle" id="postTitle" value="<?php echo $this->user->layer->post_title;  ?>" class="form-control required" placeholder="Layer Title">
				<input type="hidden" name="postContent" id="postContent" value="">
				<input type="hidden" name="postAction" id="postAction" value="save">
				
				<?php wp_nonce_field( 'user_layer_nonce', 'user_layer_nonce_field' ); ?>
				
				<input type="hidden" name="_wp_http_referer" value="<?php echo '/editor/?uri=user-layer/' . $this->layer->slug . '/&lk=' . md5( 'layer' . $this->layer->uri . $this->_time ) . '&_=' .  $this->_time; ?>">
				<input type="hidden" name="submitted" id="submitted" value="true">
				
				<button style="background-color: #3F51B5;border: 1px solid #5869ca;" class="btn btn-primary" type="button" id="saveBtn">Save</button>
				
			</form>			
			
			<?php
			
			echo '<a class="btn btn-danger" href="' . $_SERVER['SCRIPT_URI'] . '?uri=user-layer/' . $this->layer->slug . '/&postAction=delete">Delete</a>';
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
				
					echo'<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Load <span class="caret"></span></button>';
					
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
				
				echo '<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-lock" aria-hidden="true" data-toggle="popover" data-placement="bottom" title="Pro users only" data-content="You need a paid plan ' . PHP_EOL . 'to unlock this action"></span> Load <span class="caret"></span></button>';
			}
		}
		
		?>
		
		<a style="margin:0 2px;" class="btn btn-info" href="<?php echo wp_logout_url( $_SERVER['SCRIPT_URI'] ); ?>">Logout</a>				
	
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