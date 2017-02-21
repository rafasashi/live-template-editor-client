<?php 

	//get current layer id
	 
	if( $post->post_type == 'user-layer' ){
	
		$layer_id=intval(get_post_meta( $post->ID, 'defaultLayerId', true ));
	}
	else{
		
		$layer_id=$post->ID;
	}
	
	//get page def
	
	$pageDef = get_post_meta( $layer_id, 'pageDef', true );
	
	//get output config
	
	$layerOutput = get_post_meta( $layer_id, 'layerOutput', true );
	
	//get style-sheet
	
	$layerCss = get_post_meta( $layer_id, 'layerCss', true );
	
	//get layer margin
	
	$layerMargin = get_post_meta( $layer_id, 'layerMargin', true );
	
	if( empty($layerMargin) ){
		
		$layerMargin = '-120px 0px -20px 0px';
	}
	
	$layerMinWidth = get_post_meta( $layer_id, 'layerMinWidth', true );
	
	if( empty($layerMinWidth) ){
		
		$layerMinWidth = '1000px';
	}	
	
	//get layer options
	
	$layerOptions = get_post_meta( $layer_id, 'layerOptions', true );
	
	//get css libraries
	
	$cssLibraries = get_post_meta( $layer_id, 'cssLibraries', true );
	
	//get js libraries
	
	$jsLibraries = get_post_meta( $layer_id, 'jsLibraries', true );
	
	//get layer image proxy
	
	$layerImgProxy = 'http://'.$_SERVER['HTTP_HOST'].'/image-proxy.php?url=';
					
	//get layer content
	
	$layerContent 	= $post->post_content;
	$layerContent 	= str_replace(array('&quot;','cursor: pointer;'),'',$layerContent);
	
	if($layerOutput=='canvas'){
		
		// replace image sources
		
		$layerContent = str_replace(array('src =','src= "'),array('src=','src="'),$layerContent);
		$layerContent = str_replace(array($layerImgProxy,'src="'),array('','src="'.$layerImgProxy),$layerContent);			
		
		// replace background images
		
		$regex = '/(background(?:-image)?: ?url\((["|\']?))(.+)(["|\']?\))/';
		$layerContent = preg_replace($regex, "$1$layerImgProxy$3$4", $layerContent);					
	
		if(!empty($layerCss)){
			
			// replace background images
			
			$regex = '/(background(?:-image)?: ?url\((["|\']?))(.+)(["|\']?\))/';
			$layerCss = preg_replace($regex, "$1$layerImgProxy$3$4", $layerCss);				
		}
	}

	echo '<!DOCTYPE>';
	echo '<html>';

		echo '<head>';
		
			echo '<title>'.ucfirst($post->post_title).'</title>';
		
			echo '<!-- Le HTML5 shim, for IE6-8 support of HTML elements -->';
			echo '<!--[if lt IE 9]>';
			echo '<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>';
			echo '<![endif]-->';

			if( is_array($cssLibraries) ){
				
				if( in_array('bootstrap-3',$cssLibraries)){
					
					echo '<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>';
				}

				if( in_array('fontawesome-4',$cssLibraries)){
					
					echo '<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>';
				}
				
				if( in_array('elementor-1.2.3',$cssLibraries)){

					// elementor-frontend-min
				
					echo '<style>@charset "UTF-8";.elementor-video-wrapper{position:relative;height:0}.elementor-video-wrapper iframe{position:absolute;top:0;left:0;height:100%;width:100%}#wpadminbar #wp-admin-bar-elementor_edit_page>.ab-item:before{content:"\e800";font-family:eicon;font-size:12px;margin-top:5px}.elementor{-webkit-hyphens:manual;-ms-hyphens:manual;hyphens:manual}.elementor *,.elementor :after,.elementor :before{box-sizing:border-box}.elementor a{box-shadow:none;text-decoration:none}.elementor hr{margin:0;background-color:transparent}.elementor img{height:auto;max-width:100%;border:none;border-radius:0;box-shadow:none}.elementor figure{margin:0}.elementor embed,.elementor iframe,.elementor object,.elementor video{max-width:100%;width:100%;margin:0;line-height:1}.elementor .elementor-custom-embed{line-height:0}.elementor .elementor-background-video-container{height:100%;width:100%;top:0;left:0;position:absolute;overflow:hidden;z-index:0}.elementor .elementor-background-video{position:absolute;max-width:none;top:50%;left:50%;-webkit-transform:translateY(-50%) translateX(-50%);transform:translateY(-50%) translateX(-50%)}.elementor .elementor-html5-video{object-fit:cover}.elementor .elementor-background-overlay{height:100%;width:100%;top:0;left:0;position:absolute}.elementor .elementor-invisible{visibility:hidden}.elementor-align-center{text-align:center}.elementor-align-center .elementor-button{width:auto}.elementor-align-right{text-align:right}.elementor-align-right .elementor-button{width:auto}.elementor-align-left{text-align:left}.elementor-align-left .elementor-button{width:auto}.elementor-align-justify .elementor-button{width:100%}@media (max-width:1024px){.elementor-tablet-align-center{text-align:center}.elementor-tablet-align-center .elementor-button{width:auto}.elementor-tablet-align-right{text-align:right}.elementor-tablet-align-right .elementor-button{width:auto}.elementor-tablet-align-left{text-align:left}.elementor-tablet-align-left .elementor-button{width:auto}.elementor-tablet-align-justify .elementor-button{width:100%}}@media (max-width:767px){.elementor-mobile-align-center{text-align:center}.elementor-mobile-align-center .elementor-button{width:auto}.elementor-mobile-align-right{text-align:right}.elementor-mobile-align-right .elementor-button{width:auto}.elementor-mobile-align-left{text-align:left}.elementor-mobile-align-left .elementor-button{width:auto}.elementor-mobile-align-justify .elementor-button{width:100%}}#elementor-select-preset{display:none}.elementor:after{position:absolute;opacity:0;width:0;height:0;padding:0;overflow:hidden;clip:rect(0,0,0,0);border:0}@media (min-width:1025px){.elementor:after{content:"desktop"}}@media (min-width:768px) and (max-width:1024px){.elementor:after{content:"tablet"}}@media (max-width:767px){.elementor:after{content:"mobile"}}.elementor-section{position:relative}.elementor-section .elementor-container{display:-webkit-box;display:-ms-flexbox;display:flex;margin-right:auto;margin-left:auto;position:relative}.elementor-section.elementor-section-boxed>.elementor-container{max-width:1140px}.elementor-section.elementor-section-stretched{position:relative;width:100%}.elementor-section.elementor-section-items-top>.elementor-container{-webkit-box-align:start;-ms-flex-align:start;align-items:flex-start}.elementor-section.elementor-section-items-middle>.elementor-container{-webkit-box-align:center;-ms-flex-align:center;align-items:center}.elementor-section.elementor-section-items-bottom>.elementor-container{-webkit-box-align:end;-ms-flex-align:end;align-items:flex-end}@media (min-width:768px){.elementor-section.elementor-section-height-full{height:100vh}.elementor-section.elementor-section-height-full>.elementor-container{height:100%}}.elementor-section-content-top>.elementor-container>.elementor-row>.elementor-column>.elementor-column-wrap{-webkit-box-align:start;-ms-flex-align:start;align-items:flex-start}.elementor-section-content-middle>.elementor-container>.elementor-row>.elementor-column>.elementor-column-wrap{-webkit-box-align:center;-ms-flex-align:center;align-items:center}.elementor-section-content-bottom>.elementor-container>.elementor-row>.elementor-column>.elementor-column-wrap{-webkit-box-align:end;-ms-flex-align:end;align-items:flex-end}.elementor-row{width:100%;display:-webkit-box;display:-ms-flexbox;display:flex}@media (max-width:767px){.elementor-row{-ms-flex-wrap:wrap;flex-wrap:wrap}}.elementor-column-wrap{width:100%;position:relative;display:-webkit-box;display:-ms-flexbox;display:flex}.elementor-widget-wrap{position:relative;width:100%;z-index:1}.elementor-widget{position:relative}.elementor-widget:not(:last-child){margin-bottom:20px}.elementor-column{position:relative;min-height:1px;display:-webkit-box;display:-ms-flexbox;display:flex}.elementor-column-gap-narrow>.elementor-row>.elementor-column>.elementor-element-populated{padding:5px}.elementor-column-gap-default>.elementor-row>.elementor-column>.elementor-element-populated{padding:10px}.elementor-column-gap-extended>.elementor-row>.elementor-column>.elementor-element-populated{padding:15px}.elementor-column-gap-wide>.elementor-row>.elementor-column>.elementor-element-populated{padding:20px}.elementor-column-gap-wider>.elementor-row>.elementor-column>.elementor-element-populated{padding:30px}.elementor-inner-section .elementor-column-gap-no .elementor-element-populated{padding:0}@media (min-width:768px){.elementor-column.elementor-col-10,.elementor-column[data-col="10"]{width:10%}.elementor-column.elementor-col-11,.elementor-column[data-col="11"]{width:11.111%}.elementor-column.elementor-col-12,.elementor-column[data-col="12"]{width:12.5%}.elementor-column.elementor-col-14,.elementor-column[data-col="14"]{width:14.285%}.elementor-column.elementor-col-16,.elementor-column[data-col="16"]{width:16.666%}.elementor-column.elementor-col-20,.elementor-column[data-col="20"]{width:20%}.elementor-column.elementor-col-25,.elementor-column[data-col="25"]{width:25%}.elementor-column.elementor-col-30,.elementor-column[data-col="30"]{width:30%}.elementor-column.elementor-col-33,.elementor-column[data-col="33"]{width:33.333%}.elementor-column.elementor-col-40,.elementor-column[data-col="40"]{width:40%}.elementor-column.elementor-col-50,.elementor-column[data-col="50"]{width:50%}.elementor-column.elementor-col-60,.elementor-column[data-col="60"]{width:60%}.elementor-column.elementor-col-66,.elementor-column[data-col="66"]{width:66.666%}.elementor-column.elementor-col-70,.elementor-column[data-col="70"]{width:70%}.elementor-column.elementor-col-75,.elementor-column[data-col="75"]{width:75%}.elementor-column.elementor-col-80,.elementor-column[data-col="80"]{width:80%}.elementor-column.elementor-col-83,.elementor-column[data-col="83"]{width:83.333%}.elementor-column.elementor-col-90,.elementor-column[data-col="90"]{width:90%}.elementor-column.elementor-col-100,.elementor-column[data-col="100"]{width:100%}}@media (max-width:479px){.elementor-column.elementor-xs-10{width:10%}.elementor-column.elementor-xs-11{width:11.111%}.elementor-column.elementor-xs-12{width:12.5%}.elementor-column.elementor-xs-14{width:14.285%}.elementor-column.elementor-xs-16{width:16.666%}.elementor-column.elementor-xs-20{width:20%}.elementor-column.elementor-xs-25{width:25%}.elementor-column.elementor-xs-30{width:30%}.elementor-column.elementor-xs-33{width:33.333%}.elementor-column.elementor-xs-40{width:40%}.elementor-column.elementor-xs-50{width:50%}.elementor-column.elementor-xs-60{width:60%}.elementor-column.elementor-xs-66{width:66.666%}.elementor-column.elementor-xs-70{width:70%}.elementor-column.elementor-xs-75{width:75%}.elementor-column.elementor-xs-80{width:80%}.elementor-column.elementor-xs-83{width:83.333%}.elementor-column.elementor-xs-90{width:90%}.elementor-column.elementor-xs-100{width:100%}}@media (max-width:767px){.elementor-column.elementor-sm-10{width:10%}.elementor-column.elementor-sm-11{width:11.111%}.elementor-column.elementor-sm-12{width:12.5%}.elementor-column.elementor-sm-14{width:14.285%}.elementor-column.elementor-sm-16{width:16.666%}.elementor-column.elementor-sm-20{width:20%}.elementor-column.elementor-sm-25{width:25%}.elementor-column.elementor-sm-30{width:30%}.elementor-column.elementor-sm-33{width:33.333%}.elementor-column.elementor-sm-40{width:40%}.elementor-column.elementor-sm-50{width:50%}.elementor-column.elementor-sm-60{width:60%}.elementor-column.elementor-sm-66{width:66.666%}.elementor-column.elementor-sm-70{width:70%}.elementor-column.elementor-sm-75{width:75%}.elementor-column.elementor-sm-80{width:80%}.elementor-column.elementor-sm-83{width:83.333%}.elementor-column.elementor-sm-90{width:90%}.elementor-column.elementor-sm-100{width:100%}}@media (min-width:768px) and (max-width:1024px){.elementor-column.elementor-md-10{width:10%}.elementor-column.elementor-md-11{width:11.111%}.elementor-column.elementor-md-12{width:12.5%}.elementor-column.elementor-md-14{width:14.285%}.elementor-column.elementor-md-16{width:16.666%}.elementor-column.elementor-md-20{width:20%}.elementor-column.elementor-md-25{width:25%}.elementor-column.elementor-md-30{width:30%}.elementor-column.elementor-md-33{width:33.333%}.elementor-column.elementor-md-40{width:40%}.elementor-column.elementor-md-50{width:50%}.elementor-column.elementor-md-60{width:60%}.elementor-column.elementor-md-66{width:66.666%}.elementor-column.elementor-md-70{width:70%}.elementor-column.elementor-md-75{width:75%}.elementor-column.elementor-md-80{width:80%}.elementor-column.elementor-md-83{width:83.333%}.elementor-column.elementor-md-90{width:90%}.elementor-column.elementor-md-100{width:100%}}@media (max-width:767px){.elementor-column{width:100%}.elementor-reverse-mobile>.elementor-container>.elementor-row{-webkit-box-orient:vertical;-webkit-box-direction:reverse;-ms-flex-direction:column-reverse;flex-direction:column-reverse}}.elementor-screen-only,.screen-reader-text,.screen-reader-text span,.ui-helper-hidden-accessible{position:absolute;width:1px;height:1px;margin:-1px;padding:0;overflow:hidden;clip:rect(0,0,0,0);border:0}.elementor-clearfix:after{content:"";display:block;clear:both;width:0;height:0}.elementor-form-fields-wrapper{display:-webkit-box;display:-ms-flexbox;display:flex;-ms-flex-wrap:wrap;flex-wrap:wrap}.elementor-form-fields-wrapper.elementor-labels-above>.elementor-field-group .elementor-field-subgroup,.elementor-form-fields-wrapper.elementor-labels-above>.elementor-field-group>.elementor-select-wrapper,.elementor-form-fields-wrapper.elementor-labels-above>.elementor-field-group>input,.elementor-form-fields-wrapper.elementor-labels-above>.elementor-field-group>textarea{-ms-flex-preferred-size:100%;flex-basis:100%}.elementor-form-fields-wrapper.elementor-labels-inline>.elementor-field-group .elementor-select-wrapper,.elementor-form-fields-wrapper.elementor-labels-inline>.elementor-field-group>input{-webkit-box-flex:1;-ms-flex-positive:1;flex-grow:1}.elementor-field-group{-ms-flex-wrap:wrap;flex-wrap:wrap;-webkit-box-align:center;-ms-flex-align:center;align-items:center}.elementor-field-group.elementor-field-type-submit{-webkit-box-align:end;-ms-flex-align:end;align-items:flex-end}.elementor-field-group .elementor-field-textual{width:100%;border:1px solid #818a91;background-color:transparent;color:#373a3c;vertical-align:middle;-webkit-box-flex:1;-ms-flex-positive:1;flex-grow:1}.elementor-field-group .elementor-field-textual:focus{box-shadow:inset 0 0 0 1px rgba(0,0,0,.1);outline:0}.elementor-field-group .elementor-field-textual::-webkit-input-placeholder{color:inherit;opacity:.5}.elementor-field-group .elementor-field-textual::-moz-placeholder{color:inherit;opacity:.5}.elementor-field-group .elementor-field-textual:-ms-input-placeholder{color:inherit;opacity:.5}.elementor-field-group .elementor-select-wrapper{display:-webkit-box;display:-ms-flexbox;display:flex;position:relative;width:100%}.elementor-field-group .elementor-select-wrapper select{-webkit-appearance:none;-moz-appearance:none;appearance:none;color:inherit;-ms-flex-preferred-size:100%;flex-basis:100%;padding-right:20px}.elementor-field-group .elementor-select-wrapper:before{content:"\f0d7";font-family:FontAwesome;font-size:15px;position:absolute;top:50%;-webkit-transform:translateY(-50%);transform:translateY(-50%);right:10px}.elementor-field-subgroup{display:-webkit-box;display:-ms-flexbox;display:flex;-ms-flex-wrap:wrap;flex-wrap:wrap}.elementor-field-subgroup .elementor-field-option label{display:inline-block}.elementor-field-subgroup.elementor-subgroup-inline .elementor-field-option{padding-right:10px}.elementor-field-subgroup:not(.elementor-subgroup-inline) .elementor-field-option{-ms-flex-preferred-size:100%;flex-basis:100%}.elementor-field-label{cursor:pointer}.elementor-mark-required .elementor-field-label:after{content:"*";color:red;padding-left:.2em}.elementor-field-textual{line-height:1.4}.elementor-field-textual.elementor-size-xs{font-size:13px;min-height:33px;padding:4px 12px;border-radius:2px}.elementor-field-textual.elementor-size-sm{font-size:15px;min-height:40px;padding:5px 14px;border-radius:3px}.elementor-field-textual.elementor-size-md{font-size:16px;min-height:47px;padding:6px 16px;border-radius:4px}.elementor-field-textual.elementor-size-lg{font-size:18px;min-height:59px;padding:7px 20px;border-radius:5px}.elementor-field-textual.elementor-size-xl{font-size:20px;min-height:72px;padding:8px 24px;border-radius:6px}.elementor-button-align-center .elementor-field-type-submit{-webkit-box-pack:center;-ms-flex-pack:center;justify-content:center}.elementor-button-align-stretch .elementor-field-type-submit button{-ms-flex-preferred-size:100%;flex-basis:100%}.elementor-button-align-start .elementor-field-type-submit{-webkit-box-pack:start;-ms-flex-pack:start;justify-content:flex-start}.elementor-button-align-end .elementor-field-type-submit{-webkit-box-pack:end;-ms-flex-pack:end;justify-content:flex-end}@media screen and (max-width:1024px){.elementor-tablet-button-align-center .elementor-field-type-submit{-webkit-box-pack:center;-ms-flex-pack:center;justify-content:center}.elementor-tablet-button-align-stretch .elementor-field-type-submit button{-ms-flex-preferred-size:100%;flex-basis:100%}.elementor-tablet-button-align-start .elementor-field-type-submit{-webkit-box-pack:start;-ms-flex-pack:start;justify-content:flex-start}.elementor-tablet-button-align-end .elementor-field-type-submit{-webkit-box-pack:end;-ms-flex-pack:end;justify-content:flex-end}}@media screen and (max-width:767px){.elementor-mobile-button-align-center .elementor-field-type-submit{-webkit-box-pack:center;-ms-flex-pack:center;justify-content:center}.elementor-mobile-button-align-stretch .elementor-field-type-submit button{-ms-flex-preferred-size:100%;flex-basis:100%}.elementor-mobile-button-align-start .elementor-field-type-submit{-webkit-box-pack:start;-ms-flex-pack:start;justify-content:flex-start}.elementor-mobile-button-align-end .elementor-field-type-submit{-webkit-box-pack:end;-ms-flex-pack:end;justify-content:flex-end}}.elementor-error .elementor-field{border-color:#d9534f}.elementor-error .help-inline{color:#d9534f;font-size:.9em}.elementor-message{margin:10px 0;font-size:1em;line-height:1}.elementor-message:before{content:"\f00c";display:inline-block;font-family:fontawesome;font-weight:400;font-style:normal;vertical-align:middle;margin-right:5px}.elementor-message.elementor-message-danger{color:#d9534f}.elementor-message.elementor-message-danger:before{content:"\f00d"}.elementor-message.form-message-success{color:#5cb85c}.elementor-form .elementor-button{padding-top:0;padding-bottom:0;border:0 none}.elementor-form .elementor-button>span{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-pack:center;-ms-flex-pack:center;justify-content:center}.elementor-form .elementor-button .elementor-align-icon-right{-webkit-box-ordinal-group:3;-ms-flex-order:2;order:2}.elementor-form .elementor-button .elementor-align-icon-left{-webkit-box-ordinal-group:1;-ms-flex-order:0;order:0}.elementor-form .elementor-button.elementor-size-xs{min-height:33px}.elementor-form .elementor-button.elementor-size-sm{min-height:40px}.elementor-form .elementor-button.elementor-size-md{min-height:47px}.elementor-form .elementor-button.elementor-size-lg{min-height:59px}.elementor-form .elementor-button.elementor-size-xl{min-height:72px}.elementor-widget-heading .elementor-heading-title{padding:0;margin:0;line-height:1}.elementor-widget-heading .elementor-heading-title>a{color:inherit}.elementor-widget-heading .elementor-heading-title.elementor-size-small{font-size:15px}.elementor-widget-heading .elementor-heading-title.elementor-size-medium{font-size:19px}.elementor-widget-heading .elementor-heading-title.elementor-size-large{font-size:29px}.elementor-widget-heading .elementor-heading-title.elementor-size-xl{font-size:39px}.elementor-widget-heading .elementor-heading-title.elementor-size-xxl{font-size:59px}.elementor-widget-image .elementor-image>a,.elementor-widget-image .elementor-image figure>a{display:block}.elementor-widget-image .elementor-image img{vertical-align:middle}.elementor-widget-image .elementor-image.elementor-image-shape-circle{border-radius:50%}.elementor-image-gallery .gallery-item{display:inline-block;text-align:center;vertical-align:top;width:100%;max-width:100%;margin:0 auto}.elementor-image-gallery .gallery-item img{margin:0 auto}.elementor-image-gallery .gallery-item .gallery-caption{margin:0}@media (min-width:768px){.elementor-image-gallery .gallery-columns-2 .gallery-item{max-width:50%}.elementor-image-gallery .gallery-columns-3 .gallery-item{max-width:33.33%}.elementor-image-gallery .gallery-columns-4 .gallery-item{max-width:25%}.elementor-image-gallery .gallery-columns-5 .gallery-item{max-width:20%}.elementor-image-gallery .gallery-columns-6 .gallery-item{max-width:16.666%}.elementor-image-gallery .gallery-columns-7 .gallery-item{max-width:14.28%}.elementor-image-gallery .gallery-columns-8 .gallery-item{max-width:12.5%}.elementor-image-gallery .gallery-columns-9 .gallery-item{max-width:11.11%}.elementor-image-gallery .gallery-columns-10 .gallery-item{max-width:10%}}@media (min-width:480px) and (max-width:767px){.elementor-image-gallery .gallery.gallery-columns-2 .gallery-item,.elementor-image-gallery .gallery.gallery-columns-3 .gallery-item,.elementor-image-gallery .gallery.gallery-columns-4 .gallery-item,.elementor-image-gallery .gallery.gallery-columns-5 .gallery-item,.elementor-image-gallery .gallery.gallery-columns-6 .gallery-item,.elementor-image-gallery .gallery.gallery-columns-7 .gallery-item,.elementor-image-gallery .gallery.gallery-columns-8 .gallery-item,.elementor-image-gallery .gallery.gallery-columns-9 .gallery-item,.elementor-image-gallery .gallery.gallery-columns-10 .gallery-item{max-width:50%}}@media (max-width:479px){.elementor-image-gallery .gallery.gallery-columns-2 .gallery-item,.elementor-image-gallery .gallery.gallery-columns-3 .gallery-item,.elementor-image-gallery .gallery.gallery-columns-4 .gallery-item,.elementor-image-gallery .gallery.gallery-columns-5 .gallery-item,.elementor-image-gallery .gallery.gallery-columns-6 .gallery-item,.elementor-image-gallery .gallery.gallery-columns-7 .gallery-item,.elementor-image-gallery .gallery.gallery-columns-8 .gallery-item,.elementor-image-gallery .gallery.gallery-columns-9 .gallery-item,.elementor-image-gallery .gallery.gallery-columns-10 .gallery-item{max-width:100%}}.elementor-button{display:inline-block;line-height:1;background-color:#818a91;color:#fff;text-align:center;-webkit-transition:all .5s;transition:all .5s}.elementor-button:focus,.elementor-button:hover,.elementor-button:visited{color:#fff;opacity:.9}.elementor-button.elementor-size-xs{font-size:13px;padding:10px 20px;border-radius:2px}.elementor-button.elementor-size-sm{font-size:15px;padding:12px 24px;border-radius:3px}.elementor-button.elementor-size-md{font-size:16px;padding:15px 30px;border-radius:4px}.elementor-button.elementor-size-lg{font-size:18px;padding:20px 40px;border-radius:5px}.elementor-button.elementor-size-xl{font-size:20px;padding:25px 50px;border-radius:6px}.elementor-button .elementor-align-icon-right{float:right;margin-left:5px}.elementor-button .elementor-align-icon-left{float:left;margin-right:5px}.elementor-button .elementor-button-text{display:inline-block}.elementor-element.elementor-button-info .elementor-button{background-color:#5bc0de}.elementor-element.elementor-button-success .elementor-button{background-color:#5cb85c}.elementor-element.elementor-button-warning .elementor-button{background-color:#f0ad4e}.elementor-element.elementor-button-danger .elementor-button{background-color:#d9534f}.elementor-widget-button .elementor-button .elementor-button-info{background-color:#5bc0de}.elementor-widget-button .elementor-button .elementor-button-success{background-color:#5cb85c}.elementor-widget-button .elementor-button .elementor-button-warning{background-color:#f0ad4e}.elementor-widget-button .elementor-button .elementor-button-danger{background-color:#d9534f}.elementor-widget-divider .elementor-divider{line-height:0;font-size:0}.elementor-widget-divider .elementor-divider-separator{display:inline-block}.elementor-image-gallery figure img{display:block}.elementor-image-gallery figure figcaption{width:100%}.gallery-spacing-custom .elementor-image-gallery .gallery-icon{padding:0}.elementor-counter .elementor-counter-number-wrapper{display:-webkit-box;display:-ms-flexbox;display:flex;font-size:69px;font-weight:600;color:#222;line-height:1}.elementor-counter .elementor-counter-number-prefix{-webkit-box-flex:1;-ms-flex-positive:1;flex-grow:1;text-align:right}.elementor-counter .elementor-counter-number-suffix{-webkit-box-flex:1;-ms-flex-positive:1;flex-grow:1;text-align:left}.elementor-counter .elementor-counter-title{text-align:center;font-size:19px;font-weight:400;color:#666;line-height:2.5}.elementor-alert{padding:15px;border-left:5px solid transparent;position:relative;text-align:left}.elementor-alert .elementor-alert-title{display:block;font-weight:700}.elementor-alert .elementor-alert-description{font-size:13px}.elementor-alert button.elementor-alert-dismiss{position:absolute;right:10px;top:10px;padding:3px;font-size:13px;line-height:1;background:transparent;color:inherit;border:none}.elementor-alert.elementor-alert-info{color:#31708f;background-color:#d9edf7;border-color:#bcdff1}.elementor-alert.elementor-alert-success{color:#3c763d;background-color:#dff0d8;border-color:#cae6be}.elementor-alert.elementor-alert-warning{color:#8a6d3b;background-color:#fcf8e3;border-color:#f9f0c3}.elementor-alert.elementor-alert-danger{color:#a94442;background-color:#f2dede;border-color:#e8c4c4}@media (max-width:767px){.elementor-alert{padding:10px}.elementor-alert button.elementor-alert-dismiss{right:7px;top:7px}}.elementor-widget-progress{text-align:left}.elementor-progress-wrapper{position:relative;background-color:#eee;color:#fff;height:30px;line-height:30px;border-radius:2px}.elementor-progress-bar{display:-webkit-box;display:-ms-flexbox;display:flex;background-color:#818a91;width:0;font-size:11px;border-radius:2px;-webkit-transition:width 1s ease-in-out;transition:width 1s ease-in-out}.elementor-progress-text{-webkit-box-flex:1;-ms-flex-positive:1;flex-grow:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;padding-left:15px}.elementor-progress-percentage{padding-right:15px}.elementor-widget-progress .elementor-progress-wrapper.progress-info .elementor-progress-bar{background-color:#5bc0de}.elementor-widget-progress .elementor-progress-wrapper.progress-success .elementor-progress-bar{background-color:#5cb85c}.elementor-widget-progress .elementor-progress-wrapper.progress-warning .elementor-progress-bar{background-color:#f0ad4e}.elementor-widget-progress .elementor-progress-wrapper.progress-danger .elementor-progress-bar{background-color:#d9534f}.elementor-progress .elementor-title{display:block}@media (max-width:767px){.elementor-progress-wrapper{height:25px;line-height:25px}}.elementor-widget-tabs.elementor-tabs-view-vertical .elementor-tabs-wrapper{width:25%;-ms-flex-negative:0;flex-shrink:0}.elementor-widget-tabs.elementor-tabs-view-vertical .elementor-tab-desktop-title.active{border-right-style:none}.elementor-widget-tabs.elementor-tabs-view-vertical .elementor-tab-desktop-title.active:after,.elementor-widget-tabs.elementor-tabs-view-vertical .elementor-tab-desktop-title.active:before{height:999em;width:0;right:0;border-right-style:solid}.elementor-widget-tabs.elementor-tabs-view-vertical .elementor-tab-desktop-title.active:before{top:0;-webkit-transform:translateY(-100%);transform:translateY(-100%)}.elementor-widget-tabs.elementor-tabs-view-vertical .elementor-tab-desktop-title.active:after{top:100%}.elementor-widget-tabs.elementor-tabs-view-horizontal .elementor-tab-desktop-title{display:table-cell}.elementor-widget-tabs.elementor-tabs-view-horizontal .elementor-tab-desktop-title.active{border-bottom-style:none}.elementor-widget-tabs.elementor-tabs-view-horizontal .elementor-tab-desktop-title.active:after,.elementor-widget-tabs.elementor-tabs-view-horizontal .elementor-tab-desktop-title.active:before{bottom:0;height:0;width:999em;border-bottom-style:solid}.elementor-widget-tabs.elementor-tabs-view-horizontal .elementor-tab-desktop-title.active:before{right:100%}.elementor-widget-tabs.elementor-tabs-view-horizontal .elementor-tab-desktop-title.active:after{left:100%}.elementor-widget-tabs .elementor-tab-content,.elementor-widget-tabs .elementor-tab-title,.elementor-widget-tabs .elementor-tab-title:after,.elementor-widget-tabs .elementor-tab-title:before,.elementor-widget-tabs .elementor-tabs-content-wrapper{border:1px none #d4d4d4}.elementor-widget-tabs .elementor-tabs{text-align:left}.elementor-widget-tabs .elementor-tabs-wrapper{overflow:hidden}.elementor-widget-tabs .elementor-tab-title{cursor:pointer}.elementor-widget-tabs .elementor-tab-desktop-title{position:relative;padding:20px 25px;font-weight:700;line-height:1;border:solid transparent}.elementor-widget-tabs .elementor-tab-desktop-title.active{border-color:#d4d4d4}.elementor-widget-tabs .elementor-tab-desktop-title.active:after,.elementor-widget-tabs .elementor-tab-desktop-title.active:before{display:block;content:"";position:absolute}.elementor-widget-tabs .elementor-tab-mobile-title{padding:10px;cursor:pointer}.elementor-widget-tabs .elementor-tab-content{padding:20px;display:none}@media (max-width:767px){.elementor-tabs .elementor-tab-content,.elementor-tabs .elementor-tab-title{border-style:solid;border-bottom-style:none}.elementor-tabs .elementor-tabs-wrapper{display:none}.elementor-tabs .elementor-tabs-content-wrapper{border-bottom-style:solid}.elementor-tabs .elementor-tab-content{padding:10px}}@media (min-width:768px){.elementor-widget-tabs.elementor-tabs-view-vertical .elementor-tabs{display:-webkit-box;display:-ms-flexbox;display:flex}.elementor-widget-tabs.elementor-tabs-view-vertical .elementor-tabs-content-wrapper{-webkit-box-flex:1;-ms-flex-positive:1;flex-grow:1;border-style:solid;border-left-style:none}.elementor-widget-tabs.elementor-tabs-view-horizontal .elementor-tab-content{border-style:solid;border-top-style:none}.elementor-tabs .elementor-tab-mobile-title{display:none}}.elementor-accordion{text-align:left}.elementor-accordion .elementor-accordion-item{border:1px solid #d4d4d4}.elementor-accordion .elementor-accordion-item+.elementor-accordion-item{border-top:none}.elementor-accordion .elementor-accordion-title{padding:15px 20px;font-weight:700;line-height:1;cursor:pointer}.elementor-accordion .elementor-accordion-title .elementor-accordion-icon{display:inline-block;width:1.5em}.elementor-accordion .elementor-accordion-title .elementor-accordion-icon.elementor-accordion-icon-right{float:right;text-align:right}.elementor-accordion .elementor-accordion-title .elementor-accordion-icon.elementor-accordion-icon-left{float:left;text-align:left}.elementor-accordion .elementor-accordion-title .elementor-accordion-icon .fa:before{content:"\f067"}.elementor-accordion .elementor-accordion-title.active .elementor-accordion-icon .fa:before{content:"\f068"}.elementor-accordion .elementor-accordion-content{display:none;padding:15px 20px;border-top:1px solid #d4d4d4}@media (max-width:767px){.elementor-accordion .elementor-accordion-title{padding:12px 15px}.elementor-accordion .elementor-accordion-title .elementor-accordion-icon{width:1.2em}.elementor-accordion .elementor-accordion-content{padding:7px 15px}}.elementor-toggle{text-align:left}.elementor-toggle .elementor-toggle-title{font-weight:700;line-height:1;padding:15px;border-bottom:1px solid #d4d4d4;cursor:pointer}.elementor-toggle .elementor-toggle-title .elementor-toggle-icon{display:inline-block;width:1em}.elementor-toggle .elementor-toggle-title .elementor-toggle-icon .fa:before{content:""}.elementor-toggle .elementor-toggle-title.active{border-bottom:none}.elementor-toggle .elementor-toggle-title.active .elementor-toggle-icon .fa:before{content:"\f0d7"}.elementor-toggle .elementor-toggle-content{padding:0 15px 15px;border-bottom:1px solid #d4d4d4;display:none}@media (max-width:767px){.elementor-toggle .elementor-toggle-title{padding:12px}.elementor-toggle .elementor-toggle-content{padding:0 12px 10px}}.elementor-icon{display:inline-block;line-height:1;-webkit-transition:all .5s;transition:all .5s;color:#818a91;font-size:50px;text-align:center}.elementor-icon:hover{color:#818a91}.elementor-icon i{width:1em;height:1em}.elementor-view-stacked .elementor-icon{padding:.5em;background-color:#818a91;color:#fff}.elementor-view-framed .elementor-icon{padding:.5em;color:#818a91;border:3px solid #818a91;background-color:transparent}.elementor-shape-circle .elementor-icon{border-radius:50%}.elementor-widget-icon-list .elementor-icon-list-items{list-style-type:none;margin:0;padding:0}.elementor-widget-icon-list .elementor-icon-list-item{margin:0;padding:0}.elementor-widget-icon-list .elementor-icon-list-item a{display:inline}.elementor-widget-icon-list .elementor-icon-list-text{display:inline;vertical-align:middle}.elementor-widget-icon-list .elementor-icon-list-icon{width:1em;line-height:1;vertical-align:middle;display:inline-block;text-align:center}.elementor-widget-video.elementor-aspect-ratio-169 .elementor-video-wrapper{padding-bottom:56.25%}.elementor-widget-video.elementor-aspect-ratio-43 .elementor-video-wrapper{padding-bottom:75%}.elementor-widget-video.elementor-aspect-ratio-32 .elementor-video-wrapper{padding-bottom:66.6666%}.elementor-widget-video .elementor-custom-embed-image-overlay{position:absolute;top:0;left:0;width:100%;height:100%;background-size:cover;background-position:50%;cursor:pointer}.elementor-widget-video .elementor-custom-embed-image-overlay:hover .elementor-custom-embed-play i{opacity:.9;text-shadow:3px 2px 30px rgba(0,0,0,.6)}.elementor-widget-video .elementor-custom-embed-play{position:absolute;top:50%;left:50%;-webkit-transform:translateX(-50%) translateY(-50%);transform:translateX(-50%) translateY(-50%)}.elementor-widget-video .elementor-custom-embed-play i{font-size:100px;color:#fff;opacity:.7;text-shadow:3px 2px 24px rgba(0,0,0,.5);-webkit-transition:all .5s;transition:all .5s}.elementor-image-carousel-wrapper .slick-image-stretch .slick-slide .slick-slide-image,.elementor-widget-image-box .elementor-image-box-content{width:100%}@media (min-width:768px){.elementor-widget-image-box.elementor-position-left .elementor-image-box-wrapper,.elementor-widget-image-box.elementor-position-right .elementor-image-box-wrapper{display:-webkit-box;display:-ms-flexbox;display:flex}.elementor-widget-image-box.elementor-position-right .elementor-image-box-wrapper{text-align:right;-webkit-box-orient:horizontal;-webkit-box-direction:reverse;-ms-flex-direction:row-reverse;flex-direction:row-reverse}.elementor-widget-image-box.elementor-position-left .elementor-image-box-wrapper{text-align:left;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-ms-flex-direction:row;flex-direction:row}.elementor-widget-image-box.elementor-position-top .elementor-image-box-img{margin:auto}.elementor-widget-image-box.elementor-vertical-align-top .elementor-image-box-wrapper{-webkit-box-align:start;-ms-flex-align:start;align-items:flex-start}.elementor-widget-image-box.elementor-vertical-align-middle .elementor-image-box-wrapper{-webkit-box-align:center;-ms-flex-align:center;align-items:center}.elementor-widget-image-box.elementor-vertical-align-bottom .elementor-image-box-wrapper{-webkit-box-align:end;-ms-flex-align:end;align-items:flex-end}}@media (max-width:767px){.elementor-widget-image-box .elementor-image-box-img{margin-left:auto!important;margin-right:auto!important;margin-bottom:15px}}.elementor-widget-image-box .elementor-image-box-img{display:inline-block}.elementor-widget-image-box .elementor-image-box-title a{color:inherit}.elementor-widget-image-box .elementor-image-box-wrapper{text-align:center}.elementor-widget-image-box .elementor-image-box-description{margin:0}@media (min-width:768px){.elementor-widget-icon-box.elementor-position-left .elementor-icon-box-wrapper,.elementor-widget-icon-box.elementor-position-right .elementor-icon-box-wrapper{display:-webkit-box;display:-ms-flexbox;display:flex}.elementor-widget-icon-box.elementor-position-left .elementor-icon-box-icon,.elementor-widget-icon-box.elementor-position-right .elementor-icon-box-icon{-webkit-box-flex:0;-ms-flex:0 0 auto;flex:0 0 auto}.elementor-widget-icon-box.elementor-position-right .elementor-icon-box-wrapper{text-align:right;-webkit-box-orient:horizontal;-webkit-box-direction:reverse;-ms-flex-direction:row-reverse;flex-direction:row-reverse}.elementor-widget-icon-box.elementor-position-left .elementor-icon-box-wrapper{text-align:left;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-ms-flex-direction:row;flex-direction:row}.elementor-widget-icon-box.elementor-position-top .elementor-icon-box-img{margin:auto}.elementor-widget-icon-box.elementor-vertical-align-top .elementor-icon-box-wrapper{-webkit-box-align:start;-ms-flex-align:start;align-items:flex-start}.elementor-widget-icon-box.elementor-vertical-align-middle .elementor-icon-box-wrapper{-webkit-box-align:center;-ms-flex-align:center;align-items:center}.elementor-widget-icon-box.elementor-vertical-align-bottom .elementor-icon-box-wrapper{-webkit-box-align:end;-ms-flex-align:end;align-items:flex-end}}@media (max-width:767px){.elementor-widget-icon-box .elementor-icon-box-icon{margin-left:auto!important;margin-right:auto!important;margin-bottom:15px}}.elementor-widget-icon-box .elementor-icon-box-wrapper{text-align:center}.elementor-widget-icon-box .elementor-icon-box-title a{color:inherit}.elementor-widget-icon-box .elementor-icon-box-description{margin:0}.elementor-testimonial-wrapper{overflow:hidden;text-align:center}.elementor-testimonial-wrapper .elementor-testimonial-content{font-size:1.3em;margin-bottom:20px}.elementor-testimonial-wrapper .elementor-testimonial-name{line-height:1.5}.elementor-testimonial-wrapper .elementor-testimonial-job{font-size:.85em}.elementor-testimonial-wrapper.elementor-testimonial-text-align-left{text-align:left}.elementor-testimonial-wrapper.elementor-testimonial-text-align-right{text-align:right}.elementor-testimonial-wrapper .elementor-testimonial-meta{width:100%;line-height:1}.elementor-testimonial-wrapper .elementor-testimonial-meta-inner{display:inline-block}.elementor-testimonial-wrapper .elementor-testimonial-meta .elementor-testimonial-details,.elementor-testimonial-wrapper .elementor-testimonial-meta .elementor-testimonial-image{display:table-cell;vertical-align:middle}.elementor-testimonial-wrapper .elementor-testimonial-meta .elementor-testimonial-image img{width:60px;height:60px;border-radius:50%}.elementor-testimonial-wrapper .elementor-testimonial-meta.elementor-testimonial-image-position-aside .elementor-testimonial-image{padding-right:15px}.elementor-testimonial-wrapper .elementor-testimonial-meta.elementor-testimonial-image-position-aside .elementor-testimonial-details{text-align:left}.elementor-testimonial-wrapper .elementor-testimonial-meta.elementor-testimonial-image-position-top .elementor-testimonial-details,.elementor-testimonial-wrapper .elementor-testimonial-meta.elementor-testimonial-image-position-top .elementor-testimonial-image{display:block}.elementor-testimonial-wrapper .elementor-testimonial-meta.elementor-testimonial-image-position-top .elementor-testimonial-image{margin-bottom:20px}.elementor-social-icons-wrapper{font-size:0}.elementor-social-icon{color:#fff;font-size:25px;text-align:center;padding:.5em;margin-right:5px;cursor:pointer}.elementor-social-icon:last-child{margin:0}.elementor-social-icon:hover{opacity:.5;color:#fff}.elementor-social-icon-behance{background-color:#1769ff}.elementor-social-icon-bitbucket{background-color:#205081}.elementor-social-icon-codepen{background-color:#000}.elementor-social-icon-delicious{background-color:#39f}.elementor-social-icon-digg{background-color:#005be2}.elementor-social-icon-dribbble{background-color:#ea4c89}.elementor-social-icon-facebook{background-color:#3b5998}.elementor-social-icon-flickr{background-color:#0063dc}.elementor-social-icon-foursquare{background-color:#2d5be3}.elementor-social-icon-github{background-color:#333}.elementor-social-icon-google-plus{background-color:#dd4b39}.elementor-social-icon-instagram{background-color:#262626}.elementor-social-icon-jsfiddle{background-color:#487aa2}.elementor-social-icon-linkedin{background-color:#0077b5}.elementor-social-icon-medium{background-color:#00ab6b}.elementor-social-icon-pinterest{background-color:#bd081c}.elementor-social-icon-product-hunt{background-color:#da552f}.elementor-social-icon-reddit{background-color:#ff4500}.elementor-social-icon-snapchat{background-color:#fffc00}.elementor-social-icon-soundcloud{background-color:#f80}.elementor-social-icon-stack-overflow{background-color:#fe7a15}.elementor-social-icon-tumblr{background-color:#35465c}.elementor-social-icon-twitch{background-color:#6441a5}.elementor-social-icon-twitter{background-color:#55acee}.elementor-social-icon-vimeo{background-color:#1ab7ea}.elementor-social-icon-wordpress{background-color:#21759b}.elementor-social-icon-youtube{background-color:#cd201f}.elementor-shape-rounded .elementor-icon.elementor-social-icon{border-radius:10%}.elementor-shape-circle .elementor-icon.elementor-social-icon{border-radius:50%}body.elementor-page .elementor-widget-menu-anchor{margin-bottom:0}.slick-slider{box-sizing:border-box;-webkit-touch-callout:none;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;-ms-touch-action:pan-y;touch-action:pan-y;-webkit-tap-highlight-color:transparent}.slick-list,.slick-slider{position:relative;display:block}.slick-list{overflow:hidden;margin:0;padding:0}.slick-list:focus{outline:none}.slick-list.dragging{cursor:pointer}.slick-slider .slick-list,.slick-slider .slick-track{-webkit-transform:translateZ(0);transform:translateZ(0)}.slick-track{position:relative;left:0;top:0;display:block}.slick-track:after,.slick-track:before{content:"";display:table}.slick-track:after{clear:both}.slick-loading .slick-track{visibility:hidden}.slick-slide{float:left;height:100%;min-height:1px;display:none}[dir=rtl] .slick-slide{float:right}.slick-slide img{display:block}.slick-slide.slick-loading img{display:none}.slick-slide.dragging img{pointer-events:none}.slick-initialized .slick-slide{display:block}.slick-loading .slick-slide{visibility:hidden}.slick-vertical .slick-slide{display:block;height:auto;border:1px solid transparent}.slick-arrow.slick-hidden{display:none}.elementor-slick-slider .slick-loading .slick-list{background:#fff url(../images/ajax-loader.gif) 50% no-repeat}.elementor-slick-slider .slick-next,.elementor-slick-slider .slick-prev{font-size:0;line-height:0;position:absolute;top:50%;display:block;width:20px;padding:0;-webkit-transform:translateY(-50%);transform:translateY(-50%);cursor:pointer;color:transparent;border:none;outline:none;background:transparent}.elementor-slick-slider .slick-next:focus,.elementor-slick-slider .slick-next:hover,.elementor-slick-slider .slick-prev:focus,.elementor-slick-slider .slick-prev:hover{color:transparent;outline:none;background:transparent}.elementor-slick-slider .slick-next:focus:before,.elementor-slick-slider .slick-next:hover:before,.elementor-slick-slider .slick-prev:focus:before,.elementor-slick-slider .slick-prev:hover:before{opacity:1}.elementor-slick-slider .slick-next.slick-disabled:before,.elementor-slick-slider .slick-prev.slick-disabled:before{opacity:.25}.elementor-slick-slider .slick-next:before,.elementor-slick-slider .slick-prev:before{font-family:FontAwesome;font-size:35px;line-height:1;opacity:.75;color:#fff;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.elementor-slick-slider .slick-prev{left:-25px}[dir=rtl] .elementor-slick-slider .slick-prev{left:auto;right:-25px}.elementor-slick-slider .slick-prev:before{content:"\f104"}[dir=rtl] .elementor-slick-slider .slick-prev:before{content:"\f105"}.elementor-slick-slider .slick-next{right:-25px}[dir=rtl] .elementor-slick-slider .slick-next{left:-25px;right:auto}.elementor-slick-slider .slick-next:before{content:"\f105"}[dir=rtl] .elementor-slick-slider .slick-next:before{content:"\f104"}.elementor-slick-slider .slick-dotted.slick-slider{margin-bottom:30px}.elementor-slick-slider ul.slick-dots{position:absolute;bottom:-25px;display:block;width:100%;padding:0;margin:0;list-style:none;text-align:center;line-height:1}.elementor-slick-slider ul.slick-dots li{position:relative;display:inline-block;width:20px;height:20px;margin:0;padding:0;cursor:pointer}.elementor-slick-slider ul.slick-dots li button{font-size:0;line-height:0;display:block;width:20px;height:20px;padding:5px;cursor:pointer;color:transparent;border:0;outline:none;background:transparent}.elementor-slick-slider ul.slick-dots li button:focus,.elementor-slick-slider ul.slick-dots li button:hover{outline:none}.elementor-slick-slider ul.slick-dots li button:focus:before,.elementor-slick-slider ul.slick-dots li button:hover:before{opacity:1}.elementor-slick-slider ul.slick-dots li button:before{font-family:FontAwesome;font-size:6px;line-height:20px;position:absolute;top:0;left:0;width:20px;height:20px;content:"\f111";text-align:center;opacity:.25;color:#000;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.elementor-slick-slider ul.slick-dots li.slick-active button:before{opacity:.75;color:#000}.elementor-slick-slider .slick-arrows-inside .slick-prev{left:20px}[dir=rtl] .elementor-slick-slider .slick-arrows-inside .slick-prev{left:auto;right:20px}.elementor-slick-slider .slick-arrows-inside .slick-next{right:20px}[dir=rtl] .elementor-slick-slider .slick-arrows-inside .slick-next{left:20px;right:auto}.elementor-slick-slider .slick-dots-inside .slick-dots{bottom:5px}.elementor-slick-slider .slick-dots-inside.slick-dotted.slick-slider{margin-bottom:0}.elementor-slick-slider .slick-slider .slick-next,.elementor-slick-slider .slick-slider .slick-prev{z-index:1}.elementor-slick-slider .slick-slide img{margin:auto}.animated{-webkit-animation-duration:1.25s;animation-duration:1.25s}.animated.animated-slow{-webkit-animation-duration:2s;animation-duration:2s}.animated.animated-fast{-webkit-animation-duration:.75s;animation-duration:.75s}.animated.infinite{-webkit-animation-iteration-count:infinite;animation-iteration-count:infinite}@media (max-width:767px){body:not(.elementor-editor-active) .elementor-hidden-phone{display:none}}@media (min-width:768px) and (max-width:1024px){body:not(.elementor-editor-active) .elementor-hidden-tablet{display:none}}@media (min-width:1025px){body:not(.elementor-editor-active) .elementor-hidden-desktop{display:none}}body:not(.elementor-editor-active) .elementor-hidden{display:none}</style>';
					
					// elementor-animations
					
					//echo '<link href="https://raw.githubusercontent.com/pojome/elementor/master/assets/css/animations.min.css" rel="stylesheet" type="text/css"/>';
				}
				
				if( in_array('animate',$cssLibraries)){
					
					echo '<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css" rel="stylesheet" type="text/css"/>';
				}
			}
			
		echo '</head>';

		echo '<body style="padding:0;margin:0;display:flex !important;width:100%;">';
			
			//include style-sheet
			
			if($layerCss!=''){

				echo '<style id="LiveTplEditorStyleSheet">'.PHP_EOL;	
				
					echo $layerCss .PHP_EOL;
					
				echo '</style>'.PHP_EOL;					
			}
			
			//include layer
			
			echo '<layer class="editable" style="min-width:'.$layerMinWidth.';width:100%;margin:'.$layerMargin.';">';
				
				echo $layerContent;

			echo '</layer>' .PHP_EOL;
			
			if(	is_array($jsLibraries) ){
			
				if( in_array('jquery',$jsLibraries)){
					
					echo '<script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>' .PHP_EOL;
				}
				
				if( in_array('bootstrap-3',$jsLibraries)){
					
					echo '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>' .PHP_EOL;
				
					?>	
					
					<script>
					
					;(function($){
						
						$(document).ready(function(){						
										
							$('.modal').appendTo("body");
							
							$('[data-slide-to]').on('click',function(e){

								e.preventDefault();
							
								if( typeof $(this).attr('data-target') !== typeof undefined ){
									
									var carouselId 	= $(this).attr('data-target');
								}
								else{
									
									var carouselId 	= $(this).attr("href");
								}
								
								var slideTo 	= parseInt( $(this).attr('data-slide-to') );
								
								$(carouselId).carousel(slideTo);
								
								return false;
								
							});
							
							$('[data-slide]').on('click',function(e){

								e.preventDefault();
							
								if( typeof $(this).attr('data-target') !== typeof undefined ){
									
									var carouselId 	= $(this).attr('data-target');
								}
								else{
									
									var carouselId 	= $(this).attr("href");
								}

								var slideTo 	= $(this).attr('data-slide');
								
								$(carouselId).carousel(slideTo);
								
								return false;
								
							});
						});
						
					})(jQuery);	
					
					</script>
					
					<?php
				}
			}

			echo'<script>' .PHP_EOL;
				
				//include layer Output
				
				if($layerOutput!=''){
					
					echo ' var layerOutput = "' . $layerOutput . '";' .PHP_EOL;
				}
				
				//include image proxy
				
				if($layerImgProxy!=''){
				
					echo ' var imgProxy = "' . $layerImgProxy . '";' .PHP_EOL;				
				}
				
				//include page def
				
				if($pageDef!=''){
					
					echo ' var pageDef = ' . $pageDef . ';' .PHP_EOL;
				}
				else{
					
					echo ' var pageDef = {};' .PHP_EOL;
				}
				
				//include  line break setting

				if( !is_array($layerOptions) ){
					
					echo ' var disableReturn 	= true;' .PHP_EOL;
					echo ' var autoWrapText 	= false;' .PHP_EOL;
				}
				else{
					
					if(!in_array('line-break',$layerOptions)){
						
						echo ' var disableReturn = true;' .PHP_EOL;
					}
					else{
						
						echo ' var disableReturn = false;' .PHP_EOL;
					}
					
					if(in_array('wrap-text',$layerOptions)){
						
						echo ' var autoWrapText = true;' .PHP_EOL;
					}
					else{
						
						echo ' var autoWrapText = false;' .PHP_EOL;
					}
				}
				
				//include icon settings
				
				$enableIcons = 'false';
				
				if( is_array($cssLibraries) ){

					if( in_array('fontawesome-4',$cssLibraries)){
						
						$enableIcons = 'true';
					}
				}
				
				echo ' var enableIcons = '.$enableIcons.';' .PHP_EOL;
				
				//include medium editor
				
				//echo file_get_contents( trailingslashit(dirname(dirname( __FILE__ ))) . 'assets/js/medium-editor.custom.js' ).PHP_EOL;
				
			echo'</script>' .PHP_EOL;
			
		echo'</body>' .PHP_EOL;
		
	echo'</html>' .PHP_EOL;