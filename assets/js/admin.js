;(function($){
	
	$(document).ready(function(){
		
		// bootstrap + screen options compatibility
		
		$("#contextual-help-link").click(function () {
			$("#contextual-help-wrap").css("cssText", "display: block !important;");
		});
		
		$("#show-settings-link").click(function () {
			$("#screen-options-wrap").css("cssText", "display: block !important;");
		});		
		
		//input group add row

		$(".add-input-group").on('click', function(e){
			
			e.preventDefault();
			
			var clone = $(".input-group-row").eq(0).clone().removeClass('ui-state-disabled');
			
			clone.append('<a class="remove-input-group" href="#">[ x ]</a>');
			
			$(this).next(".input-group").append(clone);
			
		});
		
		$(".input-group").on('click', ".remove-input-group", function(e){

			e.preventDefault();
			$(this).closest('.input-group-row').remove();
		});	
		
		if( $( ".sortable .ui-sortable" ).length ){
		
			$( ".sortable .ui-sortable" ).sortable({
				
				placeholder	: "ui-state-highlight",
				items		: "li:not(.ui-state-disabled)"
			});
			
			$( ".sortable .ui-sortable li" ).disableSelection();
		
		}
		
		// lazyload images on scroll
		
		$("img.lazy").lazyload();
		
	
		// activate tabs 
		 
		$('a[data-toggle="tab"]').on('click', function (e) {
			
			e.preventDefault();
			
			$(this).tab('show');
		});
	
		// lazyload tab images 
		
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			
			var images = 0;
			
			$(e.target.hash).find('.lazy').each(function(){
				
				if( images < 8 ){
					
					var imageSrc = $(this).attr("data-original");
					$(this).attr("src", imageSrc).removeAttr("data-original");
					
					images++;						
				}
			});
		});
	});
	
})(jQuery);