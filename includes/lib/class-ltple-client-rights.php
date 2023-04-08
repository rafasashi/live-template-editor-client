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
		
		add_action( 'init', array( $this, 'init' ));
		
		add_action( 'ltple_user_loaded', array( $this, 'set_user_capabilities' ));
		
		/*
		
		// add rights taxonomy custom fields
		
		add_action( 'show_user_profile', array( $this, 'show_user_rights' ),99,1 );
		add_action( 'edit_user_profile', array( $this, 'show_user_rights' ),99,1 );
		
		// save rights taxonomy custom fields
		
		add_action( 'personal_options_update', array( $this, 'save_user_rights' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_user_rights' ) );
		
		*/
	}
	
	public function set_user_capabilities(){
		
		if( current_user_can('administrator') ){
			
			//dump($this->parent->user);
		}
	}
	
	public function init(){
		
		// set administrator capabilities 
		
		if( $role = get_role('administrator') ){
			
			empty($role->capabilities['edit_user-plan']) ? $role->add_cap('edit_user-plan') : true;	
			empty($role->capabilities['edit_user-plans']) ? $role->add_cap('edit_user-plans') : true;
			empty($role->capabilities['edit_other_user-plans']) ? $role->add_cap('edit_other_user-plans') : true;				
		}
	}
	
	public function show_user_rights( $user ) {
		
		if( current_user_can( 'administrator' ) ){
			
			$user_rights = $this->parent->editedUser->rights;
			
			if(!is_array($user_rights)){
				
				$user_rights = [];
			}
			
			echo '<div class="postbox" style="min-height:45px;">';
				
				echo '<h3 style="margin:10px;width:300px;display: inline-block;">' . __( 'User Rights', 'live-template-editor-client' ) . '</h3>';

				foreach($this->list as $right_slug => $right_name){
					
					echo '<input type="checkbox" name="' . $this->parent->_base . 'user-rights[]" id="user-right-'.$right_slug.'" value="'.$right_slug.'"'.( in_array( $right_slug, $user_rights ) ? ' checked="checked"' : '' ).'>';
					echo '<label for="user-right-'.$right_slug.'">'.$right_name.'</label>';
					echo '<br>';
				}
					
			echo'</div>';
		}
	}
	
	public function save_user_rights( $user_id ) {
		
		if(isset($_POST[$this->parent->_base . 'user-rights'])){
			
			update_user_meta( $user_id, $this->parent->_base . 'user-rights', json_encode($_POST[$this->parent->_base . 'user-rights']));			
		}
	}
}  