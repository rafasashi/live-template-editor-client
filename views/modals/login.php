<?php

	echo '<div class="modal fade" id="login_first" tabindex="-1" role="dialog">'.PHP_EOL;
		
		echo '<div class="modal-dialog modal-lg" role="document" style="max-width:500px !important;">'.PHP_EOL;
			
			echo '<div class="modal-content" style="height:270px !important;">'.PHP_EOL;
				
				echo '<div class="modal-header">'.PHP_EOL;
					
					echo '<button type="button" class="close m-0 p-0 border-0 bg-transparent" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
					
					echo '<h4 class="modal-title text-left">You need to Login first</h4>'.PHP_EOL;
				
				echo '</div>'.PHP_EOL;
			  
				echo '<div class="modal-body text-center">'.PHP_EOL;

					echo '<div style="display:block;margin:30px;">';
					
						echo '<a style="display:block;width:100%;" class="btn-lg btn-success" href="'.wp_login_url( 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] ).'" target="_parent" title="Login">Login</a>';
					
					echo '</div>';
					
					echo '<div style="display:block;margin:30px;">';
					
						echo '<a style="display:block;width:100%;" class="btn-lg btn-info" href="'. wp_login_url() .'?action=register" target="_parent" title="Register">Register</a>';
					
					echo '</div>';
					
				echo '</div>'.PHP_EOL;

			echo '</div>'.PHP_EOL;
			
		echo '</div>'.PHP_EOL;
		
	echo '</div>'.PHP_EOL;