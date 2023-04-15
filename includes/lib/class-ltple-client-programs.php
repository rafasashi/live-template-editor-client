<?php

	if ( ! defined( 'ABSPATH' ) ) exit;

	class LTPLE_Client_Programs {
		
		var $parent;
		var $list = array();
		
		/**
		 * Constructor function
		 */
		 
		public function __construct ( $parent ) {

			$this->parent 	= $parent;
			
			if( is_admin() ){
			
				add_action( 'show_user_profile', array( $this, 'show_user_programs' ),22,1 );
				add_action( 'edit_user_profile', array( $this, 'show_user_programs' ),22,1 );
				
				// save user programs
					
				add_action( 'personal_options_update', array( $this, 'save_user_programs' ) );
				add_action( 'edit_user_profile_update', array( $this, 'save_user_programs' ) );
			}
		}

		public function show_user_programs( $user ) {
			
			if( current_user_can( 'administrator' ) ){
				
				$user_programs = json_decode( get_user_meta( $user->ID, $this->parent->_base . 'user-programs',true) );

				if( !is_array($user_programs) ){
					
					$user_programs = [];
				}
				
				do_action('ltple_list_programs');

				if( !empty($this->list) ){

					echo '<div style="margin:10px auto;min-height:45px;">';
						
						echo '<h3 style="margin:10px;width:300px;display: inline-block;float: left;">' . __( 'Programs', 'live-template-editor-client' ) . '</h3>';

						echo '<div style="margin:10px 0 10px 0;display: inline-block;">';
						
							foreach($this->list as $slug => $name){
								
								echo '<div style="width:150px;display:inline-block;font-weight:bold;">'.$name.'</div>';
								
								echo '<label class="switch" for="user-program-'.$slug.'">';
									
									echo '<input class="form-control" type="checkbox" name="' . $this->parent->_base . 'user-programs[]" id="user-program-'.$slug.'" value="'.$slug.'"'.( in_array( $slug, $user_programs ) ? ' checked="checked"' : '' ).'>';
									echo '<div class="slider round"></div>';
								
								echo '</label>';

								echo '<br>';
							}				
								
						echo'</div>';
							
					echo'</div>';
					
				}
			}	
		}

		public function save_user_programs( $user_id ) {
			
			if( !empty($_POST[$this->parent->_base . 'user-programs']) ){
				
				update_user_meta( $user_id, $this->parent->_base . 'user-programs', json_encode($_POST[$this->parent->_base . 'user-programs']));
			}
			else{
				
				update_user_meta( $user_id, $this->parent->_base . 'user-programs', '');
			}
		}

		public function has_program( $program, $user_id = 0, $programs = NULL ){
			
			if( is_null($programs) ){
			
				$programs = json_decode( get_user_meta( $user_id, $this->parent->_base . 'user-programs',true) );
			}
			
			return ( !empty($programs) && in_array($program, $programs) );
		}		
		
		/**
		 * Main LTPLE_Client_Programs Instance
		 *
		 * Ensures only one instance of LTPLE_Client_Programs is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @see LTPLE_Client()
		 * @return Main LTPLE_Client_Programs instance
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
	