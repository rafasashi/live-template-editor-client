<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Extension { 
	
	public $parent;

	/**
	 * Constructor function
	 */ 
	 
	public function __construct( $parent ) {
		
		$this->parent = $parent;
		
		add_filter('ltple_loaded', array( $this, 'init_endpoint' ));
		
		add_filter('query_vars', function( $query_vars ){
			
			if(!in_array('ext',$query_vars)){
				
				$query_vars[] = 'ext';
			}
			
			return $query_vars;
		}, 1);
		
		add_filter( 'template_redirect', array( $this, 'get_endpoint' ),1);	
	
		add_filter('ltple_dashboard_extension', array( $this, 'get_dashboard_extension' ));
	}
	
	public function init_endpoint(){
		
		// add rewrite rules
			
		add_rewrite_rule(
		
			'extension/([^/]+)/?$',
			'index.php?ext=$matches[1]',
			'top'
		);
	}
	
	public function get_endpoint(){
		
		if( $slug = get_query_var('ext') ){
			
			do_action('ltple_before_' . $slug . '_extension');
			
			if( $this->parent->user->loggedin){
				
				do_action('ltple_' . $slug . '_extension');
			}
			else{
				
				echo '<a href="' . add_query_arg( array('output' => 'widget'),wp_login_url($this->parent->urls->current)) . '" target="_self">Login</a>';
			}
			 
			exit;
		}
	}
	
	public function get_dashboard_extension(){
		
		if( $buttons = apply_filters('ltple_extension_buttons',array(
		
			'home' => array(
			
				'name' 	 => 'Home',
				'url'	 =>	$this->parent->urls->home . '/dashboard/',
				'target' => '_blank',
			)
			
		))){
			
			echo '<style>
				
				body {
					
					background: #efefef;
				}
				
				a {
					float: left;
					display: inline-block;
					font-family: monospace;
					text-transform: uppercase;
					height: 100px;
					box-shadow:0 2px 2px 0 rgba(153, 153, 153, 0.14), 0 3px 1px -2px rgba(153, 153, 153, 0.2), 0 1px 5px 0 rgba(153, 153, 153, 0.12);
					width: 100px;
					background: #f5f5f5;
					border: none;
					margin: 3px;
					border-radius: 20px;
					text-align: center;
					line-height: 100px;
					font-size: 15px;
					color: ' . $this->parent->settings->mainColor . ';
					font-weight: bold;
					text-decoration: none;
				}
				
				a:hover {
					
					background: #fefefe;
				}
			
			</style>';
			
			foreach( $buttons as $button ){
				
				echo '<div>';
				
					echo '<a href="'.$button['url'].'" target="'.$button['target'].'">'.$button['name'].'</a>';
				
				echo '</div>';
			}
		}
	}
}
