<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Layer extends LTPLE_Client_Object {
	
	public $parent;
	public $id			= -1;
	public $defaultId	= -1;
	public $uri			= '';
	public $key			= ''; // gives the server proxy access to the layer
	public $slug		= '';
	public $type		= '';
	public $outputMode	= '';
	
	/**
	 * Constructor function
	 */
	public function __construct( $parent ) {
		
		$this->parent = $parent;
		
		$this->parent->register_post_type( 'cb-default-layer', __( 'Default Layers', 'live-template-editor-client' ), __( 'Default Layer', 'live-template-editor-client' ), '', array(

			'public' 				=> true,
			'publicly_queryable' 	=> true,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> 'cb-default-layer',
			'show_in_nav_menus' 	=> true,
			'query_var' 			=> true,
			'can_export'			=> true,
			'rewrite' 				=> array('slug'=>'default-layer'),
			'capability_type' 		=> 'post',
			'has_archive' 			=> true,
			'hierarchical' 			=> true,
			'show_in_rest' 			=> true,
			//'supports' 			=> array( 'title', 'editor', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array( 'title', 'editor', 'excerpt', 'thumbnail' ),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));

		$this->parent->register_post_type( 'user-layer', __( 'User Layers', 'live-template-editor-client' ), __( 'User Layer', 'live-template-editor-client' ), '', array(

			'public' 				=> true,
			'publicly_queryable' 	=> true,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> 'user-layer',
			'show_in_nav_menus' 	=> true,
			'query_var' 			=> true,
			'can_export' 			=> true,
			'rewrite' 				=> array('slug'=>'user-layer'),
			'capability_type' 		=> 'post',
			'has_archive' 			=> true,
			'hierarchical' 			=> true,
			'show_in_rest' 			=> true,
			//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array( 'title', 'editor', 'author' ),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));

		$this->parent->register_taxonomy( 'layer-type', __( 'Layer Type', 'live-template-editor-client' ), __( 'Layer Type', 'live-template-editor-client' ),  array('user-plan','cb-default-layer'), array(
			'hierarchical' 			=> false,
			'public' 				=> true,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> true,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> true,
			'update_count_callback' => '',
			'show_in_rest'          => true,
			'rewrite' 				=> true,
			'sort' 					=> '',
		));
		
		$this->parent->register_taxonomy( 'layer-range', __( 'Layer Range', 'live-template-editor-client' ), __( 'Layer Range', 'live-template-editor-client' ), array('user-plan','cb-default-layer'), array(
			'hierarchical' 			=> true,
			'public' 				=> true,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> true,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> true,
			'update_count_callback' => '',
			'show_in_rest'          => true,
			'rewrite' 				=> true,
			'sort' 					=> '',
		));
		
		$this->parent->register_taxonomy( 'account-option', __( 'Account Options', 'live-template-editor-client' ), __( 'Account Option', 'live-template-editor-client' ),  array('user-plan'), array(
			'hierarchical' 			=> false,
			'public' 				=> false,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> true,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> true,
			'update_count_callback' => '',
			'show_in_rest'          => true,
			'rewrite' 				=> true,
			'sort' 					=> '',
		));	

		add_action( 'add_meta_boxes', function(){

			$this->parent->admin->add_meta_box (
				
				'metabox_1',
				__( 'Layer configuration', 'live-template-editor-client' ), 
				array("cb-default-layer"),
				'advanced'
			);
			
			$this->parent->admin->add_meta_box (
				
				'layer-css',
				__( 'Layer CSS', 'live-template-editor-client' ), 
				array("cb-default-layer","user-layer"),
				'advanced'
			);
			
			$this->parent->admin->add_meta_box (
				
				'layer-js',
				__( 'Layer Javascript', 'live-template-editor-client' ), 
				array("cb-default-layer","user-layer"),
				'advanced'
			);
			
			$this->parent->admin->add_meta_box (
				
				'layer-output',
				__( 'Layer Output', 'live-template-editor-client' ), 
				array("cb-default-layer"),
				'side'
			);
			
			$this->parent->admin->add_meta_box (
				
				'layer-visibility',
				__( 'Layer Visibility', 'live-template-editor-client' ), 
				array("cb-default-layer"),
				'side'
			);
			
			$this->parent->admin->add_meta_box (
				
				'layer-form',
				__( 'Layer Form', 'live-template-editor-client' ), 
				array("cb-default-layer"),
				'side'
			);		

			$this->parent->admin->add_meta_box (
				
				'layer-options',
				__( 'Layer Options', 'live-template-editor-client' ), 
				array("cb-default-layer"),
				'side'
			);
			
			$this->parent->admin->add_meta_box (
				
				'css-libraries',
				__( 'CSS Libraries', 'live-template-editor-client' ), 
				array("cb-default-layer"),
				'side'
			);
			
			$this->parent->admin->add_meta_box (
				
				'js-libraries',
				__( 'Javascript Libraries', 'live-template-editor-client' ), 
				array("cb-default-layer"),
				'side'
			);
			
			$this->parent->admin->add_meta_box (
				
				'layer-margin',
				__( 'Layer Margin', 'live-template-editor-client' ), 
				array("cb-default-layer"),
				'side'
			);
		
			$this->parent->admin->add_meta_box (
			
				'tagsdiv-layer-type',
				__( 'Layer Type', 'live-template-editor-client' ), 
				array("cb-default-layer"),
				'side'
			);
			
			$this->parent->admin->add_meta_box ( 
			
				'layer-rangediv',
				__( 'Layer Range', 'live-template-editor-client' ), 
				array("cb-default-layer"),
				'side'
			);
		
			$this->parent->admin->add_meta_box (
			
				'default_layer_id',
				__( 'Default Layer', 'live-template-editor-client' ), 
				array("user-layer"),
				'side'
			);
		});		
		
		add_filter('cb-default-layer_custom_fields', array( $this, 'get_default_layer_fields' ));
		
		add_filter('user-layer_custom_fields', array( $this, 'get_user_layer_fields' ));

		add_filter('init', array( $this, 'init_layer' ));
		
		add_action('wp_loaded', array($this,'get_layer_types'));
	}
	
	public function get_layer_types(){

		$this->layerTypes = $this->get_terms( 'layer-type', array(
				
			'emails'  			=> 'Emails',
			'memes'  			=> 'Memes',
			'pricing-tables'	=> 'Pricing Tables',
			'sandbox'  			=> 'Sandbox',
			'tailored'  		=> 'Tailored',
			'hosted'  			=> 'Hosted',
			
		));
	}
	
	public function init_layer(){

		if( !is_admin() ) {
				
			if(isset($_GET['lk'])){
				
				$this->key = sanitize_text_field($_GET['lk']);
			}			
			
			if(isset($_GET['uri'])){
				
				$this->uri = sanitize_text_field($_GET['uri']);
				
				$args=explode('/',$_GET['uri']);

				if( isset($args[1]) && ( $args[0]=='default-layer' || $args[0]=='user-layer' ) ){

					$this->type = $args[0];
					$this->slug = $args[1];
		
					$layer_type=$this->type;
					if($layer_type == 'default-layer'){
						
						$layer_type = 'cb-' . $layer_type;
					}
		
					$q = get_posts(array(
						'post_type'      => $layer_type,
						'posts_per_page' => 1,
						'post_name__in'  => [ $this->slug ],
						//'fields'         => 'ids' 
					));
					
					//var_dump($q);exit;
					
					if(isset($q[0])){
						
						$this->id = $q[0]->ID;

						if( $this->type == 'user-layer' ){
						
							$this->content 	 = $q[0]->post_content;
							$this->defaultId = intval(get_post_meta( $this->id, 'defaultLayerId', true ));
						}
						else{
							
							$this->defaultId = $this->id;
						}

						// get output mode
						
						$this->outputMode 	= get_post_meta( $this->defaultId, 'layerOutput', true );
						
						// recalled in layer template...
						//$this->margin 		= get_post_meta( $this->defaultId, 'layerMargin', true );
						//$this->options 		= get_post_meta( $this->defaultId, 'layerOptions', true );					
					}
				}
			}
		}
	}
	
	public function get_default_layer_fields(){
		
		$fields=[];
		
		//get post id
		
		$post_id=get_the_ID();
		
		//get layer types
		
		$layer_types=[];
		
		foreach($this->layerTypes as $term){
			
			$layer_types[$term->slug]=$term->name;
		}
		
		//get current layer type
		
		$terms = wp_get_post_terms( $post_id, 'layer-type' );
		
		$default_layer_type='';

		if(isset($terms[0]->slug)){
			
			$default_layer_type=$terms[0]->slug;
		}
		
		$fields[]=array(
			"metabox" =>
				array('name'=>"tagsdiv-layer-type"),
				'id'=>"new-tag-layer-type",
				'name'=>'tax_input[layer-type]',
				'label'=>"",
				'type'=>'select',
				'options'=>$layer_types,
				'selected'=>$default_layer_type,
				'description'=>''
		);
		
		//get current layer range
		
		$terms = wp_get_post_terms( $post_id, 'layer-range' );

		$default_layer_range='';

		if(isset($terms[0]->term_id)){
			
			$default_layer_range=$terms[0]->term_id;
		}

		$fields[]=array(
			"metabox" =>
				array('name'=>"layer-rangediv"),
				'type'		=> 'dropdown_categories',
				'id'		=> 'layer-range',
				'name'		=> 'tax_input[layer-range][]',
				'label'		=> '',
				'taxonomy'	=> 'layer-range',
				'selected'	=> $default_layer_range,
				'description'=>''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"metabox_1"),
				'id'=>"pageDef",
				'label'=>"",
				'type'=>'textarea',
				'placeholder'=>"JSON object",
				'description'=>'
				
					<table class="widefat fixed striped" cellspacing="0">
						<thead>
						
							<tr>
							
								<th>option</th>
								<th>description</th>
								<th>default</th>
								<th>possible values</th>
								
							</tr>
							
						</thead>
						<tbody>
						
							<tr>
							
								<td><strong>name</strong></td>
								<td>ID of the element</td>
								<td>null</td>
								<td>String</td>
								
							</tr>
							<tr>
							
								<td><strong>iconClass</strong></td>
								<td>Class of the icon before the element name</td>
								<td>glyphicon glyphicon-plus</td>
								<td>String</td>
								
							</tr>							
							<tr>
							
								<td><strong>props</strong></td>
								<td>List of editable CSS propertises</td>
								<td>null</td>
								<td>Array</td>
							</tr>
							
							<tr>
								<td><strong>labels</strong></td>
								<td>Labels of the editable CSS propertises</td>
								<td>null</td>
								<td>Array</td>
							</tr>
							
							<tr>
							
								<td><strong>editorsConfig</strong></td>
								<td>Configuration of some editable CSS propertise surch as background-image or image source</td>
								<td>null</td>
								<td>Object{"prop":{"urls":Object}}</td>
								
							</tr>
							
							<tr>
							
								<td><strong>draggable</strong></td>
								<td>Is the element draggable inside the preview</td>
								<td>false</td>
								<td>String</td>
								
							</tr>
							
							<tr>
							
								<td><strong>contenteditable</strong></td>
								<td>Is the element content editable</td>
								<td>true</td>
								<td>String</td>
								
							</tr>
							
						</tbody>
						
					</table>'
		);
		
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"layer-css"),
				'id'=>"layerCss",
				'label'=>"",
				'type'=>'textarea',
				'placeholder'=>"Internal CSS style sheet",
				'description'=>'<i>without '.htmlentities('<style></style>').'</i>'
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"layer-js"),
				'id'=>"layerJs",
				'label'=>"",
				'type'=>'textarea',
				'placeholder'=>"Additional Javascript",
				'description'=>'<i>without '.htmlentities('<script></script>').'</i>'
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"layer-output"),
				'id'		=>"layerOutput",
				'label'		=>"",
				'type'		=>'select',
				'options'	=> array(
				
					'inline-css'	=>'Inline Style',
					'external-css'	=>'Style Sheet',
					'hosted-page'	=>'Hosted Page',
					//'self-hosted'	=>'Self Hosted',
					'canvas'		=>'Canvas'
				),
				'selected'	=>'inline-css',
				'description'=>''
		);
		
		$fields[]=array( 
		
			"metabox" =>
			
				array('name'	=> "layer-visibility"),
				'id'			=> "layerVisibility",
				'label'			=> "",
				'type'			=> 'radio',
				'options'		=> array(
				
					'subscriber'	=> 'Subscriber',
					'registered'	=> 'Registered',
					'anyone'		=> 'Anyone',
				),
				'inline'		=> false,
				'description'	=> ''
		);
		
		$fields[]=array( 
		
			"metabox" =>
			
				array('name'	=> "layer-form"),
				'id'			=> "layerForm",
				'label'			=> "",
				'type'			=> 'radio',
				'options'		=> array(
				
					'none'		=> 'None',
					'importer'	=> 'Importer',
				),
				'inline'		=> false,
				'description'	=> ''
		);
		/*
		$fields[]=array( 
		
			"metabox" =>
			
				array('name'	=> "layer-mode"),
				'id'			=> "layerMode",
				'label'			=> "",
				'type'			=> 'radio',
				'options'		=> array(
				
					'production'	=> 'Production',
					'demo'			=> 'Demo',
				),
				'inline'		=> false,
				'description'	=> ''
		);
		*/
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"layer-options"),
				'id'		=>"layerOptions",
				'label'		=>"",
				'type'		=>'checkbox_multi',
				'options'	=>array(
				
					'line-break'	=> 'Line break (Enter)',
					'wrap-text'		=> 'Auto wrap text',
				
				),
				'checked'	=>array('margin-top'),
				'description'=>''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"css-libraries"),
				'id'		=>"cssLibraries",
				'label'		=>"",
				'type'		=>'checkbox_multi',
				'options'	=>array(
				
					'bootstrap-3' 		=> 'Bootstrap 3',
					'fontawesome-4' 	=> 'Font Awesome 4',
					'elementor-1.2.3' 	=> 'Elementor 1.2.3',
					'animate' 			=> 'Animate',
					'slick' 			=> 'Slick',
				 
				),
				//'checked'		=> array('bootstrap-3'),
				'description'	=> ''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"js-libraries"),
				'id'		=>"jsLibraries",
				'label'		=>"",
				'type'		=>'checkbox_multi',
				'options'	=>array(
				
					'jquery' 		=> 'JQuery',
					'bootstrap-3' 	=> 'Bootstrap 3',
				),
				//'checked'	=>array('jquery'),
				'description'=>''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"layer-margin"),
				'id'		=>"layerMargin",
				'label'		=>"",
				'type'		=>'margin',
				'placeholder'=>'0px',
				'default'	=>'-120px 0px -20px 0px',
				'description'=>''
		);
		
		return $fields;
	}
	
	public function get_user_layer_fields(){
				
		$fields=[];
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"layer-css"),
				'id'=>"layerCss",
				'label'=>"",
				'type'=>'textarea',
				'placeholder'=>"Internal CSS style sheet",
				'description'=>'<i>without '.htmlentities('<style></style>').'</i>'
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"layer-js"),
				'id'=>"layerJs",
				'label'=>"",
				'type'=>'textarea',
				'placeholder'=>"Additional Javascript",
				'description'=>'<i>without '.htmlentities('<script></script>').'</i>'
		);		
		
		$fields[]=array(
			"metabox" =>
			
				array('name'=>"default_layer_id"),
				'id'=>"defaultLayerId",
				'label'=>"Default Layer ID",
				'type'=>'text',
				'placeholder'=>"",
				'description'=>''
		);
		
		return $fields;
	}
	
	public function show_layer(){
		
		$data = [];
		
		if( !empty($_GET['url']) ){
			
			$url = parse_url(urldecode(urldecode($_GET['url'])));
			
			if(!empty($url['host'])){
			
				$domain = get_page_by_title($url['host'], OBJECT, 'user-domain');
			
				if(!empty($domain)){
					
					$urls = get_post_meta($domain->ID,'domainUrls',true);
					
					foreach($urls as $layerId => $domainPath ){
						
						if( $url['path'] == '/'.$domainPath ){
							
							$post = get_post($layerId);
							
							if( !empty($post) ){

								include($this->parent->views . $this->parent->_dev .'/layer.php');
								
								exit;
							}							
						}
					}
				}
			}
		}
	}
	
	public static function sanitize_content($str){
		
		$str = stripslashes($str);
		
		//$str = str_replace(array('&quot;'),array(htmlentities('&quot;')),$str);
		
		$str = str_replace(array('cursor: pointer;','data-element_type="video.default"'),'',$str);
		
		$str = str_replace(array('<body','</body>'),array('<div id="main"','</div>'),$str);
		
		//$str = html_entity_decode(stripslashes($str));
		
		//$str = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $str);
		
		$str = preg_replace( array(
		
				'/<iframe(.*?)<\/iframe>/is',
				'/<title(.*?)<\/title>/is',
				'/<pre(.*?)<\/pre>/is',
				'/<frame(.*?)<\/frame>/is',
				'/<frameset(.*?)<\/frameset>/is',
				'/<object(.*?)<\/object>/is',
				'/<script(.*?)<\/script>/is',
				'/<style(.*?)<\/style>/is',
				'/<embed(.*?)<\/embed>/is',
				'/<applet(.*?)<\/applet>/is',
				'/<meta(.*?)>/is',
				'/<!doctype(.*?)>/is',
				'/<link(.*?)>/is',
				//'/<body(.*?)>/is',
				//'/<\/body>/is',
				//'/<head(.*?)>/is',
				//'/<\/head>/is',
				'/onload="(.*?)"/is',
				'/onunload="(.*?)"/is',
				'/<html(.*?)>/is',
				'/<\/html>/is'
			), 
			'', $str
		);
		
		return $str;
	}
}