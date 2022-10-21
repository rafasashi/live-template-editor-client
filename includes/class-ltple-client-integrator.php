<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Integrator {
	
	var $parent;

	var $term;
	var $app_slug;
	
	var $parameters;
	
	var $resourceUrl;
	var $action;
	var $message;
	
	/**
	 * Constructor function
	 */
	public function __construct (  $app_slug, $parent, $apps  ) {
		
		$this->parent = $parent;
		$this->parent->apps = $apps;

		// get app slug
		
		$this->app_slug = $app_slug;
		
		// get app term

		$this->term = $this->get_app_info($this->app_slug);
		
		// get app parameters
		
		$this->parameters = $this->get_app_parameters($this->app_slug);
		
		// get resource url
		
		$this->resourceUrl = $this->get_resource_url($this->parameters);

		do_action('init_app');
	}
	
	public function __debugInfo(){
		
		$vars = get_object_vars($this);
		
		// hide variables using var_dump
		
		unset($vars['parent'],$vars['parameters']);

		return $vars;
	}
	
	public function get_app_info($app_slug){
		
		return get_term_by('slug',$app_slug,'app-type');
	}
	
	public function get_app_parameters($app_slug){
		
		//TODO move to meta
		
		return get_option( 'parameters_' . $app_slug );
	}
	
	public function get_parameter($key,$parameters=null){
		
		if( is_null($parameters) )
			
			$parameters = $this->parameters;
			
		if( !empty($key) && !empty($parameters['key']) ){

			foreach($parameters['key'] as $i => $k){
				
				if( $k == $key ){
					
					return $parameters['value'][$i];	
				}
			}
		}
		
		return false;
	}
	
	public function get_resource_url($parameters){
		
		return $this->get_parameter('resource',$parameters);
	}
	
	public function get_ref_url(){
		
		$ref = $this->parent->urls->dashboard . '?list=user-app';
		
		/**
		* Note: prioritize session over query refs
		* 		to prevent infinit redirections 
		**/

		if( $redirect_url = $this->parent->session->get_user_data('ref') ){
		
			$ref = $redirect_url;
		}
		elseif( !empty($_REQUEST['redirect_to']) ){
			
			$ref = $this->parent->request->proto . str_replace(array('https://','http://'),'',urldecode($_REQUEST['redirect_to']));
		}		
		elseif( !empty($_REQUEST['ref']) ){
			
			$ref = $this->parent->request->proto . str_replace(array('https://','http://'),'',urldecode($_REQUEST['ref']));
		}
		
		return $ref;
	}
	
	public function reset_session(){
		
		$this->parent->session->update_user_data('app','');
		$this->parent->session->update_user_data('action','');
		$this->parent->session->update_user_data('access_token','');
		
		$ref_url =
		
		$this->parent->session->update_user_data('ref',$this->get_ref_url());		
		
		return true;
	}
	
	public function get_current_action(){
		
		if(!empty($_REQUEST['action'])){
			
			return wp_kses_normalize_entities($_REQUEST['action']);
		}
		elseif( $action = $this->parent->session->get_user_data('action') ){
			
			return $action;
		}

		return false;
	}

	public function init_app(){
		
		if( isset($this->parameters['key']) ){
			
			// init action
			
			if( $action = $this->get_current_action() ){
				
				$this->init_action($action);
			}
		}
	}

	public function init_action($action){
		
		$methodName = 'app'.ucfirst($action);
		
		if(method_exists($this,$methodName)){

			$response = $this->$methodName();
		}
	}
} 