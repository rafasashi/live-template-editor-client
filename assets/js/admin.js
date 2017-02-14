;(function($){
	
	$(document).ready(function(){
		
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
	});
	
})(jQuery);