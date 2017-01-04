;(function($){

	$(document).ready(function(){
		
		//modal always on top
		
		$('.modal').appendTo("body");
		
		//load modal iframes
		
		$('.modal').on('shown.bs.modal', function (e) {
			
			var modalIframe = $(this).find('iframe');
			
			if(modalIframe.length > 0){
				
				var iframeSrc = modalIframe.attr("src");
				
				if(typeof iframeSrc == typeof undefined || iframeSrc == false){
					
					iframeSrc = modalIframe.attr("data-src");
					
					if(typeof iframeSrc !== typeof undefined && iframeSrc !== false){
					
						//console.log(iframeSrc);
					
						modalIframe.attr("src", iframeSrc);
					}
				}				
			}
		});
		
		// lazyload images on scroll
		$("img.lazy").lazyload();
	
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

		// set tooltips & popovers
			
		$('[data-toggle="tooltip"]').tooltip();
		$('[data-toggle="popover"]').popover();
		
		// show active tab

		if(location.hash) {
			
			var tabs = location.hash.substring(1).split('_');
			
			$.each(tabs,function(n){

				$('a[href=#' + tabs[n] + ']').tab('show');
			});			
			
			$('a[href=' + location.hash + ']').tab('show');
		}
		
		// set hash on click without jumb
		
		$(document.body).on("click", "a[data-toggle]", function(e) {
			
			e.preventDefault();
			
			if(history.pushState) {
				
				history.pushState(null, null, this.getAttribute("href"));
			}
			else {
				
				location.hash = this.getAttribute("href");
			}

			var tabs = location.hash.substring(1).split('_');
			
			$.each(tabs,function(n){
				
				$('a[href=#' + tabs[n] + ']').tab('show');
			});
			
			$('a[href=' + location.hash + ']').tab('show');
			
			return false;
		});
		
		// store active tabs in localStorage
		
		$('a[data-toggle="tab"]').on("shown.bs.tab", function (e) {
			
			var id = $(e.target).attr("href");
			
			localStorage.setItem('selectedTab', id);
		});
		
		// get active tabs from localStorage
		
		var selectedTab = localStorage.getItem('selectedTab');
		
		// restore active tabs
		
		if (selectedTab != null) {
			
			var tabs = selectedTab.substring(1).split('_');
			
			$.each(tabs,function(n){
				
				$('a[href=#' + tabs[n] + ']').tab('show');
			});
			
			$('a[href=' + selectedTab + ']').tab('show');
		}
	});
	
	// set hash on popstate

	$(window).on('popstate', function() {
		
		var anchor = location.hash || $("a[data-toggle=tab]").first().attr("href");
		
		var tabs = anchor.substring(1).split('_');
					
		$.each(tabs,function(n){
			
			$('a[href=#' + tabs[n] + ']').tab('show');
		});
		
		$('a[href=' + anchor + ']').tab('show');
	});
	
})(jQuery);