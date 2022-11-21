<div id="newsletterPreferencesBackdrop" class="collect-info-backdrop modal-backdrop in"></div>

<div id="newsletterPreferences" class="collect-info modal fade in" tabindex="-1" role="dialog" aria-labelledby="channelModal" style="display:block;">
	
	<div style="margin-top: 15vh;" class="modal-dialog modal-lg" role="document">
		
		<div class="modal-content">
		
			<div style="border-radius:0;" class="modal-header">

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
								'options' 	=> array(
								
									'true'	=>'YES',
									'false'	=>'NO',
								),
							));							
						?>
						
						<?php wp_nonce_field( 'can_spam_nonce', 'can_spam_nonce_field' ); ?>
						
						<input type="hidden" name="submitted" id="submitted" value="true">
						
						<div style="width:100%;margin-top:15px;">
							
							<button class="btn btn-primary btn-sm" type="button" id="submitNewsletterPreferences">Submit</button>
							
						</div>
						
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
