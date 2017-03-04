<div class="loadingIframe" style="width: 100%;position: relative;background-position: 50% center;background-repeat: no-repeat;background-image:url('<?php echo $this->server->url; ?>/c/p/live-template-editor-server/assets/loader.gif');height:64px;"></div>

<iframe id="editorIframe" src="<?php echo $this->urls->editor . '?uri=' . $this->layer->uri . '&lk=' . md5( 'layer' . $this->layer->uri . $this->_time ) . '&_=' . $this->_time; ?>" style="margin-top: -65px;position: relative;width: 100%;top: 0;bottom: 0;border:0;height: 1200px;overflow: hidden;"></iframe>

<script>

	;(function($){		
		
		$(document).ready(function(){

			// dialog 

			$('[data-toggle="dialog"]').each(function(e){
				
				var id = $(this).data('target');
				
				$(id).dialog({autoOpen: false});
				
				$(this).on('click',function(e){
					
					$(id).dialog('open');
				});
			});
		});
		
	})(jQuery);

</script>