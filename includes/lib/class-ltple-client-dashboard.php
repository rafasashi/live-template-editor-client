<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Dashboard {
	
	var $all_boxes;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {

		$this->parent 	= $parent;
		
		// add dashboard style
		
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );	

		add_action( 'init', array( $this, 'init_dashboard' ), 0 );		
	}
	
	public function init_dashboard () {
		
		if( !is_admin() ){
			
			// add dashboard shortcodes
		
			add_shortcode('ltple-client-dashboard', array( $this , 'get_dashboard_shortcode' ) );
			
			add_filter('body_class',array($this,'filter_dashboard_classes'),99999,2);
			add_filter('post_class',array($this,'filter_dashboard_classes'),99999,2);
		}		
	}
	
	public function filter_dashboard_classes($classes,$css_class){
				
		if( $this->parent->urls->current_url_in('dashboard') ){
			
			foreach( $classes as $i => $class ){
				
				if( $class == 'page' ){
					
					unset($classes[$i]);
					break;
				}
			}
		}
		
		return $classes;
	}
	
	public function enqueue_styles () {
		
		/*
		if( strpos($this->parent->urls->current, $this->parent->urls->dashboard ) === 0 ){
			
			wp_register_style( $this->parent->_token . '-dashboard', false, array());
			wp_enqueue_style( $this->parent->_token . '-dashboard' );
		
			wp_add_inline_style( $this->parent->_token . '-dashboard', '
			
				
				
			');
		}
		*/
	}
	
	public function get_all_boxes(){
		
		if( is_null($this->all_boxes) ){
		
			$boxes = array();
			
			if( $articles = $this->get_recent_posts( array(

				'post_type' 	=> array('post','docs'),
				'numberposts' 	=> 10,
						
			))){
				
				$boxes['last_articles'] = array(
				
					'title' 	=> 'Articles & Docs',
					'content' 	=> $articles,
				);
			}
			
			if( $templates = $this->get_new_templates(10) ){
				
				$boxes['new_templates'] = array(
				
					'title' 	=> 'New resources',
					'content' 	=> $templates,
				);
			}
			
			if( $projects = $this->get_saved_projects(20) ){
				
				$boxes['saved_projects'] = array(
				
					'title' 	=> 'Saved Projects',
					'content' 	=> $projects,
				);
			}			
			
			$this->all_boxes = apply_filters('ltple_dashboard_boxes',$boxes);
		}
		
		return $this->all_boxes;
	}
	
	public function get_dashboard_shortcode(){
		
		ob_start();

		include($this->parent->views . '/navbar.php');
		
		if($this->parent->user->loggedin){

			if( !empty($_REQUEST['list']) ){
				
				add_action('ltple_list_sidebar',array($this,'get_sidebar'),10,3);
				
				include( $this->parent->views . '/list.php' );
			}
			else{
				
				add_action('ltple_dashboard_sidebar',array($this,'get_sidebar'),10,3);
				
				include($this->parent->views . '/dashboard.php');
			}
		}
		else{
			
			echo $this->parent->login->get_form();
		}
		
		return ob_get_clean();
	}
	
	public function get_widget_box($box){
		
		$title 		= $box['title'];
		$content 	= $box['content'];
		$class		= !empty($box['class']) ? $box['class'] : 'col-xs-12 col-sm-12 col-md-4';
		
		$widget_box = '';
		
		$widget_box .= '<div class="'.$class.'">';
			
			$widget_box .= '<h4 style="border-bottom:1px solid #eee;padding-bottom:10px;margin-bottom:10px;color:#888;">'.$title.'</h4>';				
			
			$widget_box .= '<div class="panel panel-default" style="padding:10px !important;">';
			
				$widget_box .= '<div class="panel-body" style="padding:0 !important;height:310px !important;overflow-x:hidden !important;overflow-y:auto !important;">';
					
					if(!empty($content)){
					
						$widget_box .= $content;
					}
					
				$widget_box .= '</div>';
				
			$widget_box .= '</div>';
			
		$widget_box .= '</div>';
		
		return $widget_box;
	}
	
	public function get_recent_posts( $args = array()){
		
		$default_args = array(
			
			'post_type' 	=> 'post',
			'numberposts' 	=> 5,
			'post_status' 	=> 'publish',
			'orderby' 		=> 'date',
			'order' 		=> 'DESC',
		);
		
		if( !empty($args) ){
			
			$args = array_merge($default_args,$args);
		}
		
		$recent_posts = '';
		
		if( $posts = get_posts($args)){
		
			foreach( $posts as $post ){
				
				$permalink = get_permalink($post);
				
				$recent_posts .= '<div class="media">';
				
					$recent_posts .= '<div class="media-left">';
						
						$recent_posts .= '<div class="media-object" style="width:50px;">';
							
							$recent_posts .= '<a href="'. $permalink . '/">';
								
								$thumbnail_url = get_the_post_thumbnail($post->ID, array(50,50));
								
								if( empty($thumbnail_url) ){
									
									$thumbnail_url = '<div style="background-image:url('.$this->parent->assets_url . 'images/default_item.png);background-size:cover;background-repeat:no-repeat;background-position:center center;width: 50px;height: 50px;display:block;"></div>';
								}
								
								$recent_posts .= $thumbnail_url;
						
							$recent_posts .='</a>';													
							
						$recent_posts .= '</div>';
						
					$recent_posts .= '</div>';
					
					$recent_posts .= '<div class="media-body">';
				
						$recent_posts .= '<a href="'.$permalink.'">';
						
							$recent_posts .= wp_trim_words($post->post_title,5,'...');
							
							$recent_posts .= '<br>';
							
							$recent_posts .= '<span class="label" style="color:' . $this->parent->settings->mainColor . ';border:1px solid ' . $this->parent->settings->mainColor . ';font-size:10px;">'.ucfirst($post->post_type).'</span>';
						
						$recent_posts .= '</a>';
					
					$recent_posts .= '</div>';
					
				$recent_posts .= '</div>';
			}
		}

		return $recent_posts;
	}
	
	public function get_new_templates( $number = 10 ){
		
		$new_templates = '';
		
		// get included type
		
		$layer_types = get_terms( 'layer-type', array(
		
			'fields' 		=> 'ids', 
			'hide_empty' 	=> false,
			'meta_query' 	=> array(
			
				array(
					'key' 			=> 'visibility',
					'value' 		=> 'anyone',
					'compare' 		=> '=',
				)
			),
		));	

		// get included ranges
		
		$layer_ranges = get_terms( 'layer-range', array(
		
			'fields' 		=> 'ids', 
			'hide_empty' 	=> false
		));

		if( $posts = get_posts(array(
						
			'post_type' 	=> 'cb-default-layer',
			'post_status' 	=> array('publish'),
			'numberposts' 	=> $number,
			'orderby' 		=> 'date',
			'order' 		=> 'DESC',
			'meta_query' 	=> array(
			
				array(
					'key' 			=> 'layerVisibility',
					'value' 		=> 'assigned',
					'compare' 		=> '!=',
				)
			),
			'tax_query' => array(
				array(
					'taxonomy' => 'layer-type',
					'terms'    => $layer_types,
					'operator' => 'IN'
				),
				array(
					'taxonomy' => 'layer-range',
					'terms'    => $layer_ranges,
					'operator' => 'IN'
				)
			)
		))){
		
			foreach( $posts as $post ){
				
				// get edit url
				
				$permalink = get_permalink($post);

				// get image url
				
				$alt_url = $this->parent->layer->get_thumbnail_url($post->ID,'thumbnail');
				
				$image_url = $this->parent->layer->get_preview_image_url($post->ID,'thumbnail',$alt_url);
				
				// get layer type
				
				$layer_type = $this->parent->layer->get_layer_type($post->ID);
				
				// get content
				
				$new_templates .= '<div class="media">';
				
					$new_templates .= '<div class="media-left">';
						
						$new_templates .= '<div class="media-object" style="width:50px;">';
							
							$new_templates .= '<a href="'. $permalink . '">';
						
								$new_templates .= '<img src="'. $image_url . '" style="height:50px;width:50px;" />';
						
							$new_templates .='</a>';													
							
						$new_templates .= '</div>';
						
					$new_templates .= '</div>';
					
					$new_templates .= '<div class="media-body">';
				
						$new_templates .= '<a href="'.$permalink.'">';
						
							$new_templates .= wp_trim_words($post->post_title,5,'...');
							
							$new_templates .= '<br><span class="label" style="color:' . $this->parent->settings->mainColor . ';border:1px solid ' . $this->parent->settings->mainColor . ';font-size:10px;">' . $layer_type->name . '</span>';
						
						$new_templates .= '</a>';
						
					$new_templates .= '</div>';
					
				$new_templates .= '</div>';
			}
		}

		return $new_templates;
	}	
	
	public function get_saved_projects( $number = 10 ){
		
		$saved_projects = '';
		
		if( $posts = get_posts(array(
			
			'post_type' 	=> array_keys($this->parent->layer->get_user_storage_types($this->parent->user->ID)),
			'author' 		=> $this->parent->user->ID,
			'numberposts' 	=> $number,
			'post_status' 	=> array('publish','draft'),
			'orderby' 		=> 'post_modified',
			'order' 		=> 'DESC',
		))){
		
			foreach( $posts as $post ){
				
				// get edit url
				
				$edit_url = $this->parent->urls->get_edit_url($post->ID);
				
				// get default id
				
				$default_id = $this->parent->layer->get_default_id($post->ID);
				
				// get image url
				
				$image_url = get_the_post_thumbnail($post->ID, array(50,50));
				
				if( empty($image_url) ){
					
					$image_url = get_the_post_thumbnail($default_id, array(50,50));
				
					if( empty($image_url) ){
						
						$image_url = '<div style="background-image:url('.$this->parent->assets_url . 'images/default_item.png);background-size:cover;background-repeat:no-repeat;background-position:center center;width: 50px;height: 50px;display:block;"></div>';
					}
				}
				
				// get layer type
				
				$layer_type = $this->parent->layer->get_layer_type($default_id);
				
				// get content
				
				$saved_projects .= '<div class="media">';
				
					$saved_projects .= '<div class="media-left">';
						
						$saved_projects .= '<div class="media-object" style="width:50px;">';
							
							$saved_projects .= '<a href="'. $edit_url . '">';
						
								$saved_projects .= $image_url;
						
							$saved_projects .='</a>';													
							
						$saved_projects .= '</div>';
						
					$saved_projects .= '</div>';
					
					$saved_projects .= '<div class="media-body">';
				
						$saved_projects .= '<a href="'.$edit_url.'">';
						
							$saved_projects .= wp_trim_words($post->post_title,5,'...') . ( $post->post_status == 'draft' ? ' <span class="label" style="background:#d8d6d6;padding:2px 5px;font-size:10px;border-radius:20px;">draft</span>' : '' );
							
							if( !empty($layer_type->name) ){
							
								$saved_projects .= '<br><span class="label" style="color:' . $this->parent->settings->mainColor . ';border: 1px solid ' . $this->parent->settings->mainColor . ';font-size:10px;">' . $layer_type->name . '</span>';
						
							}
						
						$saved_projects .= '</a>';
						
					$saved_projects .= '</div>';
					
				$saved_projects .= '</div>';
			}
		}
		else{
			
			$saved_projects .= '<i style="color:#bbb;">No projects saved yet</i>';
		}

		return $saved_projects;
	}
		
	public function get_sidebar( $sidebar, $currentTab = 'home', $output = '' ){
			
		$storage_count = $this->parent->layer->count_layers_by_storage();
		
		// manage section
		
		$manage_section = '<li'.( $currentTab == 'home' ? ' class="active"' : '' ).'><a href="' . $this->parent->urls->dashboard . '"><span class="glyphicon glyphicon-dashboard"></span> Overview</a></li>';
		
		$manage_section .= '<li><a href="' . $this->parent->urls->profile . $this->parent->user->profile .'"><span class="fa fa-user-cog"></span> Profile Settings</a></li>';

		$manage_section .= '<li><a href="' . $this->parent->urls->media .'user-images/"><span class="fas fa-icons"></span> Media Library</a></li>';
		
		$manage_section = apply_filters('ltple_dashboard_manage_sidebar',$manage_section,$currentTab,$output);
		
		if( !empty($manage_section) ){
			
			$sidebar .= '<li class="gallery_type_title">Manage</li>';
			
			$sidebar .= $manage_section;
		}
		
		// edit section 
		
		$edit_section = '';
		
		if( !empty($storage_count['user-layer']) ){
		
			$edit_section .= '<li'.( ( $currentTab == 'user-layer' ) ? ' class="active"' : '' ).'><a href="' . $this->parent->urls->dashboard . '?list=user-layer"><span class="glyphicon glyphicon-scissors"></span> Templates</a></li>';
		}
		
		if( !empty($storage_count['user-psd']) ){
		
			$edit_section .= '<li'.( ( $currentTab == 'user-psd' ) ? ' class="active"' : '' ).'><a href="' . $this->parent->urls->dashboard . '?list=user-psd"><span class="glyphicon glyphicon-picture"></span> Graphic Designs</a></li>';
		}
		
		$edit_section = apply_filters('ltple_dashboard_design_sidebar',$edit_section,$currentTab,$output);
		
		if( !empty($edit_section) ){
			
			$sidebar .= '<li class="gallery_type_title">Edit</li>';
			
			$sidebar .= $edit_section;
		}
		
		// deploy section
		
		$deploy_section = apply_filters('ltple_dashboard_deploy_sidebar','',$currentTab,$output);
		
		if( !empty($deploy_section) ){

			$sidebar .= '<li class="gallery_type_title">Deploy</li>';
			
			$sidebar .= $deploy_section;
		}
		
		return $sidebar;
	}
	
	/**
	 * Main LTPLE_Client_Dashboard Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Dashboard is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Dashboard instance
	 */
	public static function instance ( $parent ) {
		
		if ( is_null( self::$_instance ) ) {
			
			self::$_instance = new self( $parent );
		}
		
		return self::$_instance;
		
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()
}
