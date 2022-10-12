<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Apps extends LTPLE_Client_Object {
	
	var $parent;
	var $app;
	var $mainApps;
	var $taxonomy;
	var $list = array();
	var $userApps = array();
	
	/**
	 * Constructor function
	 */
	 
	public function __construct ( $parent) {
		
		$this->parent 	= $parent;
		
		$this->taxonomy = 'app-type';
		
		$this->parent->register_post_type( 'user-app', __( 'User Apps', 'live-template-editor-client' ), __( 'User App', 'live-template-editor-client' ), '', array(

			'public' 				=> false,
			'publicly_queryable' 	=> false,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> false,
			'show_in_nav_menus' 	=> false,
			'query_var' 			=> true,
			'can_export'			=> true,
			'rewrite' 				=> false,
			'capability_type' 		=> 'post',
			'has_archive' 			=> false,
			'hierarchical' 			=> false,
			'show_in_rest' 			=> false,
			//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array( 'title', 'author'),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));
		
		$this->parent->register_taxonomy( 'app-type', __( 'Applications', 'live-template-editor-client' ), __( 'Application', 'live-template-editor-client' ),  array('user-image','user-bookmark','user-app'), array(
			
			'hierarchical' 			=> true,
			'public' 				=> false,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> false,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> true,
			'update_count_callback' => '',
			'show_in_rest'          => false,
			'rewrite' 				=> true,
			'sort'					=> '',
		));
		
		add_action( 'add_meta_boxes', function(){
			
			$this->parent->admin->add_meta_box (
				
				'appData',
				__( 'App Data', 'live-template-editor-client' ), 
				array("user-app"),
				'advanced'
			);
			
			$this->parent->admin->add_meta_box (
				
				'appSettings',
				__( 'User Settings', 'live-template-editor-client' ), 
				array("user-app"),
				'advanced'
			);
		});		
		
		add_filter( 'wp_loaded', array( $this, 'init_apps'));
		
		add_filter( 'user-app_custom_fields', array( $this, 'get_fields' ));

		add_filter( 'ltple_dashboard_manage_sidebar', array( $this, 'get_sidebar_content' ),1,3);
	}

	// Add app data custom fields

	public function get_fields( $fields = [] ){
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"appData"),
				'id'			=>	"appData",
				'label'			=>	"",
				'type'			=>	'code_editor',
				'code'			=>	'json',
				'placeholder'	=>	"JSON object",
				'description'	=>	''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"appSettings"),
				'id'			=>	"appSettings",
				'label'			=>	"",
				'type'			=>	'code_editor',
				'code'			=>	'json',
				'placeholder'	=>	"JSON object",
				'description'	=>	''
		);
		
		return $fields;
	}
	
	public function get_sidebar_content($sidebar,$currentTab,$output){

		if( !empty($this->list) ){
		
			$sidebar .= '<li'.( $currentTab == 'user-app' ? ' class="active"' : '' ).'><a href="' . $this->parent->urls->dashboard . '?list=user-app"><span class="fa fa-plug"></span> Applications</a></li>';
		}
		
		return $sidebar;
	}
	
	public function init_apps(){
		
		// get current app

		if(!empty($_REQUEST['app'])){
			
			if( $_REQUEST['app'] != 'autoDetect' ){
			
				$this->app = $_REQUEST['app'];
			}
			elseif( !empty($_REQUEST['id'])){
				
				$terms = wp_get_object_terms($_REQUEST['id'],'app-type');
				
				if( isset($terms[0]->slug) ){
					
					$this->app = $terms[0]->slug;
				}
			}	
		}
		elseif( $app = $this->parent->session->get_user_data('app') ){
			
			if( !empty($this->parent->session->get_user_data('action')) ){
			
				$this->app = $app;
			}
		}
		
		// get all apps
		
		do_action('ltple_list_apps');
		
		if( is_admin() ){
			
			add_filter( 'app-type_row_actions', array($this, 'remove_app_quick_edit'), 10, 2 );				
			
			// add taxonomy custom fields
			
			add_action('app-type_add_form_fields', array( $this, 'get_new_app_fields' ) );
			add_action('app-type_edit_form_fields', array( $this, 'get_app_fields' ) );

			add_filter('manage_edit-app-type_columns', array( $this, 'set_app_columns' ) );
			add_filter('manage_app-type_custom_column', array( $this, 'add_app_column_content' ),10,3);			

			// save taxonomy custom fields
			
			add_action('create_app-type', array( $this, 'save_app_fields' ) );
			add_action('edit_app-type', array( $this, 'save_app_fields' ) );
		}
		else{
			
			add_filter('ltple_list_user-app_new_url', array( $this, 'get_list_new_url' ),10,3);
			
			add_filter('ltple_list_user-app_fields', array( $this, 'get_list_fields' ),10,1);
			
			if( !empty($_GET['deleteApp']) && !empty($_GET['confirmed']) && $_GET['confirmed'] == md5($_GET['deleteApp']) ){
				
				$this->delete_app(intval($_GET['deleteApp']));
			}
		}

		// get custom fields
		
		foreach( $this->list as $app ){
			
			$app->thumbnail = get_option('thumbnail_'.$app->slug);
			$app->types 	= get_option('types_'.$app->slug);
		}
		
		// get main apps
		
		$this->mainApps = get_posts(array(

			'post_type'   	=> 'user-app',
			'post_status' 	=> 'publish',
			'post__in' 		=> array( get_option( $this->parent->_base . 'wpcom_main_account' ), get_option( $this->parent->_base . 'twt_main_account' ) ),
			'numberposts' 	=> -1
		));

		if(!empty($this->app)){
			
			foreach($this->list as $app){
				
				if( $this->app == $app->slug ){
					
					$this->include_app($this->app);
					
					$this->{$this->app}->init_app();

					break;
				}
			}
		}
		elseif( is_admin() && isset($_REQUEST['post']) ){
			
			$terms = wp_get_post_terms( $_REQUEST['post'], $this->taxonomy );
			
			if(isset($terms[0]->slug)){
				
				$this->app = $terms[0]->slug;
				
				$this->include_app($this->app);
				
				if( isset($this->{$this->app}->init_app) ){
					
					$this->{$this->app}->init_app();
				}
			}
		}
		
		//Add Custom API Endpoints
		
		add_action('rest_api_init', function(){
			
			register_rest_route( 'ltple-list/v1', '/user-app/', array(
				
				'methods' 	=> 'GET',
				'callback' 	=> array($this,'get_user_app_rows'),
				'permission_callback' => '__return_true',
			));
		});
	}
	
	public function get_app_type($app_id){
		
		$app_type = null;
		
		$terms = wp_get_post_terms($app_id,$this->taxonomy);
		
		if( isset($terms[0])){
			
			$app_type = $terms[0];
		}
		
		return $app_type;
	}
	
	public function get_list_new_url($url,$currentTab,$output='') {
		
		$url = $this->parent->urls->apps . '?app&tab=accounts&output='.$output;
		
		return $url;
	}
	
	public function get_list($type=''){
		
		$apps = array();
		
		if( !empty($this->list) ){
			
			if( !empty($type) ){
				
				//  filter by type
				
				foreach( $this->list as $app ){
					
					if( in_array($type,$app->types) ){
						
						$apps[] = $app;
					}
				}
			}
			else{
				
				$apps = $this->list;
			}
		}
		
		return $apps;
	}
	
	public function get_list_fields( $fields = array() ) {
		
		$fields = array(
			
			array(

				'field' 	=> 'preview',
				'sortable' 	=> 'false',
				'content' 	=> '',
			),
			array(

				'field' 		=> 'app',
				'sortable' 		=> 'true',
				'content' 		=> 'App',
				'filter-control'=> 'select',
			),
			array(

				'field' 		=> 'user',
				'sortable' 		=> 'true',
				'content' 		=> 'User',
				'filter-control'=> 'input',
			),
			array(

				'field' 		=> 'action',
				'sortable' 		=> 'false',
				'Content' 		=> 'action',
			)				
		);		
		
		return $fields;
	}
	
	public function get_user_app_rows($request) {
		
		$referer = $request->get_header( 'referer' );
		
		$app_rows = [];
		
		if( $apps = $this->getUserApps($this->parent->user->ID) ){
			
			foreach( $apps as $i => $app ){
				
				$thumb_url = !empty($app->app_type->slug) ? get_option('thumbnail_' . $app->app_type->slug) : $this->parent->assets_url . 'images/default_item.png';
				
				$action = '<button data-toggle="dialog" data-target="#quickRemoveApp' . ( $i + 1 ) . '" class="btn btn-sm btn-danger" style="margin:1px;">Delete</button>';

				$action .= '<div style="display:none;text-align:center;" id="quickRemoveApp' . ( $i + 1 ) . '" title="Remove App #' . $app->ID . '">';
					
					$action .=  '<div class="alert alert-danger">Are you sure you want to delete this app?</div>';						

					$action .=  '<a data-toggle="action" data-refresh="self" style="margin:10px;" class="btn btn-xs btn-danger" href="' . add_query_arg( array( 'deleteApp' => $app->ID , 'confirmed' => md5($app->ID)),$referer) . '">Delete permanently</a>';
					
				$action .= '</div>';				

				$row = [];
				$row['preview'] 	= '<div style="background:url(' . $thumb_url . ');background-size:contain;background-repeat:no-repeat;background-position:top center;width:100px;height:65px;display:inline-block;"></div>';
				$row['user'] 		= ucfirst($app->user_name);
				$row['app'] 		= $app->app_name;
				$row['action'] 		= $action;
				
				$app_rows[] = $row;
			}
		}
		
		return $app_rows;
	}
	
	public function include_app( $appSlug ){
		
		if( !isset($this->{$appSlug}) ){
		
			// get api client
			
			$apiClient = preg_replace_callback(
				'/[-_](.)/', 
				function ($matches) {
					
					return '_'.strtoupper($matches[1]);
				},
				$appSlug
			);
			
			// include api client

			$className = 'LTPLE_Integrator_' .  ucfirst( $apiClient );

			if(class_exists($className)){
				
				include( $this->parent->vendor . '/autoload.php' );

				$this->{$appSlug} = new $className($appSlug, $this->parent, $this);
			}
			else{

				echo 'Could not found API Client: "'.$apiClient.'"';
				exit;
			}			
		}
	}
	
	public function parse_url_fields($url,$prefix='_'){
		
		$fields = array();
	
		preg_match_all('#{(.*?)}#', $url, $matches);
		
		if( isset($matches[1]) ){	

			foreach($matches[1] as $match){
					
				$fields[$match] = array(
				
					'type'				=> 'text',
					'id'				=> $prefix.$match,
					'name'				=> $prefix.$match,
					'placeholder'		=> $match,
					'style'				=> 'width:100px;display:inline-block;',
					'description'		=> ''
				);
			}
		}
		
		return $fields;
	}
	
	public function newAppConnected(){
		
		do_action( 'ltple_new_app_connected' );
	}
	
	public function delete_app($app){
		
		if( is_numeric($app) ){
			
			$app = get_post($app);
		}
		
		if( !empty($app->post_author) && intval($app->post_author) == $this->parent->user->ID ){
		
			//--------delete bookmark--------
			
			wp_delete_post( $app->ID, true );
			
			$this->parent->exit_message('App successfully deleted',200);
		}
		
		$this->parent->exit_message('Error deleting app',400);
	}
	
	public function getAppUrl( $appSlug, $action='connect', $tab='image-library' ){
		
		$ref_url = urlencode( $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] );
		
		$url = $this->parent->urls->media . $tab . '/?app='.$appSlug.'&action='.$action.'&ref='.str_replace(urlencode('output=widget'), urlencode('output=default'),$ref_url);
		
		return $url;
	}
	
	public function getUserApps($user_id = NULL, $app_slug=''){
		
		$apps = null;
		
		if(is_numeric($user_id)){
			
			// gety user apps
			
			if( !isset($this->userApps[$user_id]) ){
			
				if( $apps = get_posts(array(
						
					'author'      => $user_id,
					'post_type'   => 'user-app',
					'post_status' => 'publish',
					'numberposts' => -1,
					
				)) ){
					
					foreach( $apps as $app ){
						
						// get app type and user name
						
						if( strpos($app->post_title,' - ') != false ){
							
							list($app->app_slug,$app->user_name) = explode(' - ',$app->post_title);
							
							$app->app_name = ucfirst(str_replace('-',' ',$app->app_slug));
							
							// get app type
							
							$app->app_type = $this->get_app_type($app->ID);
							
							// get app data
							
							$app->app_data = $this->getAppData($app,$app->post_author,false);
							
							// get app parameters
							
							$app->app_parameters = get_option( 'parameters_' . $app->app_slug );
							
							// get app class name
							
							$className = 'App_' . ucwords(str_replace('-','_',$app->app_slug),'_');
							
							if( isset($this->parent->{$className}) ){
								
								$methods = array(
								
									'user_profile' 	=> 'get_user_profile_url',
									'social_icon' 	=> 'get_social_icon_url',
								);
								
								// get app data
								
								foreach( $methods as $key => $method ){

									$app->{$key} = ( method_exists($this->parent->{$className}, $method) ? $this->parent->{$className}->{$method}($app) : '' );
								}
							}
						}
					}
				}
				
				$this->userApps[$user_id] = $apps;
			}
			else{
				
				$apps = $this->userApps[$user_id];
			}
			
			// filter user apps

			if( !empty($apps) && !empty($app_slug) ){
				
				foreach($apps as $i => $app){

					if( $app_slug != $app->app_slug ){

						unset($apps[$i]);
					}
				}
			}
		}
		
		return $apps;
	}
	
	public function getAppData($app, $user_id = NULL, $array = false ){
		
		$app_data = NULL;
		
		if( is_numeric($app) ){
			
			$app = get_post($app);

		}

		if( isset($app->post_author) ){

			if( intval($app->post_author) != intval($user_id) && !in_array_field($app->ID, 'ID', $this->mainApps) ){
				
				echo 'User app access restricted...';
				exit;
			}
			else{

				$app_data = json_decode(get_post_meta( $app->ID, 'appData', true ),$array);
			}
		}

		return $app_data;
	}
		
	public function get_youtube_id($url){
			
		preg_match( "#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $url, $matches );
        return $matches[0];
	}
	
	public function remove_app_quick_edit( $actions, $term ){

		//unset( $actions['edit'] );
		unset( $actions['view'] );
		unset( $actions['trash'] );
		unset( $actions['inline hide-if-no-js'] );
		
		return $actions;
	}	
	
	public function set_app_columns($columns) {

		// Remove description, posts, wpseo columns
		$columns = [];
		
		// Add artist-website, posts columns

		$columns['cb'] 		= '<input type="checkbox" />';
		$columns['name'] 	= 'Name';
		$columns['slug'] 	= 'Slug';
		$columns['types'] 	= 'Types';
		$columns['thumb'] 	= 'Thumb'; // must remain last for mobile view
		
		return $columns;
	}
		
	public function add_app_column_content($content, $column_name, $term_id){
	
		$term= get_term($term_id);
	
		if($column_name == 'thumb') {

			$thumb_url = get_option('thumbnail_' . $term->slug);
			
			if(!empty($thumb_url)){
				
				$content.='<img loading="lazy" style="width: 70px;" src="'.$thumb_url.'" />';
			}
			else{
				
				$content.='<div style="width: 70px;text-align:center;">null</div>';
			}
		}
		elseif($column_name == 'types'){
			
			$types = get_option('types_' . $term->slug);
			
			if(!empty($types)){
				
				$content.='<ul style="margin:0;font-size:11px;">';
				
					foreach($types as $type){
						
						$content.='<li>'.$type.'</li>';
					}
				
				$content.='</ul>';				
			}
		}

		return $content;
	}
	
	public function get_new_app_fields($taxonomy_name){
		
		echo'<div class="form-field">';
			
			echo'<label for="'.$taxonomy_name.'-thumbnail">Thumbnail</label>';

			echo'<div class="input-group">';

				echo'<input type="text" name="'.$taxonomy_name.'-thumbnail" id="'.$taxonomy_name.'-thumbnail" value=""/>';

			echo'</div>';
			
		echo'</div>';
		
		echo'<div class="form-field">';
		
			echo'<label for="'.$taxonomy_name.'-types">Types</label>';
				
			$types = $this->parent->get_app_types();
			
			foreach($types as $type => $app){
				
				echo'<div class="input-group">';
					
					echo'<input type="checkbox" name="'.$taxonomy_name.'-types[]" id="'.$taxonomy_name.'-types" value="'.$type.'"/> '.ucfirst($type);
				
				echo'</div>';				
			}
				
		echo'</div>';
	}	
	
	public function get_app_fields($term){

		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Thumbnail</label>';
			
			echo'</th>';
			
			echo'<td>';
				
				echo'<input type="text" name="' . $term->taxonomy . '-thumbnail" id="' . $term->taxonomy . '-thumbnail" value="'.get_option('thumbnail_'.$term->slug).'"/>';
						
			echo'</td>';
			
		echo'</tr>';

		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Types</label>';
			
			echo'</th>';
			
			echo'<td>';
				
				$types 		= $this->parent->get_app_types();
				$app_types 	= get_option('types_'.$term->slug);
				
				foreach($types as $type => $app){
					
					$checked = ( ( !empty($app_types) && in_array($type,$app_types)) ? ' checked="checked"' : '' );
					
					echo'<div class="input-group">';
					
						echo'<input type="checkbox" name="'.$term->taxonomy.'-types[]" id="'.$term->taxonomy.'-types" value="'.$type.'"'.$checked.'/> '.ucfirst($type);
					
					echo'</div>';				
				}
						
			echo'</td>';
			
		echo'</tr>';	

		if($this->parent->user->is_admin){
			
			echo'<tr class="form-field">';
			
				echo'<th valign="top" scope="row">';
					
					echo'<label for="category-text">Parameters (admin)</label>';
				
				echo'</th>';
				
				echo'<td>';
					
					$this->parent->admin->display_field( array(
					
						'type'				=> 'key_value',
						'id'				=> 'parameters_'.$term->slug,
						'name'				=> 'parameters_'.$term->slug,
						'array' 			=> [],
						'description'		=> ''
						
					), false );
					
				echo'</td>';
				
			echo'</tr>';
		}		
	}
	
	public function save_app_fields($term_id){

		//collect all term related data for this new taxonomy
		
		$term = get_term($term_id);

		//save our custom fields as wp-options
		
		if(isset($_POST[$term->taxonomy . '-thumbnail'])){

			update_option('thumbnail_'.$term->slug, sanitize_text_field($_POST[$term->taxonomy . '-thumbnail'],1),false);			
		}

		if(isset($_POST[$term->taxonomy . '-types'])){

			update_option('types_'.$term->slug, $_POST[$term->taxonomy . '-types'],false);			
		}
		
		if($this->parent->user->is_admin){
		
			if(isset($_POST['parameters_'.$term->slug])){

				update_option('parameters_'.$term->slug, $_POST['parameters_'.$term->slug],false);			
			}
		}
	}
} 