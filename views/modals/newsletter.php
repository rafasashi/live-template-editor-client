<div id="newsletterPreferencesBackdrop" class="modal-backdrop in"></div>

<div id="newsletterPreferences" class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="channelModal" style="display:block;">
	
	<div class="modal-dialog modal-lg" role="document">
		
		<div class="modal-content">
		
			<div class="modal-header">

				<h4 class="modal-title" id="channelModal">Newsletter Preferences</h4>
			
			</div>
		  
			<div class="modal-body" style="height:350px;">
				
				<form target="_parent" action="<?php echo $this->urls->current; ?>" method="post" style="width:300px;">
					
					<label>Would you like to receive news from us and stay informed?</label>
					
					<div class="input-group">
						
						<?php 
						
							$this->admin->display_field( array(
								
								'id' 		=> 'can_spam',
								'type' 		=> 'radio',
								'options' 	=> array('true'=>'YES','false'=>'NO'),
							));							
						?>
						
						<?php wp_nonce_field( 'can_spam_nonce', 'can_spam_nonce_field' ); ?>
						
						<input type="hidden" name="submitted" id="submitted" value="true">
						
						<span class="input-group-btn">
							
							<button class="btn btn-primary" type="button" id="submitNewsletterPreferences">Submit</button>
							
						</span>
						
					</div>
				</form>
				
				<div style="font-style:italic;margin:30px 0px;max-width:400px;width:100%;">
				
					You can unsubscribe easily at any time.
					
					<br>
					
					Just click on the link <b>"Unsubscribe from the Newsletter"</b> located in the footer of every email we send.
				
				</div>

			</div>

		</div>
		
	</div>
	
</div>

<script>
		
	;(function($){

		$(document).ready(function(){
			
			$('#submitNewsletterPreferences').on('click', function (e) {
				
				e.preventDefault();
				
				$form = $(this).closest("form");

				$.ajax({
					
					type 		: $form.attr('method'),
					url  		: $form.attr('action'),
					data		: $form.serialize(),
					beforeSend	: function() {

						$('#newsletterPreferences').css('display','none');
						$('#newsletterPreferencesBackdrop').css('display','none');
					},
					success: function(data) {
						
					}
				});
			});
		});
		
	})(jQuery);		
		
</script>