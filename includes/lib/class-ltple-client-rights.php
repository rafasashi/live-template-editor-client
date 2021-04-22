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
			
			//dump( $this->parent->user->has_cap('edit_user-page') );
		}
	}
	
	public function init(){
		
		// set administrator capabilities 
		
		if( $role = get_role('administrator') ){
		
			empty($role->capabilities['edit_user-page']) ? $role->add_cap('edit_user-page') : true;	
			empty($role->capabilities['edit_user-pages']) ? $role->add_cap('edit_user-pages') : true;
			empty($role->capabilities['edit_other_user-pages']) ? $role->add_cap('edit_other_user-pages') : true;				
			
			/*
			empty($role->capabilities['edit_published_user-page']) ? $role->add_cap('edit_published_user-page') : true;
			empty($role->capabilities['publish_user-page']) ? $role->add_cap('publish_user-page') : true;
			empty($role->capabilities['delete_user-page']) ? $role->add_cap('delete_user-page') : true;
			empty($role->capabilities['delete_others_user-page']) ? $role->add_cap('delete_others_user-page') : true;
			empty($role->capabilities['delete_published_user-page']) ? $role->add_cap('delete_published_user-page') : true;
			empty($role->capabilities['delete_private_user-page']) ? $role->add_cap('delete_private_user-page') : true;
			empty($role->capabilities['edit_private_user-page']) ? $role->add_cap('edit_private_user-page') : true;
			empty($role->capabilities['read_private_user-page']  ) ? $role->add_cap('read_private_user-page') : true;
			*/
		}


		// set subscriber capabilities 
		
		if( $role = get_role('subscriber') ){
		
			empty($role->capabilities['edit_user-page']) ? $role->add_cap('edit_user-page') : true;
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