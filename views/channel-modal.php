<div class="modal-backdrop in"></div>
<div class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="channelModal" style="display:block;">
	
	<div class="modal-dialog modal-lg" role="document">
		
		<div class="modal-content">
		
			<div class="modal-header">

				<h4 class="modal-title" id="channelModal">Welcome <?php echo $this->user->display_name; ?>!</h4>
			
			</div>
		  
			<div class="modal-body" style="height:350px;">
				
				<form target="_parent" action="<?php ?>" method="POST" style="width:300px;">
					
					<label>How did you find us?</label>
					
					<div class="input-group">
						
						<?php 
						
							wp_dropdown_categories(array(
							
								'show_option_none' => 'Select a channel',
								'taxonomy'     => 'marketing-channel',
								'name'    	   => 'marketing-channel',
								'show_count'   => false,
								'hierarchical' => true,
								'selected'     => '',
								'class'        => 'form-control',
								'echo'		   => true,
								'hide_empty'   => false
							));							
						?>
						
						<?php wp_nonce_field( 'marketing_channel_nonce', 'marketing_channel_nonce_field' ); ?>
						
						<input type="hidden" name="submitted" id="submitted" value="true">
						
						<span class="input-group-btn">
							
							<button class="btn btn-primary" type="button" id="submitBtn">Submit</button>
							
						</span>
						
					</div>
				</form>

			</div>

		</div>
		
	</div>
	
</div>

<script>
		
	;(function($){

		$(document).ready(function(){
			
			$('#submitBtn').on('click', function (e) {
				
				this.closest( "form" ).submit();
			});
		});
		
	})(jQuery);		
		
</script>