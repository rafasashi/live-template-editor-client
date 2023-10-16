;(function($){
	
	$(document).ready(function() {
		
		function set_carousel(id,itemSelector,axis,callback){
			
			if (!$('#'+id).is(':visible')) {
				
				return;
			}
			
			var viewHeight = $('#'+id).css('height','').outerHeight(true);					
			var viewWidth  = $('#'+id).css('width','').outerWidth(true);
			 
			$('#'+id).css({
			
				'position'	: 'relative',
				'overflow'	: 'hidden',
				'height'	: viewHeight
			});
			
			$('#'+id+' .carousel-wrapper').each(function() {
				
				$(this).contents().unwrap(); // unwrap the contents of each row
			});
			
			$('#'+id+' .carousel-row').each(function() {
				
				$(this).contents().unwrap(); // unwrap the contents of each row
			});
 
			var rowWidth = 0;
			
			if( axis == 'x' ){
			
				var wrap = '<div class="carousel-row" style="flex-wrap:wrap;display:flex;width:'+viewWidth+'px;height:'+viewHeight+'px;float:left;"></div>'; // create a new row
			}
			else{
				
				var wrap = '<div class="carousel-row" style="flex-wrap:wrap;display:flex;width:'+viewWidth+'px;float:left;"></div>';
			}
			
			var $row = $(wrap); 
			
			$('#'+id+' '+itemSelector).each(function() {
				
				rowWidth += $(this).outerWidth(true); // add the width of each item
				
				if (rowWidth > viewWidth) { // if the total width is greater than or equal to the screen width
					
					$('#'+id+'').append($row); // append the current row to the body
					
					$row = $(wrap); 
					
					rowWidth = $(this).outerWidth(true); // set the total width to the width of the current item
				}
				
				$row.append($(this)); // append the current item to the current row
			  
			});

			$('#'+id+'').append($row); // append the last row to the body

			var carouselWidth = $('#'+id+' .carousel-row').length * viewWidth;
			var carouselHeight = $('#'+id+' .carousel-row').length * viewHeight;
			
			$('#'+id+' .carousel-row').wrapAll('<div class="carousel-wrapper ui-draggable" style="position:absolute;transition:0.3s;"></div>'); 
			
			if( axis == 'x' ){
				
				$('#'+id+' .carousel-wrapper').css({
					
					'width' 	: carouselWidth + 'px',
					'height' 	: viewHeight + 'px',
				});
			}
			else{
				
				$('#'+id+' .carousel-wrapper').css({
					
					'width' 	: viewWidth + 'px',
					'height' 	: carouselHeight + 'px',
				});
			}
			 
			var numItems = $('#'+id+' '+itemSelector).length;
			 
			var itemWidth = $('#'+id+' '+itemSelector).first().outerWidth(true);
			
			var itemHeight = $('#'+id+' '+itemSelector).first().outerHeight(true);

			// Set the initial position of the carousel
			
			if( axis == 'x' ){
			
				var maxPos = ( itemWidth * numItems ) - viewWidth;
			
				$('#'+id+' .carousel-wrapper').css('left',0);
			}
			else{
				
				var maxPos = ( itemHeight * numItems ) - viewHeight;
				
				$('#'+id+' .carousel-wrapper').css('top',0);
			}
			
			// On click of the 'next' button, slide the carousel to the left
			
			$('.carousel-next').off().on('click', function() {
				
				var carouselPosition = parseInt($('#'+id+' .carousel-wrapper').css('left'), 10);
				
				if( carouselPosition <= -maxPos + itemWidth ) {
					
					carouselPosition = -maxPos;
				}
				else{
					
					carouselPosition -= itemWidth;
				}
				
				$('#'+id+' .carousel-wrapper').css('left', carouselPosition + 'px');
			});

			// On click of the 'prev' button, slide the carousel to the right
			
			$('.carousel-prev').off().on('click', function() {

				// Check if the carousel is already at the beginning
				
				var carouselPosition = parseInt($('#'+id+' .carousel-wrapper').css('left'), 10);
				
				if( carouselPosition >= 0 ) {
					
					carouselPosition = 0;
				}
				else{
				
					carouselPosition += itemWidth;
				}
				
				$('#'+id+' .carousel-wrapper').css('left', carouselPosition + 'px');
			});
			
			// store the targeted link
			
			var linkTarget = false;
			
			$('#'+id+' .carousel-wrapper').on("mousedown", function(e) {

				if( $(e.target).is("a") ) {
					
					linkTarget = $(e.target);
				}
				else if( $(e.target).closest('a').length ){
					
					linkTarget = $(e.target).closest('a');
				}
				else{
					
					linkTarget = false;
				}
			});
			
			// store scrolling info 
			
			var firstY = null;      
			var lastY = null;
			var currentY = null;
			var vertScroll = false;
			var initAdjustment = 0;

			// record the initial position of the cursor on start of the touch
			
			$('#'+id+' .carousel-wrapper').on("touchstart", function(event) {
				
				lastY = currentY = firstY = event.originalEvent.touches[0].pageY;
			});

			// fires whenever the cursor moves
			
			$('#'+id+' .carousel-wrapper').on("touchmove", function(event) {
				
				currentY = event.originalEvent.touches[0].pageY;
				
				var adjustment = lastY-currentY;

				// Mimic native vertical scrolling where scrolling only starts after the
				// cursor has moved up or down from its original position by ~30 pixels.
				
				if (vertScroll == false && Math.abs(currentY-firstY) > 30) {
					
					vertScroll = true;
					
					initAdjustment = currentY-firstY;
				}

				// only apply the adjustment if the user has met the threshold for vertical scrolling
				
				if (vertScroll == true) {
					
					window.scrollBy(0,adjustment + initAdjustment);
					lastY = currentY + adjustment;
				}

			});

			// when the user lifts their finger, they will again need to meet the 
			// threshold before vertical scrolling starts.
			
			$('#'+id+' .carousel-wrapper').on("touchend", function(event) {
				
				vertScroll = false;
			});
			
			// Add draggable functionality to the carousel wrapper
			
			$('#' + id + ' .carousel-wrapper').draggable({
				
				axis : axis,
				start: function(event, ui) {
					
					// disable css transition
				  
					$(this).css("transition", "");			
				},
				drag: function(event, ui) {

					var elastic = 50;
					
					if( axis == 'x' ){
						
						var position = ui.position.left;
					}
					else{
						
						var position = ui.position.top;
					}

					if( position > elastic ){
						
						// limit left drag
						
						position = elastic;
					}
					else{ 
						
						// limit right drag

						if( position < -maxPos ){
						
							position = -maxPos - elastic;
						}
					}
				},
				stop: function(event, ui) {

					// calculate the distance between start and end positions
					
					if( axis == 'x' ){
					
						var position = ui.position.left;
						var originalPosition = ui.originalPosition.left;
						var deltaY = Math.abs(currentY-firstY);
						var itemLen = itemWidth;
					}
					else{
						
						var position = ui.position.top;
						var originalPosition = ui.originalPosition.top;
						var deltaY = 0;
						var itemLen = itemHeight;
					}
					
					var distance = position - originalPosition;
										
					// check if it's a link click or a drag
					
					var isClick = false;

					if( linkTarget.length > 0 ){
	
						// set a threshold to differentiate between a click and a drag
						
						var threshold = 5;
						
						if( Math.abs(distance) < threshold ) {
							
							isClick = true;
						}
					}
					
					if( isClick === true && deltaY < 2 ) {
					  
						// disable dragging
						$(this).draggable("option", "disabled", true);
					  
						// get the link href attribute
						var linkHref = linkTarget.attr("href");

						// navigate to the location
						window.location.href = linkHref;
						
						return false;
					}
					else {

						// enable css transition
						
						$(this).css('transition','0.3s');
						
						// check the dragging direction and adjust the newPosition accordingly
						
						if( deltaY > 30 ){
							
							// determine the current item
							
							var itemIndex = Math.round(Math.abs(position) / itemLen);
						}
						else if( distance > 0) {
							
							// determine the closest item to the current left position
							
							var itemIndex = Math.floor(Math.abs(position) / itemLen);
						} 
						else {
							
							// determine the closest item to the current left position
							
							var itemIndex = Math.round(Math.abs(position) / itemLen) + 1;				
						}
						
						// get the left position of the closest item
							
						var newPosition = -itemIndex * itemLen;
							
						if( newPosition < -maxPos ){
							
							newPosition = -maxPos;
						}
							
						// animate the wrapper to the closest item
						
						if( axis == 'x' ){
						
							$(this).css('left', newPosition + 'px');
						}
						else{
							
							$(this).css('top', newPosition + 'px');
						}
					}
				}
			});
			
			if( typeof callback === 'function' ){
				
				callback(id,itemSelector,axis);
			}
		}
		
		function set_collapsed(id) {
			
			var $elem = $('#' + id);
			
			if ($elem.hasClass('collapsed')) {
				
				var maxHeight = parseInt($elem.css('max-height'));

				// Temporarily remove the max-height property to get the actual height
				
				$elem.removeClass('collapsed');
				
				var actualHeight = $elem[0].getBoundingClientRect().height;
				
				$elem.addClass('collapsed');

				if (actualHeight > maxHeight) {
					
					if ($('.toggle_collapsed', $('#' + id).parent()).length == 0) {
						
						var moreText = 'Learn more';
						var lessText = 'Less';

						$('<a class="toggle_collapsed d-block w-100 text-center cursor-pointer font-weight-bold" style="margin-top:-20px;position:inherit;">' + moreText + '</a>').insertAfter('#' + id);

						$('.toggle_collapsed', $('#' + id).parent()).on('click', function (e) {
							
							$btn = $(this);
							
							if ($elem.hasClass('collapsed')) {
								
								$elem.css('height', '').removeClass('collapsed');
								
								actualHeight = $elem[0].getBoundingClientRect().height;
								
								$elem.css('height', maxHeight);
								
								$elem.animate({'height': actualHeight},300, function(){
									
									$btn.text(lessText);
								});
							}
							else {
								
								$elem.animate({'height': maxHeight},300, function(){
									
									$elem.addClass('collapsed');
									
									$btn.text(moreText);
								});
							}
						});
					}
				} 
				else {
				  
					$elem.removeClass('collapsed');
				}
			}
		}
		
		function set_preview_image($media){
					
			$('#product_gallery .media.active').removeClass('active');
					
			$media.addClass('active');
			
			var id = 'product_preview';
			
			var itemSelector = '.product-image';
			
			var itemIndex = $media.data('index');
			
			var itemWidth = $('#'+id+' '+itemSelector).first().outerWidth(true);
			
			// get the left position of the closest item
				
			var newPosition = -itemIndex * itemWidth;
				
			// animate the wrapper to the closest item
			
			$('#'+id+' .carousel-wrapper').css('transition','0.3s').css('left', newPosition + 'px');
		}
		
		function set_store(){
			
			set_collapsed('product_description');
			
			set_carousel('product_preview','.product-image','x',function(id,itemSelector,axis){

				if( $('#product_gallery .media').length > 1 ){
					
					if( $('#'+id+' .gallery-prev').length === 0 ){
					
						$('#'+id).append('<div class="gallery-prev d-none d-md-block"><i role="button" class="fa fa-chevron-left"></i>');
						
						$('#'+id+' .gallery-prev').on('click',function(){
							
							var $previous = $('#product_gallery .media.active').prev();
							
							if ($previous.length === 0) {
								
								$previous = $('#product_gallery').find('.media').last();
							}
							
							set_preview_image($previous);
						});
					}
					
					if( $('#'+id+' .gallery-next').length === 0 ){
					
						$('#'+id+'').append('<div class="gallery-next d-none d-md-block"><i role="button" class="fa fa-chevron-right"></i>');
						
						$('#'+id+' .gallery-next').on('click',function(){
							
							var $next = $('#product_gallery .media.active').next();
							
							if( $next.length === 0 ){
								
								$next = $('#product_gallery').find('.media').first();
							}
							
							set_preview_image($next);
						});
					}
					
					$('#product_gallery .media').on('click',function(e){

						set_preview_image($(this));	
					});
				}
			});
			
			/*
			set_carousel('product_gallery','.media','y',function(id,itemSelector,axis){

				if( $('#product_gallery .media').length > 1 ){
					
					if( $('#product_preview .gallery-prev').length === 0 ){
					
						$('#product_preview').append('<div class="gallery-prev d-none d-md-block"><i role="button" class="fa fa-chevron-left"></i>');
						
						$('#product_preview .gallery-prev').on('click',function(){
							
							var $previous = $('#product_gallery .carousel-row .media.active').parent().prev('.carousel-row').find('.media').last();
							
							if ($previous.length === 0) {
								
								$previous = $('#product_gallery .carousel-row').last().find('.media');
							}
							
							set_preview_image($previous);
						});
					}
					
					if( $('#product_preview .gallery-next').length === 0 ){
					
						$('#product_preview').append('<div class="gallery-next d-none d-md-block"><i role="button" class="fa fa-chevron-right"></i>');
						
						$('#product_preview .gallery-next').on('click',function(){
							
							var $next = $('#product_gallery .carousel-row .media.active').parent().next('.carousel-row').find('.media').first();
							
							if( $next.length === 0 ){
								
								$next = $('#product_gallery .carousel-row .media').first();
							}
							
							set_preview_image($next);
						});
					}
					
					$('#product_gallery .media').on('click',function(e){

						set_preview_image($(this));	
					});
				}
			});
			*/
			
			set_carousel('related_products','[class^="col-"]','x');
		}
		
		$(window).resize(function() {

			set_store();
		});

		set_store();
	});
	
})(jQuery);