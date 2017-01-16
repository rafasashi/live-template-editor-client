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
	
		/*
		
		// add rights taxonomy custom fields
		
		add_action( 'show_user_profile', array( $this, 'get_user_rights' ) );
		add_action( 'edit_user_profile', array( $this, 'get_user_rights' ) );
		
		// save rights taxonomy custom fields
		
		add_action( 'personal_options_update', array( $this, 'save_custom_user_rights_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_custom_user_rights_fields' ) );
		
		*/
	}
	
	public function get_user_rights( $user ) {
		
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
	
	public function save_custom_user_rights_fields( $user_id ) {
		
		if(isset($_POST[$this->parent->_base . 'user-rights'])){
			
			update_user_meta( $user_id, $this->parent->_base . 'user-rights', json_encode($_POST[$this->parent->_base . 'user-rights']));			
		}
	}
}  