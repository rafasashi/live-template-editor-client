<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Integrator {
	
	var $parent;
	var $apps;
	var $term;
	var $app_slug;
	var $parameters;
	var $resourceUrl;
	var $action;
	var $data;
	var $message;
	
	/**
	 * Constructor function
	 */
	public function __construct (  $app_slug, $parent, $apps  ) {

		$this->parent 		= $parent;
		$this->parent->apps = $apps;

		// get app slug
		
		$this->app_slug = $app_slug;
		
		// get app term

		$this->term = $this->get_app_info($this->app_slug);
		
		// get app parameters
		
		$this->parameters = $this->get_app_parameters($this->app_slug);
		
		// get resource url
		
		$this->resourceUrl = $this->get_resource_url($this->parameters);
		
		// init app
		
		do_action('init_app');
	}
	
	public function get_app_info($app_slug){
		
		return get_term_by('slug',$app_slug,'app-type');
	}
	
	public function get_app_parameters($app_slug){
		
		return get_option( 'parameters_' . $app_slug );
	}
	
	public function get_resource_url($parameters){
		
		if( !empty($parameters['key']) ){

			foreach($parameters['key'] as $i => $key){
				
				if( $key == 'resource' ){
					
					// get app resource
					
					return $parameters['value'][$i];	
				}
			}
		}
		
		return false;
	}
	
	public function get_ref_url(){
		
		$ref = $this->parent->urls->dashboard;

		if( $redirect_url = $this->parent->session->get_user_data('ref') ){
		
			$ref = $redirect_url;
		}
		elseif( !empty($_REQUEST['ref']) ){
			
			$ref = $this->parent->request->proto . str_replace(array('https://','http://'),'',urldecode($_REQUEST['ref']));
		}
		elseif( !empty($_REQUEST['redirect_to']) ){
			
			$ref = $this->parent->request->proto . str_replace(array('https://','http://'),'',urldecode($_REQUEST['redirect_to']));
		}
		
		return $ref;
	}

	public function init_app(){

		if( isset($this->parameters['key']) ){
			
			// get current action
			
			if(!empty($_REQUEST['action'])){
				
				$this->action = $_REQUEST['action'];
			}
			elseif( $action = $this->parent->session->get_user_data('action') ){
				
				$this->action = $action;
			}
			
			$methodName = 'app'.ucfirst($this->action);

			if(method_exists($this,$methodName)){
				
				$this->$methodName();
			}
		}
	}
	
	public function appConnect(){
		
		// connect here
	}
} 