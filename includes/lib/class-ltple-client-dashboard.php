<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Dashboard {
	
	var $all_boxes;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {

		$this->parent 	= $parent;
		
		// add dashboard shortcodes
		
		add_shortcode('ltple-client-dashboard', array( $this , 'get_dashboard_shortcode' ) );		
				
	}
	
	public function get_all_boxes(){
		
		if( is_null($this->all_boxes) ){
		
			$boxes = array(
			
				'new_templates' => array(
				
					'title' 	=> 'New Templates',
					'content' 	=> $this->get_recent_posts(array(

						'post_type' 	=> 'cb-default-layer',
						'numberposts' 	=> 10,
						'meta_query' 	=> array(
						
							array(
								'key' 			=> 'layerVisibility',
								'value' 		=> 'assigned',
								'compare' 		=> '!=',
							)
						),
					)),
				),
				'last_articles' => array(
				
					'title' 	=> 'Last Articles',
					'content' 	=> $this->get_recent_posts(array(

						'post_type' 	=> 'post',
						'numberposts' 	=> 10,
						
					)),
				),					
			
			);
			
			$boxes = apply_filters('ltple_dashboard_boxes',$boxes);
			
			$this->all_boxes = $boxes;
		}
		
		return $this->all_boxes;
	}
	
	public function get_dashboard_shortcode(){
		
		include($this->parent->views . '/navbar.php');
		
		if($this->parent->user->loggedin){

			include($this->parent->views . '/dashboard.php');
		}
		else{
			
			echo $this->parent->login->get_form();
		}
	}
	
	public function get_widget_box($content,$title='',$class='col-xs-12 col-sm-6 col-md-4'){
		
		$widget_box = '';
		
		if(!empty($content)){
		
			$widget_box .= '<div class="'.$class.'">';
				
				$widget_box .= '<h4 style="border-bottom:1px solid #eee;padding-bottom:10px;margin-bottom:10px;color:#888;">'.$title.'</h4>';				
				
				$widget_box .= '<div class="panel panel-default" style="padding:10px !important;">';
				
					$widget_box .= '<div class="panel-body" style="padding:0 !important;height:310px !important;overflow-x:hidden !important;overflow-y:auto !important;">';
						
						$widget_box .= $content;
						
					$widget_box .= '</div>';
					
				$widget_box .= '</div>';
				
			$widget_box .= '</div>';
		}
		
		return $widget_box;
	}
	
	public function get_recent_posts( $args = array()){
		
		$default_args = array(
			
			'post_type' 	=> 'post',
			'numberposts' 	=> 5,
			'post_status' 	=> 'publish',
			'orderby' 		=> 'post_date',
			'order' 		=> 'DESC',
		);
		
		if( !empty($args) ){
			
			$args = array_merge($default_args,$args);
		}
		
		$recent_posts = '';
		
		if( $posts = get_posts($args)){
		
			foreach( $posts as $post ){
				
				if( $post->post_type == 'cb-default-layer' ){
					
					$permalink = $this->parent->urls->product . $post->ID . '/';
				}
				else{
				
					$permalink = get_permalink($post);
				}
				
				$recent_posts .= '<div class="media">';
				
					$recent_posts .= '<div class="media-left">';
						
						$recent_posts .= '<div class="media-object" style="width:50px;">';
							
							$recent_posts .= '<a href="'. $permalink . '/">';
						
								$recent_posts .= get_the_post_thumbnail($post->ID, array(150,150));
						
							$recent_posts .='</a>';													
							
						$recent_posts .= '</div>';
						
					$recent_posts .= '</div>';
					
					$recent_posts .= '<div class="media-body">';
				
						$recent_posts .= '<a href="'.$permalink.'">' . $post->post_title . '</a>';
					
					$recent_posts .= '</div>';
					
				$recent_posts .= '</div>';
			}
		}

		return $recent_posts;
	}
	
	public function get_sidebar( $currentTab = 'home', $output = '' ){
			
		$sidebar =  '<div id="sidebar">';
			
			$sidebar .= '<ul class="nav nav-tabs tabs-left">';

				$sidebar .= '<li class="gallery_type_title gallery_head">Dashboard</li>';
				
				$sidebar .= '<li class="gallery_type_title">Manage</li>';
				
				$sidebar .= '<li'.( $currentTab == 'home' ? ' class="active"' : '' ).'><a href="' . $this->parent->urls->dashboard . '">Overview</a></li>';
				
				$sidebar .= '<li><a href="' . $this->parent->urls->profile . $this->parent->user->profile .'">Profile Settings</a></li>';
				
				$sidebar .= '<li><a href="' . $this->parent->urls->media .'user-images/">Media Library</a></li>';
				
				$sidebar = apply_filters('ltple_dashboard_manage_sidebar',$sidebar,$currentTab,$output);
				
				$sidebar .= '<li class="gallery_type_title">Design</li>';
				
				$sidebar .= '<li><a href="' . $this->parent->urls->editor . '?list=user-layer">Templates</a></li>';
				
				$sidebar .= '<li><a href="' . $this->parent->urls->editor . '?list=user-psd">Images</a></li>';
				
				//$sidebar .= '<li><a href="' . $this->parent->urls->editor . '?layer[output]=canvas">Memes & Collages</a></li>';
				
				//$sidebar .= '<li><a href="' . $this->parent->urls->media . '">Media Library</a></li>';
				
				$sidebar = apply_filters('ltple_dashboard_design_sidebar',$sidebar,$currentTab,$output);

					$sidebar .= '<li class="gallery_type_title">Publish</li>';
							
					$sidebar .= '<li'.( ( $currentTab == 'user-page' || $currentTab == 'user-menu' ) ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->editor . '?list=user-page">Hosted Pages</a></li>';
				

				$sidebar = apply_filters('ltple_dashboard_publish_sidebar',$sidebar,$currentTab,$output);

				$sidebar .= '<li class="gallery_type_title">Connect</li>';

				$sidebar = apply_filters('ltple_dashboard_connect_sidebar',$sidebar,$currentTab,$output);
				
				$sidebar = apply_filters('ltple_dashboard_sidebar',$sidebar,$currentTab,$output);
				
			$sidebar .= '</ul>';
			
		$sidebar .= '</div>';
		
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
