;(function($){

	$(document).ready(function(){
			
		//responsive menu
		
		if( $(".library-content .nav").length ){
		
			$(".library-content .nav").append('<li class="more dropdown" style="display:none;"><button style="padding:3px 8px;margin:8px 0px;background:#b1b1b1;" class="btn dropdown-toggle" type="button" data-toggle="dropdown"><span class="caret"></span></button><ul id="overflow" class="dropdown-menu dropdown-menu-right"></ul></li>').css('overflow','visible');
			
			window.onresize = navigationResize;
			navigationResize();

			function navigationResize() {

				$('.library-content .nav li.more').before($('.library-content .nav #overflow > li'));

				var $navItemMore = $('.library-content .nav > li.more'),
					$navItems = $('.library-content .nav > li:not(.more)'),
					navItemMoreWidth = navItemWidth = $navItemMore.width() + 50,
					windowWidth = $('.library-content .nav li.more').parent().width(),
					offset, navOverflowWidth;
				  
				$navItems.each(function() {
					  
					navItemWidth += $(this).width();
				});
				  
				navItemWidth > windowWidth ? $navItemMore.show() : $navItemMore.hide();
					
				while (navItemWidth > windowWidth) {
					
					navItemWidth -= $navItems.last().width();
					$navItems.last().prependTo('.library-content .nav #overflow');
					$navItems.splice(-1,1);
				}
				  
				navOverflowWidth = $('.library-content .nav #overflow').width();  
				offset = navItemMoreWidth - navOverflowWidth;
					
				$('.library-content .nav #overflow').css({
					'left': offset
				});
			}
		}		
					
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
		
		// set collapse

		if( $('.collapse').length  > 0 ){
		
			$('.collapse').collapse({"toggle": false});
		}
		
		// set tooltips & popovers
			
		$('[data-toggle="tooltip"]').tooltip();
		
		$('[data-toggle="popover"]').popover().on("click", function(e) {
			
			if($(this).attr('data-trigger') == 'hover'){
				
				e.preventDefault();
				
				$(location).attr('href',$(this).attr('href'));
			}
		});
		
		/*
		$('[data-toggle="popover"]').popover().on("shown.bs.popover", function(e) {
		
			e.preventDefault();
		
			var pop = $(this);
		
			$('.close-popover').on("click", function() {
				
				pop.popover('hide'); // need 2 clicks to re-open the second time...
			});
		});
		*/
		
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
		
		// show active tab

		if(location.hash) {
			
			var tabs = location.hash.substring(1).split('_');

			$.each(tabs,function(n){

				$('a[href=#' + tabs[n] + ']').tab('show');
			});			
			
			$('a[href=' + location.hash + ']').tab('show');
		}
		else{
			
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
		}

		// store active tabs in localStorage
		
		$('a[data-toggle="tab"]').on("shown.bs.tab", function (e) {
			
			var id = $(e.target).attr("href");
			
			localStorage.setItem('selectedTab', id);
		});
		
		// set dialog

		$('[data-toggle="dialog"]').each(function(e){
			
			var id 		= $(this).data('target');
			var width 	= $(this).attr('data-width') || 'auto';
			var height 	= $(this).attr('data-height') || 'auto';
			var resizable = $(this).attr('data-resizable') || false;
			
			$(id).dialog({
				
				autoOpen 	: false,
				width 		: width,
				height 		: height,
				resizable 	: resizable
			});
			
			$(this).on('click',function(e){
				
				$(id).dialog('open');
			});
		});
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