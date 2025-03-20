// global variables

if( typeof window.editorCallbacks == typeof undefined )
	
	window.editorCallbacks = [];

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
		
		function append_url_parameter(url,key,value){
			
			// get separator
								
			var separator = (url.indexOf("?")===-1) ? "?" : "&";
								
			return url + separator + key + "=" + value;
		}

		//responsive menu
		
		function navigationResize() {

			if( $('.nav-resizable li.more').length == 0 ){
			
				$(".nav-resizable").append('<li class="more resizable dropdown" style="display:none;margin-left:8px;margin-bottom:0;"><button style="padding:3px 8px;margin:8px 0px;height:25px;z-index:9999;background:#f5f5f5;color:#566674;" class="btn dropdown-toggle" type="button" data-toggle="dropdown"><span class="caret"></span></button><ul id="overflow" class="dropdown-menu dropdown-menu-right"></ul></li>').css('overflow','visible !important');
			}
			
			if( $('.nav-resizable li.resizable').length > 0 ){
				
				$('.nav-resizable li.more').before($('.nav-resizable #overflow > li'));

				var $navItemMore = $('.nav-resizable > li.more'),
					$navItems = $('.nav-resizable > li:not(.more)'),
					navItemMoreWidth = navItemWidth = $navItemMore.width(),
					windowWidth = $('.nav-resizable li.more').parent().width(),
					offset = -10, 
					navOverflowWidth,
					navItemMoreOffsetLeft,
					navItemMoreOffsetRight;
				
				if( windowWidth > 0 ){
					 
					$('.nav-resizable').css('overflow','hidden');
					 
					$navItems.each(function() {
						  
						navItemWidth += $(this).width();
					});
					
					navItemWidth > windowWidth ? $navItemMore.show() : $navItemMore.hide();

					while ( navItemWidth > windowWidth) {
						
						var $lastItem = $navItems.last();
						
						if( $lastItem.find('#sidebarCollapse').length === 0 ){
						
							navItemWidth -= $lastItem.width() - 20;
							
							$lastItem.prependTo('.nav-resizable #overflow');
						}
						else{
							
							navItemWidth -= $lastItem.width() - 50;
						}
						
						$navItems.splice(-1,1);
					}
			
					$navOverflow =  $('.nav-resizable #overflow');

					navOverflowWidth = $navOverflow.width();  
					
					navItemMoreOffsetLeft = $navItemMore.offset().left;
					navItemMoreOffsetRight = windowWidth - $navItemMore.offset().left - navItemMoreWidth;
					
					if ( navItemMoreOffsetLeft > 10 || navItemMoreOffsetRight < 10 ){
							
						if( $navItems.width() > navOverflowWidth ){
							
							offset = $navItems.width() - navOverflowWidth;
						}
						else{
							
							offset = -$navItems.width();
						}
					}
					
					$('.nav-resizable').css('overflow','visible');
						
					$('.nav-resizable #overflow').css('left',offset);
				}
			}
			else{
				
				$('.nav-resizable').css('overflow','visible');
			}
		}
		
		function iframeResize() {
			
			var iframe = $('iframe.full-height');
			
			iframe.css('height', iframe.contents().height() + 'px').on('load',function(){
				
				$(this).css('height',$(this).contents().height() + 'px');
			});
		}
		
		function set_modals(){
			
			if( $('.modal:not([data-loaded])').length > 0 ){
				
				//load modal iframes

				$('.modal:not([data-loaded]').on('shown.bs.modal', function(e){
					
					$('html').css('overflow','hidden');
					
					var $modal = $(this);
                    
                    if( !$modal.data('initiated') ){
                        
                        // move the modal down
                                
                        $modal.appendTo("body").attr('data-initiated','true');
                        
                        var modalIframe = $modal.find('iframe');
                        
                        if( modalIframe.length > 0 ){
                            
                            var iframeSrc = modalIframe.attr("src");
                            
                            if( typeof iframeSrc == typeof undefined || iframeSrc == false ){
                                
                                iframeSrc = modalIframe.attr("data-src");
                                
                                if( typeof iframeSrc !== typeof undefined && iframeSrc !== false ){
                                    
                                    // show loader

                                    modalIframe.addClass('svgLoader');
                                    
                                    // prevent browser caching

                                    iframeSrc = append_url_parameter(iframeSrc,"_i",Math.random());

                                    modalIframe.attr("src", iframeSrc).on('load',function(){
                                        
                                        // hide loader

                                        modalIframe.removeClass('svgLoader');
                                        
                                        $('.modal-backdrop').removeClass('svgLoader');

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
                                                 
                                                $(inputId).val(src);
                                                
                                                // trigger input change
                            
                                                $(inputId).trigger("change");
                                            
                                                // close current modal

                                                $modal.modal("toggle");
                                            
                                            });	
                                            
                                            modalIframe.contents().find('.table').on('DOMSubtreeModified',function(event) {
                                                
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
                    }

				}).on('hidden.bs.modal', function(){
					
					$('html').css('overflow','initial');
					
				}).attr('data-loaded','true');
			}			
		}
		
		function set_iframes(){
			
			var $iframe = $('iframe:visible');
			
			if( $iframe.length > 0 ){
				
				$iframe.each(function() {
					
					var iframeSrc = $(this).attr("src");
					
					if(typeof iframeSrc == typeof undefined || iframeSrc == false){
						
						iframeSrc = $(this).attr("data-src");

						if(typeof iframeSrc !== typeof undefined && iframeSrc !== false){
							
							// show loader

							$(this).addClass('svgLoader');

							// prevent browser caching

							iframeSrc = append_url_parameter(iframeSrc,"_i",Math.random());

							$(this).attr("src", iframeSrc).on('load',function(){
								
								// hide loader

								$(this).removeClass('svgLoader');
							});
						}
					}
				});
			}
		}
		
        function set_sidebar(){
            
            // responsive sidebar
            
            if( $('#sidebarCollapse').length > 0 ){
                
                if( $('body').width() < 950 ){
                    
                    $('#sidebar').addClass('collapsed');
                }
                            
                $('#sidebarCollapse').on('click', function () {
                    
                    $('#sidebar').toggleClass('collapsed');
                    
                    navigationResize();
                });
            }
        }
		
		function set_collapsibles(){
			
			if( $('[data-toggle="collapse"]').length > 0 ){
			
				$('[data-toggle="collapse"]').on('click', function(e) {
					
					e.preventDefault();
					e.stopPropagation();
					
					const target = $(this).data('target');
					
					if( $(target).hasClass('collapse') ){
						
						$(target).removeClass('collapse');
						
						$(target).addClass('in').addClass('show');
					}
					else{
						
						$(target).removeClass('in').removeClass('show');
						
						$(target).addClass('collapse');
					}
				});
			}
		}
		
		function set_actionables(){
			
			if( $('[data-toggle="copy"]:not([data-loaded]').length > 0 ){
				
				$('[data-toggle="copy"]:not([data-loaded]').on('click', function(e) {
					
					e.preventDefault();
					
					var tagName = $(this).prop('tagName');
					
					if( tagName == 'INPUT' || tagName == 'BUTTON' ){
						
						var input = $(this).data('id');
						
						if( $(input).length > 0 ){
							
							var iTag = $(input).prop('tagName');
							
							var iValue = false;
							
							if( iTag == 'INPUT' ){
								
								iValue = $(input).val();
							}
							else if( iTag == 'TEXTAREA' ){
								
								iValue = $(input).text();
							}
							
							if( iValue !== false ){
								
								var $temp = $("<input>");
							
								$("body").append($temp);
							
								$temp.val(iValue).select();
								
								document.execCommand("copy");
								
								$temp.remove();
														
								$.notify( 'Copied to clipboard', {
									
									className: 'info',
									position: 'top center'
								});
							}
						}
					}
                    
				}).attr('data-loaded','true');
			}
			
			if( $('[data-toggle="action"]').length > 0 ){
			
				$('[data-toggle="action"]').off().on('click', function(e) {
					
					e.preventDefault();
					
					var dialogId = $(this).closest('.ui-dialog-content').attr('id');
					
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
					
					var tagName 	= $(this).prop('tagName');
					var method 		= 'get';
					var url 		= $(this).attr('href');
					var validity 	= true;
					var data;
						
					if( tagName == 'INPUT' || tagName == 'BUTTON' ){
						
						var $dform = $(this).closest('div[data-action]');
						
						if( $dform.length > 0 ){
							
							durl 	= $dform.data('action');
							dmethod = $dform.attr('data-method') || method;							
							
							$dform.wrap('<form action="'+durl+'" method="'+dmethod+'"></form>');
							
							$dform.removeAttr('data-action');
							$dform.removeAttr('data-method');
						}
						
						var $form = $(this).closest('form');
						
						if( $form.length > 0 ){
							
							if( $form[0].reportValidity() ){
								
								url 	= $form.attr('action') || url;
								method 	= $form.attr('method') || method;
								
								data 	= $form.serialize();
							}
							else{
								
								validity = false;
							}
						}
					}
					
					if( validity === true ){
						
						$( '#' + dialogId ).dialog('close');
						
						$.ajaxQueue({
										
							type 		: method,
							url  		: url,
							data		: data,
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
									
									data = jqXHR.responseText;
									
									if (typeof data === 'string' || data instanceof String){
								
										try {
											
											data = JSON.parse(data);
										}
										catch(e){
											
											data = JSON.parse(JSON.stringify(data));
										}
									}
								
									if( typeof data.message != typeof undefined ){
										
										// object response
										
										var message = data.message;						
									}							
									else{
										
										// text response
										
										var message = data;
									}
								
									$.notify( message, {
										
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
								
								if (typeof data === 'string' || data instanceof String){
									
									try {
										
										data = JSON.parse(data);
									}
									catch(e){
										
										data = JSON.parse(JSON.stringify(data));
									}
								}
								
								if( typeof data.message != typeof undefined ){
									
									// object response
									
									var message = data.message;
									
									if( typeof data.callback != typeof undefined ){
										
										eval(data.callback);
									}							
								}							
								else{
									
									// text response
									
									var message = data;
								}

								$.notify( message, {
									
									className: 'success',
									position: 'top center'
								});

								if( refresh == 'self' ){
									
									$('.table').bootstrapTable('refresh');
								}
								else if( refresh == 'parent' ){
									
									$('button[name="refresh"]:not(iframe button[name="refresh"])',window.top.document).trigger('click'); // refresh modal iframe
								
									
								}
							},
							complete: function(){
								
								$btn.removeAttr('disabled');
								
								$('#' + dialogId + 'ActionLoader').hide();
								$('#' + dialogId + 'ActionText').show();
							}
						});
					}
					else {
						
						$btn.removeAttr('disabled');
								
						$('#' + dialogId + 'ActionLoader').hide();
						$('#' + dialogId + 'ActionText').show();
					}
				});
			}
		}
		
		function set_tooltips(){
			
			if( $('[data-toggle="tooltip"]:not([data-loaded]').length > 0 ){
			
				$('[data-toggle="tooltip"]:not([data-loaded]').tooltip().attr('data-loaded','true');
			}
		}
		
		function set_dialogs(){
			
			if( $('[data-toggle="dialog"]:not([data-loaded]').length > 0 ){
				
				$('[data-toggle="dialog"]:not([data-loaded]').each(function(e){
					
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
						
						if( $dialog.find('.nav-resizable').length ){
							
							navigationResize();
						}
						
						var dialogIframe = $dialog.find('iframe');
						
						if(dialogIframe.length > 0){
							
							var iframeSrc = dialogIframe.attr("src");
							
							if(typeof iframeSrc == typeof undefined || iframeSrc == false){
								
								iframeSrc = dialogIframe.attr("data-src");
						
								if(typeof iframeSrc !== typeof undefined && iframeSrc !== false){
									
									// show loader

									dialogIframe.addClass('svgLoader');

									// prevent browser caching

									iframeSrc = append_url_parameter(iframeSrc,"_i",Math.random());

									dialogIframe.attr("src", iframeSrc).on('load',function(){
									
										// hide loader

										dialogIframe.removeClass('svgLoader');
										
										// get input id
												
										//var inputId = dialogIframe.attr("data-input-id");
										
										//if( typeof inputId !== typeof undefined ){
										
											// insert media
											
											dialogIframe.contents().find(".insert_media").off();
											
											dialogIframe.contents().find(".insert_media").on("click", function(e){

												e.preventDefault();
												e.stopPropagation();
												
												// get media src
												
												var mediaSrc = imgProxy + encodeURIComponent( $(this).attr("data-src") );

												// get editor iframe
												
												var editorIframe = document.getElementById("editorIframe").contentWindow;
												
												// insert media
												
												editorIframe.insertMedia(mediaSrc);
												
												// close current dialog

												$dialog.dialog("close");
											});
											
											dialogIframe.contents().find('.table').on('DOMSubtreeModified',function(event) {
												
												dialogIframe.contents().find(".insert_media").off();
											
												dialogIframe.contents().find(".insert_media").on("click", function(e){

													e.preventDefault();
													e.stopPropagation();
													
													// get media src
													 
													var mediaSrc = imgProxy + encodeURIComponent( $(this).attr("data-src") );

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
                    
                    $(this).attr('data-loaded','true');
				});
			}
		}
		
        function set_links(){
           
            const params = new URLSearchParams(window.location.search);
            
            if( params.get('output') === 'widget' ){
                
                $('#gallery_sidebar a').attr('target','_self');
                
                $('a').each(function() {
                    
                    if( !$(this).attr('target') && $(this).attr('href') ) {
                        
                        const href = $(this).attr('href');
                        
                        if( href.indexOf('http') === 0 ){
                            
                            const linkHost = new URL(href).hostname;

                            if( linkHost === window.location.hostname ) {
                                
                                $(this).attr('target', '_parent');
                            }
                            else {
                                
                                $(this).attr('target', '_blank');
                            }
                        }
                    }
                });
            }
            
            $(".open-url").on("click",function(e){
                
                e.preventDefault();

                var url = $(this).data("target");

                window.open(url,"_blank");
                
            });
            
            $(".copy-url").on("click",function(e){
                
                e.preventDefault();
                
                var url = $(this).data("target");
                
                var $temp = $("<input>");
                
                $("body").append($temp);
            
                $temp.val(url).select();
                
                document.execCommand("copy");
                
                $temp.remove();
                                
                $.notify( "Copied to clipboard", {
                                
                    className: "info",
                    position: "top center"
                });
            
            });
        }
        
		function set_table(){
			
			set_modals();
			
			set_actionables();
			
			set_dialogs();
			
			set_tooltips();
			
			$("img.lazy:not([data-loaded]").lazyload({
				
				container: $(".table tbody")
                
			}).attr('data-loaded','true');
			
			if( $(".pagination .page-link:not([data-loaded]").length > 0 ){
			
				$(".pagination .page-link:not([data-loaded]").on('click',function(){
					
					$("tbody").animate({ scrollTop: 0 }, "fast");
				}).attr('data-loaded','true');
			}
		}
			
		function adjustContainerHeight(target) {
		
			var container = $(target);
			
			var footerHeight = $("#ltple-footer").length > 0 ? $("#ltple-footer").height() : 0;
			
			var containerOffset = container.offset().top;
			
			var height = $(window).height() - containerOffset - footerHeight;
			
			container.css("height", height + "px").css("min-height", height + "px");
		}
		
		if( $("#ltple-wrapper #gallery_wrapper").length > 0 ){

			adjustContainerHeight("#ltple-wrapper #gallery_wrapper");
			adjustContainerHeight("#ltple-wrapper #gallery_sidebar");

			$(window).resize(function(){
				
				adjustContainerHeight("#ltple-wrapper #gallery_wrapper");
				adjustContainerHeight("#ltple-wrapper #gallery_sidebar");
			});
		}
		
		if( $(".nav-resizable").length ){

			if( typeof ResizeObserver != typeof undefined ){

				new ResizeObserver(navigationResize).observe(document.querySelector('.nav-resizable'));
			}
			else{
				
				window.onresize = navigationResize;
			}

			navigationResize();
		}
		
		if( $("iframe.full-height").length ){

			if( typeof ResizeObserver != typeof undefined ){

				new ResizeObserver(iframeResize).observe(document.querySelector('iframe.full-height'));
			}
			else{
				
				window.onresize = iframeResize;
			}

			iframeResize();
		}

        set_sidebar();
        
		set_collapsibles();
		
		set_actionables();
		
		set_modals();
		
		set_iframes();
						
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
		
		if( typeof bootstrap === 'object' && typeof bootstrap.Modal === 'function' ){
			
			document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => new bootstrap.Popover(el));
		}
		else{
			
			$('[data-toggle="popover"]').popover().on("click", function(e) {
				
				if( $(this).attr('data-trigger') == 'hover' ){
					
					e.preventDefault();
					
					$(location).attr('href',$(this).attr('href'));
				}
			});
		}
		
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
        
        // links
		
		set_links();  
        
        window.addEventListener('ltple.set.links', function(event) {
            
            //const eventData = event.detail;
            
            set_links();
        });
		
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
			
			var loadingMore = false;
			
			$( window ).on('scroll touchstart', function() {
					
				if( $("#loadMore").length > 0 ){
					
					var url  = $("#loadMore").data("url");
							
					var page = $("#loadMore").data("page");

					if( typeof url != typeof undefined && typeof page != typeof undefined ){
					
						if( loadingMore == false && $(window).scrollTop() >= ( $(document).height() - $(window).height() - 200 ) ) {
							
							loadingMore = true;
							
							$.ajax({
								
								type	: "GET",
								url		: url,
								data	: {
									
									page: page + 1
								},
								complete: function() {
									
									loadingMore = false;
								},
								error: function(response) {
									
									
								},
								success: function(data) {
									
									var html = $($.parseHTML(data));
									
									// append items
									
									html.find(".hentry").each(function(i) {
										
										var elemId = $(this).attr("id");
										
										if( $( "#" + elemId ).length == 0 ){
									
											$("#loadMore").before($(this));
										}
									});
									
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
		
		$("form").on("submit",function(e){
			
			// save tinyMCE
		
			if( typeof tinyMCE != typeof undefined && tinyMCE.editors.length > 0 ){
				
				$.each(tinyMCE.editors,function(i,editor){
					
					editor.save();
				});
			}
			
			// save CodeMirror
			
			if( typeof CodeMirror != typeof undefined ){
				
				if( $('.CodeMirror').length > 0 ){
				
					$('.CodeMirror').each(function(i,el) {
						
						el.CodeMirror.save();
					});
				}
			}
		});

		// collect info modals
		
		if( $('.collect-info').length > 0 ){
		
			$('.collect-info button').on('click', function (e) {
				
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
        
         // add close button to alerts
        
        function addCloseButton(alertElement) {
            
            if (!alertElement.find('.close').length) {
                
                let closeBtn = $('<button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button>');
                
                closeBtn.on('click', function () {
                    
                    alertElement.remove();
                });
                
                alertElement.prepend(closeBtn);
            }
        }

        $('.alert').each(function () {
            
            addCloseButton($(this));
        });
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