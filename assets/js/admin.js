;(function($){
	
	$(document).ready(function(){
		
		//input group add row

		var wrapper         = $("#email_series .input-group"); //Fields wrapper
		
		$("#email_series .add-input-group").on('click', function(e){
			
			e.preventDefault();
			
			var clone = $(".input-group-row").eq(0).clone();
			
			clone.append('<a class="remove-input-group" href="#">[ x ]</a>');
			
			$(wrapper).append(clone);
			
		});
		
		$(wrapper).on('click', ".remove-input-group", function(e){

			e.preventDefault();
			$(this).closest('.input-group-row').remove();
		});		
	});
	
})(jQuery);