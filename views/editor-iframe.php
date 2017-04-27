<!DOCTYPE html>

<html>

    <head>
	
	</head>
		
    <body>
	
		<?php
			
			$ref = urlencode( $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] );
			
			$ref_key = md5( 'ref' . $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] . $this->_time . $this->user->user_email );
			
			$iframe_key = md5( 'iframe' . $this->layer->key . $ref_key . $this->_time . $this->user->user_email );
			
			$server_editor_url = $this->server->url . '/editor/?uri=' . $this->layer->id . '&lk=' . $this->layer->key . '&ref=' . $ref . '&rk='. $ref_key . '&_=' . $this->_time;
			
			echo'<form id="editorIframe" action="' . $server_editor_url . '" method="post">';
				
				echo'<input type="hidden" name="ik" value="' . $iframe_key . '">';

			echo'</form>';
		?>
		
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		
		<script>
		
			;(function($){

				$(document).ready(function(){

					$('#editorIframe').submit();	
				
				});
				
			})(jQuery);			
			
		</script>
		
	</body>
	
</html>
<?php exit; ?>