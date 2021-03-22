;(function($){

	$(document).ready(function(){
		
		// requests handler
						
		var ajaxQueue = $({});

		$.ajaxQueue = function( ajaxOpts ) {
			var jqXHR,
				dfd = $.Deferred(),
				promise = dfd.promise();

			// queue our ajax request
			ajaxQueue.queue( doRequest );

			// add the abort method
			promise.abort = function( statusText ) {

				// proxy abort to the jqXHR if it is active
				if ( jqXHR ) {
					return jqXHR.abort( statusText );
				}

				// if there wasnt already a jqXHR we need to remove from queue
				var queue = ajaxQueue.queue(),
					index = $.inArray( doRequest, queue );

				if ( index > -1 ) {
					queue.splice( index, 1 );
				}

				// and then reject the deferred
				dfd.rejectWith( ajaxOpts.context || ajaxOpts,
					[ promise, statusText, "" ] );

				return promise;
			};

			// run the actual query
			function doRequest( next ) {
				jqXHR = $.ajax( ajaxOpts )
					.done( dfd.resolve )
					.fail( dfd.reject )
					.then( next, next );
			}

			return promise;
		};

		//responsive menu
		
		function navigationResize() {

			if( $('.library-content .nav li.more').length == 0 ){
			
				$(".library-content .nav").append('<li class="more dropdown" style="display:none;margin-left:8px;margin-bottom:0;"><button style="padding:3px 8px;margin:8px 0px;background:#b1b1b1;height:25px;" class="btn dropdown-toggle" type="button" data-toggle="dropdown"><span class="caret"></span></button><ul id="overflow" class="dropdown-menu dropdown-menu-right"></ul></li>').css('overflow','visible !important');
			}

			$('.library-content .nav li.more').before($('.library-content .nav #overflow > li'));

			var $navItemMore = $('.library-content .nav > li.more'),
				$navItems = $('.library-content .nav > li:not(.more)'),
				navItemMoreWidth = navItemWidth = $navItemMore.width(),
				windowWidth = $('.library-content .nav li.more').parent().width(),
				offset, navOverflowWidth;
			  
			if( windowWidth > 0 ){
				  
				$navItems.each(function() {
					  
					navItemWidth += $(this).width();
				});
				  
				navItemWidth > windowWidth ? $navItemMore.show() : $navItemMore.hide();
				
				while ( navItemWidth > windowWidth) {
					
					navItemWidth -= $navItems.last().width();
					$navItems.last().prependTo('.library-content .nav #overflow');
					$navItems.splice(-1,1);
				}

				navOverflowWidth = $('.library-content .nav #overflow').width();  
				
				if( navItemWidth > navItemMoreWidth ){
					
					offset = navItemMoreWidth - navOverflowWidth;
				}
				else{
					
					offset = 0;
				}
					
				$('.library-content .nav #overflow').css({
					'left': offset
				});
			}
		}
		
		function set_modals(){
			
			if( $('.modal').length > 0 ){
				
				//modal always on top 
				
				$('.modal').appendTo("body");
						
				//load modal iframes
				
				$('.modal').on('shown.bs.modal', function (e) {
					
					$('html').css('overflow','hidden');
					
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
										
										modalIframe.contents().find('.table').bind('DOMSubtreeModified',function(event) {
											
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
				
				}).on('hidden.bs.modal', function(){
					
					$('html').css('overflow','initial');
					
				});
			}			
		}
		
		function set_collapsibles(){
			
			if( $('[data-toggle="collapse"]').length > 0 ){
			
				$('[data-toggle="collapse"]').collapse();
				
				$('[data-toggle="collapse"]').on('click', function(e) {
					
					e.preventDefault();
				});
			}
		}
		
		function set_actionables(){
			
			if( $('[data-toggle="action"]').length > 0 ){
			
				$('[data-toggle="action"]').unbind().on('click', function(e) {
					
					e.preventDefault();
					
					var dialogId = $(this).closest('.ui-dialog-content').attr('id');
					
					$( '#' + dialogId ).dialog('close');
					
					var $btn = $('[data-target="\\#'  + dialogId + '"]');
					
					$btn.attr('disabled','disabled');
					
					if( $( '#' + dialogId + 'ActionLoader').length === 0 ){
						
						$btn.wrapInner( '<span id="' + dialogId + 'ActionText"></span>' );
						
						$btn.append( '<svg id="' + dialogId + 'ActionLoader" style="height:4px;margin-bottom:2px;" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 53 12" enable-background="new 0 0 0 0" xml:space="preserve"><circle fill="#EEEEEE" stroke="none" cx="6" cy="6" r="6"><animate attributeName="opacity" dur="1s" values="0;1;0" repeatCount="indefinite" begin="0.1"></animate></circle><circle fill="#EEEEEE" stroke="none" cx="26" cy="6" r="6"><animate attributeName="opacity" dur="1s" values="0;1;0" repeatCount="indefinite" begin="0.2"></animate></circle><circle fill="#EEEEEE" stroke="none" cx="46" cy="6" r="6"><animate attributeName="opacity" dur="1s" values="0;1;0" repeatCount="indefinite" begin="0.3"></animate></circle></svg>');
					}
					else{
						
						$('#' + dialogId + 'ActionLoader').show();
					}
					
					$('#' + dialogId + 'ActionText').hide();
					
					var refresh = $(this).attr('data-refresh');
				
					$.ajaxQueue({
									
						type 		: "GET",
						url  		: $(this).attr('href'),
						cache		: false,
						beforeSend	: function(){
							
							
						},
						error: function(jqXHR,textStatus,errorThrown) {
							
							if( typeof textStatus !== typeof undefined && textStatus != 'error' ){
								
								$.notify( textStatus, {
									
									className: 'error',
									position: 'top center'
								});
							}
							else if( jqXHR.status == 404 ){
								
								$.notify( jqXHR.responseText, {
									
									className: 'warning',
									position: 'top center'
								});
							}
							else{
								
								$.notify( 'Error ' + jqXHR.status, {
									
									className: 'error',
									position: 'top center'
								});
							}
						},
						success: function(data) {
							
							$.notify( data, {
								
								className: 'success',
								position: 'top center'
							});

							if( refresh == 'self' ){
								
								$('.table').bootstrapTable('refresh');
							}
							else if( refresh == 'parent' ){
								
								// TODO FIX
								
								//$('button[name="refresh"]',parent.document).trigger('click'); // refresh modal iframe
								
								$('.table',parent.document).bootstrapTable('refresh'); // not working
							}
						},
						complete: function(){
							
							$btn.removeAttr('disabled');
							
							$('#' + dialogId + 'ActionLoader').hide();
							$('#' + dialogId + 'ActionText').show();
						}
					});
				});
			}
		}
		
		function set_tooltips(){
			
			if( $('[data-toggle="tooltip"]').length > 0 ){
			
				$('[data-toggle="tooltip"]').tooltip();
			}
		}
		
		function set_dialogs(){
			
			if( $('[data-toggle="dialog"]').length > 0 ){
				
				$('[data-toggle="dialog"]').each(function(e){
					
					var id 		= $(this).data('target');
					
					var width 	= $(this).data('width') || 'auto';
					
					var height 	= $(this).data('height') || 'auto';
					
					var resizable = $(this).data('resizable');
					
					if( typeof resizable == typeof undefined ) resizable = false;
					
					var draggable = $(this).data('draggable');
					
					if( typeof draggable == typeof undefined ) draggable = true;
					
					$(id).dialog({
						
						autoOpen 	: false,
						width 		: width,
						height 		: height,
						resizable 	: resizable,
						draggable 	: draggable
						
					});
					
					$(this).on('click',function(e){
						
						var $dialog = $(id);
						
						$dialog.dialog('open');
						
						if( $dialog.find('.library-content .nav').length ){
							
							navigationResize();
						}
						
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

											dialogIframe.contents().find('.table').bind('DOMSubtreeModified',function(event) {
												
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
		}
		
		function set_table(){
			
			set_modals();
			
			set_actionables();
			
			set_dialogs();
			
			set_tooltips();
			
			$("img.lazy").lazyload({
				
				container: $(".table tbody")
			});
			
			if( $(".pagination .page-link").length > 0 ){
			
				$(".pagination .page-link").on('click',function(){
					
					$("tbody").animate({ scrollTop: 0 }, "fast");
				});
			}
		}
		
		if( $(".library-content .nav").length ){

			window.onresize = navigationResize;
			
			navigationResize();
		}

		// responsive sidebar
		
		$('#sidebarCollapse').on('click', function () {
			
			$('#sidebar').toggleClass('active');
			
			//$(window).trigger('resize'); //to be fixed
		});
		
		set_collapsibles();
		
		set_actionables();
		
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
		
		// set hash on click without jumb
		
		$('[data-toggle="tab"]').on('click', function(e) {
			
			e.preventDefault();
			
			if(history.pushState) {
				
				history.pushState(null, null, this.getAttribute("href"));
			}
			else {
				
				location.hash = this.getAttribute("href");
			}

			var tabs = location.hash.substring(1).split('_');
			
			$.each(tabs,function(n){
				
				$('a[href=\\#' + tabs[n] + ']').tab('show');
			});
			
			$('a[href=\\' + location.hash + ']').tab('show');
			
			return false;
		});
		
		// show active tab

		if(location.hash) {
			
			var tabs = location.hash.substring(1).split('_');

			$.each(tabs,function(n){

				$('a[href=\\#' + tabs[n] + ']').tab('show');
			});			
			
			$('a[href=\\' + location.hash + ']').tab('show');
		}
		else{
			
			// get active tabs from localStorage
			
			var selectedTab = localStorage.getItem('selectedTab');
			
			// restore active tabs
			
			if (selectedTab != null) {
				
				var tabs = selectedTab.substring(1).split('_');
				
				$.each(tabs,function(n){
					
					$('a[href=\\#' + tabs[n] + ']').tab('show');
				});
				
				$('a[href=\\' + selectedTab + ']').tab('show');
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
		
		$('.table').on('load-success.bs.table', function(e) {
			
			set_table();
		});
		
		//load modal iframes
		
		$('.table').on('page-change.bs.table', function(e) {
			
			set_table();
		});
		
		// set infinit scroll

		if( $("#loadMore").length > 0  ){
			
			$( window ).on('scroll touchstart', function() {
					
				if( $("#loadMore").length > 0 ){
					
					var url  = $("#loadMore").data("url");
							
					var page = $("#loadMore").data("page");

					if( typeof url != typeof undefined && typeof page != typeof undefined ){
					
						if( $(window).scrollTop() >= ( $(document).height() - $(window).height() - 200 ) ) {

							$.ajax({
								
								type	: "get",
								url		: url,
								data	: {
									
									page: page + 1
								},
								error: function(response) {
									
								},
								success: function(data) {
									
									var html = $($.parseHTML(data));
									
									// append items
									
									html.find(".hentry").each(function(i) {
									 
										$("#loadMore").before($(this));
									});
									
									// replace loadMore
									
									var loadMore = html.find("#loadMore");
							
									if( loadMore.length > 0 ){
										
										$( "#loadMore" ).replaceWith(loadMore);
									}
									else{
										
										$("#loadMore").remove();
									}
									
									set_modals();
								}
							});
						}
					}
					else{
						
						$("#loadMore").remove();
					}
				}
			});
		}
		
		// collect info modals
		
		if( $('.collect-info').length > 0 ){
		
			$('.collect-info').on('click', function (e) {
				
				e.preventDefault();
				
				$form = $(this).closest("form");

				$.ajax({
					
					type 		: $form.attr('method'),
					url  		: $form.attr('action'),
					data		: $form.serialize(),
					beforeSend	: function() {

						$('.collect-info').css('display','none');
						$('.collect-info-backdrop').css('display','none');
					},
					success: function(data) {
						
					}
				});
			});
		}
	});
	
	// set hash on popstate

	$(window).on('popstate', function() {
		
		var anchor = location.hash || $("a[data-toggle=tab]").first().attr("href");
		
		if( typeof anchor != typeof undefined ){
		
			var tabs = anchor.substring(1).split('_');
						
			$.each(tabs,function(n){
				
				$('a[href=\\#' + tabs[n] + ']').tab('show');
			});
			
			$('a[href=\\' + anchor + ']').tab('show');
		}
	});
		
})(jQuery);