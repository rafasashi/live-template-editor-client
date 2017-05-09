<?php
	
	global $post;
	
	echo '<nav class="row" style="box-shadow:inset 0 -1px 10px -6px rgba(0,0,0,0.75);background: rgb(236, 236, 236);padding:0;height:50px;margin: 0;border-bottom: 1px solid #ddd;position: relative;">';
		
		echo '<div id="header_logo">';
			
			echo '<a target="_blank" href="'. $this->urls->editor .'">';
				
				echo '<img src="' . ( $this->settings->options->logo_url != '' ? $this->settings->options->logo_url : $this->assets_url . 'images/home.png' )  . '">';
			
			echo '</a>';
			
		echo '</div>';
		
		echo '<div id="main-menu">';
			
			echo '<div class="col-xs-6 text-left">';
			
				echo '<span class="label label-default" style="font-size: 15px;margin: 3px  0;display: inline-block;">Embedded</span>';
			
			echo '</div>';
			
			echo '<div class="col-xs-6 text-right">';

				if( !empty($this->layer->embedded) ){
			
					echo '<ul>';

						if( $this->layer->id > 0 ){				
							
							if($this->layer->type == 'cb-default-layer'){
								
								if( !isset($this->user->plan['info']['total_storage']) || $this->user->layerCount + 1 > $this->user->plan['info']['total_storage']['templates']){
									
									echo '<li class="pull-left">';
										
										echo '<span class="alert alert-danger" style="padding:2px 5px;">';
										
											echo 'Your storage space is full...';
											
										echo '</span>';
									
									echo '</li>';									
								}
								elseif( !empty($this->layer->embedded['title']) ){
									
									echo '<li>';

										echo'<form style="display:inline-block;" target="_parent" action="'. $this->urls->editor . '?uri=' . $this->layer->id . '" id="savePostForm" method="post">';
											
											echo'<div class="input-group">';					
												
												echo'<input type="hidden" name="postTitle" id="postTitle" value="'.$this->layer->embedded['title'].'">';
												echo'<input type="hidden" name="postContent" id="postContent" value="">';
												echo'<input type="hidden" name="postCss" id="postCss" value="">';
												echo'<input type="hidden" name="postJs" id="postJs" value="">';
												echo'<input type="hidden" name="postEmbedded" id="postEmbedded" value="' . $this->layer->embedded['url'] . '">';

												wp_nonce_field( 'user_layer_nonce', 'user_layer_nonce_field' );

												echo'<input type="hidden" name="submitted" id="submitted" value="true">';
												
												echo'<input type="hidden" name="postAction" id="postAction" value="save">';
														
												echo'<button class="btn btn-sm btn-primary" type="button" id="saveBtn">Save</button>';
												
											echo'</div>';
										echo'</form>';								
									
									echo '</li>';
								}
								else{

									echo '<li class="pull-left">';

										echo'<form style="width:250px;display:inline-block;" target="_parent" action="'. $this->urls->editor . '?uri=' . $this->layer->id . '" id="savePostForm" method="post">';
											
											echo'<div class="input-group">';					
												
												echo'<input type="text" name="postTitle" id="postTitle" value="" class="form-control input-sm required" placeholder="Template Title" ' . ( ( !isset($this->user->plan['info']['total_storage']) || $this->user->layerCount + 1 > $this->user->plan['info']['total_storage']['templates']) ? 'disabled' : '' ) .'>';
												echo'<input type="hidden" name="postContent" id="postContent" value="">';
												echo'<input type="hidden" name="postCss" id="postCss" value="">';
												echo'<input type="hidden" name="postJs" id="postJs" value="">';
												echo'<input type="hidden" name="postEmbedded" id="postEmbedded" value="' . $this->layer->embedded['url'] . '">';

												wp_nonce_field( 'user_layer_nonce', 'user_layer_nonce_field' );

												echo'<input type="hidden" name="submitted" id="submitted" value="true">';
												
												echo'<span class="input-group-btn">';
												
													if( $this->user->plan["info"]["total_price_amount"]>0 ){
														
														echo'<input type="hidden" name="postAction" id="postAction" value="save">';
														
														echo'<button class="btn btn-sm btn-primary" type="button" id="saveBtn" style="border-radius: 0 3px 3px 0;">Save</button>';
													}
													else{
													
														echo'<button class="btn btn-sm btn-primary" type="button" data-toggle="popover" data-placement="bottom" title="Pro users only" data-content="You need a paid plan <?php echo PHP_EOL; ?> to unlock this action"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span>  Save</button>';
													}
												
												echo'</span>';
												
											echo'</div>';
										echo'</form>';								
									
									echo '</li>';
								}

								echo '<li>';
								
									echo '<a target="_top" href="'.$this->layer->embedded['scheme'].'://'.$this->layer->embedded['host'].$this->layer->embedded['path'].'wp-admin/post.php?post='.$this->layer->embedded['p'].'&action=edit" class="btn btn-sm btn-default" style="background: rgb(110, 96, 96);border: 1px solid #503f3f;color: #fff;">';
								
										echo 'back';
									
									echo '</a>';
								
								echo '</li>';							
								
							}
							else{								
							
								if( !empty($this->user->layer->post_title) ){
								
									$post_title = $this->user->layer->post_title;

									echo '<li>';
										
										echo'<form style="display:inline-block;" action="' . $this->urls->editor . '?uri=' . $this->layer->id . '" id="savePostForm" method="post">';
											
											echo'<input type="hidden" name="postTitle" id="postTitle" value="' . $post_title . '" class="form-control required" placeholder="Layer Title">';
											echo'<input type="hidden" name="postContent" id="postContent" value="">';
											echo'<input type="hidden" name="postCss" id="postCss" value="">';
											echo'<input type="hidden" name="postJs" id="postJs" value="">';
											echo'<input type="hidden" name="postAction" id="postAction" value="save">';
											echo'<input type="hidden" name="postEmbedded" id="postEmbedded" value="' . $this->layer->embedded['url'] . '">';
											 
											wp_nonce_field( 'user_layer_nonce', 'user_layer_nonce_field' );
											
											echo'<input type="hidden" name="submitted" id="submitted" value="true">';
											
											echo'<button class="btn btn-sm btn-primary" type="button" id="saveBtn">Save</button>';
											
										echo'</form>';
										
									echo '</li>';
									
									/*
									echo '<li>';
									
										echo '<a class="btn btn-sm btn-danger" href="' . $this->urls->editor . '?uri=' . $this->layer->id . '&postAction=delete">Delete</a>';
									
									echo '</li>';
									*/
								}
								
								echo '<li>';
								
									echo '<a target="_blank" href="' . $this->layer->embedded['url'] . '" class="btn btn-sm btn-success" style="border-color: #9c6433;color: #fff;background-color: rgb(189, 120, 61);">';
								
										echo 'View';
									
									echo '</a>';
								
								echo '</li>';								
									
								echo '<li>';
								
									echo '<a target="_top" href="'.$this->layer->embedded['scheme'].'://'.$this->layer->embedded['host'].$this->layer->embedded['path'].'wp-admin/post.php?post='.$this->layer->embedded['p'].'&action=edit" class="btn btn-sm btn-info" style="background: rgb(110, 96, 96);border: 1px solid #503f3f;color: #fff;">';
								
										echo 'Edit';
									
									echo '</a>';
								
								echo '</li>';						
							}
						}
						else{
							
							echo '<li>';
							
								echo '<a target="_top" href="'.$this->layer->embedded['scheme'].'://'.$this->layer->embedded['host'].$this->layer->embedded['path'].'wp-admin/post.php?post='.$this->layer->embedded['p'].'&action=edit" class="btn btn-sm btn-default" style="background: rgb(110, 96, 96);border: 1px solid #503f3f;color: #fff;">';
							
									echo 'back';
								
								echo '</a>';
							
							echo '</li>';							
						}
				
					echo '</ul>';
				}
			
			echo '</div>';
			
		echo '</div>';
		
	echo'</nav>';		