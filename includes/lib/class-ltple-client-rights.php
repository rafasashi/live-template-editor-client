<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Rights {
	
	var $parent;
	var $list;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent 	= $parent;
		
		$this->list = array(
		
			'view-backend'=>'View Backend'
		);
		
		add_action('init', array( $this, 'init' ));
		
		add_action('ltple_user_loaded', array( $this, 'set_user_capabilities' ));
		
		add_filter('user_has_cap', array( $this, 'filter_user_capabilities' ),9999,4);
		
		add_filter('ajax_query_attachments_args', function ($query) {
			
			if( $user_id = get_current_user_id() ){
				
				if( !current_user_can('administrator') ){
				
					$query['author'] = $user_id;
				}
				
				return $query;
			}
			
			return false;
		});
		
	}
	
	public function set_user_capabilities(){
		
		if( current_user_can('administrator') ){
			
			//dump($this->parent->user);
		}
	}
	
	public function filter_user_capabilities( $allcaps, $caps, $args, $user ){
		
		if( !empty($caps[0]) ){
		
			$cap = $caps[0];
			
			if( !empty($args[2]) ){
			
				$post_id = $args[2];
			}
			elseif( $cap == 'upload_files' ){
				
				// TODO check user license
				
				$allcaps[$cap] = true;
			}
		}
		
		return $allcaps;
	}
	
	public function init(){
		
		// set administrator capabilities 
		
		if( $role = get_role('administrator') ){
			
			empty($role->capabilities['edit_user-plan']) ? $role->add_cap('edit_user-plan') : true;	
			empty($role->capabilities['edit_user-plans']) ? $role->add_cap('edit_user-plans') : true;
			empty($role->capabilities['edit_other_user-plans']) ? $role->add_cap('edit_other_user-plans') : true;				
		}
	}
	
	public function save_user_rights( $user_id ) {
		
		if(isset($_POST[$this->parent->_base . 'user-rights'])){
			
			update_user_meta( $user_id, $this->parent->_base . 'user-rights', json_encode($_POST[$this->parent->_base . 'user-rights']));			
		}
	}
}  