;(function($){

	$(document).ready(function(){
			
		//responsive menu
		
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
		
		function set_modals(){
			
			//modal always on top 
			
			$('.modal').appendTo("body");
					
			//load modal iframes
			
			$('.modal').on('shown.bs.modal', function (e) {
				
				var $modal = $(this);
				
				var modalIframe = $modal.find('iframe');
				
				if(modalIframe.length > 0){
					
					var iframeSrc = modalIframe.attr("src");
					
					if(typeof iframeSrc == typeof undefined || iframeSrc == false){
						
						iframeSrc = modalIframe.attr("data-src");
						
						if(typeof iframeSrc !== typeof undefined && iframeSrc !== false){
						
							//console.log(iframeSrc);
						
							modalIframe.attr("src", iframeSrc).on('load',function(){
								
								// get input id
										
								var inputId = modalIframe.attr("data-input-id");
								
								if( typeof inputId !== typeof undefined ){
								
									// insert media
									
									modalIframe.contents().find(".insert_media").off();
									
									modalIframe.contents().find(".insert_media").on("click", function(e){

										e.preventDefault();
										e.stopPropagation();
										
										// get media src
										
										var src = $(this).attr("data-src");
																			
										// set input change
										 
										$(inputId).val( src );
										
										// trigger input change
					
										$(inputId).trigger("change");
									
										// close current modal

										$modal.modal("toggle");
									
									});	
									
									modalIframe.contents().find('#table').bind('DOMSubtreeModified',function(event) {
										
										modalIframe.contents().find(".insert_media").off();
									
										modalIframe.contents().find(".insert_media").on("click", function(e){

											e.preventDefault();
											e.stopPropagation();
											
											// get media src
											
											var src = $(this).attr("data-src");
																				
											// set input change
											 
											$(inputId).val( src );
											
											// trigger input change
						
											$(inputId).trigger("change");
										
											// close current modal

											$modal.modal("toggle");
										
										});										
									});
								}							
							});
						}
					}				
				}
			});				
		}
		
		function set_dialogs(){
			
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
					
					var $dialog = $(id);
					
					$dialog.dialog('open');
					
					var dialogIframe = $dialog.find('iframe');
					
					if(dialogIframe.length > 0){
						
						var iframeSrc = dialogIframe.attr("src");
						
						if(typeof iframeSrc == typeof undefined || iframeSrc == false){
							
							iframeSrc = dialogIframe.attr("data-src");
							
							if(typeof iframeSrc !== typeof undefined && iframeSrc !== false){
							
								//console.log(iframeSrc);

								dialogIframe.attr("src", iframeSrc).on('load',function(){
									
									// get input id
											
									//var inputId = dialogIframe.attr("data-input-id");
									
									//if( typeof inputId !== typeof undefined ){
									
										// insert media
										
										dialogIframe.contents().find(".insert_media").off();
										
										dialogIframe.contents().find(".insert_media").on("click", function(e){

											e.preventDefault();
											e.stopPropagation();
											
											// get media src
											
											var mediaSrc = window.location.origin + '/image-proxy.php?url=' + encodeURIComponent( $(this).attr("data-src") );

											// get editor iframe
											
											var editorIframe = document.getElementById("editorIframe").contentWindow;
											
											// insert media
											
											editorIframe.insertMedia(mediaSrc);
											
											// close current dialog

											$dialog.dialog("close");
										});

										dialogIframe.contents().find('#table').bind('DOMSubtreeModified',function(event) {
											
											dialogIframe.contents().find(".insert_media").off();
										
											dialogIframe.contents().find(".insert_media").on("click", function(e){

												e.preventDefault();
												e.stopPropagation();
												
												// get media src
												
												var mediaSrc = window.location.origin + '/image-proxy.php?url=' + encodeURIComponent( $(this).attr("data-src") );

												// get editor iframe
												
												var editorIframe = document.getElementById("editorIframe").contentWindow;
												
												// insert media
												
												editorIframe.insertMedia(mediaSrc);
												
												// close current dialog

												$dialog.dialog("close");
											});
										});											

									//}							
								});
							}
						}				
					}
				});
			});
		}
		
		function set_table(){
			
			set_modals();
			
			set_dialogs();
			
			$("img.lazy").lazyload({
				
				container: $("tbody")
			});
			
			$(".pagination .page-link").on('click',function(){
				
				$("tbody").animate({ scrollTop: 0 }, "fast");
			});
		}
		
		if( $(".library-content .nav").length ){
		
			$(".library-content .nav").append('<li class="more dropdown" style="display:none;"><button style="padding:3px 8px;margin:8px 0px;background:#b1b1b1;" class="btn dropdown-toggle" type="button" data-toggle="dropdown"><span class="caret"></span></button><ul id="overflow" class="dropdown-menu dropdown-menu-right"></ul></li>').css('overflow','visible');
			
			window.onresize = navigationResize;
			
			navigationResize();
		}

		// responsive sidebar
		
		$('#sidebarCollapse').on('click', function () {
			
			$('#sidebar').toggleClass('active');
			
			//$(window).trigger('resize'); //to be fixed
		});
		
		set_modals();
						
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

		if( $('.navbar-collapse').length  > 0 ){
		
			$('.navbar-collapse').collapse({"toggle": false});
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

		// dialog boxes
		
		set_dialogs();
		
		// bootstrap table callbacks
		
		$('#table').on('load-success.bs.table', function(e) {
			
			set_table();
			
			// table filters

			if( $("#formFilters").length > 0 ){
								
				$("#formFilters").change(function () {

					var formFilters = {};

					$("#formFilters :input").filter(function(index, element) {
						
						var name = $(element).attr("name");
						
						var val = "";
						
						if( $(element).attr("type") == "checkbox" ){
							
							name = name.replace("[]", '[' + $(element).val() + ']');
							
							if($(element).is(':checked')){
								
								val = 'true';
							}
						}
						else{
							
							val = $(element).val();									
						}
					
						if( val != "" && val != 0 && val != $(element).attr("data-original") ){
						
							formFilters[name] = val;
						}										
					});
					
					console.log(formFilters);
					
					$("#table").bootstrapTable("filterBy",formFilters);

				});
			}
		});
		
		//load modal iframes
		
		$('#table').on('page-change.bs.table', function(e) {
			
			set_table();
		});
	});
	
	// set hash on popstate

	$(window).on('popstate', function() {
		
		var anchor = location.hash || $("a[data-toggle=tab]").first().attr("href");
		
		if( typeof anchor != typeof undefined ){
		
			var tabs = anchor.substring(1).split('_');
						
			$.each(tabs,function(n){
				
				$('a[href=#' + tabs[n] + ']').tab('show');
			});
			
			$('a[href=' + anchor + ']').tab('show');
		}
	});
		
})(jQuery);